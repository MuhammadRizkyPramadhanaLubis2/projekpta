<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dataDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }

    $pdo = new PDO('sqlite:' . $dataDir . DIRECTORY_SEPARATOR . 'ikpa.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}

function init_database(): void
{
    $pdo = db();

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            nama TEXT NOT NULL,
            role TEXT NOT NULL,
            unit TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS target_kinerja (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tahun INTEGER NOT NULL,
            unit TEXT NOT NULL,
            sasaran TEXT NOT NULL,
            indikator TEXT NOT NULL,
            target REAL NOT NULL DEFAULT 0,
            dipa01 REAL NOT NULL DEFAULT 0,
            dipa04 REAL NOT NULL DEFAULT 0,
            real_tw1 REAL NOT NULL DEFAULT 0,
            real_tw2 REAL NOT NULL DEFAULT 0,
            real_tw3 REAL NOT NULL DEFAULT 0,
            real_tw4 REAL NOT NULL DEFAULT 0
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS evaluasi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            target_id INTEGER NOT NULL,
            triwulan INTEGER NOT NULL,
            jenis TEXT NOT NULL,
            narasi TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (target_id) REFERENCES target_kinerja(id) ON DELETE CASCADE
        )'
    );

    $users = [
        ['admin', 'admin123', 'Administrator', 'Admin', 'PTA Medan'],
        ['panmudbanding', '123456', 'Panmud Banding', 'PanmudBanding', 'PTA Medan'],
        ['panmudhukum', '123456', 'Panmud Hukum', 'PanmudHukum', 'PTA Medan'],
        ['turt', '123456', 'Kasubag Tata Usaha dan Rumah Tangga', 'KasubagTURT', 'PTA Medan'],
        ['kepegawaian', '123456', 'Kasubag Kepegawaian & TI', 'Kepegawaian', 'PTA Medan'],
        ['keuangan', '123456', 'Kasubag Keuangan & Pelaporan', 'Keuangan', 'PTA Medan'],
        ['perencanaan', '123456', 'Kasubag Perencanaan Program & Anggaran', 'Perencanaan', 'PTA Medan'],
        ['satkerhukum', '123456', 'Panmud Hukum Satker PA', 'SatkerPanmudHukum', 'Satker PA'],
        ['satkerptip', '123456', 'Kasubag PTIP Satker PA', 'SatkerKasubagPTIP', 'Satker PA'],
    ];

    $stmt = $pdo->prepare(
        'INSERT OR IGNORE INTO users (username, password, nama, role, unit)
         VALUES (:username, :password, :nama, :role, :unit)'
    );

    foreach ($users as $user) {
        $stmt->execute([
            'username' => $user[0],
            'password' => $user[1],
            'nama' => $user[2],
            'role' => $user[3],
            'unit' => $user[4],
        ]);
    }
}
