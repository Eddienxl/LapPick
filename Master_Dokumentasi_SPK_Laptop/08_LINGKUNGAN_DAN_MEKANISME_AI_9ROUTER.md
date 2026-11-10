# Lingkungan Pengembangan & Mekanisme Kerja AI (9Router + Antigravity)

**Versi:** 0.3.0 — **FINAL & AKTIF (Protokol Trigger `-9router` + Full Tier Gratis)**

---

## 1. Status Lingkungan Mesin (Diperbarui 10 Nov 2026)

| Alat | Status | Dampak & Catatan |
|---|---|---|
| Node.js | ✔ `v22.14.0`, npm `10.9.2` | Menjalankan 9Router (`v0.5.116`) dan server MCP `router9-mcp` |
| **9Router** | ✔ **Terpasang & Aktif** (`http://127.0.0.1:20128`) | Dashboard: `http://127.0.0.1:20128/dashboard`, OpenAI API: `http://127.0.0.1:20128/v1` |
| **Jembatan MCP (`router9`)** | ✔ **Terpasang & Terdaftar** | `d:\TUGAS\SEMESTER 7\SPK\tools\router9-mcp\index.js` terdaftar di `~/.gemini/config/mcp_config.json` & `.agents/mcp_config.json` |
| Python | ✔ `3.13.7` via `py` (alias `python` = stub Store) | Selalu gunakan `py` atau `py -m ...` |
| Paket Python | ✔ `pandas`, `numpy`, `scipy`, `openpyxl`, `streamlit` (`1.67.0`), `plotly` (`7.1.0`) | Menjalankan `lab-streamlit` di `http://localhost:8501` |
| Git | ✔ | — |
| **PHP / Composer / SQLite** | ✔ **Terpasang & Aktif** (PHP `8.3.36`, Composer `2.11.2`) | Menjalankan `lappick-laravel` dengan database SQLite (`pdo_sqlite` & `pdo_mysql` aktif) |

---

## 2. Keputusan Provider: 100% Gratis Resmi (`smart-free`)

Pengguna memutuskan untuk **tidak menggunakan Blackbox AI** (karena API-nya berbayar) dan **hanya menggunakan provider gratis resmi** yang sudah teruji berjalan (`HTTP 200 OK`) di 9Router:

1. **Groq Official Free Tier (`provider: groq`):**
   - `groq/openai/gpt-oss-120b` (Model utama penalaran, arsitektur, kode & matematika — 120B parameter)
   - `groq/qwen/qwen3.8-27b` (Model kedua untuk analisis, kode & dwibahasa ID–EN)
   - `groq/openai/gpt-oss-20b` (Model cepat & ringan)
2. **Pollinations Public Free (`prefix: pollinations`):**
   - `pollinations/openai-fast` (Cadangan terakhir *zero-signup* tanpa kuota akun)
3. **Combo Auto-Fallback Utama (`smart-free`):**
   - Urutan otomatis di 9Router:
     `groq/openai/gpt-oss-120b` $\rightarrow$ `groq/qwen/qwen3.8-27b` $\rightarrow$ `groq/openai/gpt-oss-20b` $\rightarrow$ `pollinations/openai-fast`
   - Bila model pertama terkena *rate-limit*, 9Router otomatis mengalihkan permintaan ke model berikutnya tanpa gagal.

---

## 3. Protokol Mekanisme Kerja: Kata Kunci Trigger `-9router`

Pengguna menetapkan mekanisme kontrol eksplisit berbasis kata kunci **`-9router`** untuk mengatur kapan Antigravity memanggil model eksternal di 9Router.

### 3.1 Aturan Trigger `-9router`
1. **Tanpa kata `-9router` pada perintah pengguna:**
   - Antigravity mengeksekusi tugas secara langsung menggunakan kapabilitas internalnya (membaca/menulis file, menjalankan terminal, menganalisis kode) agar eksekusi cepat dan ringkas.
2. **Dengan menyisipkan kata `-9router` pada perintah pengguna (contoh: *"buatkan modul X -9router"* atau *"cek perhitungan ini -9router"*):**
   - Antigravity **WAJIB** memanggil tool MCP `router9` (`router9_ask_model` atau `router9_second_opinion` dengan combo default `smart-free`) sebagai **Opini Kedua (*Second Opinion*) / Rekan Diskusi AI Eksternal**.
   - Cakupan penggunaan `-9router` berlaku **secara keseluruhan (universal)** untuk segala jenis tugas yang diminta pengguna, meliputi:
     - Diskusi arsitektur sistem, desain database, dan alur UI/UX.
     - *Code review* dan pencarian *bug* / *edge-case* pada kode Python (Streamlit) maupun PHP (Laravel).
     - Verifikasi rumus matematika MCDM (AHP, TOPSIS, SAW, WP, nilai $\lambda_{\max}$, $CI$, $CR$).
     - Pembuatan & pemeriksaan kamus dwibahasa Indonesia–Inggris (`lang/id` $\leftrightarrow$ `lang/en`).
     - Penyusunan atau peninjauan narasi akademis pada `Laporan_UAS_SPK_LapPick.html`.

### 3.2 Alur Eksekusi Saat `-9router` Dipanggil
```
[Instruksi User mengandung "-9router"]
        │
        ▼
1. Antigravity merumuskan konteks masalah/kode secara padat & jelas
        │
        ▼
2. Antigravity memanggil MCP tool `router9_second_opinion` / `router9_ask_model`
   ke 9Router (http://127.0.0.1:20128/v1 -> combo "smart-free")
        │
        ▼
3. Model di 9Router (mis. openai/gpt-oss-120b / qwen3.8-27b) memberikan
   opini kedua, koreksi, atau draf solusi
        │
        ▼
4. Antigravity membandingkan/mensintesis analisisnya sendiri dengan opini 9Router,
   menampilkan ringkasan opini kedua tersebut ke pengguna, lalu menerapkan solusi
   terbaik ke kode/dokumen proyek.
```

---

## 4. Spesifikasi Teknis Server MCP (`router9-mcp`)

Lokasi file: `d:\TUGAS\SEMESTER 7\SPK\tools\router9-mcp\index.js`

| Nama Tool | Parameter | Fungsi |
|---|---|---|
| `router9_list_models` | `{}` | Mengambil daftar model dan combo aktif dari `http://127.0.0.1:20128/v1/models`. |
| `router9_ask_model` | `prompt`, `model?` (default: `"smart-free"`), `system?`, `temperature?`, `max_tokens?` | Mengirim instruksi/diskusi ke model atau combo di 9Router untuk segala topik. |
| `router9_second_opinion` | `task_type` (`code_review` \| `math_verification` \| `translate_id_en` \| `general_analysis`), `content`, `model?` | Meminta opini kedua terstruktur dari model di 9Router sesuai kategori tugas. |

---

## 5. Aturan Keamanan & Kepatuhan
* Hanya menggunakan **API key gratis resmi** (Groq Free Tier) dan **endpoint publik resmi tanpa login** (Pollinations).
* **Tidak pernah** membajak sesi OAuth langganan konsumen.
* Layanan 9Router diikat secara lokal ke `127.0.0.1:20128`.
* Hanya **Antigravity** yang memiliki hak tulis ke file proyek (`write_file` / `edit_file`); 9Router murni bertindak sebagai pemberi opini kedua & generator teks melalui jembatan MCP sehingga **tidak pernah terjadi konflik edit file**.
