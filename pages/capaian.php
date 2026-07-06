<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();
$tw = (int) ($_GET['tw'] ?? 1);
$tw = max(1, min(4, $tw));
$twColumn = 'real_tw' . $tw;

$stmt = db()->prepare(
    "SELECT indikator, target, {$twColumn} AS realisasi
     FROM target_kinerja
     WHERE tahun = :tahun AND unit = :unit
     ORDER BY id"
);
$stmt->execute(['tahun' => $tahun, 'unit' => $user['unit']]);
$rows = $stmt->fetchAll();

$total = 0.0;
$count = 0;
foreach ($rows as &$row) {
    $target = num($row['target']);
    $realisasi = num($row['realisasi']);
    $row['capaian'] = $target !== 0.0 ? round($realisasi / $target * 100, 2) : 0;
    $total += $row['capaian'];
    $count++;
}
unset($row);

$average = $count > 0 ? round($total / $count, 2) : null;

render_header('Hitung Capaian Kinerja');
?>
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
    <button type="submit">Hitung</button>
</form>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Indikator Kinerja</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th>Capaian (%)</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="4">Belum ada data Target Kinerja untuk tahun ini.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= h((string) $row['indikator']) ?></td>
                    <td><?= h((string) $row['target']) ?></td>
                    <td><?= h((string) $row['realisasi']) ?></td>
                    <td><?= h((string) $row['capaian']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($average !== null): ?>
        <div class="stat">Totalitas Capaian Kinerja TW<?= $tw ?>: <?= h((string) $average) ?>%</div>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
