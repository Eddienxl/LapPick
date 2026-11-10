# Data Dictionary — `laptops_clean.csv`

- **Jumlah Baris Bersih:** 949
- **Kurs Konversi:** 1 INR = Rp 190

| Kolom | Tipe | Peran SPK | Min | Median | Max |
|---|---|---|---|---|---|
| `id` | `int64` | Atribut Katalog / Filter | - | - | - |
| `name` | `object` | Atribut Katalog / Filter | - | - | - |
| `brand` | `object` | Atribut Katalog / Filter | - | - | - |
| `type_name` | `object` | Atribut Katalog / Filter | - | - | - |
| `os` | `object` | Atribut Katalog / Filter | - | - | - |
| `processor_brand` | `object` | Atribut Katalog / Filter | - | - | - |
| `cpu_name` | `object` | Atribut Katalog / Filter | - | - | - |
| `gpu_name` | `object` | Atribut Katalog / Filter | - | - | - |
| `ram_type` | `object` | Atribut Katalog / Filter | - | - | - |
| `ram_type_score` | `int64` | Atribut Katalog / Filter | - | - | - |
| `has_ssd` | `int64` | Atribut Katalog / Filter | - | - | - |
| `expandable_memory` | `int64` | Atribut Katalog / Filter | - | - | - |
| `touchscreen` | `int64` | Atribut Katalog / Filter | - | - | - |
| `dedicated_vram_gb` | `float64` | Atribut Katalog / Filter | - | - | - |
| `screen_inches` | `float64` | Atribut Katalog / Filter | - | - | - |
| `screen_resolution` | `object` | Atribut Katalog / Filter | - | - | - |
| `source_link` | `object` | Atribut Katalog / Filter | - | - | - |
| `price_inr` | `int64` | Atribut Katalog / Filter | - | - | - |
| `price_idr` | `int64` | C1 Harga (Cost) | 3038100.0 | 11398100.0 | 57378100.0 |
| `ram_gb` | `int64` | C2 RAM (Benefit) | 4.0 | 8.0 | 32.0 |
| `storage_gb` | `int64` | Atribut Katalog / Filter | - | - | - |
| `storage_effective_gb` | `float64` | C3 Storage Efektif (Benefit) | 19.2 | 512.0 | 2000.0 |
| `cpu_ranking` | `int64` | C4 Peringkat CPU (Cost - kecil lebih baik) | 4.0 | 108.0 | 907.0 |
| `gpu_benchmark` | `float64` | C5 Skor GPU (Benefit) | 1.0 | 17.5 | 233.0 |
| `battery_hours` | `float64` | C6 Daya Tahan Baterai (Benefit) | 4.0 | 4.5 | 5.0 |
| `weight_kg` | `float64` | C7 Bobot (Cost) | 0.78 | 1.65 | 2.9 |
| `user_rating` | `float64` | C8 Rating Pengguna (Benefit) | 1.8 | 4.3 | 5.0 |
| `refresh_rate_hz` | `int64` | C9 Refresh Rate Layar (Benefit) | 1.0 | 60.0 | 60.0 |
| `is_imputed` | `int64` | Atribut Katalog / Filter | - | - | - |