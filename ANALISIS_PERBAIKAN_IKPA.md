# Analisis Kekurangan dan Rekomendasi Perbaikan Aplikasi IKPA

## Ringkasan

Aplikasi IKPA saat ini sudah berbentuk prototype berbasis PHP dan SQLite. Aplikasi sudah memiliki portal publik, login role, input target kinerja, hitung capaian sederhana, evaluasi kinerja, serta cetak dasar untuk PK, Renaksi, dan RKT/RKA.

Namun, berdasarkan konsep pada dokumen `KONSEP IKPA KU.pdf`, aplikasi ini belum sepenuhnya memenuhi kebutuhan operasional. Kondisi saat ini lebih dekat ke portal informasi dan input data sederhana, belum menjadi sistem monitoring, evaluasi, cetak dokumen, koordinasi, dan integrasi data yang lengkap.

## Kondisi Aplikasi Saat Ini

- Sudah tersedia halaman publik IKPA.
- Sudah tersedia login untuk beberapa role pengguna.
- Sudah tersedia menu primer, sekunder, tersier, dan korelasi.
- Sudah tersedia input Target Kinerja.
- Sudah tersedia perhitungan capaian kinerja sederhana.
- Sudah tersedia form evaluasi kinerja sederhana.
- Sudah tersedia cetak dasar Perjanjian Kinerja, Rencana Aksi, dan RKT/RKA.
- Banyak konten publik masih memakai link atau iframe Google Drive/Form.

## Kekurangan Utama

### 1. Banyak Modul Masih Berupa Placeholder

Menu aplikasi sudah mengikuti konsep, tetapi banyak modul belum memiliki fungsi nyata. Beberapa menu hanya diarahkan ke halaman informasi umum.

Contoh modul yang belum lengkap:

- Renstra
- IKU
- RKA-KL dan Revisi
- E-Monev Bappenas
- Laporan Kinerja
- Manajemen Risiko
- Hibah dan MoU
- Diagram Hasil Capaian Kinerja
- SOP
- Regulasi
- Artikel
- LHE PA
- Upload TOR/KAK ABT/Baseline
- Tupoksi dan Tim

### 2. Manajemen Pengguna Belum Lengkap

Konsep membagi pengguna berdasarkan jabatan dan tanggung jawab, seperti:

- Panmud Banding
- Panmud Hukum
- Kasubag Tata Usaha dan Rumah Tangga
- Kasubag Kepegawaian dan TI
- Kasubag Keuangan dan Pelaporan
- Kasubag Perencanaan Program dan Anggaran
- Panmud Hukum Satker PA
- Kasubag PTIP Satker PA

Saat ini aplikasi hanya menyimpan username, password, nama, role, dan unit. Belum ada pembatasan akses yang detail berdasarkan tugas masing-masing role.

Kekurangan yang perlu diperbaiki:

- Belum ada role-permission yang jelas.
- Belum ada hak akses khusus untuk monitoring seluruh user.
- Belum ada fitur editing lintas user oleh Kasubag Perencanaan.
- Belum ada approval atau validasi data.
- Belum ada audit aktivitas pengguna.

### 3. Keamanan Login Masih Lemah

Password masih disimpan dalam bentuk teks biasa, misalnya `admin123` dan `123456`. Ini tidak aman untuk aplikasi yang akan digunakan secara nyata.

Kekurangan keamanan:

- Password belum di-hash.
- Belum ada proteksi CSRF pada form.
- Belum ada pembatasan percobaan login.
- Belum ada reset password.
- Belum ada audit login.
- Belum ada pengaturan session timeout.

### 4. Struktur Data Target Kinerja Masih Terlalu Sederhana

Konsep membutuhkan data target yang terhubung dengan IKU, Renstra, hasil rapat, benchmarking, DIPA, dan realisasi triwulan. Saat ini data target hanya menyimpan sasaran, indikator, target, DIPA 01, DIPA 04, dan realisasi TW1 sampai TW4.

Data yang belum tersedia:

- Kode sasaran
- Kode indikator
- Satuan indikator
- Jenis indikator
- Sumber data
- Relasi ke IKU
- Relasi ke Renstra
- Target per triwulan
- Bobot indikator
- Dokumen pendukung
- Status validasi data

### 5. Perhitungan Capaian Belum Matang

Saat ini capaian dihitung dengan rumus:

```text
realisasi / target x 100
```

