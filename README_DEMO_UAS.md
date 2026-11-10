# Panduan Demo Cepat UAS (< 2 Menit) — LapPick SPK

**Nama:** Ahmad Fadli Pratama (`NIM. 12350111030`)  
**Mata Kuliah:** Sistem Pendukung Keputusan (SPK) — Teknik Informatika UIN Suska Riau  
**Topik:** SPK Pemilihan Laptop Terbaik untuk Kebutuhan Pengguna (**Hibrida AHP + TOPSIS** + Pembanding **SAW & WP**)

---

## 1. Menjalankan Sistem Secara Instan (1-Klik)

Klik ganda berkas **[`start_lappick_demo.bat`](file:///d:/TUGAS/SEMESTER%207/SPK/start_lappick_demo.bat)** (atau jalankan melalui terminal):

1. **Aplikasi Web Utama (Laravel 13 + PHP 8.3 + SQLite):** `http://127.0.0.1:8000`
2. **Analysis Lab (Streamlit + NumPy + Plotly):** `http://localhost:8501`
3. **Laporan Akademis Live HTML:** [`Laporan_UAS_SPK_LapPick.html`](file:///d:/TUGAS/SEMESTER%207/SPK/Laporan_UAS_SPK_LapPick.html)

---

## 2. Alur Presentasi Langsung (< 2 Menit)

| Detik | Halaman / Menu | Aksi Demo | Pesan Kunci untuk Dosen Penguji |
|---|---|---|---|
| **0:00–0:20** | **Beranda Laravel** (`/`) | Tunjukkan tombol **ID \| EN**, **Light / Dark Mode**, dan panel **Juara Profil Preset** | Aplikasi dwibahasa ID/EN & Light/Dark (Opsi C + B), mengevaluasi **949 laptop bersih** dengan **9 kriteria (C1–C9)**. |
| **0:20–0:50** | **Wizard SPK** (`/wizard`) | Klik preset **Mahasiswa** (`CR = 0.0245`) → ganti ke **Gamer** (`CR = 0.0270`) | Bobot AHP tervalidasi `CR ≤ 0.10`. Mahasiswa memilih **#326 HP 245 G8** (Rp 7,6 jt, 1,00 kg); Gamer memilih **#240 Acer Predator Helios 300 300Hz** (GPU 154,0 pts). |
| **0:50–1:15** | **Transparansi & Guardrail** | Buka akordeon **Transparansi Matematis**; lalu masuk tab **Matriks Pairwise 9×9** & ubah satu nilai jadi kontradiktif | Pembuktian white-box matriks $X, R, Y, A^+/A^-, D^+/D^-, C_i$ serta *guardrail* otomatis bila $CR > 0.10$ beserta identifikasi 3 pasangan menyimpang. |
| **1:15–1:35** | **Compare & Katalog** | Buka `/compare?ids=326,240,499` dan `/catalog` | Komparasi *side-by-side* 2–4 laptop dengan *highlight* pemenang per kriteria, plus penanda transparansi *Imputed Rating* pada 277 baris. |
| **1:35–2:00** | **Streamlit Lab** (`localhost:8501`) | Tunjukkan **Tab 3 (Komparasi TOPSIS vs SAW vs WP & Sensitivitas)** & **Tab 4 (Audit $10^{-6}$)** | Membuktikan mengapa TOPSIS dipilih (Spearman $\rho = 0.9859$ vs WP pada kreator, bebas distorsi $min/x$ SAW) & stabilitas Top-1 **35/36 skenario**. |

---

## 3. Verifikasi & Tanya-Jawab Teknis (Cheat-Sheet Dosen)

Perintah pengujian siap jalan untuk membuktikan kebenaran matematis di depan penguji:

```powershell
# 1. Uji Kontrak PHPUnit & Rute Web Laravel (5 tests, 164 assertions)
cd "d:\TUGAS\SEMESTER 7\SPK\lappick-laravel"
php artisan test

# 2. Uji Kontrak Engine Python Streamlit
py "d:\TUGAS\SEMESTER 7\SPK\lab-streamlit\tests\test_engine_contract.py"

# 3. Uji Paritas Lintas-Engine Python NumPy vs PHP 8.3 pada seluruh 949 Laptop x 4 Profil
py "d:\TUGAS\SEMESTER 7\SPK\tools\verify_cross_engine.py"
```

### Kemungkinan Pertanyaan Penguji & Jawaban Singkat:
1. **"Kenapa memakai AHP + TOPSIS sekaligus?"**
   AHP unggul untuk menyusun **bobot kriteria** via perbandingan berpasangan 1–9 lengkap dengan uji konsistensi ($CR \le 0.10$), sedangkan TOPSIS sangat efisien untuk meranking **ratusan alternatif ($949$ laptop)** tanpa harus membandingkan $\binom{949}{2}$ pasangan alternatif secara manual.
2. **"Apa nilai $RI$ untuk 9 kriteria?"**
   Mengikuti tabel Saaty (1980), untuk $n = 9$, nilai **Random Index ($RI$) = $1.45$**, dan syarat konsisten mutlak adalah $CR = CI / RI \le 0.10$.
3. **"Mengapa tidak memakai metode SAW saja?"**
   Terbukti pada eksperimen Colab 02 & Tab 3 Streamlit: kolom `cpu_ranking` (`C4`) memiliki rasio ekstrem (peringkat #4 vs #907), sehingga normalisasi SAW $\min / x_{ij}$ menyebabkan penurunan hiperbolik ekstrem; normalisasi vektor Euclidean TOPSIS dan WP ($\rho = 0.9845\text{–}0.9859$) menghasilkan peringkat jauh lebih stabil.
4. **"Dari mana asal angka Refresh Rate & Rating yang hilang di dataset?"**
   Pada `notebooks/01_data_cleaning_feature_eng.ipynb`, 151 baris yang terpotong digitnya di kolom mentah dipulihkan secara forensik via regex dari nama produk asli (`name`), dan nilai `user_rating` yang kosong pada 277 baris diisi memakai median terstratifikasi per tipe laptop disertai flag transparan **`is_imputed = 1`**.
5. **"Apakah perhitungan di Laravel dan Python identik?"**
   Ya — terverifikasi melalui `tools/verify_cross_engine.py` pada seluruh **949 laptop $\times$ 4 profil**: urutan peringkat **949/949 identik** (termasuk *tie-breaker* `id ASC`), selisih bobot $\le 5.55 \times 10^{-17}$, selisih skor $C_i \le 2.05 \times 10^{-15}$, dengan waktu eksekusi **5,5–7,6 ms** di PHP.

