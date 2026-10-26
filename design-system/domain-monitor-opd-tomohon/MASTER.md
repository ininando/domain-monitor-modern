# Design System — Monitoring Domain OPD Kota Tomohon (MASTER)

Disetujui pada Fase 0 (15 Oktober 2026). File ini adalah sumber kebenaran tampilan.
Override per halaman (jika ada) diletakkan di `pages/<nama-halaman>.md`.

## Gaya
Minimalism & Swiss Style — bersih, berbasis grid, banyak ruang kosong, kontras tinggi.
Kepadatan sedang, gerak halus (150–200ms ease-out), hormati `prefers-reduced-motion`.

## Token warna (didefinisikan di `resources/css/app.css` via `@theme`)
| Token | Terang | Gelap |
|---|---|---|
| primary | `#0369A1` (sky-700), hover `#075985` | `#38BDF8`, teks di atasnya `#082F49` |
| background | slate-50 | slate-950 |
| surface/card | white | slate-900 |
| text | slate-900 | slate-100 |
| text-muted | slate-600 | slate-400 |
| border | slate-200 | slate-800 |
| Normal (>30 hari) | emerald-700 di emerald-50 | emerald-300 di emerald-500/15 |
| Warning (8–30 hari) | amber-700 di amber-50 | amber-300 di amber-500/15 |
| Critical (≤7 / lewat) | red-700 di red-50 | red-300 di red-500/15 |

Badge status selalu = titik warna + teks. Jangan pernah hanya warna.

## Tipografi
Plus Jakarta Sans Variable (self-host, `@fontsource-variable/plus-jakarta-sans`).
- Judul halaman: 24px/32px bold (`text-2xl font-bold tracking-tight`)
- Judul section: 18px semibold · Judul card: 16px semibold
- Body: 14–16px regular, line-height 1.5 · Label/caption: 12–13px medium
- Angka statistik: 28–30px bold + `tabular-nums`

## Spacing & bentuk
- Skala 4px: 4/8/12/16/24/32/48. Padding konten 24–32px, lebar maks ~1280px.
- Radius: kontrol 10px (`rounded-[10px]`), card 16px (`rounded-2xl`), badge pill.
- Bayangan terang: `shadow-card` (2 lapis halus). Gelap: tanpa bayangan, pakai border.
- Focus ring: `ring-3 ring-primary/25` + border primer.

## Layout
- Sidebar 256px (buka) / 72px (collapse, ikon + tooltip), status di localStorage. `< lg` = drawer + overlay.
- Topbar: search, toggle tema, menu profil.
- Login: split-screen (panel branding kiri, form kanan); mobile hanya form.

## Komponen (`resources/views/components`)
button, input, select, textarea, badge-status, stat-card, table, modal-confirm, toast, empty-state, pagination, page-header.

## Chart
Chart.js. Bar diurutkan menurun. Donut maks 5 kategori + "Lainnya". Setiap chart punya tabel data.

## Ikon
Lucide (`<x-lucide-*>`), dekoratif pakai `aria-hidden`. Tombol ikon wajib `aria-label`. Tanpa emoji.

## Dark mode
Kelas `dark` pada `<html>` (custom variant Tailwind v4). Default ikut sistem; pilihan disimpan di `localStorage('theme')`. Skrip inline di `<head>` mencegah flash.
