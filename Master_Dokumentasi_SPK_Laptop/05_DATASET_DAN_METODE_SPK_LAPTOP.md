# Dataset & Metodologi MCDM: LapPick

**Versi:** 0.3.0 (disinkronkan dengan hasil Colab 01 dan 02, 10 Nov 2026)

---

## 1. Dataset

* **Nama:** Laptop Ranked Specifications (2022)
* **Link:** https://www.kaggle.com/datasets/dhanushbommavaram/laptop-ranked-dataset
* **Lisensi:** CC BY-SA 4.0
* **Sumber:** Listing laptop Flipkart (India), Juni 2022. File: `Laptop ranked dataset.csv`.
* **Terverifikasi (Colab 01):** 984 baris × 27 kolom → **949 baris bersih** setelah 35 duplikat dibuang.
* **Kurs:** 1 INR = Rp 190 (konstanta tetap, asumsi dicantumkan di laporan).

### 1.1 Dataset Alternatif (tidak dipakai)
| Dataset | Catatan |
|---|---|
| https://www.kaggle.com/datasets/bhavikjikadara/brand-laptops-dataset | Lebih baru (Jan 2024) tetapi tanpa baterai dan bobot |
| https://www.kaggle.com/datasets/sumanbera19/laptop-price-dataset | Kolom belum diperiksa |
| https://www.kaggle.com/datasets/muhammetvarl/laptop-price | Tanpa baterai/rating, harga EUR (diganti) |

---

## 2. Hasil Pembersihan Data (Colab 01 — fakta terverifikasi)

| Parameter | Nilai |
|---|---|
| Baris awal | 984 |
| Duplikat dibuang | 35 |
| Baris invalid dibuang | 0 |
| **Baris akhir** | **949** |
| `user_rating` kosong | 277 (29,2%) → diimputasi median per `type_name` (median global 4,3), ditandai `is_imputed = 1` |
| Kolom kriteria lain kosong | 0 |
| Laptop tanpa SSD | 107 (faktor storage 0,6) |

**Distribusi katalog:** ASUS 284, Dell 183, Lenovo 178, HP 147, Acer 58, MSI 52, lainnya 47. Tipe: Thin and Light 511, Gaming 219, Notebook 157, 2-in-1 62.

### 2.1 Temuan Kualitas Data (wajib dipahami semua agen/pengembang)

| # | Temuan | Penanganan | Dampak |
|---|---|---|---|
| 1 | **Bug dataset asli:** `Refresh Rate` hanya menyimpan digit pertama (120/144/165→1, 240→2, 300/360→3, 90→9, 60→6). 151 baris terdampak. | Diekstrak ulang dari `name` dengan regex `(\d{2,3})\s*hz`; bila tidak ada → pemetaan {1→144, 2→240, 3→300, 6→60, 9→90}. | Tanpa perbaikan, laptop gaming 360 Hz dianggap 3 Hz. |
| 2 | Dua baris tidak memuat "Hz" di nama sehingga memakai **asumsi** pemetaan (1 baris bernilai 1 → 144 Hz; 1 baris bernilai 9 → 90 Hz). | Dicatat sebagai asumsi di laporan. | Dampak sangat kecil. |
| 3 | `battery_hours` hanya punya **3 nilai**: 4,0 (219), 4,5 (668), 5,0 (62). | Dipakai apa adanya. | Diskriminasi antar-laptop rendah; kriteria ini cenderung kurang membedakan. Dicatat sebagai keterbatasan. |
| 4 | `storage_gb` memuat nilai seperti 1256, 1512 (kombinasi SSD+HDD). | Dipakai apa adanya; faktor SSD/non-SSD per baris. | Asumsi faktor 0,6 untuk non-SSD bersifat subjektif. |
| 5 | `storage_effective_gb` minimum 19,2 GB (eMMC kecil × 0,6). | Dipakai apa adanya. | — |
| 6 | Harga INR 2022, pasar India. | Konversi kurs tetap. | Bukan harga toko Indonesia. |
| 7 | Rating diimputasi 29,2% | Flag `is_imputed` | Hasil yang bergantung pada C8 perlu diberi catatan. |

---

## 3. Preset Profil — Hasil AHP (Colab 02)

Matriks pairwise dibangun dari skor kepentingan 1–8 per kriteria (urutan C1..C9) dengan `a_ij = clamp(round(s_i − s_j + 1), 1, 9)`.

