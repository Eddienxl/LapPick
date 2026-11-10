# Product Requirements Document (PRD): LapPick — SPK Pemilihan Laptop Berbasis AHP + TOPSIS

**Mata Kuliah:** Sistem Pendukung Keputusan (SPK) — UAS Semester 7  
**Versi Dokumen:** 1.0.0 (Final Complete Release — Seluruh Fase 1 s.d. Fase 8 Selesai & Terverifikasi Paritas Lintas-Engine `< 2.05e-15`, UI Audit & Laporan Bab 4.5 Lengkap — 10 Nov 2026)  
**Nama Produk:** `LapPick`

---

## Ringkasan Progres Proyek Saat Ini (Master Progress Tracker)

| Fase | Nama Fase | Progres | Status |
|---|---|---|---|
| **Fase 1** | Perencanaan, Desain UI/UX, Kriteria & Master Dokumentasi | 100% | ✔ **SELESAI** |
| **Fase 2** | Pipeline Data Cleaning & Feature Engineering (Colab Notebook 01) | 100% | ✔ **SELESAI** (949 laptop bersih) |
| **Fase 3** | Prototipe & Validasi Matematis MCDM: AHP + TOPSIS + SAW + WP (Colab 02) | 100% | ✔ **SELESAI** (Fixture & Validasi siap) |
| **Fase 4** | Integrasi Infrastruktur AI Pendukung (9Router + Jembatan MCP `-9router`) | 100% | ✔ **SELESAI** (`smart-free` aktif) |
| **Fase 5** | Pembangunan Aplikasi **Streamlit Analysis Lab** (`lab-streamlit`) | 100% | ✔ **SELESAI** (`http://localhost:8501` aktif, WCAG & Plotly tema-selaras) |
| **Fase 6** | Persiapan Lingkungan PHP/Composer & Pondasi **Laravel** (`lappick-laravel`) | 100% | ✔ **SELESAI** (PHP 8.3.36, Composer 2.11.2, 5 Tabel & 949 Laptop Ter-seed) |
| **Fase 7** | Implementasi Mesin Inti SPK (AHP + TOPSIS) & Antarmuka UI di **Laravel** | 100% | ✔ **SELESAI** (`http://127.0.0.1:8000` aktif, UI SVG SaaS bebas *emoji slop* & 164 assertions PHPUnit PASS) |
| **Fase 8** | Pengujian Lintas-Engine (`1e-6`), Finalisasi Laporan Live Bab 4.5 & Demo UAS | 100% | ✔ **SELESAI** (Paritas 949/949 `≤ 2.05e-15`, Bab 4.5 + Tabel 4.12 + Gambar 4.1–4.7, & Launcher 1-Klik siap) |

---

## 1. Executive Summary & Visi Produk

### 1.1 Latar Belakang & Pernyataan Masalah
* **Pilihan terlalu banyak:** Ratusan model laptop dengan spesifikasi berbeda membuat konsumen sulit membandingkan secara objektif.
* **Kebutuhan tiap orang berbeda:** Mahasiswa mengutamakan harga dan bobot, gamer mengutamakan GPU dan refresh rate, kreator mengutamakan CPU dan RAM. Filter harga/brand biasa tidak mengakomodasi bobot prioritas ini.
* **Keputusan bersifat subjektif:** Tanpa metode terukur, pilihan sering dipengaruhi iklan dan *review* acak.

### 1.2 Solusi: LapPick
Aplikasi web SPK yang:
1. Menerima **profil kebutuhan** pengguna (4 preset atau kustom).
2. Menghitung **bobot 9 kriteria** dengan **AHP** (perbandingan berpasangan + uji konsistensi CR).
3. Meranking **949 alternatif laptop** dengan **TOPSIS**.
4. Menampilkan **Top-N rekomendasi** beserta langkah perhitungannya.
5. Menyediakan **Analysis Lab** (Streamlit) untuk uji sensitivitas bobot dan perbandingan TOPSIS dengan SAW dan WP.

