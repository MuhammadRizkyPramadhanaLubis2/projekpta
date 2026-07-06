<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId = (int) ($_POST['target_id'] ?? 0);
    $tw = max(1, min(4, (int) ($_POST['triwulan'] ?? 1)));
    $jenis = trim((string) ($_POST['jenis'] ?? ''));
    $narasi = trim((string) ($_POST['narasi'] ?? ''));

    $check = db()->prepare(
        'SELECT COUNT(*)
         FROM target_kinerja
         WHERE id = :id AND tahun = :tahun AND unit = :unit'
    );
    $check->execute([
        'id' => $targetId,
        'tahun' => $tahun,
        'unit' => $user['unit'],
    ]);

    if ((int) $check->fetchColumn() === 0) {
        flash('Indikator tidak valid. Isi Target Kinerja dulu.', 'error');
    } else {
        $stmt = db()->prepare(
            'INSERT INTO evaluasi (target_id, triwulan, jenis, narasi)
             VALUES (:target_id, :triwulan, :jenis, :narasi)'
        );
        $stmt->execute([
            'target_id' => $targetId,
            'triwulan' => $tw,
            'jenis' => $jenis,
            'narasi' => $narasi,
        ]);
        flash('Evaluasi kinerja tersimpan.');
    }

    header('Location: index.php?page=evaluasi&tahun=' . $tahun);
    exit;
}

$targetStmt = db()->prepare(
    'SELECT id, indikator
     FROM target_kinerja
     WHERE tahun = :tahun AND unit = :unit
     ORDER BY id'
);
$targetStmt->execute(['tahun' => $tahun, 'unit' => $user['unit']]);
$targets = $targetStmt->fetchAll();

$evalStmt = db()->prepare(
    'SELECT e.*, t.indikator
     FROM evaluasi e
     JOIN target_kinerja t ON t.id = e.target_id
     WHERE t.tahun = :tahun AND t.unit = :unit
     ORDER BY e.created_at DESC, e.id DESC'
);
$evalStmt->execute(['tahun' => $tahun, 'unit' => $user['unit']]);
$evaluations = $evalStmt->fetchAll();

render_header('Evaluasi Kinerja');
?>
<form method="get" class="toolbar">
    <input type="hidden" name="page" value="evaluasi">
    <label>
        Tahun
        <input type="number" name="tahun" min="2020" max="2100" value="<?= h((string) $tahun) ?>">
    </label>
    <button type="submit" class="secondary">Tampilkan</button>
</form>

<section class="panel">
    <form method="post" class="form-grid">
        <input type="hidden" name="tahun" value="<?= h((string) $tahun) ?>">
        <label>
            Indikator
            <select name="target_id" required>
                <?php foreach ($targets as $target): ?>
                    <option value="<?= h((string) $target['id']) ?>"><?= h((string) $target['indikator']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Triwulan
            <select name="triwulan">
                <option value="1">TW1</option>
                <option value="2">TW2</option>
                <option value="3">TW3</option>
                <option value="4">TW4</option>
            </select>
        </label>
        <label>
            Jenis
            <select name="jenis">
                <option>Keberhasilan (naik)</option>
                <option>Ketidakberhasilan (turun)</option>
            </select>
        </label>
        <label>
            Narasi
            <textarea name="narasi" required></textarea>
        </label>
        <button type="submit" <?= !$targets ? 'disabled' : '' ?>>Simpan Evaluasi</button>
    </form>
</section>

<section class="panel" style="margin-top:16px">
    <h2>Riwayat Evaluasi</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Waktu</th>
                <th>Indikator</th>
                <th>Triwulan</th>
                <th>Jenis</th>
                <th>Narasi</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$evaluations): ?>
                <tr><td colspan="5">Belum ada evaluasi tersimpan.</td></tr>
            <?php endif; ?>
            <?php foreach ($evaluations as $evaluation): ?>
                <tr>
                    <td><?= h((string) $evaluation['created_at']) ?></td>
                    <td><?= h((string) $evaluation['indikator']) ?></td>
                    <td>TW<?= h((string) $evaluation['triwulan']) ?></td>
                    <td><?= h((string) $evaluation['jenis']) ?></td>
                    <td><?= nl2br(h((string) $evaluation['narasi'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render_footer(); ?>
