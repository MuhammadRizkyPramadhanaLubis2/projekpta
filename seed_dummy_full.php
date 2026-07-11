<?php
declare(strict_types=1);

require_once __DIR__ . '/app/database.php';

$pdo = db();
$tahun = (int) date('Y');
$adminId = 1;

// Clear existing targets for the year to avoid duplicates
$pdo->exec("DELETE FROM target_kinerja WHERE tahun = $tahun");

$dummyData = [
    ['sasaran' => 'Meningkatnya Penyelesaian Perkara', 'indikator' => 'Persentase Perkara yang Diselesaikan Tepat Waktu', 'target' => 100, 'tw1' => 85, 'tw2' => 90, 'tw3' => 95, 'tw4' => 100],
    ['sasaran' => 'Meningkatnya Kualitas Putusan', 'indikator' => 'Persentase Putusan yang Tidak Ada Kesalahan Administrasi', 'target' => 100, 'tw1' => 98, 'tw2' => 99, 'tw3' => 99, 'tw4' => 100],
    ['sasaran' => 'Meningkatnya Akses Peradilan', 'indikator' => 'Persentase Perkara e-Court', 'target' => 50, 'tw1' => 30, 'tw2' => 45, 'tw3' => 55, 'tw4' => 60],
    ['sasaran' => 'Meningkatnya Akses Peradilan', 'indikator' => 'Persentase Keberhasilan Mediasi', 'target' => 30, 'tw1' => 15, 'tw2' => 20, 'tw3' => 25, 'tw4' => 35],
    ['sasaran' => 'Meningkatnya Kualitas Layanan Publik', 'indikator' => 'Indeks Kepuasan Masyarakat (IKM)', 'target' => 85, 'tw1' => 82, 'tw2' => 84, 'tw3' => 86, 'tw4' => 88],
    ['sasaran' => 'Meningkatnya Integritas Aparatur', 'indikator' => 'Indeks Persepsi Anti Korupsi (IPAK)', 'target' => 85, 'tw1' => 86, 'tw2' => 87, 'tw3' => 88, 'tw4' => 90],
    ['sasaran' => 'Meningkatnya Akuntabilitas Kinerja', 'indikator' => 'Nilai AKIP', 'target' => 80, 'tw1' => 75, 'tw2' => 78, 'tw3' => 81, 'tw4' => 82],
    ['sasaran' => 'Meningkatnya Tata Kelola Keuangan', 'indikator' => 'Persentase Serapan Anggaran DIPA 01', 'target' => 100, 'tw1' => 20, 'tw2' => 45, 'tw3' => 70, 'tw4' => 98],
    ['sasaran' => 'Meningkatnya Tata Kelola Keuangan', 'indikator' => 'Persentase Serapan Anggaran DIPA 04', 'target' => 100, 'tw1' => 25, 'tw2' => 50, 'tw3' => 75, 'tw4' => 99],
    ['sasaran' => 'Meningkatnya Kedisiplinan Pegawai', 'indikator' => 'Persentase Kehadiran Tepat Waktu', 'target' => 95, 'tw1' => 90, 'tw2' => 92, 'tw3' => 94, 'tw4' => 96],
];

$stmt = $pdo->prepare("INSERT INTO target_kinerja 
    (tahun, unit, sasaran, indikator, satuan, bobot, target, target_tw1, target_tw2, target_tw3, target_tw4, real_tw1, real_tw2, real_tw3, real_tw4, user_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($dummyData as $data) {
    $stmt->execute([
        $tahun, 'PTA Medan',
        $data['sasaran'],
        $data['indikator'],
        '%', 10,
        $data['target'],
        $data['target'], $data['target'], $data['target'], $data['target'],
        $data['tw1'],
        $data['tw2'],
        $data['tw3'],
        $data['tw4'],
        $adminId
    ]);
}

echo "Berhasil memasukkan " . count($dummyData) . " data indikator kinerja ke dalam database.\n";
