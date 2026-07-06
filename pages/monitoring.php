<?php
declare(strict_types=1);

require_permission('view_all_targets');

$tahun = year_value();
$tw = max(1, min(4, (int) ($_GET['tw'] ?? 1)));
$selectedUnit = trim((string) ($_GET['unit'] ?? ''));
$selectedRole = trim((string) ($_GET['role'] ?? ''));

$unitOptions = db()->query(
    'SELECT DISTINCT unit
     FROM users
     WHERE status = "active"
     ORDER BY unit'
)->fetchAll(PDO::FETCH_COLUMN);

$userQuery = 'SELECT id, username, nama, role, unit, status
              FROM users
              WHERE status = "active"';
$userParams = [];

if ($selectedUnit !== '') {
    $userQuery .= ' AND unit = :unit';
    $userParams['unit'] = $selectedUnit;
}

if ($selectedRole !== '') {
    $userQuery .= ' AND role = :role';
    $userParams['role'] = $selectedRole;
}

$userQuery .= ' ORDER BY unit, role, nama';
$userStmt = db()->prepare($userQuery);
$userStmt->execute($userParams);
$users = $userStmt->fetchAll();

$targetStmt = db()->prepare(
    'SELECT *
     FROM target_kinerja
     WHERE tahun = :tahun AND user_id = :user_id
     ORDER BY id'
);

$evalStmt = db()->prepare(
    'SELECT target_id, triwulan, COUNT(*) AS total
     FROM evaluasi
     WHERE target_id IN (
        SELECT id FROM target_kinerja WHERE tahun = :tahun AND user_id = :user_id
     )
     GROUP BY target_id, triwulan'
);

$rows = [];
$missingUsers = [];
$summary = [
    'users' => count($users),
    'filled' => 0,
    'missing' => 0,
    'need_eval' => 0,
    'done_eval' => 0,
    'average_sum' => 0.0,
    'average_count' => 0,
    'targets' => 0,
    'realized_targets' => 0,
];
$unitSummary = [];
$roleSummary = [];

foreach ($users as $owner) {
    $targetStmt->execute(['tahun' => $tahun, 'user_id' => (int) $owner['id']]);
    $targets = $targetStmt->fetchAll();

    $evalStmt->execute(['tahun' => $tahun, 'user_id' => (int) $owner['id']]);
    $evaluationMap = [];
    foreach ($evalStmt->fetchAll() as $eval) {
        $evaluationMap[(int) $eval['target_id'] . '-' . (int) $eval['triwulan']] = (int) $eval['total'];
    }

    $weightedTotal = 0.0;
    $weightSum = 0.0;
    $needEval = 0;
    $doneEval = 0;
    $realizedTargets = 0;
    $trendCounts = ['naik' => 0, 'turun' => 0, 'tetap' => 0, 'baseline' => 0];

    foreach ($targets as $target) {
        $weight = max(0, num($target['bobot'] ?? 1));
        $achievement = achievement_for_quarter($target, $tw);
        $weightedTotal += $achievement * $weight;
        $weightSum += $weight;

        $trend = achievement_trend($target, $tw);
        $trendCounts[$trend['status']] = ($trendCounts[$trend['status']] ?? 0) + 1;

        $needEval++;
        if (($evaluationMap[(int) $target['id'] . '-' . $tw] ?? 0) > 0) {
            $doneEval++;
        }

        if (num($target['real_tw' . $tw] ?? 0) > 0) {
            $realizedTargets++;
        }
    }

    $average = $weightSum > 0 ? round($weightedTotal / $weightSum, 2) : null;
    $targetCount = count($targets);
    $evalPercent = $needEval > 0 ? round($doneEval / $needEval * 100, 2) : 0;
    $realizationPercent = $targetCount > 0 ? round($realizedTargets / $targetCount * 100, 2) : 0;

    if ($targetCount > 0) {
        $summary['filled']++;
    } else {
        $summary['missing']++;
        $missingUsers[] = $owner;
    }

    $summary['targets'] += $targetCount;
    $summary['realized_targets'] += $realizedTargets;
    $summary['need_eval'] += $needEval;
    $summary['done_eval'] += $doneEval;

    if ($average !== null) {
        $summary['average_sum'] += $average;
        $summary['average_count']++;
    }

    $unit = (string) $owner['unit'];
    if (!isset($unitSummary[$unit])) {
        $unitSummary[$unit] = ['users' => 0, 'filled' => 0, 'sum' => 0.0, 'count' => 0];
    }
    $unitSummary[$unit]['users']++;
    $unitSummary[$unit]['filled'] += $targetCount > 0 ? 1 : 0;
    if ($average !== null) {
        $unitSummary[$unit]['sum'] += $average;
        $unitSummary[$unit]['count']++;
    }

    $role = (string) $owner['role'];
    if (!isset($roleSummary[$role])) {
        $roleSummary[$role] = ['users' => 0, 'filled' => 0, 'sum' => 0.0, 'count' => 0];
    }
    $roleSummary[$role]['users']++;
    $roleSummary[$role]['filled'] += $targetCount > 0 ? 1 : 0;
    if ($average !== null) {
        $roleSummary[$role]['sum'] += $average;
        $roleSummary[$role]['count']++;
    }

    $status = 'Lengkap';
    if ($targetCount === 0) {
        $status = 'Belum isi target';
    } elseif ($realizedTargets < $targetCount) {
        $status = 'Realisasi TW belum lengkap';
    } elseif ($doneEval < $needEval) {
        $status = 'EvKin belum lengkap';
    }

    $rows[] = [
        'user' => $owner,
        'target_count' => $targetCount,
        'realized_targets' => $realizedTargets,
        'realization_percent' => $realizationPercent,
        'average' => $average,
        'need_eval' => $needEval,
        'done_eval' => $doneEval,
        'eval_percent' => $evalPercent,
        'trend_counts' => $trendCounts,
        'status' => $status,
    ];
}

