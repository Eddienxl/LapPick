# Design System: Standar Desain Antarmuka LapPick

**Versi:** 0.3.0 (keputusan UI/UX final)
**Status:** FINAL — dipilih pengguna pada 10 Nov 2026.
**Keputusan:** Gaya **Modern SaaS Dashboard (Opsi C)**, **toggle Light/Dark** (mode gelap memakai palet *Tech Spec Dark* Opsi B), antarmuka **dwibahasa (ID/EN)**.

---

## 1. Filosofi & Prinsip Desain
1. **Jelas sebelum cantik:** Angka, bobot, dan ranking harus mudah dibaca dan ditelusuri.
2. **Transparan:** Setiap rekomendasi punya tombol "Lihat perhitungan".
3. **Cepat dipahami:** Alur 3 langkah (Profil → Bobot → Hasil) dengan indikator progres.

---

## 2. Palet Warna & Token Semantik

| Token | Light | Dark |
|---|---|---|
| `--bg` | `#F8FAFC` | `#0F1117` |
| `--surface` | `#FFFFFF` | `#1A1D27` |
| `--border` | `#E2E8F0` | `#2A2F3E` |
| `--text` | `#0F172A` | `#E6E8EE` |
| `--text-muted` | `#64748B` | `#94A3B8` |
| `--primary` | `#4F46E5` (indigo) | `#8B5CF6` (ungu) |
| `--accent` | `#10B981` (emerald) | `#22D3EE` (cyan) |
| `--warning` | `#F59E0B` | `#FBBF24` |
| `--danger` | `#EF4444` | `#F87171` |

Aturan: komponen memakai **token**, bukan hex langsung. Kontras teks minimal WCAG AA (4.5:1).

---

## 3. Tipografi
| Peran | Font | Ukuran |
|---|---|---|
| Heading | Inter (600–700) | 24–32 px |
| Body | Inter (400–500) | 14–16 px |
| Angka/tabel | Inter dengan `tabular-nums` / JetBrains Mono | 13–14 px |

---

## 4. Theme Switcher
* Tailwind `darkMode: 'class'`; tombol toggle di navbar (ikon matahari/bulan).
* Preferensi disimpan di `localStorage`; default mengikuti `prefers-color-scheme`.
* Streamlit: `config.toml` dengan tema serupa (`primaryColor`, `backgroundColor`) dan mengikuti mode via parameter URL bila di-embed.

---

## 5. Dwibahasa (ID/EN)
* Laravel *localization*: `lang/id/*.php` dan `lang/en/*.php`; tombol bahasa di navbar, tersimpan di sesi/cookie.
* Semua teks UI lewat `__('key')`; **dilarang** *hardcode* string di Blade.
* Istilah teknis (AHP, TOPSIS, CR) tidak diterjemahkan; ada tooltip penjelasan di kedua bahasa.
* Format angka mengikuti locale (`Rp 12.500.000` vs `Rp 12,500,000`).
* Streamlit: kamus `i18n.py` dengan pilihan bahasa di sidebar.

---

## 6. Komponen Kunci

### 6.1 Navbar
Logo, menu (Katalog, Rekomendasi, Bandingkan, Riwayat, Lab), toggle bahasa dan tema, menu pengguna.

### 6.2 Wizard Rekomendasi (3 Langkah)
1. **Profil & Filter:** kartu preset (Mahasiswa, Gamer, Kreator, Kantoran) + anggaran, brand, tipe.
2. **Bobot AHP:** tampilan slider perbandingan berpasangan, bar bobot langsung, *badge* CR (hijau ≤ 0,10, merah > 0,10 + saran perbaikan).
3. **Hasil:** Top-3 kartu + tabel ranking lengkap.

### 6.3 Kartu Rekomendasi
Gambar/ikon, nama, harga, *badge* **Match %** (dari $C_i$), 3 *chip* keunggulan, mini radar chart, tombol "Bandingkan" dan "Lihat perhitungan".

### 6.4 Tabel Ranking
Kolom: peringkat, laptop, skor $C_i$, harga, kriteria utama; dapat diurutkan, difilter, dan diekspor.

### 6.5 Panel Perhitungan (Accordion)
Matriks keputusan → normalisasi → terbobot → $A^+/A^-$ → $D^\pm$ → $C_i$; angka mono-spasi.

### 6.6 Perbandingan
Maksimal 4 laptop berdampingan, pemenang tiap kriteria ditandai.

### 6.7 State
Loading skeleton, *empty state* ("Tidak ada laptop sesuai filter"), pesan error jelas (sesuai aturan anti-silent fallback).

---

## 7. Responsif
Breakpoint Tailwind `sm/md/lg`. Mobile: navigasi *hamburger*, kartu satu kolom, tabel *scroll* horizontal.

## 8. Aksesibilitas
Label form, fokus terlihat, tidak mengandalkan warna saja (ikon + teks pada badge CR), `aria-label` pada toggle.