| Profil | Skor kepentingan [C1..C9] | λmax | CI | **CR** | Konsisten |
|---|---|---|---|---|---|
| Mahasiswa (`student`) | [8,5,4,4,2,6,7,4,1] | 9,2848 | 0,0356 | **0,0245** | ✔ |
| Gamer (`gamer`) | [3,6,5,7,8,2,1,4,7] | 9,3135 | 0,0392 | **0,0270** | ✔ |
| Kreator (`creator`) | [3,7,6,8,7,4,3,5,5] | 9,1772 | 0,0221 | **0,0153** | ✔ |
| Kantoran (`office`) | [6,5,4,6,2,8,7,6,2] | 9,2194 | 0,0274 | **0,0189** | ✔ |

### Bobot Hasil AHP
| Kriteria | student | gamer | creator | office |
|---|---|---|---|---|
| C1 Harga | **0,3030** | 0,0403 | 0,0308 | 0,1180 |
| C2 RAM | 0,0985 | 0,1235 | 0,1785 | 0,0752 |
| C3 Storage | 0,0626 | 0,0848 | 0,1155 | 0,0509 |
| C4 CPU | 0,0626 | 0,1841 | **0,2716** | 0,1180 |
| C5 GPU | 0,0295 | **0,2761** | 0,1785 | 0,0251 |
| C6 Baterai | 0,1469 | 0,0282 | 0,0471 | **0,2810** |
| C7 Bobot | 0,2130 | 0,0205 | 0,0308 | 0,1887 |
| C8 Rating | 0,0626 | 0,0585 | 0,0736 | 0,1180 |
| C9 Refresh | 0,0213 | 0,1841 | 0,0736 | 0,0251 |

Sumber kebenaran: `data/clean/preset_profiles.json` (jangan diketik ulang manual; seeder membaca file ini).

---

## 4. Hasil TOPSIS (Top-1 per Profil, 949 laptop)

| Profil | Top-1 | ID | Ci |
|---|---|---|---|
| Mahasiswa | HP 245 G8 (Ryzen 5 3500U, 8 GB, 1 TB, 1,0 kg) | 326 | 0,787045 |
| Gamer | Acer Predator Helios 300 i9 (RTX 3070, 300 Hz) | 240 | 0,721575 |
| Kreator | Acer Predator Helios 300 i9 (32 GB) | 240 | 0,769117 |
| Kantoran | HP Pavilion 13-be0208au (0,97 kg) | 395 | 0,722014 |

Pembagi normalisasi Euclidean (949 laptop): C1 463.166.494,1286 · C2 350,4283 · C3 21.266,9495 · C4 9.613,0173 · C5 1.796,9124 · C6 136,3121 · C7 53,0265 · C8 130,9000 · C9 2.361,3049.

---

## 5. Validasi Antar-Metode & Sensitivitas (Colab 02)

| Profil | Spearman TOPSIS–SAW | Spearman TOPSIS–WP | Irisan Top-10 (SAW / WP) |
|---|---|---|---|
| Mahasiswa | 0,6778 | 0,6485 | 0 / 5 |
| Gamer | 0,9676 | 0,9845 | 8 / 8 |
| Kreator | 0,9224 | 0,9859 | 8 / 8 |
| Kantoran | 0,3611 | 0,7744 | 4 / 4 |

**Sensitivitas** (bobot kriteria terbesar ±10% dan ±20%, 16 skenario): Top-1 bertahan di **15 dari 16**; satu pergeseran pada Kreator saat bobot CPU −20% (ID 155 menggantikan ID 240, selisih skor 0,0023), Top-5 tetap sama.

> [!NOTE]
> Penjelasan bahwa SAW terdistorsi oleh normalisasi `min/x` pada `cpu_ranking` adalah **hipotesis yang masuk akal secara matematis** tetapi belum diuji terpisah. Bila ingin dinyatakan sebagai fakta, uji dengan menukar normalisasi SAW pada Streamlit Lab.

---

## 6. Fixture untuk Uji Silang
`data/clean/topsis_fixture.json`: 10 laptop pertama (id 1–10), bobot profil Mahasiswa, berisi pembagi, $A^+$, $A^-$, dan ranking acuan (rank-1 pada sampel: id 6, Ci 0,72930396). Test PHPUnit wajib mencocokkan dengan toleransi 1e-6.

---

## 7. Rencana Validasi (Status)
| Uji | Status |
|---|---|
| CR preset ≤ 0,10 | ✔ |
| Sanity check profil | ✔ |
| Sensitivitas ±10/20% | ✔ |
| TOPSIS vs SAW vs WP | ✔ |
| Cross-check Python ↔ PHP | ☐ (menunggu Laravel) |
| Uji pengguna kecil 3–5 orang | ☐ |
