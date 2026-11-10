"""
Fase 8 Cross-Engine Parity & Performance Verification
Compares Python NumPy (`lab-streamlit/engine`) directly against Laravel PHP 8.3 (`AhpService` + `TopsisService`)
on `topsis_fixture.json` (10 laptops) and all 4 preset profiles across the full 949-laptop dataset.
Tolerance contract: < 1e-6 (exact rank & tie-breaker parity required).
"""
from __future__ import annotations
import json
import subprocess
import sys
import time
from pathlib import Path
import shutil
import numpy as np
import pandas as pd

ROOT = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(ROOT / "lab-streamlit"))

from engine import CRITERIA, compute_ahp, compute_topsis  # noqa: E402

_WINGET_PHP = Path(
    r"C:\Users\ahmdf\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
)
PHP_BIN = str(_WINGET_PHP) if _WINGET_PHP.exists() else (shutil.which("php") or "php")
PHP_EVAL = ROOT / "tools" / "php_mcdm_eval.php"


def run_php(pairwise_matrix, laptops):
    payload = json.dumps({"pairwise_matrix": pairwise_matrix, "laptops": laptops})
    res = subprocess.run(
        [PHP_BIN, str(PHP_EVAL)],
        input=payload,
        text=True,
        capture_output=True,
        check=True,
    )
    return json.loads(res.stdout)


def main() -> None:
    df_all = pd.read_csv(ROOT / "data" / "clean" / "laptops_clean.csv")
    presets = json.loads((ROOT / "data" / "clean" / "preset_profiles.json").read_text(encoding="utf-8"))
    fixture = json.loads((ROOT / "data" / "clean" / "topsis_fixture.json").read_text(encoding="utf-8"))

    print("=== [FASE 8.2] CROSS-ENGINE PARITY VERIFICATION (PYTHON NUMPY <-> PHP 8.3) ===\n")

    # 1. Fixture 10-laptop verification
    fix_laptops = fixture["sample_10_laptops_input"]
    student_matrix = next(p["pairwise_matrix"] for p in presets if p["slug"] == "student")
    py_ahp = compute_ahp(np.array(student_matrix, dtype=np.float64))
    w_fix = np.array([float(fixture["weights_used"][c["code"]]) for c in CRITERIA], dtype=np.float64)
    py_fix = compute_topsis(pd.DataFrame(fix_laptops), w_fix, criteria=CRITERIA)
    php_fix = run_php(student_matrix, fix_laptops)

    max_score_diff_fix = max(
        abs(float(r_py["topsis_score"]) - float(r_php["topsis_score"]))
        for (_, r_py), r_php in zip(py_fix["ranking_df"].iterrows(), php_fix["ranking"])
    )
    ids_match_fix = list(py_fix["ranking_df"]["id"].astype(int)) == [int(r["id"]) for r in php_fix["ranking"]]
    assert ids_match_fix and max_score_diff_fix < 1e-6
    print(f"[Fixture 10 Laptops] Exact rank order match={ids_match_fix} | max|dCi|={max_score_diff_fix:.3e} (< 1e-6 PASS)")

    # 2. Full 949-laptop dataset across all 4 preset profiles
    all_records = df_all.to_dict(orient="records")
    summary_rows = []

    for preset in presets:
        slug = preset["slug"]
        mat = preset["pairwise_matrix"]

        t0 = time.perf_counter()
        ahp_py = compute_ahp(np.array(mat, dtype=np.float64))
        top_py = compute_topsis(df_all, ahp_py["weights"], criteria=CRITERIA)
        py_ms = (time.perf_counter() - t0) * 1000.0

        php_out = run_php(mat, all_records)
        php_ms = float(php_out["elapsed_ms"])

        cr_diff = abs(float(ahp_py["cr"]) - float(php_out["cr"]))
        w_diff = float(np.max(np.abs(ahp_py["weights"] - np.array(php_out["weights"], dtype=np.float64))))
        denom_arr_py = np.array(top_py["denom"], dtype=np.float64)
        denom_arr_php = np.array(php_out["denom"], dtype=np.float64)
        denom_diff = float(np.max(np.abs(denom_arr_py - denom_arr_php) / np.maximum(np.abs(denom_arr_py), 1.0)))

        py_ids = list(top_py["ranking_df"]["id"].astype(int))
        php_ids = [int(r["id"]) for r in php_out["ranking"]]
        exact_949_match = (py_ids == php_ids)

        ci_diffs = [
            abs(float(py_ci) - float(php_r["topsis_score"]))
            for py_ci, php_r in zip(top_py["ranking_df"]["topsis_score"], php_out["ranking"])
        ]
        max_ci_diff = max(ci_diffs)

        assert cr_diff < 1e-9, f"CR mismatch for {slug}: {cr_diff}"
        assert w_diff < 1e-9, f"Weights mismatch for {slug}: {w_diff}"
        assert denom_diff < 1e-9, f"Relative Denom mismatch for {slug}: {denom_diff}"
        assert exact_949_match, f"949-laptop rank order mismatch for {slug}!"
        assert max_ci_diff < 1e-9, f"Ci score mismatch for {slug}: {max_ci_diff}"

        top1 = php_out["ranking"][0]
        print(
            f"[Preset {slug:<7}] 949/949 exact rank match={exact_949_match} | "
            f"max|dW|={w_diff:.2e} | max|dCi|={max_ci_diff:.2e} | "
            f"Top-1=ID #{top1['id']} (Ci={top1['topsis_score']:.6f}) | "
            f"Py={py_ms:.1f}ms, PHP={php_ms:.1f}ms"
        )
        summary_rows.append({
            "slug": slug,
            "candidates": len(all_records),
            "exact_rank_949_match": exact_949_match,
            "max_dw": w_diff,
            "max_dcr": cr_diff,
            "max_dci": max_ci_diff,
            "top1_id": top1["id"],
            "top1_ci": round(top1["topsis_score"], 6),
            "py_ms": round(py_ms, 2),
            "php_ms": round(php_ms, 2),
        })

    out_report = ROOT / "data" / "clean" / "cross_engine_parity_report.json"
    out_report.write_text(json.dumps(summary_rows, indent=2), encoding="utf-8")
    print(f"\nALL 4 x 949-LAPTOP CROSS-ENGINE PARITY CHECKS PASSED! Report saved to {out_report}")


if __name__ == "__main__":
    main()
