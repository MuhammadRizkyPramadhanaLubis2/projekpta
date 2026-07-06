<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();

$stmt = db()->prepare(
    'SELECT *
     FROM target_kinerja
     WHERE tahun = :tahun AND unit = :unit
     ORDER BY id'
);
$stmt->execute(['tahun' => $tahun, 'unit' => $user['unit']]);
$targets = $stmt->fetchAll();

render_header('Cetak Rencana Aksi');
?>
<form method="get" class="toolbar">
    <input type="hidden" name="page" value="renaksi">
    <label>
        Tahun
        <input type="number" name="tahun" min="2020" max="2100" value="<?= h((string) $tahun) ?>">
    </label>
    <button type="submit" class="secondary">Tampilkan</button>
    <button type="button" onclick="window.print()">Cetak</button>
</form>

<section class="print-sheet">
    <h2>RENCANA AKSI TAHUN <?= h((string) $tahun) ?></h2>
    <h3><?= h((string) $user['unit']) ?></h3>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>No</th>
                <th>Sasaran</th>
                <th>Indikator</th>
                <th>Target TW1</th>
                <th>Target TW2</th>
                <th>Target TW3</th>
                <th>Target TW4</th>
                <th>Aksi/Kegiatan</th>
                <th>Keluaran</th>
                <th>Dana</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$targets): ?>
                <tr><td colspan="10">Belum ada target kinerja.</td></tr>
            <?php endif; ?>
            <?php foreach ($targets as $i => $target): ?>
                <?php $quarterTarget = round(num($target['target']) / 4, 2); ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= h((string) $target['sasaran']) ?></td>
                    <td><?= h((string) $target['indikator']) ?></td>
                    <td><?= h((string) $quarterTarget) ?></td>
                    <td><?= h((string) $quarterTarget) ?></td>
                    <td><?= h((string) $quarterTarget) ?></td>
                    <td><?= h((string) $quarterTarget) ?></td>
                    <td>Pelaksanaan program dan kegiatan sesuai IKU/Renstra.</td>
                    <td>Laporan capaian kinerja triwulan.</td>
                    <td><?= h((string) (num($target['dipa01']) + num($target['dipa04']))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render_footer(); ?>
