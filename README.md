# Aplikasi IKPA PHP

Rebuild web IKPA dari Google Sites menjadi aplikasi web PHP dengan SQLite. Halaman publik mengikuti struktur portal IKPA, sedangkan login dipakai untuk modul internal target kinerja, capaian, evaluasi, dan cetak dokumen.

## Kebutuhan

- PHP 8 atau lebih baru
- Ekstensi PHP `pdo_sqlite`

Di komputer ini PHP sudah tersedia dari XAMPP dan `pdo_sqlite` sudah aktif.

## Menjalankan

```bash
php -S 127.0.0.1:8000 -t .
```

Buka:

```text
http://127.0.0.1:8000
```

Halaman utama publik:

```text
http://127.0.0.1:8000/index.php?page=beranda
```

Login internal:

```text
http://127.0.0.1:8000/index.php?page=login
```

## Akun Awal

| Username | Password | Role |
| --- | --- | --- |
| admin | admin123 | Administrator |
| panmudbanding | 123456 | Panmud Banding |
| panmudhukum | 123456 | Panmud Hukum |
| turt | 123456 | Kasubag Tata Usaha dan Rumah Tangga |
| kepegawaian | 123456 | Kasubag Kepegawaian & TI |
| keuangan | 123456 | Kasubag Keuangan & Pelaporan |
| perencanaan | 123456 | Kasubag Perencanaan Program & Anggaran |
| satkerhukum | 123456 | Panmud Hukum Satker PA |
| satkerptip | 123456 | Kasubag PTIP Satker PA |

## Modul

- Portal publik IKPA seperti Google Sites
- Beranda, Revisi, Notifikasi, Program Kerja & SOP
- Penyusunan Anggaran, Baseline, Pagu Indikatif, Pagu Definitif, ABT
- Hibah, Manajemen Risiko, SAKIP, Evaluasi AKIP
- e-Monev Bappenas, Monev Capaian Kinerja, Tugas Fungsi, Squad, Pojok Baca
- Login role
- Menu Primer, Skunder, Tertier, Korelasi
- Input Target Kinerja
- Cetak Perjanjian Kinerja
- Cetak Rencana Aksi
- Cetak RKT & RKA
- Hitung Capaian Kinerja
- Evaluasi Kinerja

Database dibuat otomatis di `data/ikpa.sqlite`.