### 1.3 Ruang Lingkup UAS
| Termasuk | Tidak Termasuk |
|---|---|
| AHP + TOPSIS (inti) | Pembelian / e-commerce |
| SAW dan WP sebagai pembanding validasi | Harga *real-time* dari marketplace |
| Katalog statis dari dataset Kaggle (2022, pasar India) | Scraping website toko |
| Auth, riwayat keputusan, admin CRUD | Pembayaran |
| Antarmuka dwibahasa ID/EN, Light/Dark | Aplikasi mobile native |

---

## 2. Target Pengguna (User Persona)

| Persona | Prioritas Utama (bobot AHP tertinggi) | Preset |
|---|---|---|
| **Mahasiswa** | Harga 30,3%, Bobot 21,3%, Baterai 14,7% | `student` |
| **Gamer** | GPU 27,6%, CPU 18,4%, Refresh Rate 18,4% | `gamer` |
| **Kreator/Desainer** | CPU 27,2%, RAM 17,9%, GPU 17,9% | `creator` |
| **Pekerja Kantoran** | Baterai 28,1%, Bobot 18,9%, Harga/CPU/Rating 11,8% | `office` |
| **Admin** | Mengelola katalog dan kriteria, melihat riwayat | — |

---

## 3. Spesifikasi Fungsional

### 3.1 Modul 1: Katalog & Manajemen Data Laptop (Laravel)
* CRUD laptop (Admin), impor dari `data/clean/laptops_clean.csv` lewat Seeder/Artisan command.
* Katalog dengan filter (brand, tipe, rentang harga, refresh rate, OS) dan pencarian.
* Detail laptop menampilkan seluruh atribut dan tautan sumber (`source_link`).
* Laptop dengan `is_imputed = 1` (rating diimputasi, 277 baris) diberi penanda "rating perkiraan".

### 3.2 Modul 2: Profil Kebutuhan & Bobot AHP
* Pengguna memilih preset **atau** mengisi matriks perbandingan berpasangan 9×9 (skala Saaty 1–9) lewat UI slider.
* Sistem menghitung bobot, $\lambda_{max}$, **CI**, **CR** (RI untuk n=9 adalah **1,45**).
* **CR > 0,10** → tampilkan peringatan dan pasangan yang paling tidak konsisten; ranking tidak dihitung tanpa konfirmasi eksplisit.
* *Hard filter* (anggaran maks, brand, tipe, refresh rate minimum) diterapkan **sebelum** TOPSIS. Bila sisa alternatif < 2, tampilkan pesan dan jangan hitung.

### 3.3 Modul 3: Perankingan TOPSIS
* Normalisasi vektor, pembobotan, $A^+/A^-$, $D^\pm$, $C_i$.
* Output: tabel ranking, Top-N kartu dengan badge **Match %**, radar chart per laptop.
* Panel "Lihat perhitungan" menampilkan seluruh matriks antara.

### 3.4 Modul 4: Perbandingan Laptop (Compare)
2–4 laptop berdampingan, pemenang tiap kriteria ditandai.

### 3.5 Modul 5: Riwayat & Ekspor
Setiap keputusan tersimpan (profil, bobot, CR, filter, hasil) dan dapat diekspor ke PDF/CSV.

### 3.6 Modul 6: Analysis Lab (Streamlit)
* EDA katalog dan korelasi antar kriteria.
* Simulator bobot (*what-if*) dengan ranking yang berubah langsung.
* Uji sensitivitas ±10% dan ±20%.
* Komparasi TOPSIS vs SAW vs WP (Spearman, irisan Top-10).
* *Cross-check* engine Python terhadap Laravel (toleransi 1e-6) memakai `topsis_fixture.json`.

---

## 4. Kriteria Keputusan (FINAL — 9 Kriteria)

