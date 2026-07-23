<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();
$canViewAll = user_can('view_all_targets');
$selectedUserId = $canViewAll ? (int) ($_GET['user_id'] ?? 0) : (int) $user['id'];
$bulan = (int) ($_GET['bulan'] ?? 1);
$bulan = max(1, min(12, $bulan));
$months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
$bulanColumn = 'real_' . $months[$bulan - 1];

$owners = [];
if ($canViewAll) {
    $owners = db()->query('SELECT id, nama, role, unit FROM users WHERE status = "active" ORDER BY unit, role, nama')->fetchAll();
}

$isFilterSubmitted = isset($_GET['filter_submitted']);
$rows = [];
$average = null;

if ($isFilterSubmitted) {
    $query = "SELECT tk.*, tk.{$bulanColumn} AS realisasi, u.nama AS owner_nama, u.role AS owner_role
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
        $target = target_for_month($row, $bulan);
        $realisasi = num($row['realisasi']);
        $weight = max(0, num($row['bobot'] ?? 1));
        $row['target_bulan'] = $target;
        $row['capaian'] = achievement_value($target, $realisasi, (string) ($row['tipe_indikator'] ?? 'max'));
        $row['nilai_tertimbang'] = round($row['capaian'] * $weight, 2);
        $weightedTotal += $row['nilai_tertimbang'];
        $weightSum += $weight;
    }
    unset($row);

    $average = $weightSum > 0 ? round($weightedTotal / $weightSum, 2) : null;
}

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
    <input type="hidden" name="filter_submitted" value="1">
    <label>
        Tahun
        <input type="number" name="tahun" min="2020" max="2100" value="<?= h((string) $tahun) ?>">
    </label>
    <label>
        Bulan
        <select name="bulan">
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= $i === $bulan ? 'selected' : '' ?>>Bulan <?= $i ?></option>
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
                        <?= format_user_label($owner['nama'] ?? '', $owner['role'] ?? '', false) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>
    <button type="submit">Hitung</button>
</form>

<?php if ($isFilterSubmitted): ?>
<section class="panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="margin: 0;">Data Capaian Kinerja</h3>
        <button type="button" class="button secondary" onclick="printSelectedPDF()">Print Preview (PDF)</button>
    </div>
    <div class="table-wrap">
        <div class="table-responsive" id="dataTableContainer" style="max-height: 500px; overflow-y: auto;">
<table>
            <thead>
            <tr>
                <th style="width: 40px; text-align: center;">
                    <input type="checkbox" id="selectAllPrint" onclick="document.querySelectorAll('.print-checkbox').forEach(cb => cb.checked = this.checked)">
                </th>
                <?php if ($canViewAll): ?>
                    <th>Pemilik</th>
                <?php endif; ?>
                <th>Indikator Kinerja</th>
                <th>Sumber Data</th>
                <th>Tipe</th>
                <th>Bobot</th>
                <th>Target Bulan <?= $bulan ?></th>
                <th>Realisasi</th>
                <th>Capaian (%)</th>
                <th>Nilai Tertimbang</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="<?= $canViewAll ? '10' : '9' ?>">Belum ada data Target Kinerja untuk tahun ini.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="print-checkbox" value="<?= h((string) $row['id']) ?>">
                    </td>
                    <?php if ($canViewAll): ?>
                        <td class="hck-owner-cell" data-owner-key="<?= h(strtolower(trim((string) ($row['owner_nama'] ?? '')) . '|' . role_label((string) ($row['owner_role'] ?? '')))) ?>">
                            <?= format_user_label($row['owner_nama'] ?? '', $row['owner_role'] ?? '', true) ?>
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
                    <td><?= h((string) $row['target_bulan']) ?></td>
                    <td>
                        <?= h((string) $row['realisasi']) ?>
                    </td>
                    <td><?= h((string) $row['capaian']) ?></td>
                    <td><?= h((string) $row['nilai_tertimbang']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
</div>
    </div>
    <div class="stat">
        Totalitas Capaian Kinerja Berbobot Bulan <?= $bulan ?>:
        <?= $average !== null ? h((string) $average) . '%' : 'Belum tersedia' ?>
    </div>
</section>
<?php endif; ?>

<style>
@media print {
    @page {
        size: landscape;
        margin: 1cm;
    }
    .table-responsive {
        max-height: none !important;
        overflow: visible !important;
    }
    .hck-owner-cell {
        vertical-align: middle !important;
        page-break-inside: avoid;
    }
}
</style>

<script>
function groupPrintedOwners() {
    const visibleRows = Array.from(document.querySelectorAll('#dataTableContainer tbody tr')).filter(row => {
        const checkbox = row.querySelector('.print-checkbox');
        return checkbox && checkbox.checked;
    });
    const groups = new Map();
    visibleRows.forEach(row => {
        const cell = row.querySelector('.hck-owner-cell');
        if (!cell) return;
        const key = cell.dataset.ownerKey || cell.textContent.trim().toLocaleLowerCase('id-ID');
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(cell);
    });
    groups.forEach(cells => {
        cells[0].rowSpan = cells.length;
        cells.slice(1).forEach(cell => cell.style.display = 'none');
    });
}

function restorePrintedOwners() {
    document.querySelectorAll('.hck-owner-cell').forEach(cell => {
        cell.removeAttribute('rowspan');
        cell.style.display = '';
    });
}

function printSelectedPDF() {
    const selectedCheckboxes = Array.from(document.querySelectorAll('.print-checkbox:checked'));
    if (selectedCheckboxes.length === 0) {
        alert('Pilih setidaknya satu data untuk di-print.');
        return;
    }
    
    // Add .print-hide to unselected rows
    const allRows = document.querySelectorAll('#dataTableContainer tbody tr');
    allRows.forEach(tr => {
        const cb = tr.querySelector('.print-checkbox');
        if (cb && !cb.checked) {
            tr.classList.add('print-hide');
        }
    });

    const analysisRule = document.querySelector('.analysis-rule');
    if (analysisRule) analysisRule.classList.add('print-hide');
    
    const tableHeaderActions = document.querySelector('section.panel > div:first-of-type');
    if (tableHeaderActions) tableHeaderActions.classList.add('print-hide');

    document.querySelectorAll('#selectAllPrint, .print-checkbox').forEach(el => {
        if (el.parentElement) el.parentElement.classList.add('print-hide');
    });

    groupPrintedOwners();
    window.print();
    restorePrintedOwners();

    allRows.forEach(tr => tr.classList.remove('print-hide'));
    if (analysisRule) analysisRule.classList.remove('print-hide');
    if (tableHeaderActions) tableHeaderActions.classList.remove('print-hide');
    document.querySelectorAll('#selectAllPrint, .print-checkbox').forEach(el => {
        if (el.parentElement) el.parentElement.classList.remove('print-hide');
    });
}
</script>

<?php render_footer(); ?>
