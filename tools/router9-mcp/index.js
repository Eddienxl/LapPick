#!/usr/bin/env node
/**
 * 9Router MCP Bridge Server (stdio JSON-RPC 2.0)
 * Connects Google Antigravity to local 9Router gateway (http://localhost:20128/v1)
 * Zero external dependencies — runs natively on Node.js 18+/22+.
 */

const readline = require('readline');

const BASE_URL = (process.env.ROUTER9_BASE_URL || 'http://127.0.0.1:20128/v1').replace(/\/+$/, '');
const API_KEY = process.env.ROUTER9_API_KEY || '';
const DEFAULT_MODEL = process.env.ROUTER9_DEFAULT_MODEL || 'smart-free';
const TIMEOUT_MS = parseInt(process.env.ROUTER9_TIMEOUT_MS || '90000', 10);

const VERIFIED_MODELS = [
  'smart-free',
  'groq/openai/gpt-oss-120b',
  'groq/qwen/qwen3.8-27b',
  'groq/openai/gpt-oss-20b',
  'pollinations/openai-fast',
];

async function fetchJson(url, options = {}) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), TIMEOUT_MS);
  try {
    const headers = {
      'Content-Type': 'application/json',
      ...(API_KEY ? { Authorization: `Bearer ${API_KEY}` } : {}),
      ...(options.headers || {}),
    };
    const res = await fetch(url, {
      ...options,
      headers,
      signal: controller.signal,
    });
    const text = await res.text();
    let data;
    const cleaned = text.replace(/\r?\ndata:\s*\[DONE\]\s*$/i, '').trim();
    try {
      data = JSON.parse(cleaned);
    } catch {
      data = { raw: text };
    }
    if (!res.ok) {
      throw new Error(`HTTP ${res.status}: ${typeof data === 'object' ? JSON.stringify(data) : text}`);
    }
    return data;
  } finally {
    clearTimeout(timer);
  }
}

const TOOLS = [
  {
    name: 'router9_list_models',
    description:
      'List all active AI models and fallback combos currently registered in the local 9Router gateway (http://localhost:20128/v1/models).',
    inputSchema: {
      type: 'object',
      properties: {},
      additionalProperties: false,
    },
  },
  {
    name: 'router9_ask_model',
    description:
      'Send a prompt to any model or fallback combo in 9Router via OpenAI-compatible /v1/chat/completions. If model is omitted, uses the first available model/combo in 9Router.',
    inputSchema: {
      type: 'object',
      properties: {
        prompt: {
          type: 'string',
          description: 'The user prompt / task instruction to send to the model.',
        },
        model: {
          type: 'string',
          description:
            'Model ID or Combo name from router9_list_models (e.g., "smart-free", "groq/openai/gpt-oss-120b", "groq/qwen/qwen3.8-27b", "pollinations/openai-fast"). Defaults to "smart-free".',
        },
        system: {
          type: 'string',
          description: 'Optional system instruction.',
        },
        temperature: {
          type: 'number',
          description: 'Sampling temperature (default: 0.2).',
        },
        max_tokens: {
          type: 'integer',
          description: 'Maximum tokens to generate (default: 2048).',
        },
      },
      required: ['prompt'],
    },
  },
  {
    name: 'router9_second_opinion',
    description:
      'Ask an external model on 9Router for a second opinion, code review, MCDM math check, or bilingual (ID/EN) translation.',
    inputSchema: {
      type: 'object',
      properties: {
        task_type: {
          type: 'string',
          enum: ['code_review', 'math_verification', 'translate_id_en', 'general_analysis'],
          description: 'Type of specialized task.',
        },
        content: {
          type: 'string',
          description: 'The code snippet, formula, or text to analyze/translate.',
        },
        model: {
          type: 'string',
          description: 'Optional model or combo name in 9Router.',
        },
      },
      required: ['task_type', 'content'],
    },
  },
];

// Fix minor typo in schema type if any
TOOLS[1].inputSchema.properties.max_tokens.type = 'integer';

async function resolveModel(requestedModel) {
  if (requestedModel && requestedModel.trim()) {
    return requestedModel.trim();
  }
  if (DEFAULT_MODEL && DEFAULT_MODEL.trim()) {
    return DEFAULT_MODEL.trim();
  }
  const modelsData = await fetchJson(`${BASE_URL}/models`, { method: 'GET' });
  const list = Array.isArray(modelsData?.data) ? modelsData.data : [];
  if (list.length === 0) {
    throw new Error(
      'Belum ada model/provider aktif di 9Router. Buka http://localhost:20128/dashboard lalu tambahkan minimal 1 provider gratis (mis. Google AI Studio / Groq / OpenRouter / Pollinations).'
    );
  }
  return list[0].id;
}

