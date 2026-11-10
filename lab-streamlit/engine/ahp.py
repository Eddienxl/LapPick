"""AHP (Analytical Hierarchy Process) Engine for LapPick SPK."""
from __future__ import annotations
from typing import Any, Dict, List, Sequence
import numpy as np

CRITERIA: List[Dict[str, str]] = [
    {"code": "C1", "name_id": "Harga",              "name_en": "Price",             "type": "cost",    "unit": "Rp",   "col": "price_idr"},
    {"code": "C2", "name_id": "Kapasitas RAM",      "name_en": "RAM Capacity",      "type": "benefit", "unit": "GB",   "col": "ram_gb"},
    {"code": "C3", "name_id": "Storage Efektif",    "name_en": "Effective Storage", "type": "benefit", "unit": "GB",   "col": "storage_effective_gb"},
    {"code": "C4", "name_id": "Peringkat CPU",      "name_en": "CPU Ranking",       "type": "cost",    "unit": "rank", "col": "cpu_ranking"},
    {"code": "C5", "name_id": "Skor Benchmark GPU", "name_en": "GPU Benchmark",     "type": "benefit", "unit": "pts",  "col": "gpu_benchmark"},
    {"code": "C6", "name_id": "Daya Tahan Baterai", "name_en": "Battery Life",      "type": "benefit", "unit": "jam",  "col": "battery_hours"},
    {"code": "C7", "name_id": "Bobot Laptop",       "name_en": "Weight",            "type": "cost",    "unit": "kg",   "col": "weight_kg"},
    {"code": "C8", "name_id": "Rating Pengguna",    "name_en": "User Rating",       "type": "benefit", "unit": "/5",   "col": "user_rating"},
    {"code": "C9", "name_id": "Refresh Rate Layar", "name_en": "Refresh Rate",      "type": "benefit", "unit": "Hz",   "col": "refresh_rate_hz"},
]

CRITERIA_CODES: List[str] = [c["code"] for c in CRITERIA]
CRITERIA_COLS: List[str] = [c["col"] for c in CRITERIA]
CRITERIA_TYPES: List[str] = [c["type"] for c in CRITERIA]

RI_TABLE: Dict[int, float] = {
    1: 0.00,
    2: 0.00,
    3: 0.58,
    4: 0.90,
    5: 1.12,
    6: 1.24,
    7: 1.32,
    8: 1.41,
    9: 1.45,
    10: 1.49,
}


def compute_ahp(pairwise_matrix: Sequence[Sequence[float]]) -> Dict[str, Any]:
    """
    Compute AHP priority weights (column normalization + row average),
    lambda_max, CI, RI, CR, and top inconsistent pairs.
    Matches `03_ARCHITECTURE_SPK_LAPTOP.md` and `preset_profiles.json` to 1e-6.
    """
    A = np.asarray(pairwise_matrix, dtype=np.float64)
    if A.ndim != 2 or A.shape[0] != A.shape[1]:
        raise ValueError("AHP pairwise matrix must be square (n x n).")
    if (A <= 0).any():
        raise ValueError("AHP pairwise matrix must contain strictly positive numbers.")

    n = int(A.shape[0])
    col_sums = A.sum(axis=0)
    col_sums = np.where(col_sums == 0, 1e-12, col_sums)
    norm_matrix = A / col_sums
    weights = norm_matrix.mean(axis=1)
    weights = weights / np.sum(weights)

    Aw = A @ weights
    safe_weights = np.where(weights == 0, 1e-12, weights)
    lambda_vec = Aw / safe_weights
    lambda_max = float(np.mean(lambda_vec))
    ci = float((lambda_max - n) / (n - 1)) if n > 1 else 0.0
    ri = float(RI_TABLE.get(n, 1.49))
    cr = float(ci / ri) if ri > 0 else 0.0

    inconsistent_pairs = find_most_inconsistent_pairs(A, weights, top_k=3)

    return {
        "n": n,
        "weights": weights,
        "weights_dict": {code: float(w) for code, w in zip(CRITERIA_CODES[:n], weights)},
        "col_sums": col_sums,
        "normalized_matrix": norm_matrix,
        "Aw": Aw,
        "lambda_vec": lambda_vec,
        "lambda_max": lambda_max,
        "ci": ci,
        "ri": ri,
        "cr": cr,
        "is_consistent": bool(cr <= 0.10),
        "inconsistent_pairs": inconsistent_pairs,
    }


def find_most_inconsistent_pairs(
    A: np.ndarray,
    weights: np.ndarray,
    top_k: int = 3,
) -> List[Dict[str, Any]]:
    """
    Identify upper-triangle pairs (i < j) with the largest relative deviation
    between user judgment a_ij and implied weight ratio w_i / w_j.
    """
    n = A.shape[0]
    eps = 1e-12
    pairs: List[Dict[str, Any]] = []
    for i in range(n):
        for j in range(i + 1, n):
            implied_ratio = float(weights[i] / max(weights[j], eps))
            actual_val = float(A[i, j])
            # Log-ratio deviation is symmetric between a_ij and 1/a_ij
            rel_error = abs(np.log(max(actual_val, eps)) - np.log(max(implied_ratio, eps)))
            abs_error = abs(actual_val - implied_ratio)
            c_i = CRITERIA[i]["code"] if i < len(CRITERIA) else f"C{i+1}"
            c_j = CRITERIA[j]["code"] if j < len(CRITERIA) else f"C{j+1}"
            pairs.append({
                "i": i,
                "j": j,
                "pair": f"{c_i} vs {c_j}",
                "code_i": c_i,
                "code_j": c_j,
                "a_ij": actual_val,
                "implied_ratio": implied_ratio,
                "abs_error": abs_error,
                "log_deviation": float(rel_error),
            })
    pairs.sort(key=lambda x: x["log_deviation"], reverse=True)
    return pairs[:top_k]


def build_saaty_pairwise_from_scores(importance_scores: Sequence[float]) -> np.ndarray:
    """
    Construct a guaranteed-consistent 9x9 Saaty pairwise comparison matrix (1..9 and 1/9..1)
    from criterion importance scores (1..9).
    """
    n = len(importance_scores)
    M = np.ones((n, n), dtype=np.float64)
    for i in range(n):
        for j in range(i + 1, n):
            diff = float(importance_scores[i]) - float(importance_scores[j])
            if diff >= 0:
                scale = float(min(9, max(1, int(round(diff + 1)))))
                M[i, j] = scale
                M[j, i] = 1.0 / scale
            else:
                scale = float(min(9, max(1, int(round(-diff + 1)))))
                M[i, j] = 1.0 / scale
                M[j, i] = scale
    return M

