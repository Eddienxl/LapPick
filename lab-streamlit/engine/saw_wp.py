"""SAW (Simple Additive Weighting), WP (Weighted Product), Spearman Comparison & Sensitivity Analysis Engine."""
from __future__ import annotations
from typing import Any, Dict, List, Sequence
import numpy as np
import pandas as pd
from scipy.stats import spearmanr
from .ahp import CRITERIA
from .topsis import compute_topsis


def compute_saw(
    df_items: pd.DataFrame,
    weights_vec: Sequence[float],
    criteria: List[Dict[str, str]] = CRITERIA,
) -> np.ndarray:
    """
    Compute Simple Additive Weighting (SAW) scores:
    - Benefit: r_ij = x_ij / max(x_j)
    - Cost:    r_ij = min(x_j) / x_ij
    - Score:   S_i  = sum(w_j * r_ij)
    """
    cols = [c["col"] for c in criteria]
    types = [c["type"] for c in criteria]
    X = df_items[cols].to_numpy(dtype=np.float64)
    w = np.asarray(weights_vec, dtype=np.float64)
    w = w / np.sum(w)

    R_saw = np.zeros_like(X, dtype=np.float64)
    for j, t in enumerate(types):
        col = X[:, j]
        if t == "benefit":
            c_max = float(np.max(col))
            R_saw[:, j] = col / max(c_max, 1e-12)
        else:
            c_min = float(np.min(col))
            R_saw[:, j] = max(c_min, 1e-12) / np.where(col <= 0, 1e-12, col)

    scores = R_saw @ w
    return scores


def compute_wp(
    df_items: pd.DataFrame,
    weights_vec: Sequence[float],
    criteria: List[Dict[Dict[str, str], Any]] = CRITERIA,
) -> np.ndarray:
    """
    Compute Weighted Product (WP) relative preference scores V_i in log-space for numerical stability:
    - log(S_i) = sum( (+w_j for benefit else -w_j) * log(x_ij) )
    - V_i = S_i / sum(S_i)
    """
    cols = [c["col"] for c in criteria]
    types = [c["type"] for c in criteria]
    X = df_items[cols].to_numpy(dtype=np.float64)
    w = np.asarray(weights_vec, dtype=np.float64)
    w = w / np.sum(w)

    signed_w = np.array([wj if t == "benefit" else -wj for wj, t in zip(w, types)], dtype=np.float64)
    log_S = (np.log(np.maximum(X, 1e-12)) * signed_w).sum(axis=1)
    S = np.exp(log_S - np.max(log_S))
    s_sum = float(np.sum(S))
    V = S / max(s_sum, 1e-12)
    return V


def compare_methods(
    df_items: pd.DataFrame,
    weights_vec: Sequence[float],
    criteria: List[Dict[str, str]] = CRITERIA,
) -> Dict[str, Any]:
    """
    Run TOPSIS, SAW, and WP on the same dataset and weights, returning:
    - Combined DataFrame with scores & ranks for all 3 methods
    - Spearman rank correlation coefficients (TOPSIS vs SAW, TOPSIS vs WP, SAW vs WP)
    - Top-5 and Top-10 overlap counts
    """
    topsis_res = compute_topsis(df_items, weights_vec, criteria=criteria)
    saw_scores = compute_saw(df_items, weights_vec, criteria=criteria)
    wp_scores = compute_wp(df_items, weights_vec, criteria=criteria)

    comp = df_items[["id", "name", "brand", "type_name", "price_idr", "ram_gb", "storage_gb", "refresh_rate_hz"]].copy()
    comp["topsis_score"] = topsis_res["scores"]
    comp["saw_score"] = saw_scores
    comp["wp_score"] = wp_scores

    # Assign deterministic ranks (score DESC, id ASC)
    comp = comp.sort_values(by=["topsis_score", "id"], ascending=[False, True]).reset_index(drop=True)
    comp["rank_topsis"] = np.arange(1, len(comp) + 1)

    comp = comp.sort_values(by=["saw_score", "id"], ascending=[False, True]).reset_index(drop=True)
    comp["rank_saw"] = np.arange(1, len(comp) + 1)

    comp = comp.sort_values(by=["wp_score", "id"], ascending=[False, True]).reset_index(drop=True)
    comp["rank_wp"] = np.arange(1, len(comp) + 1)

    # Restore TOPSIS order as primary view
    comp = comp.sort_values(by=["rank_topsis", "id"], ascending=[True, True]).reset_index(drop=True)

    rho_ts_saw, _ = spearmanr(comp["rank_topsis"], comp["rank_saw"])
    rho_ts_wp, _ = spearmanr(comp["rank_topsis"], comp["rank_wp"])
    rho_saw_wp, _ = spearmanr(comp["rank_saw"], comp["rank_wp"])

    top5_ts = set(comp.nsmallest(5, "rank_topsis")["id"])
    top5_saw = set(comp.nsmallest(5, "rank_saw")["id"])
    top5_wp = set(comp.nsmallest(5, "rank_wp")["id"])

    top10_ts = set(comp.nsmallest(10, "rank_topsis")["id"])
    top10_saw = set(comp.nsmallest(10, "rank_saw")["id"])
    top10_wp = set(comp.nsmallest(10, "rank_wp")["id"])

    return {
        "comparison_df": comp,
        "spearman_topsis_saw": float(np.nan_to_num(rho_ts_saw, nan=1.0)),
        "spearman_topsis_wp": float(np.nan_to_num(rho_ts_wp, nan=1.0)),
        "spearman_saw_wp": float(np.nan_to_num(rho_saw_wp, nan=1.0)),
        "overlap_top5_saw": len(top5_ts & top5_saw),
        "overlap_top5_wp": len(top5_ts & top5_wp),
        "overlap_top10_saw": len(top10_ts & top10_saw),
        "overlap_top10_wp": len(top10_ts & top10_wp),
    }


