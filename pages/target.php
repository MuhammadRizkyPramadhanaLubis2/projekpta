<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();
$canViewAll = user_can('view_all_targets');
$canEditAll = user_can('edit_all_targets');
$selectedUserId = $canViewAll ? (int) ($_GET['user_id'] ?? $_POST['user_id'] ?? 0) : (int) $user['id'];
$selectedSasaran = trim((string) ($_GET['sasaran_filter'] ?? $_POST['sasaran_filter'] ?? ''));
$profile = role_profile((string) $user['role']);
$satuanOptions = ['Persen', 'Nilai', 'Perkara', 'Dokumen', 'Laporan', 'Kegiatan', 'Orang', 'Hari', 'Bulan', 'Unit'];
$sourceOptions = array_values(array_unique(array_merge(
    $profile['sources'],
    ['SAKTI', 'OMSPAN Kemenkeu', 'E-BIMA MARI', 'E-SADEWA MARI', 'SIPP', 'SIKEP', 'MY ASN', 'Direktori Putusan', 'Database aplikasi IKPA']
)));

$sasaranKinerjaOptions = [
    '1. Terwujudnya peradilan yang efektif transparan, akuntabel, responsive dan modern',
    '2. Meningkatnya tingkat keyakinan dan kepercayaan public',
    '3. Terwujudnya manajemen peradilan yang transparan dan profesional',
];
$indikatorKinerjaOptions = [
    '1.1 Persentase penyelesaian perkara secara tepat Waktu',
    '1.2 Persentase pengiriman salinan putusan tepat waktu oleh pengadilan tingkat banding kepada pengadilan pengaju',
    '1.3 Persentase putusan pengadilan yang diunggah pada direktori putusan',
    '1.4 Persentase perkara perdata pada tingkat banding yang menggunakan e-Court',
    '2.1 Indeks kepuasan pengguna layanan pengadilan berdasarkan standar layanan yang ditetapkan',
    '3.1 Indeks Profesionalitas Aparatur Sipil Negara (IP ASN) satuan kerja pengadilan',
    '3.2 a. Nilai Indikator Kinerja Pelaksanaan Anggaran (IKPA) DIPA BUA (01) satuan kerja pengadilan',
    '3.2 b. Nilai Indikator Kinerja Pelaksanaan Anggaran (IKPA) DIPA BADILAG (04) satuan kerja pengadilan',
    '3.3 a. Nilai Kinerja Perencanaan Anggaran DIPA BUA (01)',
    '3.3 b. Nilai Kinerja Perencanaan Anggaran DIPA BADILAG (04)',
    '3.4 Nilai Indikator Pengelolaan Aset (IPA) satuan kerja pengadilan',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $deleteId = (int) ($_POST['delete_id'] ?? 0);
        $where = 'id = :id';
        $params = ['id' => $deleteId];
        if (!user_can('edit_all_targets')) {
            $where .= ' AND user_id = :user_id';
            $params['user_id'] = (int) $user['id'];
        }

        $stmt = db()->prepare('DELETE FROM target_kinerja WHERE ' . $where);
        $stmt->execute($params);
        flash('Baris target kinerja dihapus.');
        header('Location: index.php?page=target&filter_submitted=1&tahun=' . year_value() . ($selectedUserId > 0 ? '&user_id=' . $selectedUserId : '') . ($selectedSasaran !== '' ? '&sasaran_filter=' . urlencode($selectedSasaran) : ''));
        exit;
    }

    $ids = $_POST['id'] ?? [];
    $sasaran = $_POST['sasaran'] ?? [];
    $indikator = $_POST['indikator'] ?? [];
    $satuan = $_POST['satuan'] ?? [];
    $tipeIndikator = $_POST['tipe_indikator'] ?? [];
    $sumberData = $_POST['sumber_data'] ?? [];
    $bobot = $_POST['bobot'] ?? [];
    $targets = $_POST['target'] ?? [];
    $pilihanDipa = $_POST['pilihan_dipa'] ?? [];
    $nilaiDipa = $_POST['nilai_dipa'] ?? [];
    $real_jan = $_POST['real_jan'] ?? [];
    $real_feb = $_POST['real_feb'] ?? [];
    $real_mar = $_POST['real_mar'] ?? [];
    $real_apr = $_POST['real_apr'] ?? [];
    $real_mei = $_POST['real_mei'] ?? [];
    $real_jun = $_POST['real_jun'] ?? [];
    $real_jul = $_POST['real_jul'] ?? [];
    $real_agu = $_POST['real_agu'] ?? [];
    $real_sep = $_POST['real_sep'] ?? [];
    $real_okt = $_POST['real_okt'] ?? [];
    $real_nov = $_POST['real_nov'] ?? [];
    $real_des = $_POST['real_des'] ?? [];
    $realTw1 = $_POST['real_tw1'] ?? [];
    $realTw2 = $_POST['real_tw2'] ?? [];
    $realTw3 = $_POST['real_tw3'] ?? [];
    $realTw4 = $_POST['real_tw4'] ?? [];
                                    $analisisCapaian = $_POST['analisis_capaian'] ?? [];

                                
    $insert = db()->prepare(
        'INSERT INTO target_kinerja
         (tahun, unit, sasaran, indikator, satuan, tipe_indikator, sumber_data, bobot,
          target, target_jan, target_feb, target_mar, target_apr, target_mei, target_jun, target_jul, target_agu, target_sep, target_okt, target_nov, target_des, target_tw1, target_tw2, target_tw3, target_tw4, dipa01, dipa04, real_jan, real_feb, real_mar, real_apr, real_mei, real_jun, real_jul, real_agu, real_sep, real_okt, real_nov, real_des, real_tw1, real_tw2, real_tw3, real_tw4, analisis_capaian,
          user_id, metadata)
         VALUES
         (:tahun, :unit, :sasaran, :indikator, :satuan, :tipe_indikator, :sumber_data, :bobot,
          :target, :target_jan, :target_feb, :target_mar, :target_apr, :target_mei, :target_jun, :target_jul, :target_agu, :target_sep, :target_okt, :target_nov, :target_des, :target_tw1, :target_tw2, :target_tw3, :target_tw4, :dipa01, :dipa04, :real_jan, :real_feb, :real_mar, :real_apr, :real_mei, :real_jun, :real_jul, :real_agu, :real_sep, :real_okt, :real_nov, :real_des, :real_tw1, :real_tw2, :real_tw3, :real_tw4, :analisis_capaian,
          :user_id, :metadata)'
    );
    $update = db()->prepare(
        'UPDATE target_kinerja
         SET sasaran = :sasaran, indikator = :indikator,
             satuan = :satuan, tipe_indikator = :tipe_indikator, sumber_data = :sumber_data, bobot = :bobot,
             target = :target, target_jan = :target_jan,
             target_feb = :target_feb,
             target_mar = :target_mar,
             target_apr = :target_apr,
             target_mei = :target_mei,
             target_jun = :target_jun,
             target_jul = :target_jul,
             target_agu = :target_agu,
             target_sep = :target_sep,
             target_okt = :target_okt,
             target_nov = :target_nov,
             target_des = :target_des,
             target_tw1 = :target_tw1, target_tw2 = :target_tw2,
             target_tw3 = :target_tw3, target_tw4 = :target_tw4,
             dipa01 = :dipa01, dipa04 = :dipa04,
             real_jan = :real_jan,
             real_feb = :real_feb,
             real_mar = :real_mar,
             real_apr = :real_apr,
             real_mei = :real_mei,
             real_jun = :real_jun,
             real_jul = :real_jul,
             real_agu = :real_agu,
             real_sep = :real_sep,
             real_okt = :real_okt,
             real_nov = :real_nov,
             real_des = :real_des,
             real_tw1 = :real_tw1, real_tw2 = :real_tw2,
             real_tw3 = :real_tw3, real_tw4 = :real_tw4,
             analisis_capaian = :analisis_capaian,
             metadata = :metadata,
             updated_at = CURRENT_TIMESTAMP
         WHERE id = :id'
    );

    foreach ($sasaran as $i => $value) {
        $rowSasaran = trim((string) $value);
        $rowIndikator = trim((string) ($indikator[$i] ?? ''));
        if ($rowSasaran === '' && $rowIndikator === '') {
            continue;
        }

        $ownerId = $selectedUserId > 0 && user_can('edit_all_targets') ? $selectedUserId : (int) $user['id'];
        $ownerUnit = $user['unit'];


        if ($ownerId !== (int) $user['id']) {
            $ownerStmt = db()->prepare('SELECT unit FROM users WHERE id = :id AND status = "active"');
            $ownerStmt->execute(['id' => $ownerId]);
            $ownerUnit = (string) ($ownerStmt->fetchColumn() ?: $user['unit']);
        }

        $r_jan = num($real_jan[$i] ?? 0);
        $r_feb = num($real_feb[$i] ?? 0);
        $r_mar = num($real_mar[$i] ?? 0);
        $r_apr = num($real_apr[$i] ?? 0);
        $r_mei = num($real_mei[$i] ?? 0);
        $r_jun = num($real_jun[$i] ?? 0);
        $r_jul = num($real_jul[$i] ?? 0);
        $r_agu = num($real_agu[$i] ?? 0);
        $r_sep = num($real_sep[$i] ?? 0);
        $r_okt = num($real_okt[$i] ?? 0);
        $r_nov = num($real_nov[$i] ?? 0);
        $r_des = num($real_des[$i] ?? 0);

        $id = (int) ($ids[$i] ?? 0);
        $existingMetaStr = '{}';
        if ($id > 0) {
            $metaStmt = db()->prepare('SELECT metadata FROM target_kinerja WHERE id = :id');
            $metaStmt->execute(['id' => $id]);
            $existingMetaStr = (string) ($metaStmt->fetchColumn() ?: '{}');
        }
        $metaData = json_decode($existingMetaStr, true);
        if (!is_array($metaData)) {
            $metaData = [];
        }

        $payload = [
            'unit' => $ownerUnit,
            'sasaran' => $rowSasaran,
            'indikator' => $rowIndikator,
            'satuan' => trim((string) ($satuan[$i] ?? '')),
            'tipe_indikator' => array_key_exists((string) ($tipeIndikator[$i] ?? 'max'), indicator_type_options()) ? (string) $tipeIndikator[$i] : 'max',
            'sumber_data' => trim((string) ($sumberData[$i] ?? '')),
            'bobot' => max(0, num($bobot[$i] ?? 1)),
            'target' => num($targets[$i] ?? 0),
            'target_jan' => num($targets[$i] ?? 0), 'target_feb' => num($targets[$i] ?? 0), 'target_mar' => num($targets[$i] ?? 0),
            'target_apr' => num($targets[$i] ?? 0), 'target_mei' => num($targets[$i] ?? 0), 'target_jun' => num($targets[$i] ?? 0),
            'target_jul' => num($targets[$i] ?? 0), 'target_agu' => num($targets[$i] ?? 0), 'target_sep' => num($targets[$i] ?? 0),
            'target_okt' => num($targets[$i] ?? 0), 'target_nov' => num($targets[$i] ?? 0), 'target_des' => num($targets[$i] ?? 0),
            'target_tw1' => num($targets[$i] ?? 0), 'target_tw2' => num($targets[$i] ?? 0),
            'target_tw3' => num($targets[$i] ?? 0), 'target_tw4' => num($targets[$i] ?? 0),
            'dipa01' => ($pilihanDipa[$i] ?? '01') === '01' ? num($nilaiDipa[$i] ?? 0) : 0,
            'dipa04' => ($pilihanDipa[$i] ?? '01') === '04' ? num($nilaiDipa[$i] ?? 0) : 0,
            'real_jan' => $r_jan, 'real_feb' => $r_feb, 'real_mar' => $r_mar,
            'real_apr' => $r_apr, 'real_mei' => $r_mei, 'real_jun' => $r_jun,
            'real_jul' => $r_jul, 'real_agu' => $r_agu, 'real_sep' => $r_sep,
            'real_okt' => $r_okt, 'real_nov' => $r_nov, 'real_des' => $r_des,
            'real_tw1' => num($realTw1[$i] ?? 0), 'real_tw2' => num($realTw2[$i] ?? 0),
            'real_tw3' => num($realTw3[$i] ?? 0), 'real_tw4' => num($realTw4[$i] ?? 0),
            'analisis_capaian' => trim((string) ($analisisCapaian[$i] ?? '')),
            'metadata' => json_encode($metaData),
        ];

        if ($id > 0) {
            $checkStmt = db()->prepare('SELECT * FROM target_kinerja WHERE id = :id');
            $checkStmt->execute(['id' => $id]);
            $existing = $checkStmt->fetch();
            if ($existing && can_manage_target_row($existing)) {
                $updatePayload = $payload;
                unset($updatePayload['unit']);
                $update->execute($updatePayload + ['id' => $id]);
            }
        } else {
            $insert->execute($payload + ['tahun' => $tahun, 'user_id' => $ownerId]);
        }
    }

    flash('Data target kinerja tersimpan.');
    header('Location: index.php?page=target&filter_submitted=1&tahun=' . $tahun . ($selectedUserId > 0 ? '&user_id=' . $selectedUserId : '') . ($selectedSasaran !== '' ? '&sasaran_filter=' . urlencode($selectedSasaran) : ''));
    exit;
}

