"""
LapPick Analysis Lab — Streamlit Application (Fase 5)
SPK Pemilihan Laptop Terbaik untuk Kebutuhan Pengguna (AHP + TOPSIS + SAW + WP)
"""
from __future__ import annotations
import json
from pathlib import Path
from typing import Any, Dict, List, Tuple

import numpy as np
import pandas as pd
import plotly.express as px
import plotly.graph_objects as go
import streamlit as st

from engine import (
    CRITERIA,
    CRITERIA_CODES,
    CRITERIA_COLS,
    build_saaty_pairwise_from_scores,
    compare_methods,
    compute_ahp,
    compute_topsis,
    run_sensitivity_analysis,
)
from i18n import inject_theme_css, t

# ============================================================================
# PAGE CONFIG & PATHS
# ============================================================================
st.set_page_config(
    page_title="LapPick Analysis Lab — AHP + TOPSIS",
    page_icon="💻",
    layout="wide",
    initial_sidebar_state="expanded",
)

ROOT_DIR = Path(__file__).resolve().parent.parent
DATA_CLEAN_DIR = ROOT_DIR / "data" / "clean"
CSV_PATH = DATA_CLEAN_DIR / "laptops_clean.csv"
PRESETS_PATH = DATA_CLEAN_DIR / "preset_profiles.json"
FIXTURE_PATH = DATA_CLEAN_DIR / "topsis_fixture.json"


# ============================================================================
# CACHED DATA LOADERS
# ============================================================================
@st.cache_data(show_spinner=False)
def load_catalog() -> pd.DataFrame:
    if not CSV_PATH.exists():
        raise FileNotFoundError(f"File tidak ditemukan: {CSV_PATH}")
    return pd.read_csv(CSV_PATH)


@st.cache_data(show_spinner=False)
def load_presets() -> List[Dict[str, Any]]:
    if not PRESETS_PATH.exists():
        raise FileNotFoundError(f"File tidak ditemukan: {PRESETS_PATH}")
    with open(PRESETS_PATH, "r", encoding="utf-8") as f:
        return json.load(f)


@st.cache_data(show_spinner=False)
def load_fixture() -> Dict[str, Any]:
    if not FIXTURE_PATH.exists():
        raise FileNotFoundError(f"File tidak ditemukan: {FIXTURE_PATH}")
    with open(FIXTURE_PATH, "r", encoding="utf-8") as f:
        return json.load(f)


def format_idr(val: float) -> str:
    return f"Rp {int(round(val)):,}".replace(",", ".")


