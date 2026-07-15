<?php
declare(strict_types=1);

$user = current_user();



$tahun = year_value();
$canViewAll = user_can('view_all_targets');
$selectedUserId = $canViewAll ? (int) ($_GET['user_id'] ?? $_POST['user_id'] ?? 0) : (int) $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId = (int) ($_POST['target_id'] ?? 0);
    $tw = max(1, min(12, (int) ($_POST['triwulan'] ?? 1)));
    $narasi = trim((string) ($_POST['narasi'] ?? ''));

    $check = db()->prepare(
        'SELECT *
         FROM target_kinerja
         WHERE id = :id AND tahun = :tahun'
    );
    $check->execute([
        'id' => $targetId,
        'tahun' => $tahun,
    ]);
    $targetRow = $check->fetch();

    if (!$targetRow || !can_manage_target_row($targetRow)) {
        flash('Indikator tidak valid. Isi Target Kinerja dulu.', 'error');
    } elseif ($narasi === '') {
        flash('Narasi evaluasi wajib diisi sesuai konsep EvKin.', 'error');
    } else {
        $trend = achievement_trend($targetRow, $tw);
        $stmt = db()->prepare(
            'INSERT INTO evaluasi (target_id, triwulan, jenis, narasi)
             VALUES (:target_id, :triwulan, :jenis, :narasi)'
        );
        $stmt->execute([
            'target_id' => $targetId,
            'triwulan' => $tw,
            'jenis' => $trend['jenis'],
            'narasi' => $narasi,
        ]);
        flash('Evaluasi kinerja tersimpan.');
    }

    header('Location: index.php?page=evaluasi&tahun=' . $tahun . ($selectedUserId > 0 ? '&user_id=' . $selectedUserId : ''));
    exit;
}

$owners = [];
if ($canViewAll) {
    $owners = db()->query('SELECT id, nama, role, unit FROM users WHERE status = "active" ORDER BY unit, role, nama')->fetchAll();
}

$selectedTw = max(1, min(12, (int) ($_GET['triwulan'] ?? 1)));

$targetQuery = 'SELECT tk.*, u.nama AS owner_nama, u.role AS owner_role
                FROM target_kinerja tk
                LEFT JOIN users u ON u.id = tk.user_id
                WHERE tk.tahun = :tahun';
$targetParams = ['tahun' => $tahun];

if ($canViewAll && $selectedUserId > 0) {
    $targetQuery .= ' AND tk.user_id = :user_id';
    $targetParams['user_id'] = $selectedUserId;
} elseif (!$canViewAll) {
    $targetQuery .= ' AND tk.user_id = :user_id';
    $targetParams['user_id'] = (int) $user['id'];
}

$targetQuery .= ' ORDER BY u.unit, u.role, u.nama, tk.id';
$targetStmt = db()->prepare($targetQuery);
$targetStmt->execute($targetParams);
$targets = $targetStmt->fetchAll();

$evaluationMap = [];
$evalCheckStmt = db()->prepare(
    'SELECT target_id, triwulan, COUNT(*) AS total
     FROM evaluasi
     GROUP BY target_id, triwulan'
);
foreach ($evalCheckStmt->execute() ? $evalCheckStmt->fetchAll() : [] as $row) {
    $evaluationMap[(int) $row['target_id'] . '-' . (int) $row['triwulan']] = (int) $row['total'];
}

$evalQuery = 'SELECT e.*, t.indikator, u.nama AS owner_nama, u.role AS owner_role
     FROM evaluasi e
     JOIN target_kinerja t ON t.id = e.target_id
     LEFT JOIN users u ON u.id = t.user_id
     WHERE t.tahun = :tahun';
$evalParams = ['tahun' => $tahun];

if ($canViewAll && $selectedUserId > 0) {
    $evalQuery .= ' AND t.user_id = :user_id';
    $evalParams['user_id'] = $selectedUserId;
} elseif (!$canViewAll) {
    $evalQuery .= ' AND t.user_id = :user_id';
    $evalParams['user_id'] = (int) $user['id'];
}

$evalQuery .= ' ORDER BY e.created_at DESC, e.id DESC';
$evalStmt = db()->prepare($evalQuery);
$evalStmt->execute($evalParams);
$evaluations = $evalStmt->fetchAll();

render_header('Evaluasi Kinerja');
?>
<style>
#form-evkin:target {
    animation: highlightForm 2s ease-out;
    box-shadow: 0 0 15px rgba(22, 163, 74, 0.4);
    border: 1px solid var(--primary);
}
@keyframes highlightForm {
    0% { background-color: rgba(22, 163, 74, 0.15); }
    100% { background-color: var(--surface); }
}
</style>
<section class="panel analysis-rule">
    <strong>Evaluasi Otomatis Sesuai Konsep</strong>
    <p>
        Sistem membandingkan capaian triwulan berjalan dengan triwulan sebelumnya.
        Jika naik maka jenis evaluasi menjadi keberhasilan, jika turun menjadi ketidakberhasilan,
        dan narasi tetap wajib diisi sebagai kertas kerja EvKin.
    </p>
</section>

<form method="get" class="toolbar">
    <input type="hidden" name="page" value="evaluasi">
    <label>
        Tahun
        <input type="number" name="tahun" min="2020" max="2100" value="<?= h((string) $tahun) ?>">
    </label>
    <?php if ($canViewAll): ?>
        <label>
            Pemilik Data
            <select name="user_id">
                <option value="0">Semua Pengguna</option>
                <?php foreach ($owners as $owner): ?>
                    <option value="<?= h((string) $owner['id']) ?>" <?= (int) $owner['id'] === $selectedUserId ? 'selected' : '' ?>>
                        <?= format_user_label($owner['nama'] ?? '', $owner['role'] ?? '', false) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>
    <label>
        Triwulan
        <select name="triwulan">
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= $i === $selectedTw ? 'selected' : '' ?>>TW<?= $i ?></option>
            <?php endfor; ?>
        </select>
    </label>
    <button type="submit" class="secondary">Tampilkan</button>