| Kode | Kriteria | Tipe | Kolom `laptops_clean.csv` | Min – Median – Maks |
|---|---|---|---|---|
| C1 | Harga | Cost | `price_idr` (INR × 190) | Rp 3,04 jt – 11,40 jt – 57,38 jt |
| C2 | RAM (GB) | Benefit | `ram_gb` | 4 – 8 – 32 |
| C3 | Storage efektif (GB) | Benefit | `storage_effective_gb` | 19,2 – 512 – 2.000 |
| C4 | Peringkat CPU | Cost | `cpu_ranking` | 4 – 108 – 907 |
| C5 | Skor GPU | Benefit | `gpu_benchmark` | 1 – 17,5 – 233 |
| C6 | Baterai (jam) | Benefit | `battery_hours` | 4,0 – 4,5 – 5,0 |
| C7 | Bobot (kg) | Cost | `weight_kg` | 0,78 – 1,65 – 2,90 |
| C8 | Rating pengguna | Benefit | `user_rating` | 1,8 – 4,3 – 5,0 |
| C9 | Refresh Rate (Hz) | Benefit | `refresh_rate_hz` | 60 – 60 – 360 |

Atribut layar lain (resolusi, ukuran, touchscreen) menjadi filter/info, bukan kriteria.

---

## 5. Spesifikasi Non-Fungsional

| Aspek | Target |
|---|---|
| Kinerja | Ranking ≤ 1 detik untuk 949 alternatif |
| Akurasi | Hasil Laravel = hasil Python (toleransi 1e-6) pada `topsis_fixture.json` |
| Transparansi | Setiap angka dapat ditelusuri ke langkah perhitungan |
| Responsif | Desktop dan mobile |
| Keamanan | Auth Laravel, validasi input, CSRF, role Admin/User |
| Reproducibility | Pipeline data dari Colab (notebook 01 dan 02) dapat diulang |
| Bahasa | ID/EN lewat `__('key')`, tanpa string *hardcode* |
| Integrasi AI | Mendukung opini kedua lintas-model lewat protokol kata kunci `-9router` (Dokumen 08) |

---

## 6. Tahapan Detail Penyelesaian Proyek & Pelacak Progres (Detailed Roadmap)

Bagian ini menjadi acuan utama (*Single Source of Truth*) untuk memantau apa yang **sudah selesai** dan **tahapan detail berikutnya** sampai proyek siap dikumpulkan/didemokan pada UAS.

### Fase 1: Perencanaan, Desain UI/UX, Kriteria & Master Dokumentasi — ✔ SELESAI
- [x] **1.1** Penentuan topik SPK (`LapPick` — Pemilihan Laptop AHP + TOPSIS + pembanding SAW & WP) dan arsitektur *hybrid* (Colab + Laravel + Streamlit).
- [x] **1.2** Kesepakatan desain UI/UX: **Opsi C (Modern SaaS Dashboard)** dengan *toggle* Light/Dark Mode (*Dark Mode* memakai palet **Opsi B — Tech Spec Dark**) dan antarmuka **Dwibahasa (ID–EN)**.
- [x] **1.3** Finalisasi dataset (`dhanushbommavaram/laptop-ranked-dataset`) dan 9 kriteria keputusan (`C1`–`C9`) dengan kurs tetap `1 INR = Rp 190`.
- [x] **1.4** Penyusunan 8 dokumen di folder `Master_Dokumentasi_SPK_Laptop/` (`01_PRD` s.d. `08_LINGKUNGAN_DAN_MEKANISME_AI_9ROUTER`).