$owners = [];
if ($canViewAll) {
    $owners = db()->query('SELECT id, nama, role, unit FROM users WHERE status = "active" ORDER BY unit, role, nama')->fetchAll();
}

$query = 'SELECT tk.*, u.nama AS owner_nama, u.role AS owner_role
          FROM target_kinerja tk
          LEFT JOIN users u ON u.id = tk.user_id
          WHERE tk.tahun = :tahun';
$params = ['tahun' => $tahun];

if ($canViewAll && $selectedUserId > 0) {
    $query .= ' AND tk.user_id = :user_id';

    $params['user_id'] = $selectedUserId;
} elseif (!$canViewAll) {
    $query .= ' AND tk.user_id = :user_id';
    $params['user_id'] = (int) $user['id'];
}

if ($selectedSasaran !== '') {
    $query .= ' AND tk.sasaran = :sasaran_filter';
    $params['sasaran_filter'] = $selectedSasaran;
}

$query .= ' ORDER BY u.unit, u.role, u.nama, tk.id';

$sasaranQuery = 'SELECT DISTINCT sasaran FROM target_kinerja WHERE tahun = :tahun AND TRIM(sasaran) <> ""';
$sasaranParams = ['tahun' => $tahun];
if ($canViewAll && $selectedUserId > 0) {
    $sasaranQuery .= ' AND user_id = :user_id';
    $sasaranParams['user_id'] = $selectedUserId;
} elseif (!$canViewAll) {
    $sasaranQuery .= ' AND user_id = :user_id';
    $sasaranParams['user_id'] = (int) $user['id'];
}
$sasaranQuery .= ' ORDER BY sasaran';
$sasaranStmt = db()->prepare($sasaranQuery);
$sasaranStmt->execute($sasaranParams);
$sasaranOptions = $sasaranStmt->fetchAll(PDO::FETCH_COLUMN);

