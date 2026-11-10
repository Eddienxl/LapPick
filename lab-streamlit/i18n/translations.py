"""Bilingual Dictionary (ID/EN) & Design System Theme Tokens (Opsi C Light / Opsi B Dark)."""
from __future__ import annotations
from typing import Any, Dict
import streamlit as st

TRANSLATIONS: Dict[str, Dict[str, str]] = {
    "id": {
        "app_title": "LapPick Analysis Lab — SPK Pemilihan Laptop (AHP + TOPSIS)",
        "app_subtitle": "Laboratorium Analisis Eksploratif, Simulator What-If AHP-TOPSIS, Komparasi Metode (SAW & WP), Uji Sensitivitas, dan Audit Kontrak Numerik.",
        "sidebar_settings": "Pengaturan Tampilan & Profil",
        "lang_label": "Bahasa Antarmuka",
        "theme_label": "Mode Tema Visual",
        "theme_light": "Light — Modern SaaS (Opsi C)",
        "theme_dark": "Dark — Tech Spec (Opsi B)",
        "profile_header": "Profil Kebutuhan & Bobot AHP",
        "weight_mode": "Sumber Bobot Kriteria",
        "mode_preset": "Profil Preset (Tervalidasi AHP)",
        "mode_custom_slider": "Simulator Bobot Kustom (What-If Slider)",
        "mode_custom_ahp": "Skor Kepentingan Saaty (1–9)",
        "select_preset": "Pilih Profil Pengguna",
        "filter_header": "Hard Filter Pra-TOPSIS",
        "max_budget": "Anggaran Maksimum (Rp Juta)",
        "brand_filter": "Filter Merek (Kosong = Semua)",
        "type_filter": "Filter Kategori Laptop (Kosong = Semua)",
        "min_ram": "RAM Minimum (GB)",
        "min_rr": "Refresh Rate Minimum (Hz)",
        "only_ssd": "Wajib menggunakan SSD",
        "filtered_count": "Alternatif lolos filter",
        "min_alt_warning": "Minimal 2 laptop diperlukan untuk menjalankan perhitungan TOPSIS. Longgarkan filter Anda.",
        "tab1_title": "1. Katalog & EDA",
        "tab2_title": "2. Simulator AHP & TOPSIS",
        "tab3_title": "3. Komparasi (SAW/WP) & Sensitivitas",
        "tab4_title": "4. Audit Kontrak Numerik (1e-6)",
        "kpi_total_laptops": "Total Katalog Bersih",
        "kpi_median_price": "Median Harga (IDR)",
        "kpi_ssd_ratio": "Proporsi Laptop SSD",
        "kpi_high_rr": "Layar High Refresh (≥120Hz)",
        "eda_dist_brand": "Distribusi Jumlah Laptop & Rata-Rata Harga per Merek",
        "eda_scatter_title": "Peta Harga vs Performa GPU & Peringkat CPU",
        "eda_corr_title": "Matriks Korelasi Pearson Antar 9 Kriteria Keputusan (C1–C9)",
        "eda_catalog_header": "Tabel Penjelajah Katalog Laptop (949 Alternatif)",
        "imputed_note": "Catatan Transparansi: {n} laptop ({pct}%) memiliki user_rating hasil imputasi median terstratifikasi (ditandai is_imputed = 1).",
        "ahp_metrics_title": "Indikator Konsistensi Matriks AHP (n = 9, RI = 1.45)",
        "ahp_consistent_ok": "KONSISTEN (CR ≤ 0.10) — Bobot sah digunakan",
        "ahp_consistent_bad": "TIDAK KONSISTEN (CR > 0.10) — Tinjau kembali perbandingan!",
        "ahp_weights_chart": "Distribusi Bobot Prioritas 9 Kriteria (w_j)",
        "top_n_header": "Peringkat Rekomendasi Terbaik (TOPSIS)",
        "top3_podium": "Podium 3 Rekomendasi Terbaik",
        "radar_title": "Perbandingan Profil Spesifikasi Top-4 Laptop (Ternormalisasi 0–100)",
        "matrix_inspector": "Transparansi Perhitungan Matematis (Matriks X, R, Y, A+/A-, D+/D-, Ci)",
        "step_x": "Tahap 1: Matriks Keputusan Awal (X)",
        "step_r": "Tahap 2: Matriks Ternormalisasi Vektor (R)",
        "step_y": "Tahap 3: Matriks Ternormalisasi Terbobot (Y = w · R)",
        "step_ideal": "Tahap 4: Solusi Ideal Positif (A+) & Ideal Negatif (A-)",
        "step_dist": "Tahap 5 & 6: Jarak Euclidean (D+, D-) dan Skor Preferensi (Ci)",
        "comp_header": "Komparasi Lintas Metode: TOPSIS vs SAW vs WP",
        "comp_desc": "Membandingkan konsistensi peringkat antara metode jarak Euclidean (TOPSIS), penjumlahan linier (SAW), dan perkalian pangkat (WP).",
        "spearman_ts_saw": "Spearman ρ (TOPSIS vs SAW)",
        "spearman_ts_wp": "Spearman ρ (TOPSIS vs WP)",
        "spearman_saw_wp": "Spearman ρ (SAW vs WP)",
        "overlap_top10": "Irisan Top-10 (TOPSIS ∩ WP)",
        "sens_header": "Uji Sensitivitas Bobot Kriteria (±10% dan ±20%)",
        "sens_desc": "Menguji ketahanan (robustness) alternatif peringkat #1 dan Top-5 ketika masing-masing bobot kriteria C1–C9 digeser sebesar -20%, -10%, +10%, dan +20%.",
        "sens_stability_rate": "Stabilitas Top-1 (36 Skenario)",
        "sens_avg_spearman": "Rata-rata Korelasi Spearman vs Basis",
        "audit_header": "Verifikasi Kontrak Lintas-Engine (topsis_fixture.json)",
        "audit_desc": "Memastikan engine Python Streamlit menghasilkan perhitungan yang identik hingga toleransi 1e-6 terhadap artefak acuan Colab Notebook 02 & PHPUnit Laravel.",
        "audit_pass": "LULUS AUDIT KONTRAK NUMERIK (Seluruh deviasi maksimum < 1e-6)",
        "audit_fail": "GAGAL AUDIT KONTRAK NUMERIK (Terdapat deviasi ≥ 1e-6)",
        "download_csv": "Unduh Hasil Ranking (CSV)",
    },
    "en": {
        "app_title": "LapPick Analysis Lab — Laptop Decision Support System (AHP + TOPSIS)",
        "app_subtitle": "Exploratory Data Analysis Lab, AHP-TOPSIS What-If Simulator, Multi-Method Comparison (SAW & WP), Sensitivity Testing, and Numerical Contract Audit.",
        "sidebar_settings": "Display & Profile Settings",
        "lang_label": "Interface Language",
        "theme_label": "Visual Theme Mode",
        "theme_light": "Light — Modern SaaS (Option C)",
        "theme_dark": "Dark — Tech Spec (Option B)",
        "profile_header": "User Profile & AHP Weights",
        "weight_mode": "Criterion Weight Source",
        "mode_preset": "Preset Profile (AHP Validated)",
        "mode_custom_slider": "Custom Weight Simulator (What-If Sliders)",
        "mode_custom_ahp": "Saaty Importance Scores (1–9)",
        "select_preset": "Select User Persona",
        "filter_header": "Pre-TOPSIS Hard Filters",
        "max_budget": "Maximum Budget (Million IDR)",
        "brand_filter": "Brand Filter (Empty = All)",
        "type_filter": "Laptop Category Filter (Empty = All)",
        "min_ram": "Minimum RAM (GB)",
        "min_rr": "Minimum Refresh Rate (Hz)",
        "only_ssd": "Require SSD Storage",
        "filtered_count": "Alternatives passing filter",
        "min_alt_warning": "At least 2 laptops are required to run TOPSIS ranking. Please relax your filters.",
        "tab1_title": "1. Catalog & EDA",
        "tab2_title": "2. AHP & TOPSIS Simulator",
        "tab3_title": "3. Method Comparison (SAW/WP) & Sensitivity",
        "tab4_title": "4. Numerical Contract Audit (1e-6)",
        "kpi_total_laptops": "Clean Catalog Size",
        "kpi_median_price": "Median Price (IDR)",
        "kpi_ssd_ratio": "SSD Equipped Share",
        "kpi_high_rr": "High Refresh Display (≥120Hz)",
        "eda_dist_brand": "Laptop Count & Mean Price Distribution by Brand",
        "eda_scatter_title": "Price vs GPU Benchmark & CPU Ranking Map",
        "eda_corr_title": "Pearson Correlation Matrix Across 9 Decision Criteria (C1–C9)",
        "eda_catalog_header": "Interactive Laptop Catalog Explorer (949 Alternatives)",
        "imputed_note": "Transparency Note: {n} laptops ({pct}%) use stratified median imputation for user_rating (flagged with is_imputed = 1).",
        "ahp_metrics_title": "AHP Matrix Consistency Indicators (n = 9, RI = 1.45)",
        "ahp_consistent_ok": "CONSISTENT (CR ≤ 0.10) — Valid priority weights",
        "ahp_consistent_bad": "INCONSISTENT (CR > 0.10) — Please revise pairwise judgments!",
        "ahp_weights_chart": "9-Criteria Priority Weight Distribution (w_j)",
        "top_n_header": "Top Recommended Laptops (TOPSIS Ranking)",
        "top3_podium": "Top-3 Recommendation Podium",
        "radar_title": "Top-4 Laptop Normalized Specification Radar (0–100 Scale)",
        "matrix_inspector": "Inspect Step-by-Step Mathematical Matrices (X, R, Y, A+/A-, D+/D-, Ci)",
        "step_x": "Step 1: Raw Decision Matrix (X)",
        "step_r": "Step 2: Vector Normalized Matrix (R)",
        "step_y": "Step 3: Weighted Normalized Matrix (Y = w · R)",
        "step_ideal": "Step 4: Positive Ideal (A+) & Negative Ideal (A-) Solutions",
        "step_dist": "Step 5 & 6: Euclidean Distances (D+, D-) and Closeness Score (Ci)",
        "comp_header": "Cross-Method Comparison: TOPSIS vs SAW vs WP",
        "comp_desc": "Evaluate ranking agreement across Euclidean distance (TOPSIS), additive linear utility (SAW), and multiplicative power weighting (WP).",
        "spearman_ts_saw": "Spearman ρ (TOPSIS vs SAW)",
        "spearman_ts_wp": "Spearman ρ (TOPSIS vs WP)",
        "spearman_saw_wp": "Spearman ρ (SAW vs WP)",
        "overlap_top10": "Top-10 Overlap (TOPSIS ∩ WP)",
        "sens_header": "Criterion Weight Sensitivity Analysis (±10% and ±20%)",
        "sens_desc": "Test the stability of the #1 ranked laptop and Top-5 set when each criterion weight C1–C9 is perturbed by -20%, -10%, +10%, and +20%.",
        "sens_stability_rate": "Top-1 Stability (36 Scenarios)",
        "sens_avg_spearman": "Mean Spearman ρ vs Baseline",
        "audit_header": "Cross-Engine Numerical Contract Verification (topsis_fixture.json)",
        "audit_desc": "Verify that the Streamlit Python engine matches the Colab Notebook 02 & Laravel PHPUnit reference fixture within 1e-6 tolerance.",
        "audit_pass": "NUMERICAL CONTRACT AUDIT PASSED (All max deviations < 1e-6)",
        "audit_fail": "NUMERICAL CONTRACT AUDIT FAILED (Deviation ≥ 1e-6 detected)",
        "download_csv": "Download Ranking Results (CSV)",
    },
}