async function handleToolCall(name, args = {}) {
  if (name === 'router9_list_models') {
    const data = await fetchJson(`${BASE_URL}/models`, { method: 'GET' });
    const rawModels = Array.isArray(data?.data) ? data.data.map((m) => m.id) : [];
    const models = Array.from(new Set([...VERIFIED_MODELS, ...rawModels]));
    return {
      base_url: BASE_URL,
      default_model: DEFAULT_MODEL,
      verified_working_models: VERIFIED_MODELS,
      total_models: models.length,
      models,
    };
  }

  if (name === 'router9_ask_model') {
    const model = await resolveModel(args.model);
    const messages = [];
    if (args.system) {
      messages.push({ role: 'system', content: String(args.system) });
    }
    messages.push({ role: 'user', content: String(args.prompt || '') });

    const payload = {
      model,
      stream: false,
      messages,
      temperature: typeof args.temperature === 'number' ? args.temperature : 0.2,
      max_tokens: typeof args.max_tokens === 'number' ? args.max_tokens : 2048,
    };

    const res = await fetchJson(`${BASE_URL}/chat/completions`, {
      method: 'POST',
      body: JSON.stringify(payload),
    });

    const reply = res?.choices?.[0]?.message?.content ?? JSON.stringify(res);
    return {
      model_used: res?.model || model,
      reply,
      usage: res?.usage || null,
    };
  }

  if (name === 'router9_second_opinion') {
    const systemMap = {
      code_review:
        'You are a senior software engineer reviewing code for bugs, edge cases, and adherence to clean architecture. Be concise and actionable.',
      math_verification:
        'You are a Decision Support System (MCDM: AHP, TOPSIS, SAW, WP) mathematical auditor. Verify formulas, normalization, and consistency ratio strictly.',
      translate_id_en:
        'You are a bilingual Indonesian-English technical localization specialist for a software UI. Return accurate, natural translations.',
      general_analysis: 'You are a helpful technical consultant. Provide clear, concise analysis.',
    };
    const sys = systemMap[args.task_type] || systemMap.general_analysis;
    return await handleToolCall('router9_ask_model', {
      model: args.model,
      system: sys,
      prompt: args.content,
      temperature: 0.1,
    });
  }

  throw new Error(`Unknown tool: ${name}`);
}

function sendResponse(id, result) {
  const msg = JSON.stringify({ jsonrpc: '2.0', id, result });
  process.stdout.write(msg + '\n');
}

function sendError(id, code, message) {
  const msg = JSON.stringify({
    jsonrpc: '2.0',
    id,
    error: { code, message: String(message) },
  });
  process.stdout.write(msg + '\n');
}

const rl = readline.createInterface({
  input: process.stdin,
  terminal: false,
});

rl.on('line', async (line) => {
  const trimmed = line.trim();
  if (!trimmed) return;
  let req;
  try {
    req = JSON.parse(trimmed);
  } catch (err) {
    return;
  }

  const { id, method, params } = req;

  try {
    if (method === 'initialize') {
      sendResponse(id, {
        protocolVersion: params?.protocolVersion || '2024-11-05',
        capabilities: {
          tools: {},
        },
        serverInfo: {
          name: 'router9-mcp',
          version: '1.0.0',
        },
      });
    } else if (method === 'notifications/initialized') {
      // No response needed for notifications
    } else if (method === 'ping') {
      sendResponse(id, {});
    } else if (method === 'tools/list') {
      sendResponse(id, { tools: TOOLS });
    } else if (method === 'tools/call') {
      const toolName = params?.name;
      const toolArgs = params?.arguments || {};
      try {
        const output = await handleToolCall(toolName, toolArgs);
        sendResponse(id, {
          content: [
            {
              type: 'text',
              text: typeof output === 'string' ? output : JSON.stringify(output, null, 2),
            },
          ],
          isError: false,
        });
      } catch (toolErr) {
        sendResponse(id, {
          content: [
            {
              type: 'text',
              text: `Error calling 9Router (${BASE_URL}): ${toolErr.message}`,
            },
          ],
          isError: true,
        });
      }
    } else if (id !== undefined) {
      sendError(id, -32601, `Method not found: ${method}`);
    }
  } catch (err) {
    if (id !== undefined) {
      sendError(id, -32603, err.message || 'Internal error');
    }
  }
});