$isFilterSubmitted = isset($_GET['filter_submitted']);

$rows = [];
if ($isFilterSubmitted) {
    $stmt = db()->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
}

$newRow = [
    'id' => '',
    'user_id' => $selectedUserId > 0 ? $selectedUserId : $user['id'],
    'owner_nama' => $selectedUserId > 0 ? '' : $user['nama'],
    'owner_role' => $user['role'],
    'sasaran' => '',
    'indikator' => '',
    'satuan' => '',
    'tipe_indikator' => 'max',
    'sumber_data' => '',
    'bobot' => 1,
    'target' => 0,
    'target_tw1' => 0,
    'target_tw2' => 0,
    'target_tw3' => 0,
    'target_tw4' => 0,
    'dipa01' => 0,
    'dipa04' => 0,
    'real_tw1' => 0,
    'real_tw2' => 0,
    'real_tw3' => 0,
    'real_tw4' => 0,
    'analisis_kegiatan' => '',
    'analisis_upaya' => '',
    'analisis_strategi' => '',
    'analisis_kendala' => '',
    'analisis_solusi' => '',
];

render_header('Input Target Kinerja');
?>
<section class="panel analysis-rule">
    <strong>Format Input untuk <?= h(role_label((string) $user['role'])) ?></strong>
    <p><?= h((string) $profile['scope']) ?> Realisasi triwulan diisi berdasarkan sumber: <?= h(implode(', ', $profile['sources'])) ?>.</p>