### Fase 2: Data Cleaning & Feature Engineering (Colab Notebook 01) — ✔ SELESAI
- [x] **2.1** Pembuatan & eksekusi `notebooks/01_data_cleaning_feature_eng.ipynb`.
- [x] **2.2** Perbaikan forensik bug *string-slicing* pada kolom `Refresh Rate` mentah (memulihkan 151 laptop 60/90/120/144/165/240/300/360 Hz dari nama produk).
- [x] **2.3** Konversi `price_idr`, perhitungan `storage_effective_gb` (SSD $\times 1.0$, non-SSD $\times 0.6$), imputasi median terstratifikasi untuk `user_rating` (dengan flag `is_imputed`), dan deduplikasi menjadi **949 laptop bersih**.
- [x] **2.4** Ekstraksi artefak bersih ke `data/clean/`: `laptops_clean.csv`, `cleaning_log.json`, dan `data_dictionary.md`.

### Fase 3: Prototipe & Validasi Matematis MCDM (Colab Notebook 02) — ✔ SELESAI
- [x] **3.1** Pembuatan & eksekusi `notebooks/02_mcdm_prototype_validation.ipynb`.
- [x] **3.2** Penyusunan matriks berpasangan Saaty $9\times 9$ untuk 4 profil preset (`student`, `gamer`, `creator`, `office`) dan verifikasi rasio konsistensi ($CR \le 0.0270 < 0.10$).
- [x] **3.3** Implementasi & komparasi 3 metode perankingan (TOPSIS, SAW, WP) serta uji korelasi peringkat Spearman ($\rho = 0.9617\text{–}0.9963$) dan uji sensitivitas bobot $\pm 10\%, \pm 20\%$.
- [x] **3.4** Ekstraksi artefak validasi ke `data/clean/`: `preset_profiles.json`, `topsis_fixture.json` (kontrak uji 10 laptop), dan `mcdm_validation_summary.json`.

### Fase 4: Integrasi Lingkungan AI Pendukung (9Router + MCP Bridge) — ✔ SELESAI
- [x] **4.1** Instalasi global `9router` (`v0.5.116`) pada `http://127.0.0.1:20128` dan perbaikan kompatibilitas `undici` untuk Node.js `v22.14.0`.
- [x] **4.2** Konfigurasi provider gratis resmi (Groq Free Tier + Pollinations Public Free) dan pembuatan combo auto-fallback **`smart-free`**.
- [x] **4.3** Pembuatan server MCP `tools/router9-mcp/index.js` (`router9_list_models`, `router9_ask_model`, `router9_second_opinion`) dan pendaftaran di konfigurasi Antigravity.
- [x] **4.4** Penetapan protokol kata kunci **`-9router`** di Master Dokumentasi (`04_AGENTS` dan `08_LINGKUNGAN`).

---

### Fase 5: Pembangunan Aplikasi Streamlit Analysis Lab (`lab-streamlit`) — ✔ SELESAI
> **Status:** Seluruh modul `lab-streamlit/` telah selesai dibangun, diuji terhadap `topsis_fixture.json` (deviasi maksimum $4,88 \times 10^{-9} < 10^{-6}$), dan berjalan di `http://localhost:8501`.

- [x] **5.1 Instalasi Paket & Struktur Folder `lab-streamlit/`:**
  - Menginstal `streamlit` (`1.67.0`) dan `plotly` (`7.1.0`) via `py -m pip install streamlit plotly`.
  - Membangun struktur modular:
    - `lab-streamlit/app.py` (entry point, navigasi, toggle bahasa ID/EN & tema visual).
    - `lab-streamlit/engine/ahp.py` (perhitungan vektor eigen, $\lambda_{\max}$, $CI$, $CR$).
    - `lab-streamlit/engine/topsis.py` (normalisasi vektor, matriks terbobot, $A^+/A^-$, $D^\pm$, $C_i$).
    - `lab-streamlit/engine/saw_wp.py` (mesin pembanding SAW & WP).
    - `lab-streamlit/i18n/translations.py` (kamus dwibahasa Indonesia & Inggris).
