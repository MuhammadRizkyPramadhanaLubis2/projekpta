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

$totalDipa01 = 0.0;
$totalDipa04 = 0.0;
foreach ($targets as $target) {
    $totalDipa01 += num($target['dipa01']);
    $totalDipa04 += num($target['dipa04']);
}

render_header('Cetak RKT & RKA');
?>
<form method="get" class="toolbar">
    <input type="hidden" name="page" value="rkt_rka">
    <label>
        Tahun
        <input type="number" name="tahun" min="2020" max="2100" value="<?= h((string) $tahun) ?>">
    </label>
    <button type="submit" class="secondary">Tampilkan</button>
    <button type="button" onclick="window.print()">Cetak</button>
</form>

<section class="print-sheet">
    <h2>RENCANA KERJA TAHUNAN & ANGGARAN</h2>
    <h3><?= h((string) $user['unit']) ?> Tahun <?= h((string) $tahun) ?></h3>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>No</th>
                <th>Sasaran Kinerja</th>
                <th>Indikator Kinerja</th>
                <th>Target</th>
                <th>DIPA 01</th>
                <th>DIPA 04</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$targets): ?>
                <tr><td colspan="6">Belum ada target kinerja.</td></tr>
            <?php endif; ?>
            <?php foreach ($targets as $i => $target): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= h((string) $target['sasaran']) ?></td>
                    <td><?= h((string) $target['indikator']) ?></td>
                    <td><?= h((string) $target['target']) ?></td>
                    <td><?= h((string) $target['dipa01']) ?></td>
                    <td><?= h((string) $target['dipa04']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="4">Total Anggaran</th>
                <th><?= h((string) $totalDipa01) ?></th>
                <th><?= h((string) $totalDipa04) ?></th>
            </tr>
            </tfoot>
        </table>
    </div>

    <p class="muted">
        Bagian RKA pada konsep PDF dapat dilengkapi dengan upload dokumen RKA-KL/DIPA 01 dan 04 tahun berjalan.
    </p>
</section>
<?php render_footer(); ?>
