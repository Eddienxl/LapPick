"""LapPick MCDM Engine Package (AHP, TOPSIS, SAW, WP)."""
from .ahp import CRITERIA, CRITERIA_CODES, CRITERIA_COLS, CRITERIA_TYPES, RI_TABLE, compute_ahp, build_saaty_pairwise_from_scores, find_most_inconsistent_pairs
from .topsis import compute_topsis
from .saw_wp import compute_saw, compute_wp, compare_methods, run_sensitivity_analysis

__all__ = [
    "CRITERIA",
    "CRITERIA_CODES",
    "CRITERIA_COLS",
    "CRITERIA_TYPES",
    "RI_TABLE",
    "compute_ahp",
    "build_saaty_pairwise_from_scores",
    "find_most_inconsistent_pairs",
    "compute_topsis",
    "compute_saw",
    "compute_wp",
    "compare_methods",
    "run_sensitivity_analysis",
]