- [x] **5.2 Tab 1 — Eksplorasi Katalog & EDA Interaktif:**
  - Kartu metrik ringkasan (949 laptop, rentang harga, distribusi brand & tipe, persentase SSD & Refresh Rate tinggi).
  - Grafik distribusi harga per kategori, *scatter plot* Harga vs Performa (CPU/GPU), dan *heatmap* korelasi antar 9 kriteria (`C1`–`C9`).
- [x] **5.3 Tab 2 — Simulator Bobot AHP & What-If TOPSIS Real-Time:**
  - Pemilih 4 profil preset (`Mahasiswa`, `Gamer`, `Kreator`, `Kantoran`) atau atur slider bobot kustom secara langsung.
  - Filter keras (*hard filter*: budget maksimum Rp, brand, tipe laptop, refresh rate minimum).
  - Tabel Top-N rekomendasi real-time lengkap dengan radar chart profil spesifikasi dan rincian matriks tahapan TOPSIS.
- [x] **5.4 Tab 3 — Komparasi Metode (TOPSIS vs SAW vs WP) & Uji Sensitivitas:**
  - Tabel perbandingan berdampingan peringkat TOPSIS, SAW, dan WP beserta metrik korelasi Spearman ($\rho$) dan irisan Top-10.
  - Grafik stabilitas Top-1 dan Top-5 saat bobot kriteria digeser $\pm 10\%$ dan $\pm 20\%$.
- [x] **5.5 Tab 4 — Verifikasi Kontrak Numerik (`topsis_fixture.json`):**
  - Pengujian otomatis (`lab-streamlit/tests/test_engine_contract.py`) membuktikan `engine/ahp.py` dan `engine/topsis.py` menghasilkan angka yang identik (selisih $< 10^{-6}$) dengan `data/clean/topsis_fixture.json` dari Colab 02.

---

### Fase 6: Persiapan Lingkungan PHP/Composer & Pondasi Laravel (`lappick-laravel`) — ✔ SELESAI
> **Status:** PHP `8.3.36` dan Composer `2.11.2` telah terpasang dan dikonfigurasi; proyek Laravel `13.x` (`lappick-laravel`) telah dibuat; 5 tabel inti dimigrasi (`49 ms`) dan 949 laptop bersih telah di-seed (`76 ms`).

