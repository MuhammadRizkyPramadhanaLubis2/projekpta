<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();
$canViewAll = user_can('view_all_targets');
$selectedUserId = $canViewAll ? (int) ($_GET['user_id'] ?? 0) : (int) $user['id'];
$tw = (int) ($_GET['tw'] ?? 1);
$tw = max(1, min(4, $tw));
$twColumn = 'real_tw' . $tw;

$owners = [];
if ($canViewAll) {
    $owners = db()->query('SELECT id, nama, role, unit FROM users WHERE status = "active" ORDER BY unit, role, nama')->fetchAll();
}

$query = "SELECT tk.*, tk.{$twColumn} AS realisasi, u.nama AS owner_nama, u.role AS owner_role
          FROM target_kinerja tk
          LEFT JOIN users u ON u.id = tk.user_id
          WHERE tk.tahun = :tahun";
$params = ['tahun' => $tahun];

if ($canViewAll && $selectedUserId > 0) {
    $query .= ' AND tk.user_id = :user_id';
    $params['user_id'] = $selectedUserId;
} elseif (!$canViewAll) {
    $query .= ' AND tk.user_id = :user_id';
    $params['user_id'] = (int) $user['id'];
}

$query .= ' ORDER BY u.unit, u.role, u.nama, tk.id';
$stmt = db()->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$weightedTotal = 0.0;
$weightSum = 0.0;
foreach ($rows as &$row) {
    $target = target_for_quarter($row, $tw);
    $realisasi = num($row['realisasi']);
    $weight = max(0, num($row['bobot'] ?? 1));
    $row['target_triwulan'] = $target;
    $row['capaian'] = achievement_value($target, $realisasi, (string) ($row['tipe_indikator'] ?? 'max'));
    $row['nilai_tertimbang'] = round($row['capaian'] * $weight, 2);
    $weightedTotal += $row['nilai_tertimbang'];
    $weightSum += $weight;
}
unset($row);

$average = $weightSum > 0 ? round($weightedTotal / $weightSum, 2) : null;

render_header('Hitung Capaian Kinerja');
?>
<section class="panel analysis-rule">
    <strong>Rumus Capaian Kinerja</strong>
    <p>
        Tipe "semakin tinggi semakin baik" dihitung realisasi dibagi target triwulan.
        Tipe "semakin rendah semakin baik" dihitung target triwulan dibagi realisasi.
        Totalitas capaian menggunakan rata-rata berbobot dari seluruh indikator.
    </p>
</section>

<form method="get" class="toolbar">
    <input type="hidden" name="page" value="capaian">
    <label>
        Tahun
        <input type="number" name="tahun" min="2020" max="2100" value="<?= h((string) $tahun) ?>">
    </label>
    <label>
        Triwulan
        <select name="tw">
            <?php for ($i = 1; $i <= 4; $i++): ?>
                <option value="<?= $i ?>" <?= $i === $tw ? 'selected' : '' ?>>TW<?= $i ?></option>
            <?php endfor; ?>
        </select>
    </label>
    <?php if ($canViewAll): ?>
        <label>
            Pemilik Data
            <select name="user_id">
                <option value="0">Semua Pengguna</option>
                <?php foreach ($owners as $owner): ?>
                    <option value="<?= h((string) $owner['id']) ?>" <?= (int) $owner['id'] === $selectedUserId ? 'selected' : '' ?>>
                        <?= h((string) $owner['nama']) ?> - <?= h(role_label((string) $owner['role'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>
    <button type="submit">Hitung</button>
</form>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <?php if ($canViewAll): ?>
                    <th>Pemilik</th>
                <?php endif; ?>
                <th>Indikator Kinerja</th>
                <th>Sumber Data</th>
                <th>Tipe</th>
                <th>Bobot</th>
                <th>Target TW<?= $tw ?></th>
                <th>Realisasi</th>
                <th>Capaian (%)</th>
                <th>Nilai Tertimbang</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="<?= $canViewAll ? '9' : '8' ?>">Belum ada data Target Kinerja untuk tahun ini.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php if ($canViewAll): ?>
                        <td>
                            <?= h((string) ($row['owner_nama'] ?? '-')) ?>
                            <br><small><?= h(role_label((string) ($row['owner_role'] ?? ''))) ?></small>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?= h((string) $row['indikator']) ?>
                        <?php if (!empty($row['satuan'])): ?>
                            <br><small>Satuan: <?= h((string) $row['satuan']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= h((string) ($row['sumber_data'] ?: '-')) ?></td>
                    <td><?= h(indicator_type_label((string) ($row['tipe_indikator'] ?? 'max'))) ?></td>
                    <td><?= h((string) ($row['bobot'] ?? 1)) ?></td>
                    <td><?= h((string) $row['target_triwulan']) ?></td>
                    <td>
                        <?= h((string) $row['realisasi']) ?>
                        <?php
                        if (($row['is_mandatory'] ?? 0) == 1) {
                            $meta = json_decode((string)($row['metadata'] ?? '{}'), true);
                            if (is_array($meta) && isset($meta['tw' . $tw])) {
                                $m = $meta['tw' . $tw];
                                if (($row['owner_role'] ?? '') === 'PanmudBanding') {
                                    echo '<br><small style="color:#64748b;">(Masuk: ' . h((string)($m['a'] ?? 0)) . ', Selesai: ' . h((string)($m['b'] ?? 0)) . ')</small>';
                                } elseif (($row['owner_role'] ?? '') === 'PanmudHukum') {
                                    echo '<br><small style="color:#64748b;">(E-Court: ' . h((string)($m['a'] ?? 0)) . ', Non: ' . h((string)($m['b'] ?? 0)) . ')</small>';
                                }
                            }
                        }
                        ?>
                    </td>
                    <td><?= h((string) $row['capaian']) ?></td>
                    <td><?= h((string) $row['nilai_tertimbang']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="stat">
        Totalitas Capaian Kinerja Berbobot TW<?= $tw ?>:
        <?= $average !== null ? h((string) $average) . '%' : 'Belum tersedia' ?>
    </div>
</section>
<?php render_footer(); ?>