def run_sensitivity_analysis(
    df_items: pd.DataFrame,
    base_weights: Sequence[float],
    criteria: List[Dict[str, str]] = CRITERIA,
    deltas: Sequence[float] = (-0.20, -0.10, 0.10, 0.20),
) -> pd.DataFrame:
    """
    Perturb each criterion weight C1..C9 by deltas (-20%, -10%, +10%, +20%),
    re-normalize weights to sum to 1.0, and check:
    - Whether the Top-1 laptop remains identical to the baseline Top-1
    - Overlap of Top-5 laptops (out of 5)
    - Spearman correlation of full rankings against the baseline ranking
    """
    base_w = np.asarray(base_weights, dtype=np.float64)
    base_w = base_w / np.sum(base_w)

    base_res = compute_topsis(df_items, base_w, criteria=criteria)["ranking_df"]
    base_top1_id = int(base_res.iloc[0]["id"])
    base_top1_name = str(base_res.iloc[0]["name"])
    base_top5_ids = set(base_res.head(5)["id"].astype(int))
    base_rank_map = base_res.set_index("id")["rank"]

    records: List[Dict[str, Any]] = []
    for idx, crit in enumerate(criteria):
        code = crit["code"]
        for d in deltas:
            w_pert = base_w.copy()
            w_pert[idx] = max(1e-6, w_pert[idx] * (1.0 + float(d)))
            w_pert = w_pert / np.sum(w_pert)

            pert_df = compute_topsis(df_items, w_pert, criteria=criteria)["ranking_df"]
            new_top1_id = int(pert_df.iloc[0]["id"])
            new_top1_name = str(pert_df.iloc[0]["name"])
            new_top1_score = float(pert_df.iloc[0]["topsis_score"])
            new_top5_ids = set(pert_df.head(5)["id"].astype(int))

            pert_rank_map = pert_df.set_index("id").loc[base_rank_map.index, "rank"]
            rho, _ = spearmanr(base_rank_map.values, pert_rank_map.values)

            records.append({
                "criterion_code": code,
                "criterion_name_id": crit["name_id"],
                "criterion_name_en": crit["name_en"],
                "delta_pct": f"{int(round(d * 100)):+d}%",
                "delta_val": float(d),
                "base_weight": float(base_w[idx]),
                "perturbed_weight": float(w_pert[idx]),
                "base_top1_id": base_top1_id,
                "new_top1_id": new_top1_id,
                "new_top1_name": new_top1_name,
                "new_top1_score": new_top1_score,
                "top1_unchanged": bool(new_top1_id == base_top1_id),
                "top5_overlap": int(len(base_top5_ids & new_top5_ids)),
                "spearman_vs_base": float(np.nan_to_num(rho, nan=1.0)),
            })

    return pd.DataFrame(records)

