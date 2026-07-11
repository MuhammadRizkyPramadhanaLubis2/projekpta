<?php
require 'app/database.php';
$pdo = db();
$query = "SELECT tk.*, tk.real_tw4 AS realisasi, u.nama AS owner_nama, u.role AS owner_role
          FROM target_kinerja tk
          LEFT JOIN users u ON u.id = tk.user_id
          WHERE tk.tahun = :tahun AND tk.user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['tahun' => 2026, 'user_id' => 10]);
$rows = $stmt->fetchAll();
echo "Number of rows: " . count($rows) . "\n";
print_r($rows[0] ?? 'No data');
