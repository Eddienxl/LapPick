# Diskusi UI/UX: Opsi Gaya Desain LapPick

> [!IMPORTANT]
> **KEPUTUSAN FINAL (10 Nov 2026):** Opsi C (Modern SaaS Dashboard) dengan toggle Light/Dark, mode gelap memakai palet Opsi B. Bahasa antarmuka **dwibahasa (ID/EN)**. Hasilnya dikunci di `02_DESIGN_SYSTEM_SPK_LAPTOP.md`. Dokumen ini hanya arsip diskusi.

> Dokumen diskusi. Setelah Anda memilih, hasilnya dikunci ke `02_DESIGN_SYSTEM_SPK_LAPTOP.md`.
> Link referensi adalah situs publik untuk inspirasi visual. Kondisi tampilan terkini bisa berubah.

## Opsi A — "Clean Product Showcase" (Terang, minimalis, gaya Apple)
* **Nuansa:** Putih lapang, tipografi besar, kartu produk menonjol, animasi halus.
* **Palet:** Background `#FFFFFF` / `#F5F5F7`, teks `#1D1D1F`, aksen biru `#0071E3`, sukses `#34C759`.
* **Referensi:** https://www.apple.com/mac/compare/ (halaman compare), https://www.apple.com/shop/buy-mac
* **Cocok untuk:** Kesan profesional dan mudah dibaca saat presentasi.
* **Risiko:** Terkesan generik jika tidak diberi identitas.

## Opsi B — "Tech Spec Dark" (Gelap, aksen neon, gaya gaming/hardware)
* **Nuansa:** Dark mode, kartu spesifikasi bergaya HUD, grafik radar menyala.
* **Palet:** Background `#0F1117`, kartu `#1A1D27`, teks `#E6E8EE`, aksen cyan `#22D3EE`, ungu `#8B5CF6`.
* **Referensi:** https://www.notebookcheck.net/ , https://rog.asus.com/laptops/ , https://pcpartpicker.com/
* **Cocok untuk:** Tema teknologi, serasi dengan Streamlit dark theme.
* **Risiko:** Kurang nyaman untuk tabel panjang di layar terang; perlu kontras dijaga.

## Opsi C — "Modern SaaS Dashboard" (Indigo, kartu lembut, gaya Linear/Vercel)
* **Nuansa:** Sidebar, kartu rounded, badge, tabel rapi, mendukung light/dark.
* **Palet:** Indigo `#4F46E5`, slate `#0F172A` / `#F8FAFC`, aksen emerald `#10B981`, peringatan amber `#F59E0B`.
* **Referensi:** https://linear.app/ , https://vercel.com/dashboard , https://tailwindui.com/templates/catalyst , https://ui.shadcn.com/examples/dashboard
* **Cocok untuk:** Aplikasi berorientasi data (wizard, tabel ranking, grafik), mudah dibangun dengan Tailwind.
* **Risiko:** Sangat umum; perlu 1–2 elemen khas agar berkarakter.

## Opsi D — "Friendly Marketplace" (Hangat, akrab, gaya e-commerce lokal)
* **Nuansa:** Kartu produk dengan badge, *chip* filter, label "Cocok untuk kamu 92%".
* **Palet:** Hijau `#03AC0E`, oranye `#FF6B00` sebagai aksen, background `#F7F8FA`, teks `#31353B`.
* **Referensi:** https://www.tokopedia.com/ , https://www.blibli.com/ , https://www.gsmarena.com/ (tabel spesifikasi)
* **Cocok untuk:** Pengguna awam, nuansa familiar bagi dosen/penguji.
* **Risiko:** Dapat terkesan seperti tiruan merek tertentu bila terlalu mirip; hindari meniru logo/layout persis.

## Perbandingan Singkat

| Aspek | A Showcase | B Dark | C SaaS | D Marketplace |
|---|---|---|---|---|
| Kesan profesional | ★★★★★ | ★★★★ | ★★★★★ | ★★★ |
| Cocok data/tabel/grafik | ★★★ | ★★★★ | ★★★★★ | ★★★★ |
| Kemudahan implementasi (Tailwind) | ★★★★ | ★★★★ | ★★★★★ | ★★★ |
| Keselarasan dengan Streamlit | ★★★ | ★★★★★ | ★★★★ | ★★★ |
| Karakter unik | ★★★ | ★★★★ | ★★ | ★★★ |

**Rekomendasi saya:** **Opsi C** sebagai basis (paling cocok untuk wizard + tabel + grafik, mudah konsisten dengan Streamlit), dipadukan **toggle light/dark** (dark mengambil palet Opsi B). Elemen khas: badge *"Match %"* dan grafik radar per laptop.

## Hal Lain yang Perlu Diputuskan
1. **Bahasa antarmuka:** Indonesia / Inggris / dwibahasa.
2. **Layout hasil:** kartu Top-3 + tabel (rekomendasi) atau hanya tabel.
3. **Mode input bobot:** preset-saja, atau preset + mode kustom pairwise (rekomendasi: keduanya).
4. **Streamlit:** di-embed lewat iframe atau menu terpisah yang terhubung via tautan.
5. **Nama produk & logo:** tetap `LapPick` atau ganti.

