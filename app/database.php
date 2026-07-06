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
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function table_has_column(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->query('PRAGMA table_info(' . $table . ')');
    foreach ($stmt->fetchAll() as $row) {
        if (($row['name'] ?? '') === $column) {
            return true;
        }
    }

    return false;
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
            unit TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT "active",
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS target_kinerja (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tahun INTEGER NOT NULL,
            unit TEXT NOT NULL,
            sasaran TEXT NOT NULL,
            indikator TEXT NOT NULL,
            satuan TEXT NOT NULL DEFAULT "",
            tipe_indikator TEXT NOT NULL DEFAULT "max",
            sumber_data TEXT NOT NULL DEFAULT "",
            bobot REAL NOT NULL DEFAULT 1,
            target REAL NOT NULL DEFAULT 0,
            target_tw1 REAL NOT NULL DEFAULT 0,
            target_tw2 REAL NOT NULL DEFAULT 0,
            target_tw3 REAL NOT NULL DEFAULT 0,
            target_tw4 REAL NOT NULL DEFAULT 0,
            dipa01 REAL NOT NULL DEFAULT 0,
            dipa04 REAL NOT NULL DEFAULT 0,
            real_tw1 REAL NOT NULL DEFAULT 0,
            real_tw2 REAL NOT NULL DEFAULT 0,
            real_tw3 REAL NOT NULL DEFAULT 0,
            real_tw4 REAL NOT NULL DEFAULT 0,
            user_id INTEGER,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
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

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS document_meta (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tahun INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            jenis TEXT NOT NULL,
            no_surat TEXT NOT NULL DEFAULT "",
            tanggal_surat TEXT NOT NULL DEFAULT "",
            lokasi TEXT NOT NULL DEFAULT "",
            pihak1_nama TEXT NOT NULL DEFAULT "",
            pihak1_jabatan TEXT NOT NULL DEFAULT "",
            pihak2_nama TEXT NOT NULL DEFAULT "",
            pihak2_jabatan TEXT NOT NULL DEFAULT "",
            catatan TEXT NOT NULL DEFAULT "",
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(tahun, user_id, jenis),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )'
    );

    if (!table_has_column($pdo, 'users', 'status')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN status TEXT NOT NULL DEFAULT "active"');
    }

    if (!table_has_column($pdo, 'users', 'created_at')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN created_at TEXT');
        $pdo->exec('UPDATE users SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL');
    }

    if (!table_has_column($pdo, 'target_kinerja', 'user_id')) {
        $pdo->exec('ALTER TABLE target_kinerja ADD COLUMN user_id INTEGER');
    }

    if (!table_has_column($pdo, 'target_kinerja', 'created_at')) {
        $pdo->exec('ALTER TABLE target_kinerja ADD COLUMN created_at TEXT');
        $pdo->exec('UPDATE target_kinerja SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL');
    }

    if (!table_has_column($pdo, 'target_kinerja', 'updated_at')) {
        $pdo->exec('ALTER TABLE target_kinerja ADD COLUMN updated_at TEXT');
    }

    $targetColumns = [
        'satuan' => 'TEXT NOT NULL DEFAULT ""',
        'tipe_indikator' => 'TEXT NOT NULL DEFAULT "max"',
        'sumber_data' => 'TEXT NOT NULL DEFAULT ""',
        'bobot' => 'REAL NOT NULL DEFAULT 1',
        'target_tw1' => 'REAL NOT NULL DEFAULT 0',
        'target_tw2' => 'REAL NOT NULL DEFAULT 0',
        'target_tw3' => 'REAL NOT NULL DEFAULT 0',
        'target_tw4' => 'REAL NOT NULL DEFAULT 0',
    ];

    foreach ($targetColumns as $column => $definition) {
        if (!table_has_column($pdo, 'target_kinerja', $column)) {
            $pdo->exec('ALTER TABLE target_kinerja ADD COLUMN ' . $column . ' ' . $definition);
        }
    }

    $pdo->exec(
        'UPDATE target_kinerja
         SET target_tw1 = CASE WHEN target_tw1 = 0 THEN target / 4 ELSE target_tw1 END,
             target_tw2 = CASE WHEN target_tw2 = 0 THEN target / 4 ELSE target_tw2 END,
             target_tw3 = CASE WHEN target_tw3 = 0 THEN target / 4 ELSE target_tw3 END,
             target_tw4 = CASE WHEN target_tw4 = 0 THEN target / 4 ELSE target_tw4 END,
             bobot = CASE WHEN bobot = 0 THEN 1 ELSE bobot END'
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
        'INSERT OR IGNORE INTO users (username, password, nama, role, unit, status)
         VALUES (:username, :password, :nama, :role, :unit, "active")'
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

    $adminId = $pdo->query('SELECT id FROM users WHERE username = "admin"')->fetchColumn();
    if ($adminId) {
        $legacyStmt = $pdo->prepare('UPDATE target_kinerja SET user_id = :admin_id WHERE user_id IS NULL');
        $legacyStmt->execute(['admin_id' => (int) $adminId]);
    }
}
