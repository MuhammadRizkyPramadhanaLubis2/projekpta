<?php
declare(strict_types=1);

require_once __DIR__ . '/app/database.php';

$pdo = db();
$tahun = (int) date('Y');
$adminId = 1; // Assuming 1 is Admin based on previous context

$dir = __DIR__ . '/dummy_data';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// ==========================================
// 1. Data Hak Akses dan Beban Kerja
// ==========================================
$csv1 = fopen($dir . '/01_user_role_matrix.csv', 'w');
fputcsv($csv1, ['Nama_Role', 'Ruang_Lingkup', 'Daftar_Menu_Yang_Diizinkan', 'Tugas_Utama_Checklist', 'Output_Wajib_Sistem']);
$roles = [
    ['Admin', 'PTA Medan', 'Semua Menu', 'Manajemen User, Pengaturan Sistem', 'Laporan Sistem'],
    ['Ketua', 'PTA Medan', 'Dashboard, Rekap Capaian, Monitoring', 'Memonitoring Kinerja, Validasi Dokumen', 'Lembar Pengesahan'],
    ['Panitera', 'Kepaniteraan', 'Dashboard, Target Kinerja, Capaian', 'Evaluasi Kinerja Kepaniteraan, Laporan Perkara', 'Laporan Kinerja Kepaniteraan'],
    ['Sekretaris', 'Kesekretariatan', 'Dashboard, Target Kinerja, Capaian', 'Evaluasi Anggaran, Laporan DIPA', 'Laporan Penyerapan Anggaran'],
    ['Hakim Tinggi Pengawas', 'PA se-Sumut', 'Monitoring Target, Rekap Capaian', 'Pengawasan PA, Review IKU', 'Laporan Hatiwasda']
];
foreach ($roles as $row) fputcsv($csv1, $row);
fclose($csv1);

// ==========================================
// Data Indikator (Master Kinerja)
// ==========================================
$indikatorData = [
    ['sas' => 'Meningkatnya Penyelesaian Perkara', 'iku' => 'IKU-01', 'nama' => 'Persentase Perkara yang Diselesaikan Tepat Waktu', 'target' => 100, 'satuan' => '%', 'pj' => 'Kepaniteraan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Kualitas Putusan', 'iku' => 'IKU-02', 'nama' => 'Persentase Putusan yang Tidak Ada Kesalahan Administrasi', 'target' => 100, 'satuan' => '%', 'pj' => 'Kepaniteraan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Akses Peradilan', 'iku' => 'IKU-03', 'nama' => 'Persentase Perkara e-Court', 'target' => 50, 'satuan' => '%', 'pj' => 'Kepaniteraan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Akses Peradilan', 'iku' => 'IKU-04', 'nama' => 'Persentase Keberhasilan Mediasi', 'target' => 30, 'satuan' => '%', 'pj' => 'Kepaniteraan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Kualitas Layanan Publik', 'iku' => 'IKU-05', 'nama' => 'Indeks Kepuasan Masyarakat (IKM)', 'target' => 85, 'satuan' => 'Indeks', 'pj' => 'Kesekretariatan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Integritas Aparatur', 'iku' => 'IKU-06', 'nama' => 'Indeks Persepsi Anti Korupsi (IPAK)', 'target' => 85, 'satuan' => 'Indeks', 'pj' => 'Kesekretariatan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Akuntabilitas Kinerja', 'iku' => 'IKU-07', 'nama' => 'Nilai AKIP', 'target' => 80, 'satuan' => 'Nilai', 'pj' => 'Kesekretariatan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Tata Kelola Keuangan', 'iku' => 'IKU-08', 'nama' => 'Persentase Serapan Anggaran DIPA 01', 'target' => 100, 'satuan' => '%', 'pj' => 'Kesekretariatan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Tata Kelola Keuangan', 'iku' => 'IKU-09', 'nama' => 'Persentase Serapan Anggaran DIPA 04', 'target' => 100, 'satuan' => '%', 'pj' => 'Kesekretariatan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Kedisiplinan Pegawai', 'iku' => 'IKU-10', 'nama' => 'Persentase Kehadiran Tepat Waktu', 'target' => 95, 'satuan' => '%', 'pj' => 'Kesekretariatan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Tertib Administrasi', 'iku' => 'IKU-11', 'nama' => 'Persentase Berkas Kasasi Diajukan Tepat Waktu', 'target' => 100, 'satuan' => '%', 'pj' => 'Kepaniteraan', 'tipe' => 'MAX'],
    ['sas' => 'Meningkatnya Penanganan Pengaduan', 'iku' => 'IKU-12', 'nama' => 'Persentase Pengaduan yang Ditindaklanjuti', 'target' => 100, 'satuan' => '%', 'pj' => 'Kesekretariatan', 'tipe' => 'MAX'],
    ['sas' => 'Peningkatan Kompetensi Aparatur', 'iku' => 'IKU-13', 'nama' => 'Jumlah Pegawai Mengikuti Diklat', 'target' => 20, 'satuan' => 'Orang', 'pj' => 'Kesekretariatan', 'tipe' => 'MAX'],
    ['sas' => 'Peningkatan Kualitas Sarpras', 'iku' => 'IKU-14', 'nama' => 'Persentase Pemeliharaan Gedung Kantor', 'target' => 100, 'satuan' => '%', 'pj' => 'Kesekretariatan', 'tipe' => 'MAX'],
    ['sas' => 'Kepatuhan Pelaporan LHKPN', 'iku' => 'IKU-15', 'nama' => 'Persentase Kepatuhan Pelaporan LHKPN', 'target' => 100, 'satuan' => '%', 'pj' => 'Kesekretariatan', 'tipe' => 'MAX'],
    ['sas' => 'Efisiensi Waktu Pelayanan', 'iku' => 'IKU-16', 'nama' => 'Rata-rata Waktu Tunggu Sidang (Minimalisir)', 'target' => 30, 'satuan' => 'Menit', 'pj' => 'Kepaniteraan', 'tipe' => 'MIN'],
    ['sas' => 'Optimalisasi Penerimaan Negara', 'iku' => 'IKU-17', 'nama' => 'Persentase Penyetoran PNBP Tepat Waktu', 'target' => 100, 'satuan' => '%', 'pj' => 'Kepaniteraan', 'tipe' => 'MAX'],
    ['sas' => 'Inovasi Pelayanan', 'iku' => 'IKU-18', 'nama' => 'Jumlah Inovasi Layanan Publik Diimplementasikan', 'target' => 2, 'satuan' => 'Inovasi', 'pj' => 'Kepaniteraan', 'tipe' => 'MAX'],
    ['sas' => 'Penyelesaian Sisa Perkara', 'iku' => 'IKU-19', 'nama' => 'Persentase Sisa Perkara Diselesaikan', 'target' => 100, 'satuan' => '%', 'pj' => 'Kepaniteraan', 'tipe' => 'MAX'],
    ['sas' => 'Kualitas Data SIPP', 'iku' => 'IKU-20', 'nama' => 'Persentase Kelengkapan Data SIPP', 'target' => 100, 'satuan' => '%', 'pj' => 'Kepaniteraan', 'tipe' => 'MAX']
];