def t(key: str, lang: str = "id", **kwargs: Any) -> str:
    """Translate a key into the selected language ('id' or 'en')."""
    lang_dict = TRANSLATIONS.get(lang, TRANSLATIONS["id"])
    text = lang_dict.get(key, TRANSLATIONS["id"].get(key, key))
    if kwargs:
        try:
            return text.format(**kwargs)
        except Exception:
            return text
    return text


def get_theme_tokens(theme_mode: str = "light") -> Dict[str, str]:
    """
    Return exact color tokens from `02_DESIGN_SYSTEM_SPK_LAPTOP.md`:
    - Light Mode: Opsi C (Modern SaaS Dashboard)
    - Dark Mode:  Opsi B (Tech Spec Dark)
    """
    if theme_mode == "dark":
        return {
            "mode": "dark",
            "bg_primary": "#0F1117",
            "bg_surface": "#1A1D27",
            "bg_elevated": "#222634",
            "text_primary": "#E6E8EE",
            "text_secondary": "#94A3B8",
            "accent_primary": "#8B5CF6",
            "accent_secondary": "#22D3EE",
            "border": "#2A2F3E",
            "success": "#10B981",
            "warning": "#FBBF24",
            "danger": "#F87171",
            "plotly_template": "plotly_dark",
        }
    return {
        "mode": "light",
        "bg_primary": "#F8FAFC",
        "bg_surface": "#FFFFFF",
        "bg_elevated": "#F1F5F9",
        "text_primary": "#0F172A",
        "text_secondary": "#64748B",
        "accent_primary": "#4F46E5",
        "accent_secondary": "#10B981",
        "border": "#E2E8F0",
        "success": "#10B981",
        "warning": "#F59E0B",
        "danger": "#EF4444",
        "plotly_template": "plotly_white",
    }


