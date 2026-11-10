"""TOPSIS (Technique for Order of Preference by Similarity to Ideal Solution) Engine for LapPick SPK."""
from __future__ import annotations
from typing import Any, Dict, List, Sequence
import numpy as np
import pandas as pd
from .ahp import CRITERIA


def compute_topsis(
    df_items: pd.DataFrame,
    weights_vec: Sequence[float],
    criteria: List[Dict[str, str]] = CRITERIA,
) -> Dict[str, Any]:
    """
    Execute TOPSIS ranking strictly following `03_ARCHITECTURE_SPK_LAPTOP.md` and `04_AGENTS_SPK_LAPTOP.md`:
    1. Decision Matrix X (m x n)
    2. Vector Normalization: r_ij = x_ij / sqrt(sum(x_ij^2))
    3. Weighted Normalized Matrix: y_ij = w_j * r_ij
    4. Positive Ideal (A+) and Negative Ideal (A-) solutions based on benefit/cost type
    5. Euclidean Distances D_i^+ and D_i^-
    6. Preference Score C_i = D_i^- / (D_i^+ + D_i^-)
    7. Deterministic sorting: score DESC, id ASC
    """
    if df_items is None or len(df_items) < 2:
        raise ValueError("Minimal 2 alternatif laptop diperlukan untuk menjalankan perankingan TOPSIS.")

    cols = [c["col"] for c in criteria]
    types = [c["type"] for c in criteria]
    codes = [c["code"] for c in criteria]

    X = df_items[cols].to_numpy(dtype=np.float64)
    w = np.asarray(weights_vec, dtype=np.float64)
    w_sum = float(np.sum(w))
    if w_sum <= 0:
        raise ValueError("Jumlah bobot kriteria harus lebih besar dari 0.")
    w = w / w_sum

    # 1. Vector Normalization
    denom = np.sqrt((X ** 2).sum(axis=0))
    denom_safe = np.where(denom == 0, 1e-12, denom)
    R = X / denom_safe

    # 2. Weighted Normalized Matrix
    Y = R * w

    # 3. Ideal Positive (A+) & Ideal Negative (A-)
    n_crit = len(criteria)
    ideal_pos = np.zeros(n_crit, dtype=np.float64)
    ideal_neg = np.zeros(n_crit, dtype=np.float64)
    for j, t in enumerate(types):
        if t == "benefit":
            ideal_pos[j] = np.max(Y[:, j])
            ideal_neg[j] = np.min(Y[:, j])
        else:
            ideal_pos[j] = np.min(Y[:, j])
            ideal_neg[j] = np.max(Y[:, j])

    # 4. Euclidean Distances D+ and D-
    d_plus = np.sqrt(((Y - ideal_pos) ** 2).sum(axis=1))
    d_minus = np.sqrt(((Y - ideal_neg) ** 2).sum(axis=1))

    # 5. Closeness Coefficient C_i (guard against D+ + D- == 0)
    dist_sum = d_plus + d_minus
    scores = np.where(dist_sum == 0, 0.0, d_minus / np.where(dist_sum == 0, 1e-12, dist_sum))
    scores = np.nan_to_num(scores, nan=0.0, posinf=1.0, neginf=0.0)

    # Build output DataFrame preserving all helpful columns
    keep_cols = [
        c for c in [
            "id", "name", "brand", "type_name", "os", "processor_brand",
            "cpu_name", "gpu_name", "ram_type", "has_ssd", "screen_inches",
            "screen_resolution", "source_link", "price_inr", "price_idr",
            "ram_gb", "storage_gb", "storage_effective_gb", "cpu_ranking",
            "gpu_benchmark", "battery_hours", "weight_kg", "user_rating",
            "refresh_rate_hz", "is_imputed",
        ]
        if c in df_items.columns
    ]
    out = df_items[keep_cols].copy()
    out["d_plus"] = d_plus
    out["d_minus"] = d_minus
    out["topsis_score"] = scores
    out["match_pct"] = np.round(scores * 100.0, 2)

    # Tie-breaker contract: score DESC, id ASC
    out = out.sort_values(by=["topsis_score", "id"], ascending=[False, True]).reset_index(drop=True)
    out["rank"] = np.arange(1, len(out) + 1)

    # Also build indexed DataFrames for step-by-step matrix transparency
    item_labels = [f"#{int(row['id'])} {str(row['name'])[:42]}..." for _, row in df_items.iterrows()]
    df_X = pd.DataFrame(X, index=item_labels, columns=codes)
    df_R = pd.DataFrame(R, index=item_labels, columns=codes)
    df_Y = pd.DataFrame(Y, index=item_labels, columns=codes)

    return {
        "ranking_df": out,
        "weights_normalized": w,
        "denom": denom,
        "X": X,
        "R": R,
        "Y": Y,
        "df_X": df_X,
        "df_R": df_R,
        "df_Y": df_Y,
        "ideal_pos": ideal_pos,
        "ideal_neg": ideal_neg,
        "d_plus": d_plus,
        "d_minus": d_minus,
        "scores": scores,
    }

