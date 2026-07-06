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

render_header('Cetak Perjanjian Kinerja');
?>
<form method="get" class="toolbar">
    <input type="hidden" name="page" value="pk">
    <label>
        Tahun
        <input type="number" name="tahun" min="2020" max="2100" value="<?= h((string) $tahun) ?>">
    </label>
    <button type="submit" class="secondary">Tampilkan</button>
    <button type="button" onclick="window.print()">Cetak</button>
</form>

<section class="print-sheet">
    <h2>PERJANJIAN KINERJA TAHUN <?= h((string) $tahun) ?></h2>
    <h3><?= h((string) $user['unit']) ?></h3>

    <p>
        Dalam rangka mewujudkan manajemen pemerintahan yang efektif, transparan, dan akuntabel,
        pihak yang bertanda tangan di bawah ini menetapkan target kinerja sesuai sasaran dan
        indikator kinerja yang tercantum pada lampiran.
    </p>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>No</th>
                <th>Sasaran Kinerja</th>
                <th>Indikator Kinerja</th>
                <th>Target</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$targets): ?>
                <tr><td colspan="4">Belum ada target kinerja.</td></tr>
            <?php endif; ?>
            <?php foreach ($targets as $i => $target): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= h((string) $target['sasaran']) ?></td>
                    <td><?= h((string) $target['indikator']) ?></td>
                    <td><?= h((string) $target['target']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="signature-grid">
        <div class="signature-box">
            <strong>Pihak I</strong>
            <span><?= h((string) $user['nama']) ?></span>
        </div>
        <div class="signature-box">
            <strong>Pihak II</strong>
            <span>Ketua PTA Medan</span>
        </div>
    </div>
</section>
<?php render_footer(); ?>