$overallAverage = $summary['average_count'] > 0
    ? round($summary['average_sum'] / $summary['average_count'], 2)
    : null;
$overallEvalPercent = $summary['need_eval'] > 0
    ? round($summary['done_eval'] / $summary['need_eval'] * 100, 2)
    : 0;
$overallRealizationPercent = $summary['targets'] > 0
    ? round($summary['realized_targets'] / $summary['targets'] * 100, 2)
    : 0;
$fillPercent = $summary['users'] > 0
    ? round($summary['filled'] / $summary['users'] * 100, 2)
    : 0;

render_header('Dashboard Monitoring');
?>
<form method="get" class="toolbar monitor-toolbar">
    <input type="hidden" name="page" value="monitoring">
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
    <label>
        Unit
        <select name="unit">
            <option value="">Semua Unit</option>
            <?php foreach ($unitOptions as $unit): ?>
                <option value="<?= h((string) $unit) ?>" <?= (string) $unit === $selectedUnit ? 'selected' : '' ?>>
                    <?= h((string) $unit) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Role
        <select name="role">
            <option value="">Semua Role</option>
            <?php foreach (role_options() as $role => $label): ?>
                <option value="<?= h($role) ?>" <?= $role === $selectedRole ? 'selected' : '' ?>>
                    <?= h($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit" class="secondary">Tampilkan</button>
    <button type="button" onclick="window.print()">Cetak Rekap TW<?= $tw ?></button>
</form>

<section class="panel monitoring-print-title">
    <h2>Rekap Monitoring Capaian Kinerja TW<?= $tw ?> Tahun <?= h((string) $tahun) ?></h2>
    <p class="muted">
        Filter:
        unit <?= h($selectedUnit !== '' ? $selectedUnit : 'semua') ?>,
        role <?= h($selectedRole !== '' ? role_label($selectedRole) : 'semua') ?>.
        Dokumen ini disiapkan sebagai bahan cetak rekap triwulan untuk Badan Pengawasan MA RI.
    </p>
</section>

<section class="monitoring-stats">
    <article class="panel stat-card">
        <span>User Aktif</span>
        <strong><?= h((string) $summary['users']) ?></strong>
    </article>
    <article class="panel stat-card">
        <span>Sudah Isi Target</span>
        <strong><?= h((string) $summary['filled']) ?></strong>
    </article>
    <article class="panel stat-card">
        <span>Belum Isi Target</span>
        <strong><?= h((string) $summary['missing']) ?></strong>
    </article>
    <article class="panel stat-card">
        <span>Realisasi TW<?= $tw ?></span>
        <strong><?= h((string) $overallRealizationPercent) ?>%</strong>
    </article>
    <article class="panel stat-card">
        <span>Rata-rata Capaian</span>
        <strong><?= $overallAverage !== null ? h((string) $overallAverage) . '%' : '-' ?></strong>
    </article>
    <article class="panel stat-card">
        <span>Kelengkapan EvKin</span>
        <strong><?= h((string) $overallEvalPercent) ?>%</strong>
    </article>
</section>

<section class="monitoring-dashboard-grid">
    <article class="panel monitor-chart-card">
        <h2>Grafik Status Pengisian</h2>
        <div class="progress-row">
            <div>
                <strong>Target Kinerja</strong>
                <span><?= h((string) $summary['filled']) ?>/<?= h((string) $summary['users']) ?> user</span>
            </div>
            <div class="progress-track"><span style="width: <?= h((string) $fillPercent) ?>%"></span></div>
        </div>
        <div class="progress-row">
            <div>
                <strong>Realisasi TW<?= $tw ?></strong>
                <span><?= h((string) $summary['realized_targets']) ?>/<?= h((string) $summary['targets']) ?> indikator</span>
            </div>
            <div class="progress-track accent"><span style="width: <?= h((string) $overallRealizationPercent) ?>%"></span></div>
        </div>
        <div class="progress-row">
            <div>
                <strong>Evaluasi Kinerja</strong>
                <span><?= h((string) $summary['done_eval']) ?>/<?= h((string) $summary['need_eval']) ?> narasi</span>
            </div>
            <div class="progress-track gold"><span style="width: <?= h((string) $overallEvalPercent) ?>%"></span></div>
        </div>
    </article>

    <article class="panel monitor-chart-card">
        <h2>Grafik Capaian per Unit</h2>
        <?php if (!$unitSummary): ?>
            <p class="muted">Belum ada user sesuai filter.</p>
        <?php endif; ?>
        <?php foreach ($unitSummary as $unit => $detail): ?>
            <?php $unitAverage = $detail['count'] > 0 ? round($detail['sum'] / $detail['count'], 2) : 0; ?>
            <div class="mini-bar-row">
                <span><?= h((string) $unit) ?></span>
                <div class="mini-bar"><span style="width: <?= h((string) min(100, $unitAverage)) ?>%"></span></div>
                <strong><?= h((string) $unitAverage) ?>%</strong>
            </div>
        <?php endforeach; ?>
    </article>
</section>

<section class="panel missing-users-panel">
    <div class="section-heading-row">
        <div>
            <h2>User Belum Mengisi Target Tahun <?= h((string) $tahun) ?></h2>
            <p class="muted">Daftar ini membantu Perencanaan menindaklanjuti unit yang belum memiliki data target.</p>
        </div>
        <strong><?= h((string) count($missingUsers)) ?> user</strong>
    </div>
    <?php if (!$missingUsers): ?>
        <p class="muted">Semua user pada filter ini sudah memiliki target kinerja.</p>
    <?php else: ?>
        <div class="missing-user-list">
            <?php foreach ($missingUsers as $owner): ?>
                <a href="index.php?page=target&tahun=<?= h((string) $tahun) ?>&user_id=<?= h((string) $owner['id']) ?>">
                    <strong><?= h((string) $owner['nama']) ?></strong>
                    <span><?= h(role_label((string) $owner['role'])) ?> | <?= h((string) $owner['unit']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>Rekap Monitoring User TW<?= $tw ?> Tahun <?= h((string) $tahun) ?></h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>User</th>
                <th>Role</th>
                <th>Unit</th>
                <th>Target</th>
                <th>Realisasi TW<?= $tw ?></th>
                <th>Capaian Berbobot</th>
                <th>EvKin</th>
                <th>Tren</th>
                <th>Status</th>
                <th class="print-hide">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="10">Tidak ada user sesuai filter monitoring.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php $owner = $row['user']; ?>
                <tr>
                    <td>
                        <strong><?= h((string) $owner['nama']) ?></strong>
                        <br><small><?= h((string) $owner['username']) ?></small>
                    </td>
                    <td><?= h(role_label((string) $owner['role'])) ?></td>
                    <td><?= h((string) $owner['unit']) ?></td>
                    <td><?= h((string) $row['target_count']) ?> indikator</td>
                    <td>
                        <?= h((string) $row['realized_targets']) ?>/<?= h((string) $row['target_count']) ?>
                        <br><small><?= h((string) $row['realization_percent']) ?>%</small>
                    </td>
                    <td><?= $row['average'] !== null ? h((string) $row['average']) . '%' : '-' ?></td>
                    <td><?= h((string) $row['done_eval']) ?>/<?= h((string) $row['need_eval']) ?> (<?= h((string) $row['eval_percent']) ?>%)</td>
                    <td>
                        Naik: <?= h((string) ($row['trend_counts']['naik'] ?? 0)) ?>,
                        Turun: <?= h((string) ($row['trend_counts']['turun'] ?? 0)) ?>,
                        Tetap: <?= h((string) ($row['trend_counts']['tetap'] ?? 0)) ?>
                    </td>
                    <td><span class="small-badge"><?= h((string) $row['status']) ?></span></td>
                    <td class="print-hide">
                        <div class="table-actions">
                            <a class="button secondary" href="index.php?page=target&tahun=<?= h((string) $tahun) ?>&user_id=<?= h((string) $owner['id']) ?>">Target</a>
                            <a class="button secondary" href="index.php?page=capaian&tahun=<?= h((string) $tahun) ?>&tw=<?= h((string) $tw) ?>&user_id=<?= h((string) $owner['id']) ?>">Capaian</a>
                            <a class="button secondary" href="index.php?page=evaluasi&tahun=<?= h((string) $tahun) ?>&triwulan=<?= h((string) $tw) ?>&user_id=<?= h((string) $owner['id']) ?>">EvKin</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel role-summary-panel">
    <h2>Rekap Berdasarkan Role</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Role</th>
                <th>User</th>
                <th>Sudah Isi Target</th>
                <th>Rata-rata Capaian TW<?= $tw ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$roleSummary): ?>
                <tr><td colspan="4">Belum ada data role sesuai filter.</td></tr>
            <?php endif; ?>
            <?php foreach ($roleSummary as $role => $detail): ?>
                <?php $roleAverage = $detail['count'] > 0 ? round($detail['sum'] / $detail['count'], 2) : null; ?>
                <tr>
                    <td><?= h(role_label((string) $role)) ?></td>
                    <td><?= h((string) $detail['users']) ?></td>
                    <td><?= h((string) $detail['filled']) ?></td>
                    <td><?= $roleAverage !== null ? h((string) $roleAverage) . '%' : '-' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render_footer(); ?>