def inject_theme_css(theme_mode: str = "light") -> Dict[str, str]:
    """Inject crisp WCAG-compliant CSS matching Opsi C Light / Opsi B Dark palette into Streamlit."""
    tokens = get_theme_tokens(theme_mode)
    css = f"""
    <style>
        .stApp, [data-testid="stAppViewContainer"], [data-testid="stMain"] {{
            background-color: {tokens['bg_primary']} !important;
            color: {tokens['text_primary']} !important;
        }}
        [data-testid="stHeader"] {{
            background-color: {tokens['bg_surface']} !important;
            border-bottom: 1px solid {tokens['border']} !important;
        }}
        [data-testid="stHeader"] * {{
            color: {tokens['text_primary']} !important;
        }}
        [data-testid="stSidebar"] {{
            background-color: {tokens['bg_surface']} !important;
            border-right: 1px solid {tokens['border']} !important;
        }}
        [data-testid="stSidebar"] h1,
        [data-testid="stSidebar"] h2,
        [data-testid="stSidebar"] h3,
        [data-testid="stSidebar"] p,
        [data-testid="stSidebar"] label,
        [data-testid="stSidebar"] span {{
            color: {tokens['text_primary']} !important;
        }}
        [data-testid="stSidebar"] .stCaption,
        [data-testid="stSidebar"] [data-testid="stCaptionContainer"] * {{
            color: {tokens['text_secondary']} !important;
        }}
        [data-testid="stMetric"] {{
            background-color: {tokens['bg_surface']};
            border: 1px solid {tokens['border']};
            border-radius: 10px;
            padding: 12px 14px;
        }}
        [data-testid="stMetricLabel"] * {{
            color: {tokens['text_secondary']} !important;
        }}
        [data-testid="stMetricValue"] * {{
            color: {tokens['text_primary']} !important;
            font-variant-numeric: tabular-nums;
        }}
        .stMarkdown h1, .stMarkdown h2, .stMarkdown h3, .stMarkdown h4,
        .stMarkdown p, .stMarkdown li {{
            color: {tokens['text_primary']};
        }}
        button[data-baseweb="tab"] {{
            color: {tokens['text_secondary']} !important;
            font-weight: 600 !important;
        }}
        button[data-baseweb="tab"][aria-selected="true"] {{
            color: {tokens['accent_primary']} !important;
        }}
        .lappick-hero {{
            background: {tokens['bg_surface']};
            border: 1px solid {tokens['border']};
            border-left: 4px solid {tokens['accent_primary']};
            border-radius: 12px;
            padding: 18px 22px;
            margin-bottom: 18px;
        }}
        .lappick-hero h1 {{
            margin: 0 0 6px 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: {tokens['text_primary']} !important;
        }}
        .lappick-hero p {{
            margin: 0;
            font-size: 0.92rem;
            color: {tokens['text_secondary']} !important;
        }}
        .lappick-card {{
            background-color: {tokens['bg_surface']};
            border: 1px solid {tokens['border']};
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
        }}
        .lappick-badge-rank1 {{
            display: inline-block;
            background: {tokens['accent_primary']};
            color: #FFFFFF;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 3px 10px;
            border-radius: 6px;
            margin-bottom: 6px;
        }}
        .lappick-badge-match {{
            display: inline-block;
            background: {tokens['success']};
            color: #FFFFFF;
            font-weight: 700;
            font-size: 0.78rem;
            padding: 3px 10px;
            border-radius: 6px;
            float: right;
        }}
        .lappick-spec-pill {{
            display: inline-block;
            background: {tokens['bg_elevated']};
            color: {tokens['text_primary']};
            border: 1px solid {tokens['border']};
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 0.76rem;
            margin: 2px 4px 2px 0;
            font-family: 'JetBrains Mono', monospace;
        }}
        .lappick-status-ok {{
            background-color: rgba(16, 185, 129, 0.12);
            border: 1px solid {tokens['success']};
            color: {tokens['text_primary']};
            padding: 10px 14px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 10px;
        }}
        .lappick-status-bad {{
            background-color: rgba(239, 68, 68, 0.12);
            border: 1px solid {tokens['danger']};
            color: {tokens['text_primary']};
            padding: 10px 14px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 10px;
        }}
    </style>
    """
    st.markdown(css, unsafe_allow_html=True)
    return tokens

