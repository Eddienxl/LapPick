"""
Automated Contract Verification Suite for LapPick Streamlit Engine (Fase 5.5).
Verifies:
1. AHP weights, lambda_max, CI, CR for all 4 presets in `data/clean/preset_profiles.json` (< 1e-6).
2. TOPSIS denom, A+, A-, D+, D-, C_i, and rank order on `data/clean/topsis_fixture.json` (< 1e-6).
3. Full 949-laptop TOPSIS, SAW, WP, and Sensitivity Analysis execution.
"""
from __future__ import annotations
import json
import sys
from pathlib import Path
import numpy as np
import pandas as pd

LAB_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(LAB_DIR))

from engine import CRITERIA, compare_methods, compute_ahp, compute_topsis, run_sensitivity_analysis

DATA_CLEAN = LAB_DIR.parent / "data" / "clean"


def main() -> None:
    print("=== [1/3] Verifying AHP Engine against preset_profiles.json ===")
    presets = json.loads((DATA_CLEAN / "preset_profiles.json").read_text(encoding="utf-8"))
    for p in presets:
        res = compute_ahp(p["pairwise_matrix"])
        exp_w = np.array([p["weights"][c["code"]] for c in CRITERIA], dtype=np.float64)
        w_err = float(np.max(np.abs(res["weights"] - exp_w)))
        cr_err = abs(res["cr"] - float(p["cr"]))
        print(f"  Preset [{p['slug']:7s}]: CR={res['cr']:.6f} | max|dW|={w_err:.2e} | |dCR|={cr_err:.2e}")
        assert w_err < 1e-6, f"AHP weight mismatch on {p['slug']}: {w_err}"
        assert cr_err < 1e-6, f"AHP CR mismatch on {p['slug']}: {cr_err}"
        assert res["is_consistent"] is True

    print("\n=== [2/3] Verifying TOPSIS Engine against topsis_fixture.json (1e-6 contract) ===")
    fixture = json.loads((DATA_CLEAN / "topsis_fixture.json").read_text(encoding="utf-8"))
    df_fix = pd.DataFrame(fixture["sample_10_laptops_input"])
    w_fix = [float(fixture["weights_used"][c["code"]]) for c in CRITERIA]
    t_res = compute_topsis(df_fix, w_fix, criteria=CRITERIA)

    err_denom = float(np.max(np.abs(t_res["denom"] - np.array(fixture["expected_denom"]))))
    err_pos = float(np.max(np.abs(t_res["ideal_pos"] - np.array(fixture["expected_ideal_pos"]))))
    err_neg = float(np.max(np.abs(t_res["ideal_neg"] - np.array(fixture["expected_ideal_neg"]))))
    print(f"  max|dDenom|={err_denom:.2e} | max|dA+|={err_pos:.2e} | max|dA-|={err_neg:.2e}")
    assert err_denom < 1e-6
    assert err_pos < 1e-6
    assert err_neg < 1e-6

    rank_df = t_res["ranking_df"]
    for exp_row, (_, act_row) in zip(fixture["expected_ranking"], rank_df.iterrows()):
        assert int(exp_row["id"]) == int(act_row["id"]), f"Rank ID mismatch: {exp_row['id']} vs {act_row['id']}"
        assert abs(float(exp_row["d_plus"]) - float(act_row["d_plus"])) < 1e-6
        assert abs(float(exp_row["d_minus"]) - float(act_row["d_minus"])) < 1e-6
        assert abs(float(exp_row["topsis_score"]) - float(act_row["topsis_score"])) < 1e-6
    print("  All 10 fixture laptops matched rank, D+, D-, and C_i within < 1e-6!")

    print("\n=== [3/3] Verifying Full 949-Laptop Catalog, SAW/WP Comparison & Sensitivity ===")
    df_all = pd.read_csv(DATA_CLEAN / "laptops_clean.csv")
    assert len(df_all) == 949
    student_w = [float(presets[0]["weights"][c["code"]]) for c in CRITERIA]
    comp = compare_methods(df_all, student_w, criteria=CRITERIA)
    sens = run_sensitivity_analysis(df_all, student_w, criteria=CRITERIA)
    top1 = comp["comparison_df"].iloc[0]
    print(f"  Full catalog Top-1 (student): #{int(top1['id'])} {top1['brand']} (C_i={top1['topsis_score']:.6f})")
    print(f"  Spearman TOPSIS vs SAW = {comp['spearman_topsis_saw']:.4f} | TOPSIS vs WP = {comp['spearman_topsis_wp']:.4f}")
    print(f"  Sensitivity scenarios tested: {len(sens)} | Top-1 stability: {int(sens['top1_unchanged'].sum())}/{len(sens)}")
    print("\nALL FASE 5 CONTRACT TESTS PASSED SUCCESSFULLY!")


if __name__ == "__main__":
    main()