- [x] **6.1 Instalasi & Verifikasi PHP 8.3 + Composer:**
  - Menginstal PHP `8.3.36` via `winget` (`PHP.PHP.8.3`), mengaktifkan ekstensi `pdo_sqlite`, `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `zip`, serta memasang Composer `2.11.2`.
- [x] **6.2 Inisialisasi Proyek `lappick-laravel` & Konfigurasi Dasar:**
  - Membuat proyek Laravel di `d:\TUGAS\SEMESTER 7\SPK\lappick-laravel`.
  - Mengatur koneksi database SQLite (`database/database.sqlite`) dan arsitektur *localization* (`lang/id/lappick.php` dan `lang/en/lappick.php`) beserta middleware `SetLocaleMiddleware`.
- [x] **6.3 Migrasi Database (5 Tabel Inti) & Model Eloquent:**
  - `users` (dengan kolom `role`: `admin` / `user`).
  - `criteria` (9 kriteria `C1`–`C9`, bobot default, tipe `cost`/`benefit`, satuan, nama ID & EN).
  - `laptops` (29 atribut dari `laptops_clean.csv` dengan primary key `id` non-autoincrement `1..949` + indeks pencarian/filter).
  - `preset_profiles` (4 profil preset beserta matriks Saaty $9\times 9$ JSON dan vektor bobot AHP dari `preset_profiles.json`).
  - `decision_histories` (menyimpan riwayat perhitungan pengguna: profil, matriks/bobot AHP, nilai $CR$, filter, dan JSON Top-10 hasil rekomendasi).
- [x] **6.4 Database Seeder Otomatis dari `data/clean/`:**
  - `CriteriaSeeder` (memuat 9 kriteria resmi — selesai dalam `31 ms`).
  - `PresetProfileSeeder` (membaca langsung `data/clean/preset_profiles.json` — selesai dalam `15 ms`).
  - `LaptopSeeder` (mengimpor seluruh **949 baris** dari `data/clean/laptops_clean.csv` secara idempotent dengan `Laptop::upsert` — selesai dalam `76 ms`).
  - `DatabaseSeeder` (membuat akun demo Admin `admin@lappick.id` & User `fadli@uin-suska.ac.id`).

---

### Fase 7: Implementasi Mesin SPK & Antarmuka Web Utama di Laravel — ✔ SELESAI
> **Status:** Seluruh layanan & UI Laravel telah dibangun di `lappick-laravel/`, diaudit secara visual untuk desktop (`1440px`) dan mobile (`375px`), serta lulus **5 pengujian / 164 asersi PHPUnit**.

- [x] **7.1 Service Layer SPK (`app/Services/`):**
  - `AhpService.php`: Menerima matriks $9\times 9$, memvalidasi resiprokal $a_{ji} = 1/a_{ij}$, menghitung vektor bobot, $\lambda_{\max}$, $CI$, $CR$ ($RI=1.45$), serta mendeteksi 3 pasangan kriteria paling tidak konsisten (`inconsistent_pairs`) bila $CR > 0.10$.
  - `TopsisService.php`: Menerima koleksi laptop terfilter + bobot AHP, menghitung matriks ternormalisasi $R$, matriks terbobot $Y$, solusi ideal $A^+/A^-$, jarak Euclidean $D^+/D^-$, skor preferensi $C_i$, dan mengurutkan alternatif (*deterministic tie-breaker*: `topsis_score DESC, id ASC`).
- [x] **7.2 Sistem Tema (Opsi C Light + Opsi B Dark), Ikonografi SVG & Dwibahasa (ID/EN):**
  - Layout utama `layouts/app.blade.php` dengan token Design System (`#F8FAFC`/`#0F1117`, `#FFFFFF`/`#1A1D27`, Indigo/Cyan), *navbar* 1-baris responsif + drawer mobile, *locale switcher* bersih (`ID | EN`), *icon toggle* Matahari/Bulan, dan komponen SVG `<x-icon>` bebas *emoji slop*.
  - Seluruh kamus `lang/id/lappick.php` & `lang/en/lappick.php` tersinkronisasi penuh.
- [x] **7.3 Halaman Publik & Katalog Laptop:**
  - *Landing Page* hero dua-kolom asimetris berisi pratinjau langsung juara profil preset + *Quick Preset Picker* (`home.blade.php`).
  - Halaman Katalog 949 Laptop (`catalog.blade.php`) dan Detail Laptop + Grafik Radar 9 Dimensi reaktif tema (`laptop_detail.blade.php`) lengkap dengan *badge* transparan untuk `is_imputed` dan tautan sumber asli.
- [x] **7.4 Halaman Inti SPK — Wizard Rekomendasi (AHP + TOPSIS):**
  - Pilihan 3 Mode: **4 Profil Preset**, **Slider Skala Saaty (1–9)**, dan **Matriks Pairwise $9\times 9$** (`wizard.blade.php`).
  - Indikator *Real-Time Consistency Ratio ($CR$)* dan *guardrail* otomatis bila $CR > 0.10$ beserta opsi *override* edukatif.
  - Panel *Hard Filter* pra-TOPSIS (Anggaran Maksimum Rp, Brand, Tipe Laptop, RAM Min, Refresh Rate Min, Wajib SSD) dengan validasi minimum $\ge 2$ kandidat.
  - Hasil Rekomendasi: Podium Top-3 (`Match %`), Chart.js Radar Top-4 reaktif Light/Dark, tabel Top-25, dan akordeon **"Transparansi Matematis"** ($X, R, Y, A^+/A^-, D^+/D^-, C_i$) terformat HTML matematis rapi.
