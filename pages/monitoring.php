<?php
declare(strict_types=1);

require_permission('view_all_targets');

$tahun = year_value();
$tw = max(1, min(4, (int) ($_GET['tw'] ?? 1)));

$users = db()->query(
    'SELECT id, username, nama, role, unit, status
     FROM users
     WHERE status = "active"
     ORDER BY unit, role, nama'
)->fetchAll();

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
$summary = [
    'users' => count($users),
    'filled' => 0,
    'missing' => 0,
    'need_eval' => 0,
    'done_eval' => 0,
    'average_sum' => 0.0,
    'average_count' => 0,
];

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
    }

    $average = $weightSum > 0 ? round($weightedTotal / $weightSum, 2) : null;
    $targetCount = count($targets);
    $evalPercent = $needEval > 0 ? round($doneEval / $needEval * 100, 2) : 0;

    if ($targetCount > 0) {
        $summary['filled']++;
    } else {
        $summary['missing']++;
    }

    $summary['need_eval'] += $needEval;
    $summary['done_eval'] += $doneEval;

    if ($average !== null) {
        $summary['average_sum'] += $average;
        $summary['average_count']++;
    }

    $rows[] = [
        'user' => $owner,
        'target_count' => $targetCount,
        'average' => $average,
        'need_eval' => $needEval,
        'done_eval' => $doneEval,
        'eval_percent' => $evalPercent,
        'trend_counts' => $trendCounts,
    ];
}

$overallAverage = $summary['average_count'] > 0
    ? round($summary['average_sum'] / $summary['average_count'], 2)
    : null;
$overallEvalPercent = $summary['need_eval'] > 0
    ? round($summary['done_eval'] / $summary['need_eval'] * 100, 2)
    : 0;

render_header('Monitoring Seluruh User');
?>
<form method="get" class="toolbar">
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
    <button type="submit" class="secondary">Tampilkan</button>
</form>

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
        <span>Rata-rata Capaian TW<?= $tw ?></span>
        <strong><?= $overallAverage !== null ? h((string) $overallAverage) . '%' : '-' ?></strong>
    </article>
    <article class="panel stat-card">
        <span>Kelengkapan EvKin</span>
        <strong><?= h((string) $overallEvalPercent) ?>%</strong>
    </article>
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
                <th>Capaian Berbobot</th>
                <th>EvKin</th>
                <th>Tren</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <?php
                $owner = $row['user'];
                $status = $row['target_count'] === 0
                    ? 'Belum isi target'
                    : ($row['done_eval'] < $row['need_eval'] ? 'EvKin belum lengkap' : 'Lengkap');
                ?>
                <tr>
                    <td>
                        <strong><?= h((string) $owner['nama']) ?></strong>
                        <br><small><?= h((string) $owner['username']) ?></small>
                    </td>
                    <td><?= h(role_label((string) $owner['role'])) ?></td>
                    <td><?= h((string) $owner['unit']) ?></td>
                    <td><?= h((string) $row['target_count']) ?> indikator</td>
                    <td><?= $row['average'] !== null ? h((string) $row['average']) . '%' : '-' ?></td>
                    <td><?= h((string) $row['done_eval']) ?>/<?= h((string) $row['need_eval']) ?> (<?= h((string) $row['eval_percent']) ?>%)</td>
                    <td>
                        Naik: <?= h((string) ($row['trend_counts']['naik'] ?? 0)) ?>,
                        Turun: <?= h((string) ($row['trend_counts']['turun'] ?? 0)) ?>,
                        Tetap: <?= h((string) ($row['trend_counts']['tetap'] ?? 0)) ?>
                    </td>
                    <td><span class="small-badge"><?= h($status) ?></span></td>
                    <td>
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
<?php render_footer(); ?>