# ============================================================================
# MAIN APPLICATION
# ============================================================================
def main() -> None:
    df_all = load_catalog()
    presets = load_presets()
    fixture = load_fixture()

    # --- SIDEBAR: LANGUAGE, THEME, PROFILE & HARD FILTERS ---
    with st.sidebar:
        st.markdown("### LapPick Control Center")
        col_lang, col_theme = st.columns(2)
        with col_lang:
            lang_choice = st.selectbox(
                "Language",
                options=["id", "en"],
                format_func=lambda x: "Indonesia (ID)" if x == "id" else "English (EN)",
                index=0,
            )
        with col_theme:
            theme_choice = st.selectbox(
                "Theme",
                options=["light", "dark"],
                format_func=lambda x: "Light (Opsi C)" if x == "light" else "Dark (Opsi B)",
                index=0,
            )

        tokens = inject_theme_css(theme_choice)
        name_key = "name_id" if lang_choice == "id" else "name_en"
        desc_key = "description_id" if lang_choice == "id" else "description_en"

        st.markdown("---")
        st.subheader(t('profile_header', lang_choice))

        weight_mode = st.radio(
            t("weight_mode", lang_choice),
            options=["preset", "saaty_scores", "custom_sliders"],
            format_func=lambda m: {
                "preset": t("mode_preset", lang_choice),
                "saaty_scores": t("mode_custom_ahp", lang_choice),
                "custom_sliders": t("mode_custom_slider", lang_choice),
            }[m],
        )

        preset_map = {p["slug"]: p for p in presets}
        selected_slug = st.selectbox(
            t("select_preset", lang_choice),
            options=[p["slug"] for p in presets],
            format_func=lambda s: f"{preset_map[s][name_key]} ({s})",
        )
        active_preset = preset_map[selected_slug]
        st.caption(active_preset[desc_key])

        default_saaty_map = {
            "student": [8, 5, 4, 4, 2, 6, 7, 4, 1],
            "gamer": [3, 6, 5, 7, 8, 2, 1, 4, 7],
            "creator": [3, 7, 6, 8, 7, 4, 3, 5, 5],
            "office": [6, 5, 4, 6, 2, 8, 7, 6, 2],
        }

        if weight_mode == "preset":
            pairwise_matrix = np.array(active_preset["pairwise_matrix"], dtype=np.float64)
            ahp_res = compute_ahp(pairwise_matrix)
            active_weights = ahp_res["weights"]
        elif weight_mode == "saaty_scores":
            st.caption(
                "Atur tingkat kepentingan Saaty (1–9) per kriteria; matriks 9×9 resiprokal dibentuk otomatis:"
                if lang_choice == "id"
                else "Adjust Saaty importance (1–9) per criterion; reciprocal 9×9 matrix is built automatically:"
            )
            base_scores = default_saaty_map.get(selected_slug, [5] * 9)
            user_scores = []
            for idx, crit in enumerate(CRITERIA):
                s_val = st.slider(
                    f"{crit['code']} — {crit[name_key]}",
                    min_value=1,
                    max_value=9,
                    value=int(base_scores[idx]),
                    key=f"saaty_{selected_slug}_{crit['code']}",
                )
                user_scores.append(s_val)
            pairwise_matrix = build_saaty_pairwise_from_scores(user_scores)
            ahp_res = compute_ahp(pairwise_matrix)
            active_weights = ahp_res["weights"]
        else:
            st.caption(
                "Geser bobot langsung (otomatis dinormalisasi menjadi ∑w = 1,000):"
                if lang_choice == "id"
                else "Slide weights directly (auto-normalized to ∑w = 1.000):"
            )
            raw_sliders = []
            for crit in CRITERIA:
                code = crit["code"]
                def_w = float(active_preset["weights"][code])
                v = st.slider(
                    f"{code} — {crit[name_key]}",
                    min_value=0.01,
                    max_value=1.00,
                    value=round(def_w, 3),
                    step=0.01,
                    key=f"wslider_{selected_slug}_{code}",
                )
                raw_sliders.append(v)
            active_weights = np.array(raw_sliders, dtype=np.float64)
            active_weights = active_weights / np.sum(active_weights)
            pairwise_matrix = np.outer(active_weights, 1.0 / active_weights)
            ahp_res = compute_ahp(pairwise_matrix)

        # --- HARD FILTERS ---
        st.markdown("---")
        st.subheader(t('filter_header', lang_choice))
        max_price_m = float(np.ceil(df_all["price_idr"].max() / 1_000_000))
        min_price_m = float(np.floor(df_all["price_idr"].min() / 1_000_000))
        budget_m = st.slider(
            t("max_budget", lang_choice),
            min_value=min_price_m,
            max_value=max_price_m,
            value=max_price_m,
            step=0.5,
        )
        all_brands = sorted(df_all["brand"].dropna().unique().tolist())
        sel_brands = st.multiselect(t("brand_filter", lang_choice), options=all_brands, default=[])

        all_types = sorted(df_all["type_name"].dropna().unique().tolist())
        sel_types = st.multiselect(t("type_filter", lang_choice), options=all_types, default=[])

        col_f1, col_f2 = st.columns(2)
        with col_f1:
            min_ram_val = st.selectbox(t("min_ram", lang_choice), options=[4, 8, 16, 32], index=0)
        with col_f2:
            min_rr_val = st.selectbox(t("min_rr", lang_choice), options=[60, 90, 120, 144, 165, 240], index=0)

        only_ssd_flag = st.checkbox(t("only_ssd", lang_choice), value=False)

        # Apply Hard Filters
        df_filtered = df_all[df_all["price_idr"] <= budget_m * 1_000_000].copy()
        if sel_brands:
            df_filtered = df_filtered[df_filtered["brand"].isin(sel_brands)]
        if sel_types:
            df_filtered = df_filtered[df_filtered["type_name"].isin(sel_types)]
        df_filtered = df_filtered[df_filtered["ram_gb"] >= min_ram_val]
        df_filtered = df_filtered[df_filtered["refresh_rate_hz"] >= min_rr_val]
        if only_ssd_flag:
            df_filtered = df_filtered[df_filtered["has_ssd"] == 1]

        st.metric(
            label=t("filtered_count", lang_choice),
            value=f"{len(df_filtered)} / {len(df_all)}",
        )

    # --- HERO HEADER ---
    st.markdown(
        f"""
        <div class="lappick-hero">
            <h1>{t('app_title', lang_choice)}</h1>
            <p>{t('app_subtitle', lang_choice)}</p>
        </div>
        """,
        unsafe_allow_html=True,
    )

    # --- 4 TABS ---
    tab1, tab2, tab3, tab4 = st.tabs([
        t("tab1_title", lang_choice),
        t("tab2_title", lang_choice),
        t("tab3_title", lang_choice),
        t("tab4_title", lang_choice),
    ])

    # ========================================================================
    # TAB 1: CATALOG EXPLORER & INTERACTIVE EDA
    # ========================================================================
    with tab1:
        k1, k2, k3, k4 = st.columns(4)
        k1.metric(t("kpi_total_laptops", lang_choice), f"{len(df_all):,} unit")
        k2.metric(t("kpi_median_price", lang_choice), format_idr(float(df_all["price_idr"].median())))
        ssd_pct = 100.0 * float((df_all["has_ssd"] == 1).mean())
        k3.metric(t("kpi_ssd_ratio", lang_choice), f"{ssd_pct:.1f}%")
        hrr_cnt = int((df_all["refresh_rate_hz"] >= 120).sum())
        hrr_pct = 100.0 * hrr_cnt / len(df_all)
        k4.metric(t("kpi_high_rr", lang_choice), f"{hrr_cnt} unit ({hrr_pct:.1f}%)")

        imputed_cnt = int((df_all["is_imputed"] == 1).sum())
        imputed_pct = round(100.0 * imputed_cnt / len(df_all), 2)
        st.info(t("imputed_note", lang_choice, n=imputed_cnt, pct=imputed_pct))

        c_left, c_right = st.columns(2)
        with c_left:
            st.markdown(f"#### {t('eda_dist_brand', lang_choice)}")
            brand_stats = (
                df_all.groupby("brand", as_index=False)
                .agg(
                    count=("id", "count"),
                    mean_price_m=("price_idr", lambda s: round(float(s.mean()) / 1_000_000, 2)),
                )
                .sort_values("count", ascending=False)
            )
            fig_brand = px.bar(
                brand_stats,
                x="brand",
                y="count",
                color="mean_price_m",
                text="count",
                color_continuous_scale="Tealgrn" if theme_choice == "dark" else "Blues",
                template=tokens["plotly_template"],
                labels={
                    "brand": "Brand",
                    "count": "Count",
                    "mean_price_m": "Mean Price (M IDR)",
                },
            )
            fig_brand.update_layout(
                height=390,
                margin=dict(l=20, r=20, t=20, b=20),
                paper_bgcolor=tokens["bg_surface"],
                plot_bgcolor=tokens["bg_surface"],
                font=dict(color=tokens["text_primary"]),
            )
            st.plotly_chart(fig_brand, use_container_width=True, theme=None)

        with c_right:
            st.markdown(f"#### {t('eda_scatter_title', lang_choice)}")
            df_plot = df_all.copy()
            df_plot["price_m_idr"] = np.round(df_plot["price_idr"] / 1_000_000.0, 2)
            fig_scatter = px.scatter(
                df_plot,
                x="price_m_idr",
                y="gpu_benchmark",
                color="type_name",
                size="ram_gb",
                hover_name="name",
                hover_data=["brand", "cpu_ranking", "refresh_rate_hz", "weight_kg"],
                template=tokens["plotly_template"],
                labels={
                    "price_m_idr": "Price (Million IDR)",
                    "gpu_benchmark": "GPU Benchmark (pts)",
                    "type_name": "Category",
                },
            )
            fig_scatter.update_layout(
                height=390,
                margin=dict(l=20, r=20, t=20, b=20),
                paper_bgcolor=tokens["bg_surface"],
                plot_bgcolor=tokens["bg_surface"],
                font=dict(color=tokens["text_primary"]),
            )
            st.plotly_chart(fig_scatter, use_container_width=True, theme=None)

        st.markdown(f"#### {t('eda_corr_title', lang_choice)}")
        corr_labels = [f"{c['code']} ({c[name_key]})" for c in CRITERIA]
        corr_mat = df_all[CRITERIA_COLS].corr(method="pearson").to_numpy()
        fig_corr = px.imshow(
            np.round(corr_mat, 2),
            x=corr_labels,
            y=corr_labels,
            text_auto=True,
            color_continuous_scale="RdBu_r",
            zmin=-1,
            zmax=1,
            template=tokens["plotly_template"],
        )
        fig_corr.update_layout(
            height=460,
            margin=dict(l=20, r=20, t=20, b=20),
            paper_bgcolor=tokens["bg_surface"],
            plot_bgcolor=tokens["bg_surface"],
            font=dict(color=tokens["text_primary"]),
        )
        st.plotly_chart(fig_corr, use_container_width=True, theme=None)

        st.markdown(f"#### {t('eda_catalog_header', lang_choice)}")
        st.dataframe(
            df_filtered[
                [
                    "id", "brand", "name", "type_name", "price_idr",
                    "ram_gb", "storage_effective_gb", "cpu_ranking",
                    "gpu_benchmark", "battery_hours", "weight_kg",
                    "user_rating", "refresh_rate_hz", "is_imputed",
                ]
            ],
            use_container_width=True,
            height=360,
        )

    # ========================================================================
    # TAB 2: AHP WEIGHT SIMULATOR & REAL-TIME TOPSIS RANKING
    # ========================================================================
    with tab2:
        st.markdown(f"#### {t('ahp_metrics_title', lang_choice)}")
        m1, m2, m3, m4 = st.columns(4)
        m1.metric("λ_max (Principal Eigenvalue)", f"{ahp_res['lambda_max']:.4f}")
        m2.metric("CI (Consistency Index)", f"{ahp_res['ci']:.4f}")
        m3.metric("RI (Random Index, n=9)", f"{ahp_res['ri']:.2f}")
        m4.metric("CR (Consistency Ratio)", f"{ahp_res['cr']:.4f}")

        if ahp_res["is_consistent"]:
            st.markdown(
                f'<div class="lappick-status-ok">[OK] {t("ahp_consistent_ok", lang_choice)} — CR = {ahp_res["cr"]:.4f} ≤ 0.10</div>',
                unsafe_allow_html=True,
            )
        else:
            top_inc = ahp_res["inconsistent_pairs"][0]
            st.markdown(
                f'<div class="lappick-status-bad">[!] {t("ahp_consistent_bad", lang_choice)} — CR = {ahp_res["cr"]:.4f} > 0.10 '
                f'(Pasangan paling menyimpang / Most inconsistent pair: {top_inc["pair"]})</div>',
                unsafe_allow_html=True,
            )

        # Weight Bar Chart
        w_df = pd.DataFrame([
            {
                "Code": crit["code"],
                "Criterion": f"{crit['code']} — {crit[name_key]}",
                "Type": crit["type"].upper(),
                "Weight": float(active_weights[i]),
                "WeightPct": round(float(active_weights[i]) * 100.0, 2),
            }
            for i, crit in enumerate(CRITERIA)
        ])
        fig_w = px.bar(
            w_df,
            x="Criterion",
            y="WeightPct",
            color="Type",
            text="WeightPct",
            color_discrete_map={"BENEFIT": tokens["accent_primary"], "COST": tokens["accent_secondary"]},
            template=tokens["plotly_template"],
            labels={"WeightPct": "Weight (%)"},
            title=t("ahp_weights_chart", lang_choice),
        )
        fig_w.update_layout(
            height=320,
            margin=dict(l=20, r=20, t=40, b=20),
            paper_bgcolor=tokens["bg_surface"],
            plot_bgcolor=tokens["bg_surface"],
            font=dict(color=tokens["text_primary"]),
        )
        st.plotly_chart(fig_w, use_container_width=True, theme=None)

        if len(df_filtered) < 2:
            st.warning(t("min_alt_warning", lang_choice))
        else:
            topsis_out = compute_topsis(df_filtered, active_weights, criteria=CRITERIA)
            rank_df = topsis_out["ranking_df"]

            # --- TOP-3 PODIUM CARDS ---
            st.markdown(f"#### {t('top3_podium', lang_choice)}")
            podium_cols = st.columns(min(3, len(rank_df)))
            for idx_p in range(min(3, len(rank_df))):
                row_p = rank_df.iloc[idx_p]
                imp_badge = " · Imputed Rating" if int(row_p.get("is_imputed", 0)) == 1 else ""
                with podium_cols[idx_p]:
                    st.markdown(
                        f"""
                        <div class="lappick-card">
                            <span class="lappick-badge-rank1">RANK #{int(row_p['rank'])} · ID #{int(row_p['id'])}</span>
                            <span class="lappick-badge-match">{row_p['match_pct']:.2f}% Match</span>
                            <h4 style="margin:6px 0 4px 0; font-size:1.0rem;">{row_p['brand']} — {str(row_p['name'])[:58]}...</h4>
                            <p style="margin:0 0 8px 0; font-weight:700; color:{tokens['accent_primary']}; font-size:1.08rem;">
                                {format_idr(float(row_p['price_idr']))}
                                <span style="font-size:0.78rem; font-weight:400; color:{tokens['text_secondary']};">({row_p['type_name']}{imp_badge})</span>
                            </p>
                            <div>
                                <span class="lappick-spec-pill">RAM {int(row_p['ram_gb'])}GB</span>
                                <span class="lappick-spec-pill">Storage {int(row_p['storage_effective_gb'])}GB</span>
                                <span class="lappick-spec-pill">CPU #{int(row_p['cpu_ranking'])}</span>
                                <span class="lappick-spec-pill">GPU {float(row_p['gpu_benchmark']):.1f}</span>
                                <span class="lappick-spec-pill">{int(row_p['refresh_rate_hz'])}Hz</span>
                                <span class="lappick-spec-pill">{float(row_p['weight_kg']):.2f}kg</span>
                                <span class="lappick-spec-pill">Rating {float(row_p['user_rating']):.1f}/5</span>
                            </div>
                            <p style="margin:8px 0 0 0; font-size:0.78rem; font-family:'JetBrains Mono',monospace; color:{tokens['text_secondary']};">
                                C_i = {float(row_p['topsis_score']):.6f} | D+ = {float(row_p['d_plus']):.5f} | D- = {float(row_p['d_minus']):.5f}
                            </p>
                        </div>
                        """,
                        unsafe_allow_html=True,
                    )

            # --- RADAR CHART FOR TOP-4 LAPTOPS ---
            st.markdown(f"#### {t('radar_title', lang_choice)}")
            top4 = rank_df.head(min(4, len(rank_df))).copy()
            fig_radar = go.Figure()
            radar_cats = [f"{c['code']} {c[name_key]}" for c in CRITERIA]
            for _, r_row in top4.iterrows():
                vals_0_100 = []
                for c in CRITERIA:
                    col = c["col"]
                    c_min = float(df_all[col].min())
                    c_max = float(df_all[col].max())
                    v = float(r_row[col])
                    if c_max == c_min:
                        norm_v = 100.0
                    elif c["type"] == "benefit":
                        norm_v = 100.0 * (v - c_min) / (c_max - c_min)
                    else:
                        norm_v = 100.0 * (c_max - v) / (c_max - c_min)
                    vals_0_100.append(round(norm_v, 1))
                fig_radar.add_trace(
                    go.Scatterpolar(
                        r=vals_0_100 + [vals_0_100[0]],
                        theta=radar_cats + [radar_cats[0]],
                        fill="toself",
                        name=f"#{int(r_row['rank'])} {r_row['brand']} (ID {int(r_row['id'])})",
                    )
                )
            fig_radar.update_layout(
                polar=dict(radialaxis=dict(visible=True, range=[0, 100])),
                template=tokens["plotly_template"],
                height=420,
                margin=dict(l=30, r=30, t=30, b=30),
                paper_bgcolor=tokens["bg_surface"],
                plot_bgcolor=tokens["bg_surface"],
                font=dict(color=tokens["text_primary"]),
            )
            st.plotly_chart(fig_radar, use_container_width=True, theme=None)

            # --- FULL RANKING TABLE & CSV EXPORT ---
            st.markdown(f"#### {t('top_n_header', lang_choice)}")
            st.dataframe(
                rank_df[
                    [
                        "rank", "id", "brand", "name", "type_name", "price_idr",
                        "ram_gb", "storage_effective_gb", "cpu_ranking", "gpu_benchmark",
                        "battery_hours", "weight_kg", "user_rating", "refresh_rate_hz",
                        "d_plus", "d_minus", "topsis_score", "match_pct", "is_imputed",
                    ]
                ],
                use_container_width=True,
                height=380,
            )
            csv_bytes = rank_df.to_csv(index=False).encode("utf-8")
            st.download_button(
                label=t('download_csv', lang_choice),
                data=csv_bytes,
                file_name=f"lappick_topsis_{selected_slug}.csv",
                mime="text/csv",
            )

            # --- STEP-BY-STEP MATHEMATICAL TRANSPARENCY ACCORDION ---
            with st.expander(t("matrix_inspector", lang_choice), expanded=False):
                st.caption("Menampilkan 15 alternatif teratas untuk keterbacaan tabel matriks:")
                top_idx_ids = rank_df.head(15)["id"].tolist()
                mask_15 = df_filtered["id"].isin(top_idx_ids).to_numpy()

                st.markdown(f"**{t('step_x', lang_choice)}**")
                st.dataframe(topsis_out["df_X"].iloc[mask_15], use_container_width=True)

                st.markdown(f"**{t('step_r', lang_choice)}**")
                st.dataframe(topsis_out["df_R"].iloc[mask_15].round(6), use_container_width=True)

                st.markdown(f"**{t('step_y', lang_choice)}**")
                st.dataframe(topsis_out["df_Y"].iloc[mask_15].round(6), use_container_width=True)

                st.markdown(f"**{t('step_ideal', lang_choice)}**")
                ideal_df = pd.DataFrame(
                    [topsis_out["ideal_pos"], topsis_out["ideal_neg"]],
                    index=["A+ (Solusi Ideal Positif)", "A- (Solusi Ideal Negatif)"],
                    columns=CRITERIA_CODES,
                )
                st.dataframe(ideal_df.round(6), use_container_width=True)

                st.markdown(f"**{t('step_dist', lang_choice)}**")
                st.dataframe(
                    rank_df.head(15)[["rank", "id", "brand", "name", "d_plus", "d_minus", "topsis_score", "match_pct"]],
                    use_container_width=True,
                )

    # ========================================================================
    # TAB 3: METHOD COMPARISON (TOPSIS vs SAW vs WP) & SENSITIVITY ANALYSIS
    # ========================================================================
    with tab3:
        st.markdown(f"#### {t('comp_header', lang_choice)}")
        st.caption(t("comp_desc", lang_choice))

        if len(df_filtered) < 2:
            st.warning(t("min_alt_warning", lang_choice))
        else:
            comp_res = compare_methods(df_filtered, active_weights, criteria=CRITERIA)
            c1, c2, c3, c4 = st.columns(4)
            c1.metric(t("spearman_ts_saw", lang_choice), f"{comp_res['spearman_topsis_saw']:.4f}")
            c2.metric(t("spearman_ts_wp", lang_choice), f"{comp_res['spearman_topsis_wp']:.4f}")
            c3.metric(t("spearman_saw_wp", lang_choice), f"{comp_res['spearman_saw_wp']:.4f}")
            c4.metric(t("overlap_top10", lang_choice), f"{comp_res['overlap_top10_wp']} / 10")

            st.dataframe(
                comp_res["comparison_df"].head(25)[
                    [
                        "id", "brand", "name", "price_idr",
                        "rank_topsis", "rank_saw", "rank_wp",
                        "topsis_score", "saw_score", "wp_score",
                    ]
                ],
                use_container_width=True,
                height=340,
            )

            st.markdown("---")
            st.markdown(f"#### {t('sens_header', lang_choice)}")
            st.caption(t("sens_desc", lang_choice))

            sens_df = run_sensitivity_analysis(df_filtered, active_weights, criteria=CRITERIA)
            unchanged_count = int(sens_df["top1_unchanged"].sum())
            total_scen = len(sens_df)
            mean_rho = float(sens_df["spearman_vs_base"].mean())

            s1, s2 = st.columns(2)
            s1.metric(
                t("sens_stability_rate", lang_choice),
                f"{unchanged_count} / {total_scen} ({100.0 * unchanged_count / total_scen:.1f}%)",
            )
            s2.metric(t("sens_avg_spearman", lang_choice), f"{mean_rho:.5f}")

            fig_sens = px.bar(
                sens_df,
                x="criterion_code",
                y="top5_overlap",
                color="delta_pct",
                barmode="group",
                template=tokens["plotly_template"],
                labels={
                    "criterion_code": "Criterion",
                    "top5_overlap": "Top-5 Overlap (out of 5)",
                    "delta_pct": "Weight Perturbation",
                },
            )
            fig_sens.update_yaxes(range=[0, 5.2])
            fig_sens.update_layout(
                height=350,
                margin=dict(l=20, r=20, t=20, b=20),
                paper_bgcolor=tokens["bg_surface"],
                plot_bgcolor=tokens["bg_surface"],
                font=dict(color=tokens["text_primary"]),
            )
            st.plotly_chart(fig_sens, use_container_width=True, theme=None)

            st.dataframe(sens_df, use_container_width=True, height=320)

    # ========================================================================
    # TAB 4: NUMERICAL CONTRACT VERIFICATION (topsis_fixture.json @ 1e-6)
    # ========================================================================
    with tab4:
        st.markdown(f"#### {t('audit_header', lang_choice)}")
        st.caption(t("audit_desc", lang_choice))

        df_fix_in = pd.DataFrame(fixture["sample_10_laptops_input"])
        w_fix = [float(fixture["weights_used"][c["code"]]) for c in CRITERIA]
        fix_res = compute_topsis(df_fix_in, w_fix, criteria=CRITERIA)

        exp_denom = np.array(fixture["expected_denom"], dtype=np.float64)
        exp_pos = np.array(fixture["expected_ideal_pos"], dtype=np.float64)
        exp_neg = np.array(fixture["expected_ideal_neg"], dtype=np.float64)
        exp_rank = pd.DataFrame(fixture["expected_ranking"])

        err_denom = float(np.max(np.abs(fix_res["denom"] - exp_denom)))
        err_pos = float(np.max(np.abs(fix_res["ideal_pos"] - exp_pos)))
        err_neg = float(np.max(np.abs(fix_res["ideal_neg"] - exp_neg)))

        merged_audit = pd.merge(
            exp_rank,
            fix_res["ranking_df"][["id", "rank", "d_plus", "d_minus", "topsis_score"]],
            on="id",
            suffixes=("_expected", "_actual"),
        )
        merged_audit["abs_err_d_plus"] = np.abs(merged_audit["d_plus_expected"] - merged_audit["d_plus_actual"])
        merged_audit["abs_err_d_minus"] = np.abs(merged_audit["d_minus_expected"] - merged_audit["d_minus_actual"])
        merged_audit["abs_err_score"] = np.abs(merged_audit["topsis_score_expected"] - merged_audit["topsis_score_actual"])
        merged_audit["rank_match"] = merged_audit["rank_expected"] == merged_audit["rank_actual"]

        max_score_err = float(merged_audit["abs_err_score"].max())
        max_dplus_err = float(merged_audit["abs_err_d_plus"].max())
        max_dminus_err = float(merged_audit["abs_err_d_minus"].max())
        all_ranks_match = bool(merged_audit["rank_match"].all())

        passed = (
            max_score_err < 1e-6
            and max_dplus_err < 1e-6
            and max_dminus_err < 1e-6
            and err_pos < 1e-6
            and err_neg < 1e-6
            and all_ranks_match
        )

        if passed:
            st.markdown(
                f'<div class="lappick-status-ok">[PASS] {t("audit_pass", lang_choice)} — '
                f'Max |ΔC_i| = {max_score_err:.2e} < 1e-6</div>',
                unsafe_allow_html=True,
            )
        else:
            st.markdown(
                f'<div class="lappick-status-bad">[FAIL] {t("audit_fail", lang_choice)} — '
                f'Max |ΔC_i| = {max_score_err:.2e}</div>',
                unsafe_allow_html=True,
            )

        a1, a2, a3, a4 = st.columns(4)
        a1.metric("Max |Δ A+|", f"{err_pos:.2e}")
        a2.metric("Max |Δ A-|", f"{err_neg:.2e}")
        a3.metric("Max |Δ D+ / D-|", f"{max(max_dplus_err, max_dminus_err):.2e}")
        a4.metric("Max |Δ C_i (TOPSIS)|", f"{max_score_err:.2e}")

        st.dataframe(merged_audit, use_container_width=True)


if __name__ == "__main__":
    main()

