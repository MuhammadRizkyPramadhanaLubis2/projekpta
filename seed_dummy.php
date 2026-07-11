<?php
declare(strict_types=1);

require_once __DIR__ . '/app/database.php';

$pdo = db();

$tahun = (int) date('Y');
$adminId = $pdo->query('SELECT id FROM users WHERE username = "admin"')->fetchColumn() ?: 1;

// Seed 1: IKU 01 - Persentase Perkara yang Diselesaikan Tepat Waktu
$stmt = $pdo->prepare("INSERT INTO target_kinerja 
    (tahun, unit, sasaran, indikator, satuan, bobot, target, target_tw1, target_tw2, target_tw3, target_tw4, real_tw1, real_tw2, real_tw3, real_tw4, user_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
    $tahun, 'PTA Medan', 'Meningkatnya Penyelesaian Perkara', 'Persentase Perkara yang Diselesaikan Tepat Waktu', '%', 10, 
    100, 100, 100, 100, 100, // target
    85, 92, 96, 100, // realisasi
    $adminId
]);

// Seed 2: IKU 02 - Persentase Putusan Tidak Ada Kesalahan Administrasi
$stmt->execute([
    $tahun, 'PTA Medan', 'Meningkatnya Kualitas Putusan', 'Persentase Putusan yang Tidak Ada Kesalahan Administrasi', '%', 10, 
    100, 100, 100, 100, 100, // target
    98, 100, 99, 100, // realisasi
    $adminId
]);

// Seed 3: IKU 03 - Penyerapan Anggaran (DIPA 01)
$stmt->execute([
    $tahun, 'PTA Medan', 'Meningkatnya Akuntabilitas Kinerja dan Keuangan', 'Persentase Penyerapan Anggaran DIPA', '%', 20, 
    100, 20, 50, 75, 100, // target
    18, 45, 70, 99, // realisasi
    $adminId
]);

echo "Berhasil memasukkan data dummy ke dalam database (tabel target_kinerja).\n";