- [x] **7.5 Fitur Compare (2–4 Laptop), Riwayat Keputusan & Panel Admin CRUD:**
  - Komparasi *side-by-side* 2–4 laptop (`compare.blade.php`) dengan penanda pemenang (*winner highlight*) di tiap kriteria.
  - Penyimpanan & ekspor riwayat keputusan pengguna (cetak/PDF & unduh CSV Top-10 di `history.blade.php`).
  - Dashboard Admin & Login Demo 1-Klik (`login.blade.php`, `admin.blade.php`) untuk CRUD data laptop & peninjauan kriteria.

---

### Fase 8: Pengujian Lintas-Engine, Finalisasi Laporan Live Bab 4.5 & Persiapan Demo — ✔ SELESAI
> **Status:** Seluruh verifikasi lintas-engine, dokumentasi visual Laporan Bab 4.5, dan perangkat demo 1-klik telah diselesaikan dan diuji langsung.

- [x] **8.1** Penulisan Laporan Akademis Live HTML (`Laporan_UAS_SPK_LapPick.html`) Bab 1, Bab 2, Bab 3, Bab 4.1–4.4, Bab 5, dan Daftar Pustaka.
- [x] **8.2 Unit Testing & Cross-Check PHP $\leftrightarrow$ Python (`< 1e-6`):**
  - Menjalankan `php artisan test` (`McdmContractTest.php` + `SpkWebRoutesTest.php`: **5 tests, 164 assertions LULUS**).
  - Menjalankan `tools/verify_cross_engine.py` terhadap `tools/php_mcdm_eval.php`: membuktikan **949/949 kesamaan urutan peringkat identik** pada seluruh 4 profil dengan selisih bobot $|\Delta w_j| \le 5,55 \times 10^{-17}$, selisih skor $|\Delta C_i| \le 2,05 \times 10^{-15}$, dan waktu komputasi **5,5–7,6 ms** di PHP (serta `3,907e-7` pada fixture Colab 02), tersimpan di `data/clean/cross_engine_parity_report.json`.
- [x] **8.3 Pengambilan Screenshot Aplikasi & Penyelesaian Subbab 4.5 Laporan HTML:**
  - Menangkap 7+ tangkapan layar terverifikasi (desktop `1440px` Light Mode, Dark Mode, Wizard AHP+TOPSIS, Radar Chart, Guardrail `CR > 0.10`, Side-by-Side Compare, Streamlit Analysis Lab, dan Mobile `375px`) ke `assets/screenshots/`, serta melengkapi **Subbab 4.5, Tabel 4.12, Gambar 4.1–4.7, dan Kesimpulan #4** pada `Laporan_UAS_SPK_LapPick.html`.
- [x] **8.4 Panduan Demo UAS Cepat (< 2 Menit):**
  - Menyusun skrip peluncur satu-klik [`start_lappick_demo.bat`](../start_lappick_demo.bat) (Laravel port `8000` + Streamlit port `8501`) beserta panduan skenario presentasi & tanya-jawab penguji di [`README_DEMO_UAS.md`](../README_DEMO_UAS.md).

---

## 7. Acceptance Criteria
1. Empat preset menghasilkan ranking berbeda dan masuk akal. **Sudah terbukti di Colab 02:** Top-1 Mahasiswa HP 245 G8 ($C_i = 0,7871$), Gamer Acer Predator Helios 300 ($C_i = 0,7222$), Kreator Acer Predator Helios 300 ($C_i = 0,7691$), Kantoran HP Pavilion 13 ($C_i = 0,7225$).
2. CR setiap preset $\le 0,10$ (hasil: $0,0245$; $0,0270$; $0,0153$; $0,0189$).
3. TOPSIS Laravel identik dengan Python pada `topsis_fixture.json` (10 laptop, toleransi `1e-6`).
4. $CR > 0,10$ memunculkan peringatan *guardrail* konsistensi.
5. Demo dari pilih profil sampai rekomendasi $< 2$ menit.
