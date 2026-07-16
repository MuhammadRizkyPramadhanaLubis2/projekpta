<?php
declare(strict_types=1);

require_login();

$idsParam = $_GET['ids'] ?? '';
$ids = array_filter(array_map('intval', explode(',', $idsParam)));

if (empty($ids)) {
    die('Tidak ada data yang dipilih untuk di-print.');
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

$query = "SELECT tk.*, u.nama AS owner_nama, u.role AS owner_role
          FROM target_kinerja tk
          LEFT JOIN users u ON u.id = tk.user_id
          WHERE tk.id IN ($placeholders)
          ORDER BY u.unit, u.role, u.nama, tk.id";

$stmt = db()->prepare($query);
$stmt->execute($ids);
$rows = $stmt->fetchAll();

if (empty($rows)) {
    die('Data tidak ditemukan.');
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Target Kinerja - IKPA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        @media print {
            @page {
                size: landscape;
                margin: 1cm;
            }
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: flex-end; gap: 10px;">
        <button onclick="window.print()" style="padding: 8px 16px; font-size: 14px; cursor: pointer; background: #047857; color: white; border: none; border-radius: 4px;">Cetak PDF</button>
        <button onclick="window.close()" style="padding: 8px 16px; font-size: 14px; cursor: pointer; background: #64748b; color: white; border: none; border-radius: 4px;">Tutup</button>
    </div>

    <h2>Data Target Kinerja</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Pemilik</th>
                <th>Sasaran Kinerja</th>
                <th>Indikator Kinerja</th>
                <th>Target</th>
                <th>Satuan</th>
                <th>Total Realisasi</th>
                <th>Capaian (%)</th>
                <th>Analisis Capaian</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($rows as $row): 
                $targetValue = num($row['target'] ?? 0);
                $realizationTotal = 0;
                $months_list = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
                foreach ($months_list as $m) {
                    $realizationTotal += num($row['real_'.$m] ?? 0);
                }
                $achievementRaw = achievement_value($targetValue, $realizationTotal, (string) ($row['tipe_indikator'] ?? 'max'));
                $achievementPercent = max(0, min(100, $achievementRaw));
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td>
                    <?= h($row['owner_nama']) ?><br>
                    <small><?= h(role_label((string)$row['owner_role'])) ?></small>
                </td>
                <td><?= nl2br(h((string)$row['sasaran'])) ?></td>
                <td><?= nl2br(h((string)$row['indikator'])) ?></td>
                <td class="text-center"><?= h((string)$targetValue) ?></td>
                <td class="text-center"><?= h((string)$row['satuan']) ?></td>
                <td class="text-center"><?= h((string)$realizationTotal) ?></td>
                <td class="text-center"><?= round($achievementPercent, 2) ?>%</td>
                <td><?= nl2br(h((string)$row['analisis_capaian'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        // Otomatis membuka dialog print saat halaman dimuat
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