// ==========================================
// 2. Data Master Kinerja CSV
// ==========================================
$csv2 = fopen($dir . '/02_master_kinerja.csv', 'w');
fputcsv($csv2, ['Tahun_Anggaran', 'Kode_Sasaran_Strategis', 'Kode_IKU', 'Nama_Indikator_Kinerja', 'Target_Tahunan', 'Satuan', 'Penanggung_Jawab_Bidang']);
foreach ($indikatorData as $ind) {
    fputcsv($csv2, [$tahun, $ind['sas'], $ind['iku'], $ind['nama'], $ind['target'], $ind['satuan'], $ind['pj']]);
}
fclose($csv2);

// ==========================================
// 3. Data Uji Formula / Perumusan CSV & DB INJECT
// ==========================================
// Clear DB first
$pdo->exec("DELETE FROM target_kinerja WHERE tahun = $tahun");

$csv3 = fopen($dir . '/03_uji_formula.csv', 'w');
fputcsv($csv3, ['Periode', 'Indikator_IKPA', 'Target_Angka', 'Realisasi_Angka', 'Rumus_Perhitungan_Baku', 'Hasil_Kalkulasi_Manual_Atasan', 'Status_Capaian']);

$stmt = $pdo->prepare("INSERT INTO target_kinerja 
    (tahun, unit, sasaran, indikator, tipe_indikator, satuan, bobot, target, target_tw1, target_tw2, target_tw3, target_tw4, real_tw1, real_tw2, real_tw3, real_tw4, user_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

function getStatus($capaian) {
    if ($capaian >= 100) return 'Sangat Baik';
    if ($capaian >= 90) return 'Baik';
    if ($capaian >= 75) return 'Cukup';
    return 'Kurang';
}

$allUsers = $pdo->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
if (empty($allUsers)) {
    $allUsers = [1];
}

foreach ($allUsers as $uid) {
    foreach ($indikatorData as $ind) {
        // Generate logical random realization for each quarter
        $tw1_real = round($ind['target'] * (mt_rand(75, 105) / 100), 2);
        $tw2_real = round($ind['target'] * (mt_rand(80, 110) / 100), 2);
        $tw3_real = round($ind['target'] * (mt_rand(85, 115) / 100), 2);
        $tw4_real = round($ind['target'] * (mt_rand(90, 120) / 100), 2);
        
        // Calculate Capaian
        $calcTw1 = $ind['tipe'] === 'MAX' ? ($tw1_real / $ind['target']) * 100 : ($ind['target'] / $tw1_real) * 100;
        $calcTw2 = $ind['tipe'] === 'MAX' ? ($tw2_real / $ind['target']) * 100 : ($ind['target'] / $tw2_real) * 100;
        $calcTw3 = $ind['tipe'] === 'MAX' ? ($tw3_real / $ind['target']) * 100 : ($ind['target'] / $tw3_real) * 100;
        $calcTw4 = $ind['tipe'] === 'MAX' ? ($tw4_real / $ind['target']) * 100 : ($ind['target'] / $tw4_real) * 100;

        // Cap at 120% max as per general IKPA rules
        $calcTw1 = min(120, $calcTw1);
        $calcTw2 = min(120, $calcTw2);
        $calcTw3 = min(120, $calcTw3);
        $calcTw4 = min(120, $calcTw4);

        $rumusMax = "(Realisasi/Target) x 100%";
        $rumusMin = "(Target/Realisasi) x 100%";

        // Write to CSV for each quarter ONLY ONCE (for user 1) so CSV doesn't get ridiculously huge
        if ($uid == $allUsers[0]) {
            fputcsv($csv3, ['Triwulan I', $ind['iku'] . ' - ' . $ind['nama'], $ind['target'], $tw1_real, $ind['tipe'] == 'MAX' ? $rumusMax : $rumusMin, round($calcTw1, 2) . '%', getStatus($calcTw1)]);
            fputcsv($csv3, ['Triwulan II', $ind['iku'] . ' - ' . $ind['nama'], $ind['target'], $tw2_real, $ind['tipe'] == 'MAX' ? $rumusMax : $rumusMin, round($calcTw2, 2) . '%', getStatus($calcTw2)]);
            fputcsv($csv3, ['Triwulan III', $ind['iku'] . ' - ' . $ind['nama'], $ind['target'], $tw3_real, $ind['tipe'] == 'MAX' ? $rumusMax : $rumusMin, round($calcTw3, 2) . '%', getStatus($calcTw3)]);
            fputcsv($csv3, ['Triwulan IV', $ind['iku'] . ' - ' . $ind['nama'], $ind['target'], $tw4_real, $ind['tipe'] == 'MAX' ? $rumusMax : $rumusMin, round($calcTw4, 2) . '%', getStatus($calcTw4)]);
        }

        // Insert into DB
        $stmt->execute([
            $tahun, 'PTA Medan',
            $ind['sas'],
            $ind['nama'],
            strtolower($ind['tipe']),
            $ind['satuan'],
            5, // Bobot setarat (100 / 20 = 5)
            $ind['target'],
            $ind['target'], $ind['target'], $ind['target'], $ind['target'], // Target per tw same as tahunan typically
            $tw1_real, $tw2_real, $tw3_real, $tw4_real,
            $uid
        ]);
    }
}
fclose($csv3);

// ==========================================
// 4. Data Parameter Penilaian dan Bobot
// ==========================================
$csv4 = fopen($dir . '/04_parameter_bobot.csv', 'w');
fputcsv($csv4, ['Nama_Komponen_Penilaian', 'Persentase_Bobot_Total', 'Batas_Ambang_Bawah', 'Batas_Ambang_Atas', 'Status_Penilaian']);
$params = [
    ['Sangat Baik', 'Bobot menyesuaikan (misal 5% per IKU)', '100%', '120%', 'Lulus Target Memuaskan'],
    ['Baik', 'Bobot menyesuaikan', '90%', '99.99%', 'Lulus Target Normal'],
    ['Cukup', 'Bobot menyesuaikan', '75%', '89.99%', 'Perlu Perbaikan / Peringatan'],
    ['Kurang', 'Bobot menyesuaikan', '0%', '74.99%', 'Evaluasi Ketat / Gagal']
];
foreach ($params as $row) fputcsv($csv4, $row);
fclose($csv4);

echo "Berhasil membuat 4 file CSV data dummy lengkap di folder dummy_data/.\n";
echo "Berhasil menginjeksi 20 Indikator Utama ke database untuk Hitung Capaian Kinerja!\n";