</section>


<form method="get" id="filterForm">
    <input type="hidden" name="page" value="target">
    <input type="hidden" name="filter_submitted" value="1">
</form>

<form method="post" id="targetForm">
    <input type="hidden" name="tahun" value="<?= h((string) $tahun) ?>">
    <input type="hidden" name="user_id" value="<?= h((string) $selectedUserId) ?>">
    <input type="hidden" name="sasaran_filter" value="<?= h($selectedSasaran) ?>">
    <datalist id="sasaran-kinerja-options">
        <?php foreach ($sasaranKinerjaOptions as $option): ?>
            <option value="<?= h($option) ?>"></option>
        <?php endforeach; ?>
    </datalist>
    <datalist id="indikator-kinerja-options">
        <?php foreach ($indikatorKinerjaOptions as $option): ?>
            <option value="<?= h($option) ?>"></option>
        <?php endforeach; ?>
    </datalist>
    
    <?php if ($selectedUserId === (int)$user['id'] || $selectedUserId === 0 || $canViewAll): ?>
    <section class="panel" style="margin-bottom: 1.5rem;">
        <h3 style="margin-bottom: 1rem;">Input Data Target Kinerja Baru</h3>
        <div class="table-wrap">
            <div class="table-responsive">
<table>
            <thead>
            <tr>
                <?php if ($canViewAll): ?>
                    <th>Pemilik</th>
                <?php endif; ?>
                <th>Sasaran Kinerja</th>
                <th>Indikator Kinerja</th>
                <th>Satuan</th>
                <th>Tipe</th>
                <th>Bobot</th>
                <th>Sumber Data</th>
                <th>Target</th>
                <th>Realisasi TW I</th>
                <th>Realisasi TW II</th>
                <th>Realisasi TW III</th>
                <th>Realisasi TW IV</th>
                <th>Pilihan DIPA</th>
                <th>Nilai DIPA</th>
                <th>Realisasi Januari</th>
                <th>Realisasi Februari</th>
                <th>Realisasi Maret</th>
                <th>Realisasi April</th>
                <th>Realisasi Mei</th>
                <th>Realisasi Juni</th>
                <th>Realisasi Juli</th>
                <th>Realisasi Agustus</th>
                <th>Realisasi September</th>
                <th>Realisasi Oktober</th>
                <th>Realisasi November</th>
                <th>Realisasi Desember</th>
                <th>Analisis Capaian Kinerja</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ([$newRow] as $rowIndex => $row): $rowIndex = "new"; ?>
                <?php
                $targetValue = num($row['target'] ?? 0);
                $realizationTotal = 0;
                $months_list = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
                foreach ($months_list as $m) $realizationTotal += num($row['real_'.$m] ?? 0);
                $achievementRaw = achievement_value($targetValue, $realizationTotal, (string) ($row['tipe_indikator'] ?? 'max'));
                $achievementPercent = max(0, min(100, $achievementRaw));
                $currentSatuan = (string) ($row['satuan'] ?? '');
                $currentSource = (string) ($row['sumber_data'] ?? '');
                $modalId = 'target-analysis-modal-' . $rowIndex;
                $analysisFilled = trim((string) ($row['analisis_capaian'] ?? '')) !== '';
                ?>
                <tr data-user-id="<?= h((string)($row['user_id'])) ?>">
                    <?php if ($canViewAll): ?>
                        <td>
                            <?= h((string) ($row['owner_nama'] ?: 'Baris Baru')) ?>
                            <?php if (!empty($row['owner_role'])): ?>
                                <br><small><?= h(role_label((string) $row['owner_role'])) ?></small>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    <td>
                        <input type="hidden" name="id[]" value="<?= h((string) $row['id']) ?>">
                        <input name="sasaran[]" list="sasaran-kinerja-options" value="<?= h((string) $row['sasaran']) ?>">
                    </td>
                    <td><input name="indikator[]" list="indikator-kinerja-options" value="<?= h((string) $row['indikator']) ?>"></td>
                    <td>
                        <select name="satuan[]">
                            <option value="">Pilih satuan</option>
                            <?php if ($currentSatuan !== '' && !in_array($currentSatuan, $satuanOptions, true)): ?>
                                <option value="<?= h($currentSatuan) ?>" selected><?= h($currentSatuan) ?></option>
                            <?php endif; ?>
                            <?php foreach ($satuanOptions as $option): ?>
                                <option value="<?= h($option) ?>" <?= $currentSatuan === $option ? 'selected' : '' ?>>
                                    <?= h($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="tipe_indikator[]">
                            <?php foreach (indicator_type_options() as $type => $label): ?>
                                <option value="<?= h($type) ?>" <?= ($row['tipe_indikator'] ?? 'max') === $type ? 'selected' : '' ?>>
                                    <?= h($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="bobot[]" value="<?= h((string) ($row['bobot'] ?? 1)) ?>"></td>
                    <td>
                        <select name="sumber_data[]">
                            <option value="">Pilih sumber</option>
                            <?php if ($currentSource !== '' && !in_array($currentSource, $sourceOptions, true)): ?>
                                <option value="<?= h($currentSource) ?>" selected><?= h($currentSource) ?></option>
                            <?php endif; ?>
                            <?php foreach ($sourceOptions as $option): ?>
                                <option value="<?= h($option) ?>" <?= $currentSource === $option ? 'selected' : '' ?>>
                                    <?= h($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="target[]" value="<?= h((string) $row['target']) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw1[]" value="<?= h((string) ($row['real_tw1'] ?? 0)) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw2[]" value="<?= h((string) ($row['real_tw2'] ?? 0)) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw3[]" value="<?= h((string) ($row['real_tw3'] ?? 0)) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw4[]" value="<?= h((string) ($row['real_tw4'] ?? 0)) ?>"></td>
                    <?php
                        $d01 = num($row['dipa01'] ?? 0);
                        $d04 = num($row['dipa04'] ?? 0);
                        $pilDipa = $d04 > 0 && $d01 == 0 ? '04' : '01';
                        $nilDipa = $pilDipa === '04' ? $d04 : $d01;
                    ?>
                    <td>
                        <select name="pilihan_dipa[]">
                            <option value="01" <?= $pilDipa === '01' ? 'selected' : '' ?>>DIPA 01</option>
                            <option value="04" <?= $pilDipa === '04' ? 'selected' : '' ?>>DIPA 04</option>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="nilai_dipa[]" value="<?= h((string) $nilDipa) ?>"></td>
                    <?php
                    $months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
                    foreach ($months as $m):
                        $rtw = $row['real_' . $m] ?? 0;
                    ?>
                        <td>
                            <input type="number" step="0.01" name="real_<?= $m ?>[]" value="<?= h((string) $rtw) ?>">
                        </td>
                    <?php endforeach; ?>
                    <td class="target-analysis-cell">
                        <div class="analysis-compact">
                            <span class="analysis-score"><?= h((string) round($achievementPercent, 1)) ?>%</span>
                            <span class="analysis-status <?= $analysisFilled ? 'filled' : '' ?>">
                                <?= $analysisFilled ? 'Sudah diisi' : 'Belum diisi' ?>
                            </span>
                            <button type="button" class="secondary analysis-open" data-modal-target="<?= h($modalId) ?>">
                                Analisis
                            </button>
                        </div>
                        <div class="analysis-modal" id="<?= h($modalId) ?>" aria-hidden="true">
                            <div class="analysis-modal-backdrop" data-modal-close></div>
                            <section class="analysis-dialog" role="dialog" aria-modal="true" aria-labelledby="<?= h($modalId) ?>-title">
                                <header class="analysis-dialog-header">
                                    <div>
                                        <span>Analisis Capaian Kinerja</span>
                                        <h2 id="<?= h($modalId) ?>-title"><?= h((string) ($row['indikator'] ?: 'Baris Target Baru')) ?></h2>
                                    </div>
                                    <button type="button" class="analysis-close" aria-label="Tutup analisis" data-modal-close>&times;</button>
                                </header>
                                <div class="analysis-dialog-body">
                                    <aside class="analysis-summary">
                                        <div class="target-chart large" style="--percent: <?= h((string) $achievementPercent) ?>;">
                                            <span><?= h((string) round($achievementPercent, 1)) ?>%</span>
                                        </div>
                                        <div class="analysis-metrics">
                                            <div>
                                                <span>Target</span>
                                                <strong><?= h((string) $targetValue) ?></strong>
                                            </div>
                                            <div>
                                                <span>Total Realisasi</span>
                                                <strong><?= h((string) $realizationTotal) ?></strong>
                                            </div>
                                            <div>
                                                <span>Status Analisis</span>
                                                <strong><?= $analysisFilled ? 'Sudah diisi' : 'Belum diisi' ?></strong>
                                            </div>
                                        </div>
                                    </aside>
                                    <div class="analysis-fields">
                                        <label>
                                            Kegiatan / Upaya / Strategi / Kendala / Solusi yang Dilaksanakan dalam Mencapai Target
                                            <textarea name="analisis_capaian[]" rows="10"><?= h((string) ($row['analisis_capaian'] ?? '')) ?></textarea>
                                        </label>
                                    </div>
                                </div>
                                <footer class="analysis-dialog-footer">
                                    <span>Perubahan tersimpan saat tombol Simpan di halaman utama ditekan.</span>
                                    <button type="button" data-modal-close>Tutup</button>
                                </footer>
                            </section>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($row['id'] && ($selectedUserId === (int)$user['id'] || $canEditAll)): ?>
                            <button class="danger target-delete" name="action" value="delete" onclick="this.form.delete_id.value='<?= h((string) $row['id']) ?>'" style="margin-bottom: 5px;">Hapus</button>
                        <?php endif; ?>
                        
                        <?php if (!empty($row['updated_at'])): ?>
                            <div style="font-size: 0.65rem; color: #64748b; line-height: 1.2; white-space: nowrap;">
                                Terakhir diisi:<br>
                                <?= h((string) date('d M Y, H:i', strtotime($row['updated_at']))) ?>
                            </div>
                        <?php elseif (!empty($row['created_at'])): ?>
                            <div style="font-size: 0.65rem; color: #64748b; line-height: 1.2; white-space: nowrap;">
                                Terakhir diisi:<br>
                                <?= h((string) date('d M Y, H:i', strtotime($row['created_at']))) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
            </div>
            <div style="margin-top: 1rem; text-align: left;">
                <button type="submit" name="action" value="save">Simpan Input Baru</button>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <div class="toolbar" style="margin-bottom: 1.5rem;">
        <label>
            Tahun
            <input type="number" name="tahun" min="2020" max="2100" value="<?= h((string) $tahun) ?>" form="filterForm">
        </label>
        <?php if ($canViewAll): ?>
            <label>
                Pemilik Data
                <select name="user_id" form="filterForm" onchange="document.getElementById('filterForm').querySelector('[name=sasaran_filter]').value = '';">
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
            Sasaran Kinerja
            <select name="sasaran_filter" form="filterForm">
                <option value="">Semua Sasaran Kinerja</option>
                <?php foreach ($sasaranOptions as $sasaranOption): ?>
                    <option value="<?= h((string) $sasaranOption) ?>" <?= (string) $sasaranOption === $selectedSasaran ? 'selected' : '' ?>>
                        <?= h((string) $sasaranOption) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" form="filterForm" class="secondary">Tampilkan</button>
    </div>
    
    <?php if ($isFilterSubmitted): ?>
    <section class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="margin: 0;">Data Target Kinerja</h3>
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
                <th>Sasaran Kinerja</th>
                <th>Indikator Kinerja</th>
                <th>Satuan</th>
                <th>Tipe</th>
                <th>Bobot</th>
                <th>Sumber Data</th>
                <th>Target</th>
                <th>Realisasi TW I</th>
                <th>Realisasi TW II</th>
                <th>Realisasi TW III</th>
                <th>Realisasi TW IV</th>
                <th>Pilihan DIPA</th>
                <th>Nilai DIPA</th>
                <th>Realisasi Januari</th>
                <th>Realisasi Februari</th>
                <th>Realisasi Maret</th>
                <th>Realisasi April</th>
                <th>Realisasi Mei</th>
                <th>Realisasi Juni</th>
                <th>Realisasi Juli</th>
                <th>Realisasi Agustus</th>
                <th>Realisasi September</th>
                <th>Realisasi Oktober</th>
                <th>Realisasi November</th>
                <th>Realisasi Desember</th>
                <th>Analisis Capaian Kinerja</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $rowIndex => $row): ?>
                <?php
                $targetValue = num($row['target'] ?? 0);
                $realizationTotal = 0;
                $months_list = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
                foreach ($months_list as $m) $realizationTotal += num($row['real_'.$m] ?? 0);
                $achievementRaw = achievement_value($targetValue, $realizationTotal, (string) ($row['tipe_indikator'] ?? 'max'));
                $achievementPercent = max(0, min(100, $achievementRaw));
                $currentSatuan = (string) ($row['satuan'] ?? '');
                $currentSource = (string) ($row['sumber_data'] ?? '');
                $modalId = 'target-analysis-modal-' . $rowIndex;
                $analysisFilled = trim((string) ($row['analisis_capaian'] ?? '')) !== '';
                ?>
                <tr data-user-id="<?= h((string)($row['user_id'])) ?>">
                    <td style="text-align: center;">
                        <?php if ($row['id']): ?>
                            <input type="checkbox" name="print_ids[]" value="<?= h((string)$row['id']) ?>" class="print-checkbox">
                        <?php endif; ?>
                    </td>
                    <?php if ($canViewAll): ?>
                        <td>
                            <?= h((string) ($row['owner_nama'] ?: 'Baris Baru')) ?>
                            <?php if (!empty($row['owner_role'])): ?>
                                <br><small><?= h(role_label((string) $row['owner_role'])) ?></small>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    <td>
                        <input type="hidden" name="id[]" value="<?= h((string) $row['id']) ?>">
                        <input name="sasaran[]" list="sasaran-kinerja-options" value="<?= h((string) $row['sasaran']) ?>">
                    </td>
                    <td><input name="indikator[]" list="indikator-kinerja-options" value="<?= h((string) $row['indikator']) ?>"></td>
                    <td>
                        <select name="satuan[]">
                            <option value="">Pilih satuan</option>
                            <?php if ($currentSatuan !== '' && !in_array($currentSatuan, $satuanOptions, true)): ?>
                                <option value="<?= h($currentSatuan) ?>" selected><?= h($currentSatuan) ?></option>
                            <?php endif; ?>
                            <?php foreach ($satuanOptions as $option): ?>
                                <option value="<?= h($option) ?>" <?= $currentSatuan === $option ? 'selected' : '' ?>>
                                    <?= h($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="tipe_indikator[]">
                            <?php foreach (indicator_type_options() as $type => $label): ?>
                                <option value="<?= h($type) ?>" <?= ($row['tipe_indikator'] ?? 'max') === $type ? 'selected' : '' ?>>
                                    <?= h($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="bobot[]" value="<?= h((string) ($row['bobot'] ?? 1)) ?>"></td>
                    <td>
                        <select name="sumber_data[]">
                            <option value="">Pilih sumber</option>
                            <?php if ($currentSource !== '' && !in_array($currentSource, $sourceOptions, true)): ?>
                                <option value="<?= h($currentSource) ?>" selected><?= h($currentSource) ?></option>
                            <?php endif; ?>
                            <?php foreach ($sourceOptions as $option): ?>
                                <option value="<?= h($option) ?>" <?= $currentSource === $option ? 'selected' : '' ?>>
                                    <?= h($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="target[]" value="<?= h((string) $row['target']) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw1[]" value="<?= h((string) ($row['real_tw1'] ?? 0)) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw2[]" value="<?= h((string) ($row['real_tw2'] ?? 0)) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw3[]" value="<?= h((string) ($row['real_tw3'] ?? 0)) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw4[]" value="<?= h((string) ($row['real_tw4'] ?? 0)) ?>"></td>
                    <?php
                        $d01 = num($row['dipa01'] ?? 0);
                        $d04 = num($row['dipa04'] ?? 0);
                        $pilDipa = $d04 > 0 && $d01 == 0 ? '04' : '01';
                        $nilDipa = $pilDipa === '04' ? $d04 : $d01;
                    ?>
                    <td>
                        <select name="pilihan_dipa[]">
                            <option value="01" <?= $pilDipa === '01' ? 'selected' : '' ?>>DIPA 01</option>
                            <option value="04" <?= $pilDipa === '04' ? 'selected' : '' ?>>DIPA 04</option>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="nilai_dipa[]" value="<?= h((string) $nilDipa) ?>"></td>
                    <?php
                    $months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
                    foreach ($months as $m):
                        $rtw = $row['real_' . $m] ?? 0;
                    ?>
                        <td>
                            <input type="number" step="0.01" name="real_<?= $m ?>[]" value="<?= h((string) $rtw) ?>">
                        </td>
                    <?php endforeach; ?>
                    <td class="target-analysis-cell">
                        <div class="analysis-compact">
                            <span class="analysis-score"><?= h((string) round($achievementPercent, 1)) ?>%</span>
                            <span class="analysis-status <?= $analysisFilled ? 'filled' : '' ?>">
                                <?= $analysisFilled ? 'Sudah diisi' : 'Belum diisi' ?>
                            </span>
                            <button type="button" class="secondary analysis-open" data-modal-target="<?= h($modalId) ?>">
                                Analisis
                            </button>
                        </div>
                        <div class="analysis-modal" id="<?= h($modalId) ?>" aria-hidden="true">
                            <div class="analysis-modal-backdrop" data-modal-close></div>
                            <section class="analysis-dialog" role="dialog" aria-modal="true" aria-labelledby="<?= h($modalId) ?>-title">
                                <header class="analysis-dialog-header">
                                    <div>
                                        <span>Analisis Capaian Kinerja</span>
                                        <h2 id="<?= h($modalId) ?>-title"><?= h((string) ($row['indikator'] ?: 'Baris Target Baru')) ?></h2>
                                    </div>
                                    <button type="button" class="analysis-close" aria-label="Tutup analisis" data-modal-close>&times;</button>
                                </header>
                                <div class="analysis-dialog-body">
                                    <aside class="analysis-summary">
                                        <div class="target-chart large" style="--percent: <?= h((string) $achievementPercent) ?>;">
                                            <span><?= h((string) round($achievementPercent, 1)) ?>%</span>
                                        </div>
                                        <div class="analysis-metrics">
                                            <div>
                                                <span>Target</span>
                                                <strong><?= h((string) $targetValue) ?></strong>
                                            </div>
                                            <div>
                                                <span>Total Realisasi</span>
                                                <strong><?= h((string) $realizationTotal) ?></strong>
                                            </div>
                                            <div>
                                                <span>Status Analisis</span>
                                                <strong><?= $analysisFilled ? 'Sudah diisi' : 'Belum diisi' ?></strong>
                                            </div>
                                        </div>
                                    </aside>
                                    <div class="analysis-fields">
                                        <label>
                                            Kegiatan / Upaya / Strategi / Kendala / Solusi yang Dilaksanakan dalam Mencapai Target
                                            <textarea name="analisis_capaian[]" rows="10"><?= h((string) ($row['analisis_capaian'] ?? '')) ?></textarea>
                                        </label>
                                    </div>
                                </div>
                                <footer class="analysis-dialog-footer">
                                    <span>Perubahan tersimpan saat tombol Simpan di halaman utama ditekan.</span>
                                    <button type="button" data-modal-close>Tutup</button>
                                </footer>
                            </section>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($row['id'] && ($selectedUserId === (int)$user['id'] || $canEditAll)): ?>
                            <button class="danger target-delete" name="action" value="delete" onclick="this.form.delete_id.value='<?= h((string) $row['id']) ?>'" style="margin-bottom: 5px;">Hapus</button>
                        <?php endif; ?>
                        
                        <?php if (!empty($row['updated_at'])): ?>
                            <div style="font-size: 0.65rem; color: #64748b; line-height: 1.2; white-space: nowrap;">
                                Terakhir diisi:<br>
                                <?= h((string) date('d M Y, H:i', strtotime($row['updated_at']))) ?>
                            </div>
                        <?php elseif (!empty($row['created_at'])): ?>
                            <div style="font-size: 0.65rem; color: #64748b; line-height: 1.2; white-space: nowrap;">
                                Terakhir diisi:<br>
                                <?= h((string) date('d M Y, H:i', strtotime($row['created_at']))) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
            </div>
        </div>
    </section>
    <?php endif; ?>



    <input type="hidden" name="delete_id" value="">
    <?php if ($selectedUserId === (int)$user['id'] || $selectedUserId === 0 || $canEditAll): ?>
    <div class="toolbar panel" id="mainSaveContainer" style="margin-top: 1.5rem; display: none;">
        <button type="submit" name="action" value="save">Simpan Perubahan Data</button>
    </div>
    <?php else: ?>
    <div class="toolbar panel" style="margin-top: 1.5rem;">
        <p class="muted" style="margin: 0; font-style: italic;">Anda sedang dalam mode melihat. Anda hanya dapat mengisi atau mengubah target pada akun Anda sendiri.</p>
    </div>
    <?php endif; ?>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currentUserId = <?= (int)$user['id'] ?>;
    const canEditAllTargets = <?= $canEditAll ? 'true' : 'false' ?>;
    document.querySelectorAll('#dataTableContainer tbody tr').forEach(tr => {
        const rowUserId = parseInt(tr.dataset.userId || 0, 10);
        if (!canEditAllTargets) {
            const inputs = tr.querySelectorAll('input, select, textarea');
            inputs.forEach(el => {
                if (el.type !== 'hidden' && !el.classList.contains('print-checkbox')) {
                    el.disabled = true;
                    el.style.backgroundColor = '#f8fafc';
                    el.style.color = '#64748b';
                    el.style.cursor = 'not-allowed';
                }
            });
        }
    });
});

document.addEventListener('click', function (event) {
    const openButton = event.target.closest('[data-modal-target]');
    if (openButton) {
        const modal = document.getElementById(openButton.dataset.modalTarget);
        if (modal) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        }
        return;
    }

    if (event.target.closest('[data-modal-close]')) {
        const modal = event.target.closest('.analysis-modal');
        if (modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        }
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
        return;
    }

    document.querySelectorAll('.analysis-modal.is-open').forEach(function (modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    });
    document.body.classList.remove('modal-open');
});

document.addEventListener('DOMContentLoaded', function() {
    const dataTableContainer = document.getElementById('dataTableContainer');
    const mainSaveContainer = document.getElementById('mainSaveContainer');
    if (dataTableContainer && mainSaveContainer) {
        const showSaveBtn = function(e) {
            if (['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) {
                // Don't show if it's the print checkbox
                if (e.target.id === 'selectAllPrint' || e.target.classList.contains('print-checkbox')) return;
                mainSaveContainer.style.display = 'block';
            }
        };
        dataTableContainer.addEventListener('input', showSaveBtn);
        dataTableContainer.addEventListener('change', showSaveBtn);
    }
});

function printSelectedPDF() {
    const selectedIds = Array.from(document.querySelectorAll('.print-checkbox:checked')).map(cb => cb.value);
    if (selectedIds.length === 0) {
        alert('Pilih setidaknya satu data untuk di-print.');
        return;
    }
    const idsParam = selectedIds.join(',');
    window.open('index.php?page=target_print&ids=' + idsParam, '_blank');
}
</script>
<?php render_footer(); ?>