Rumus ini belum cukup untuk semua jenis indikator. Dalam praktik pengukuran kinerja, ada indikator yang targetnya semakin tinggi semakin baik, tetapi ada juga yang semakin rendah semakin baik.

Kekurangan perhitungan:

- Belum ada tipe indikator.
- Belum ada target per triwulan.
- Belum ada bobot indikator.
- Belum ada batas maksimum capaian.
- Belum ada akumulasi capaian per unit.
- Belum ada perbandingan otomatis antar triwulan.

### 6. Evaluasi Kinerja Belum Otomatis

Konsep menyebutkan bahwa setiap kenaikan atau penurunan capaian per triwulan wajib diberi penjelasan keberhasilan atau ketidakberhasilan.

Saat ini evaluasi masih diisi manual tanpa sistem yang memeriksa apakah capaian naik atau turun.

Kekurangan evaluasi:

- Belum ada deteksi otomatis capaian naik/turun.
- Belum ada kewajiban narasi berdasarkan perubahan capaian.
- Belum ada status evaluasi, misalnya draft, diajukan, direview, disetujui.
- Belum ada catatan reviewer.
- Belum ada rekap evaluasi seluruh user.

### 7. Cetak Dokumen Belum Sesuai Format Konsep

Aplikasi sudah menyediakan cetak PK, Renaksi, dan RKT/RKA, tetapi masih sangat dasar.

Kekurangan pada cetak Perjanjian Kinerja:

- Belum ada nomor surat.
- Belum ada identitas lengkap Pihak I dan Pihak II.
- Belum ada tanggal dan lokasi.
- Belum ada jabatan penandatangan.
- Belum ada format lampiran resmi.

Kekurangan pada cetak Rencana Aksi:

- Aksi/kegiatan masih teks umum.
- Jadwal pelaksanaan belum bisa diinput.
- Keluaran belum bisa diinput.
- Program, kegiatan, dan dana belum detail.
- Target triwulan masih dibagi rata dari target tahunan.

Kekurangan pada cetak RKT/RKA:

- Belum ada nomor surat.
- Belum ada tanggal surat.
- Belum ada tanda tangan pimpinan.
- Belum ada upload dokumen RKA-KL/DIPA 01 dan 04.
- Belum ada ekspor PDF resmi.

### 8. Belum Ada Upload dan Manajemen Dokumen Internal

Konsep membutuhkan banyak dokumen yang wajib tampil dan dapat dikelola dalam aplikasi.

Dokumen yang perlu dikelola:

- Program Kerja
- Renstra
- IKU
- Renaksi
- RKA-KL dan revisi
- Laporan Kinerja
- Manajemen Risiko
- Hibah dan MoU
- SOP
- Regulasi
- Artikel
- Info dan pengumuman
- LHE PA
- TOR/KAK ABT/Baseline
- Tupoksi dan Tim

Saat ini sebagian besar masih berupa konten statis atau link Google Drive. Belum ada sistem upload, kategori dokumen, versi dokumen, status validasi, atau pencarian dokumen.

### 9. Integrasi Eksternal Belum Berjalan

Konsep menyebutkan integrasi dengan beberapa aplikasi penting:

- SIPP
- E-SEMAR
- KOMDANAS
- MY ASN
- SAKTI
- OMSPAN
- SATUDJA
- E-BIMA
- E-SADEWA
- Survei Badilag

Saat ini aplikasi belum benar-benar mengambil, menyimpan, atau menyinkronkan data dari aplikasi tersebut. Yang tersedia baru berupa link atau embed.

### 10. Dashboard Monitoring Belum Lengkap

Kasubag Perencanaan Program dan Anggaran pada konsep harus bisa memonitor seluruh data capaian kinerja dari semua user.

Kekurangan dashboard:

- Belum ada rekap seluruh user.
- Belum ada filter berdasarkan unit, role, tahun, dan triwulan.
- Belum ada grafik capaian.
- Belum ada status pengisian data.
- Belum ada indikator user yang belum mengisi data.
- Belum ada fitur cetak rekap triwulan untuk dikirim ke Badan Pengawasan MA RI.

### 11. Kualitas Teknis Perlu Ditingkatkan

Beberapa bagian aplikasi masih perlu dirapikan sebelum siap digunakan secara serius.

Catatan teknis:

- Belum ada migration database.
- Belum ada konfigurasi environment.
- Belum ada backup database.
- Belum ada logging error.
- Belum ada test otomatis.
- Masih banyak style inline di halaman.
- Ada file halaman yang sangat besar karena menyimpan gambar base64 langsung di HTML.
- Belum ada struktur folder upload dokumen.
- Belum ada dokumentasi teknis instalasi dan deployment yang lengkap.

## Prioritas Perbaikan

### Prioritas 1: Keamanan dan Fondasi Data

- Hash password menggunakan `password_hash`.
- Ubah login agar memakai `password_verify`.
- Tambahkan proteksi CSRF pada semua form POST.
- Tambahkan role-permission.
- Tambahkan audit log aktivitas pengguna.
- Rapikan struktur database untuk target, indikator, realisasi, evaluasi, dokumen, dan user.

### Prioritas 2: Modul Inti Sesuai Konsep

- Lengkapi input Target Kinerja.
- Pisahkan input target tahunan dan target triwulan.
- Tambahkan input realisasi triwulan.
- Tambahkan hitung capaian berdasarkan tipe indikator.
- Tambahkan evaluasi wajib saat capaian naik atau turun.
- Tambahkan monitoring seluruh user untuk role Perencanaan.
- Tambahkan status validasi data.

### Prioritas 3: Cetak dan Laporan

- Lengkapi format Perjanjian Kinerja.
- Lengkapi format Rencana Aksi.
- Lengkapi format RKT dan RKA.
- Tambahkan ekspor PDF.
- Tambahkan ekspor Excel.
- Tambahkan rekap triwulan seluruh user.
- Tambahkan dashboard grafik capaian.

### Prioritas 4: Dokumen dan Portal Referensi

- Tambahkan upload dokumen internal.
- Tambahkan kategori dokumen.
- Tambahkan versi dokumen.
- Tambahkan pencarian dokumen.
- Tambahkan status dokumen aktif/nonaktif.
- Kurangi ketergantungan pada Google Drive jika data perlu disimpan resmi di aplikasi.

### Prioritas 5: Integrasi Eksternal

- Mulai dari impor manual Excel/CSV jika API belum tersedia.
- Simpan sumber data setiap realisasi.
- Siapkan tabel mapping aplikasi eksternal.
- Buat jadwal sinkronisasi jika akses API tersedia.
- Prioritaskan integrasi berdasarkan kebutuhan paling penting, misalnya SAKTI, OMSPAN, SIPP, dan Survei Badilag.

## Rekomendasi Roadmap Pengembangan

### Tahap 1: Prototype Layak Uji

Target tahap ini adalah membuat aplikasi aman dan bisa diuji oleh user internal.

- Perbaiki login dan password.
- Buat permission role.
- Lengkapi target, realisasi, capaian, dan evaluasi.
- Buat dashboard status pengisian data.

### Tahap 2: Sistem Monitoring Triwulan

Target tahap ini adalah mendukung proses monitoring dan evaluasi per triwulan.

- Tambahkan perbandingan capaian antar triwulan.
- Wajibkan narasi keberhasilan atau ketidakberhasilan.
- Tambahkan rekap seluruh user.
- Tambahkan cetak laporan triwulan.

### Tahap 3: Dokumen Resmi dan Arsip

Target tahap ini adalah membuat aplikasi dapat menghasilkan dokumen resmi.

- Lengkapi format PK, Renaksi, RKT, dan RKA.
- Tambahkan ekspor PDF.
- Tambahkan upload dokumen pendukung.
- Tambahkan arsip dokumen per tahun.

### Tahap 4: Integrasi Data

Target tahap ini adalah mengurangi input manual.

- Tambahkan impor data dari Excel/CSV.
- Siapkan integrasi dengan aplikasi eksternal bila akses tersedia.
- Buat mapping sumber data per indikator.
- Tambahkan validasi data berdasarkan sumber.

## Kesimpulan

Aplikasi IKPA saat ini sudah cukup baik sebagai awal pengembangan dan demo konsep. Akan tetapi, aplikasi belum siap disebut sebagai sistem IKPA operasional penuh karena masih banyak modul yang belum berfungsi, keamanan login belum aman, struktur data masih sederhana, evaluasi belum otomatis, cetak dokumen belum lengkap, dan integrasi eksternal belum berjalan.

Fokus perbaikan paling penting adalah memperkuat fondasi keamanan, membangun role-permission, melengkapi modul target-realisasi-capaian-evaluasi, serta membuat dashboard monitoring seluruh user sesuai peran Kasubag Perencanaan Program dan Anggaran.
