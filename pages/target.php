<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();
$canViewAll = user_can('view_all_targets');
$selectedUserId = $canViewAll ? (int) ($_GET['user_id'] ?? $_POST['user_id'] ?? 0) : (int) $user['id'];
$profile = role_profile((string) $user['role']);
$satuanOptions = ['Persen', 'Nilai', 'Perkara', 'Dokumen', 'Laporan', 'Kegiatan', 'Orang', 'Hari', 'Bulan', 'Unit'];
$sourceOptions = array_values(array_unique(array_merge(
    $profile['sources'],
    ['SAKTI', 'OMSPAN Kemenkeu', 'E-BIMA MARI', 'E-SADEWA MARI', 'SIPP', 'SIKEP', 'MY ASN', 'Direktori Putusan', 'Database aplikasi IKPA']
)));

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
        header('Location: index.php?page=target&tahun=' . year_value() . ($selectedUserId > 0 ? '&user_id=' . $selectedUserId : ''));
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
    $realTw1 = $_POST['real_tw1'] ?? [];
    $realTw2 = $_POST['real_tw2'] ?? [];
    $realTw3 = $_POST['real_tw3'] ?? [];
    $realTw4 = $_POST['real_tw4'] ?? [];
    $analisisKegiatan = $_POST['analisis_kegiatan'] ?? [];
    $analisisUpaya = $_POST['analisis_upaya'] ?? [];
    $analisisStrategi = $_POST['analisis_strategi'] ?? [];
    $analisisKendala = $_POST['analisis_kendala'] ?? [];
    $analisisSolusi = $_POST['analisis_solusi'] ?? [];

    $metaTw1a = $_POST['meta_tw1a'] ?? [];
    $metaTw1b = $_POST['meta_tw1b'] ?? [];
    $metaTw2a = $_POST['meta_tw2a'] ?? [];
    $metaTw2b = $_POST['meta_tw2b'] ?? [];
    $metaTw3a = $_POST['meta_tw3a'] ?? [];
    $metaTw3b = $_POST['meta_tw3b'] ?? [];
    $metaTw4a = $_POST['meta_tw4a'] ?? [];
    $metaTw4b = $_POST['meta_tw4b'] ?? [];

    $insert = db()->prepare(
        'INSERT INTO target_kinerja
         (tahun, unit, sasaran, indikator, satuan, tipe_indikator, sumber_data, bobot,
          target, target_tw1, target_tw2, target_tw3, target_tw4,
          dipa01, dipa04, real_tw1, real_tw2, real_tw3, real_tw4,
          analisis_kegiatan, analisis_upaya, analisis_strategi, analisis_kendala, analisis_solusi,
          user_id, metadata)
         VALUES
         (:tahun, :unit, :sasaran, :indikator, :satuan, :tipe_indikator, :sumber_data, :bobot,
          :target, :target_tw1, :target_tw2, :target_tw3, :target_tw4,
          :dipa01, :dipa04, :real_tw1, :real_tw2, :real_tw3, :real_tw4,
          :analisis_kegiatan, :analisis_upaya, :analisis_strategi, :analisis_kendala, :analisis_solusi,
          :user_id, :metadata)'
    );
    $update = db()->prepare(
        'UPDATE target_kinerja
         SET sasaran = :sasaran, indikator = :indikator,
             satuan = :satuan, tipe_indikator = :tipe_indikator, sumber_data = :sumber_data, bobot = :bobot,
             target = :target, target_tw1 = :target_tw1, target_tw2 = :target_tw2,
             target_tw3 = :target_tw3, target_tw4 = :target_tw4,
             dipa01 = :dipa01, dipa04 = :dipa04,
             real_tw1 = :real_tw1, real_tw2 = :real_tw2, real_tw3 = :real_tw3, real_tw4 = :real_tw4,
             analisis_kegiatan = :analisis_kegiatan,
             analisis_upaya = :analisis_upaya,
             analisis_strategi = :analisis_strategi,
             analisis_kendala = :analisis_kendala,
             analisis_solusi = :analisis_solusi,
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

        $r1 = num($realTw1[$i] ?? 0);
        $r2 = num($realTw2[$i] ?? 0);
        $r3 = num($realTw3[$i] ?? 0);
        $r4 = num($realTw4[$i] ?? 0);

        $m1a = num($metaTw1a[$i] ?? 0);
        $m1b = num($metaTw1b[$i] ?? 0);
        $m2a = num($metaTw2a[$i] ?? 0);
        $m2b = num($metaTw2b[$i] ?? 0);
        $m3a = num($metaTw3a[$i] ?? 0);
        $m3b = num($metaTw3b[$i] ?? 0);
        $m4a = num($metaTw4a[$i] ?? 0);
        $m4b = num($metaTw4b[$i] ?? 0);

        $id = (int) ($ids[$i] ?? 0);
        $existingMetaStr = '{}';
        $isMandatory = false;
        $rowOwnerRole = $user['role'];
        
        if ($id > 0) {
            $metaStmt = db()->prepare('SELECT metadata, is_mandatory, user_id FROM target_kinerja WHERE id = :id');
            $metaStmt->execute(['id' => $id]);
            $rowDb = $metaStmt->fetch();
            if ($rowDb) {
                $existingMetaStr = $rowDb['metadata'] ?: '{}';
                $isMandatory = (int) $rowDb['is_mandatory'] === 1;
                
                $rStmt = db()->prepare('SELECT role FROM users WHERE id = :id');
                $rStmt->execute(['id' => $rowDb['user_id']]);
                $rowOwnerRole = $rStmt->fetchColumn() ?: $rowOwnerRole;
            }
        }

        $metaData = json_decode($existingMetaStr, true) ?: [];

        $isBanding = $rowOwnerRole === 'PanmudBanding' && $isMandatory;
        $isHukum = $rowOwnerRole === 'PanmudHukum' && $isMandatory;

        if ($isBanding || $isHukum) {
            if ($isBanding) {
                if ($m1a > 0) $r1 = round(($m1b / $m1a) * 100, 2); else $r1 = 0;
                if ($m2a > 0) $r2 = round(($m2b / $m2a) * 100, 2); else $r2 = 0;
                if ($m3a > 0) $r3 = round(($m3b / $m3a) * 100, 2); else $r3 = 0;
                if ($m4a > 0) $r4 = round(($m4b / $m4a) * 100, 2); else $r4 = 0;
            } elseif ($isHukum) {
                $t1 = $m1a + $m1b; if ($t1 > 0) $r1 = round(($m1a / $t1) * 100, 2); else $r1 = 0;
                $t2 = $m2a + $m2b; if ($t2 > 0) $r2 = round(($m2a / $t2) * 100, 2); else $r2 = 0;
                $t3 = $m3a + $m3b; if ($t3 > 0) $r3 = round(($m3a / $t3) * 100, 2); else $r3 = 0;
                $t4 = $m4a + $m4b; if ($t4 > 0) $r4 = round(($m4a / $t4) * 100, 2); else $r4 = 0;
            }
            $metaData['tw1'] = ['a' => $m1a, 'b' => $m1b];
            $metaData['tw2'] = ['a' => $m2a, 'b' => $m2b];
            $metaData['tw3'] = ['a' => $m3a, 'b' => $m3b];
            $metaData['tw4'] = ['a' => $m4a, 'b' => $m4b];
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
            'target_tw1' => 0,
            'target_tw2' => 0,
            'target_tw3' => 0,
            'target_tw4' => 0,
            'dipa01' => ($pilihanDipa[$i] ?? '01') === '01' ? num($nilaiDipa[$i] ?? 0) : 0,
            'dipa04' => ($pilihanDipa[$i] ?? '01') === '04' ? num($nilaiDipa[$i] ?? 0) : 0,
            'real_tw1' => $r1,
            'real_tw2' => $r2,
            'real_tw3' => $r3,
            'real_tw4' => $r4,
            'analisis_kegiatan' => trim((string) ($analisisKegiatan[$i] ?? '')),
            'analisis_upaya' => trim((string) ($analisisUpaya[$i] ?? '')),
            'analisis_strategi' => trim((string) ($analisisStrategi[$i] ?? '')),
            'analisis_kendala' => trim((string) ($analisisKendala[$i] ?? '')),
            'analisis_solusi' => trim((string) ($analisisSolusi[$i] ?? '')),
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
    header('Location: index.php?page=target&tahun=' . $tahun . ($selectedUserId > 0 ? '&user_id=' . $selectedUserId : ''));
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

$query .= ' ORDER BY u.unit, u.role, u.nama, tk.id';

if ($selectedUserId > 0) {
    $targetUserRole = $user['role'];
    if ($selectedUserId !== (int)$user['id'] && $canViewAll) {
        $uStmt = db()->prepare('SELECT role FROM users WHERE id = :id');
        $uStmt->execute(['id' => $selectedUserId]);
        $targetUserRole = $uStmt->fetchColumn() ?: $user['role'];
    }
    generate_mandatory_targets($selectedUserId, $targetUserRole, $tahun);
} elseif ($canViewAll && $selectedUserId === 0) {
    foreach ($owners as $owner) {
        generate_mandatory_targets((int)$owner['id'], $owner['role'], $tahun);
    }
}

$stmt = db()->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$rows[] = [
    'id' => '',
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
    'is_mandatory' => 0,
];

render_header('Input Target Kinerja');
?>
<section class="panel analysis-rule">
    <strong>Format Input untuk <?= h(role_label((string) $user['role'])) ?></strong>
    <p><?= h((string) $profile['scope']) ?> Realisasi triwulan diisi berdasarkan sumber: <?= h(implode(', ', $profile['sources'])) ?>.</p>
</section>

<form method="get" class="toolbar">
    <input type="hidden" name="page" value="target">
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
                        <?= h((string) $owner['nama']) ?> - <?= h(role_label((string) $owner['role'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>
    <button type="submit" class="secondary">Tampilkan</button>
</form>

<form method="post" class="panel">
    <input type="hidden" name="tahun" value="<?= h((string) $tahun) ?>">
    <input type="hidden" name="user_id" value="<?= h((string) $selectedUserId) ?>">
    <div class="table-wrap">
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
                <th>Pilihan DIPA</th>
                <th>Nilai DIPA</th>
                <th>Realisasi TW1</th>
                <th>Realisasi TW2</th>
                <th>Realisasi TW3</th>
                <th>Realisasi TW4</th>
                <th>Analisis Capaian Kinerja</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $rowIndex => $row): ?>
                <?php
                $targetValue = num($row['target'] ?? 0);
                $realizationTotal = num($row['real_tw1'] ?? 0) + num($row['real_tw2'] ?? 0) + num($row['real_tw3'] ?? 0) + num($row['real_tw4'] ?? 0);
                $achievementRaw = achievement_value($targetValue, $realizationTotal, (string) ($row['tipe_indikator'] ?? 'max'));
                $achievementPercent = max(0, min(100, $achievementRaw));
                $currentSatuan = (string) ($row['satuan'] ?? '');
                $currentSource = (string) ($row['sumber_data'] ?? '');
                $modalId = 'target-analysis-modal-' . $rowIndex;
                $analysisFilled = trim(
                    (string) ($row['analisis_kegiatan'] ?? '') .
                    (string) ($row['analisis_upaya'] ?? '') .
                    (string) ($row['analisis_strategi'] ?? '') .
                    (string) ($row['analisis_kendala'] ?? '') .
                    (string) ($row['analisis_solusi'] ?? '')
                ) !== '';
                ?>
                <?php 
                    $isMandatory = (int) ($row['is_mandatory'] ?? 0) === 1; 
                    $isPanmudBanding = $row['owner_role'] === 'PanmudBanding' && $isMandatory;
                    $isPanmudHukum = $row['owner_role'] === 'PanmudHukum' && $isMandatory;
                    $isMulti = $isPanmudBanding || $isPanmudHukum;
                    $meta = json_decode((string)($row['metadata'] ?? '{}'), true);
                    if (!is_array($meta)) $meta = [];
                    $lblA = $isPanmudBanding ? 'Perkara Masuk' : 'Jml E-Court';
                    $lblB = $isPanmudBanding ? 'Selesai Tepat Waktu' : 'Jml Non E-Court';
                ?>
                <tr>
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
                        <input name="sasaran[]" value="<?= h((string) $row['sasaran']) ?>" <?= $isMandatory ? 'readonly style="background:#f1f5f9;color:#64748b;"' : '' ?>>
                    </td>
                    <td><input name="indikator[]" value="<?= h((string) $row['indikator']) ?>" <?= $isMandatory ? 'readonly style="background:#f1f5f9;color:#64748b;"' : '' ?>></td>
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
                    <?php for ($t = 1; $t <= 4; $t++): 
                        $rtw = $row['real_tw' . $t];
                        $m_a = $meta['tw' . $t]['a'] ?? 0;
                        $m_b = $meta['tw' . $t]['b'] ?? 0;
                    ?>
                        <td>
                            <?php if ($isMulti): ?>
                                <div style="display:flex; flex-direction:column; gap:4px; margin-bottom:4px; font-size:0.8rem;">
                                    <label><?= $lblA ?></label>
                                    <input type="number" step="0.01" name="meta_tw<?= $t ?>a[]" value="<?= h((string)$m_a) ?>">
                                    <label><?= $lblB ?></label>
                                    <input type="number" step="0.01" name="meta_tw<?= $t ?>b[]" value="<?= h((string)$m_b) ?>">
                                </div>
                                <input type="hidden" name="real_tw<?= $t ?>[]" value="<?= h((string) $rtw) ?>">
                            <?php else: ?>
                                <input type="number" step="0.01" name="real_tw<?= $t ?>[]" value="<?= h((string) $rtw) ?>">
                                <input type="hidden" name="meta_tw<?= $t ?>a[]" value="0">
                                <input type="hidden" name="meta_tw<?= $t ?>b[]" value="0">
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
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
                                            Kegiatan
                                            <textarea name="analisis_kegiatan[]" rows="3"><?= h((string) ($row['analisis_kegiatan'] ?? '')) ?></textarea>
                                        </label>
                                        <label>
                                            Upaya
                                            <textarea name="analisis_upaya[]" rows="3"><?= h((string) ($row['analisis_upaya'] ?? '')) ?></textarea>
                                        </label>
                                        <label>
                                            Strategi
                                            <textarea name="analisis_strategi[]" rows="3"><?= h((string) ($row['analisis_strategi'] ?? '')) ?></textarea>
                                        </label>
                                        <label>
                                            Kendala
                                            <textarea name="analisis_kendala[]" rows="3"><?= h((string) ($row['analisis_kendala'] ?? '')) ?></textarea>
                                        </label>
                                        <label>
                                            Solusi
                                            <textarea name="analisis_solusi[]" rows="3"><?= h((string) ($row['analisis_solusi'] ?? '')) ?></textarea>
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
                        <?php if ($row['id'] && !$isMandatory): ?>
                            <button class="danger target-delete" name="action" value="delete" onclick="this.form.delete_id.value='<?= h((string) $row['id']) ?>'" style="margin-bottom: 5px;">Hapus</button>
                        <?php elseif ($isMandatory): ?>
                            <span class="small-badge" style="background:#10b981; color:#fff; border:none; display:inline-block; margin-bottom: 5px;">Mandatory</span>
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
    <input type="hidden" name="delete_id" value="">
    <div class="toolbar">
        <button type="submit" name="action" value="save">Simpan</button>
        <a class="button secondary" href="index.php?page=target&tahun=<?= h((string) $tahun) ?>">Tambah Baris Kosong</a>
    </div>
</form>
<script>
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
</script>
<?php render_footer(); ?>
