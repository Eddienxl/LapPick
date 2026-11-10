# AI Agents Development Guide: Aturan & Standar Rekayasa LapPick

**Proyek:** LapPick (SPK Pemilihan Laptop — AHP + TOPSIS)
**Versi:** 0.4.0 (Ditambahkan Protokol Trigger `-9router`, 10 Nov 2026)
**Sasaran:** AI Coding Assistant (Antigravity, Cursor, dll.) dan pengembang.

---

## 1. Maksud & Tujuan
Kontrak aturan bagi AI/pengembang saat membaca, mengubah, atau menambah fitur pada repositori LapPick (`lappick-laravel`, `lab-streamlit`, `notebooks`, `data/clean`). **Baca dokumen 01, 03, 05, dan 08 sebelum menulis kode.**

---

## 2. Prinsip Inti

### 2.1 Integritas Perhitungan SPK
* **DILARANG** mengubah rumus AHP/TOPSIS tanpa memperbarui `03_ARCHITECTURE` dan test.
* Engine PHP dan Python **wajib** identik (toleransi `1e-6`) pada `data/clean/topsis_fixture.json`. Setiap perubahan engine menjalankan cross-check.
* **DILARANG** membulatkan di tengah perhitungan; pembulatan hanya saat tampilan.
* Tipe `cost`/`benefit`, kolom, dan satuan dibaca dari tabel `criteria`, **tidak** di-*hardcode*.
* Tie-breaker ranking: skor turun, lalu `id` naik.
* Bobot preset dibaca dari `data/clean/preset_profiles.json`; **dilarang** mengetik ulang angka bobot manual.

### 2.2 Aturan Konsistensi AHP
* CR dihitung untuk setiap matriks (n=9, RI=1,45). **CR > 0,10** → peringatan; lanjut hanya dengan konfirmasi eksplisit pengguna.
* Validasi: elemen ∈ {1/9 … 9}, diagonal = 1, resiprokal otomatis.

### 2.3 Anti-Silent Fallback
* **DILARANG** mengembalikan data/ranking dummy bila query kosong atau error. Tampilkan *empty state* atau pesan error.
* Bila filter menyisakan < 2 alternatif, jangan jalankan TOPSIS.
* Nilai kosong (NaN) **dilarang** diisi diam-diam. Imputasi hanya lewat aturan terdokumentasi di notebook dan wajib meninggalkan penanda (contoh: `is_imputed`).

### 2.4 Transparansi
Setiap hasil dapat ditelusuri: matriks keputusan → normalisasi → terbobot → $A^+/A^-$ → $D^\pm$ → $C_i$. Hasil yang bergantung pada data imputasi harus diberi catatan di UI.

---

## 3. Aturan Data & Colab
* File mentah Kaggle tidak di-*commit*; cantumkan sumber (link) dan tanggal unduh.
* **Sumber kebenaran data: `data/clean/laptops_clean.csv`** (949 baris, 29 kolom). Jangan membangun ulang dari CSV mentah di aplikasi.
* **Peringatan data:** kolom `Refresh Rate` pada CSV mentah Kaggle rusak (hanya digit pertama). Jangan pernah memakai kolom mentah itu; gunakan `refresh_rate_hz` dari `laptops_clean.csv`. Detail di dokumen 05 §2.1.
* Konstanta: kurs **1 INR = Rp 190**, faktor storage **SSD 1,0 / non-SSD 0,6**. Perubahan wajib dicatat di dokumen 05 dan dijalankan ulang di Colab.
* Notebook harus bisa dijalankan ulang dari awal (*Restart & Run All*) dan memakai `assert` sebagai sanity check.

---

## 4. Aturan Lingkungan, Alat AI & Protokol `-9router`
* Mesin pengembang: Windows. `python` = stub Store (tidak dipakai); gunakan `py`. PHP, Composer, Docker **belum terpasang** (lihat dokumen 08).
* **DILARANG** menjalankan instalasi sistem (PHP/Laragon/dll.) tanpa persetujuan pengguna.
* **DILARANG** mengirim kunci API, `.env`, atau data pribadi ke layanan model eksternal. Dataset publik (Kaggle) boleh.
* Kunci API gateway/model hanya di `.env` atau konfigurasi global mesin, tidak di repositori atau `.agents/` yang di-commit.
* **DILARANG** menyalurkan sesi OAuth langganan (Antigravity/Claude/Copilot/dll.) ke proxy pihak ketiga. Gunakan hanya API key resmi atau tier gratis resmi.

### 4.1 Protokol Trigger Eksplisit `-9router` (Wajib Dipatuhi Agen)
* Apabila pesan/instruksi dari pengguna menyertakan kata kunci **`-9router`**, agen utama (Antigravity) **WAJIB** memanggil tool MCP `router9` (`router9_second_opinion` atau `router9_ask_model` melalui `d:\TUGAS\SEMESTER 7\SPK\tools\router9-mcp\index.js` dengan model default `smart-free`).
* Fungsi pemanggilan ini berlaku **secara menyeluruh (universal)** untuk meminta opini kedua (*second opinion*), diskusi desain/arsitektur, review kode, audit rumus matematika MCDM, translasi dwibahasa ID–EN, maupun penyusunan laporan.
* Agen utama harus menampilkan secara ringkas masukan/opini kedua dari model di 9Router kepada pengguna dan menggabungkannya dengan analisis agen utama sebelum atau saat mengeksekusi tugas.
* Apabila pengguna **tidak** menyertakan kata `-9router`, agen utama mengerjakan tugas secara langsung tanpa wajib memanggil 9Router (kecuali diminta kemudian).

---

## 5. Standar Kode
| Area | Standar |
|---|---|
| PHP | PSR-12, *Service class* untuk logika (bukan di Controller), FormRequest untuk validasi |
| Python | PEP 8, type hints, fungsi murni di `engine/` |
| UI | Mengikuti `02_DESIGN_SYSTEM` (token warna), dwibahasa lewat `__('key')`, tanpa string *hardcode* |
| Git | Commit kecil (`feat:`, `fix:`, `docs:`) |
| Test | AHP (contoh Saaty), TOPSIS (`topsis_fixture.json`), cross-check PHP↔Python |

---

## 6. Definition of Done (per Fitur)
1. Berfungsi dan lolos test.
2. Konsisten dengan design system dan dwibahasa.
3. Tidak melanggar aturan §2–§4.
4. Dokumen terkait (01/03/05/08) dan bagian laporan HTML diperbarui.