</form>

<section class="panel" style="margin-bottom:16px">
    <h2>Kewajiban Evaluasi TW<?= $selectedTw ?></h2>
    <div class="table-wrap">
        <div class="table-responsive">
<table>
            <thead>
            <tr>
                <?php if ($canViewAll): ?>
                    <th>Pemilik</th>
                <?php endif; ?>
                <th>Indikator</th>
                <th>Target TW<?= $selectedTw ?></th>
                <th>Realisasi TW<?= $selectedTw ?></th>
                <?php if ($selectedTw > 1): ?>
                    <th>Capaian TW<?= $selectedTw - 1 ?></th>
                <?php endif; ?>
                <th>Capaian TW<?= $selectedTw ?></th>
                <th>Status</th>
                <th>Evaluasi</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$targets): ?>
                <tr><td colspan="<?= $canViewAll ? '9' : '8' ?>">Belum ada target kinerja untuk dievaluasi.</td></tr>
            <?php endif; ?>
            <?php foreach ($targets as $target): ?>
                <?php
                $trend = achievement_trend($target, $selectedTw);
                $hasEvaluation = ($evaluationMap[(int) $target['id'] . '-' . $selectedTw] ?? 0) > 0;
                ?>
                <tr>
                    <?php if ($canViewAll): ?>
                        <td>
                            <?= format_user_label($target['owner_nama'] ?? '', $target['owner_role'] ?? '', true) ?>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?= h((string) $target['indikator']) ?>
                        <br><small><?= h((string) ($target['sumber_data'] ?: 'Sumber data belum diisi')) ?></small>
                    </td>
                    <td><?= h((string) target_for_month($target, $selectedTw)) ?></td>
                    <td><?= h((string) num($target['real_tw' . $selectedTw] ?? 0)) ?></td>
                    <?php if ($selectedTw > 1): ?>
                        <td><?= h((string) $trend['previous']) ?>%</td>
                    <?php endif; ?>
                    <td><?= h((string) $trend['current']) ?>%</td>
                    <td><span class="small-badge"><?= h((string) $trend['label']) ?></span></td>
                    <td><?= $hasEvaluation ? 'Sudah ada narasi' : 'Wajib narasi' ?></td>
                    <td>
                        <a class="button secondary" href="index.php?page=evaluasi&tahun=<?= h((string) $tahun) ?>&triwulan=<?= $selectedTw ?>&target_id=<?= h((string) $target['id']) ?><?= $selectedUserId > 0 ? '&user_id=' . h((string) $selectedUserId) : '' ?>#form-evkin">
                            Isi EvKin
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
</div>
    </div>
</section>

<section class="panel" id="form-evkin">
    <form method="post" class="form-grid">
        <input type="hidden" name="tahun" value="<?= h((string) $tahun) ?>">
        <input type="hidden" name="user_id" value="<?= h((string) $selectedUserId) ?>">
        <label>
            Indikator
            <select name="target_id" required>
                <?php foreach ($targets as $target): ?>
                    <option value="<?= h((string) $target['id']) ?>" <?= (int) ($_GET['target_id'] ?? 0) === (int) $target['id'] ? 'selected' : '' ?>>
                        <?= h((string) $target['indikator']) ?>
                        <?php if ($canViewAll): ?>
                            - <?= h((string) ($target['owner_nama'] ?? '-')) ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Triwulan
            <select name="triwulan">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= $i === $selectedTw ? 'selected' : '' ?>>TW<?= $i ?></option>
                <?php endfor; ?>
            </select>
        </label>
        <div class="auto-eval-note">
            Jenis evaluasi akan ditentukan otomatis dari perbandingan capaian triwulan.
        </div>
        <label>
            Narasi
            <textarea name="narasi" required placeholder="Jelaskan keberhasilan, ketidakberhasilan, atau kondisi capaian tetap berdasarkan data sumber dan tindak lanjut unit kerja."></textarea>
        </label>
        <button type="submit" <?= !$targets ? 'disabled' : '' ?>>Simpan Evaluasi</button>
    </form>
</section>

<section class="panel" style="margin-top:16px">
    <h2>Riwayat Evaluasi</h2>
    <div class="table-wrap">
        <div class="table-responsive">
<table>
            <thead>
            <tr>
                <th>Waktu</th>
                <?php if ($canViewAll): ?>
                    <th>Pemilik</th>
                <?php endif; ?>
                <th>Indikator</th>
                <th>Triwulan</th>
                <th>Jenis</th>
                <th>Narasi</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$evaluations): ?>
                <tr><td colspan="<?= $canViewAll ? '6' : '5' ?>">Belum ada evaluasi tersimpan.</td></tr>
            <?php endif; ?>
            <?php foreach ($evaluations as $evaluation): ?>
                <tr>
                    <td><?= h((string) $evaluation['created_at']) ?></td>
                    <?php if ($canViewAll): ?>
                        <td>
                            <?= format_user_label($evaluation['owner_nama'] ?? '', $evaluation['owner_role'] ?? '', true) ?>
                        </td>
                    <?php endif; ?>
                    <td><?= h((string) $evaluation['indikator']) ?></td>
                    <td>TW<?= h((string) $evaluation['triwulan']) ?></td>
                    <td><?= h((string) $evaluation['jenis']) ?></td>
                    <td><?= nl2br(h((string) $evaluation['narasi'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
</div>
    </div>
</section>
<?php render_footer(); ?>
