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
    $dipa01 = $_POST['dipa01'] ?? [];
    $dipa04 = $_POST['dipa04'] ?? [];
    $realTw1 = $_POST['real_tw1'] ?? [];
    $realTw2 = $_POST['real_tw2'] ?? [];
    $realTw3 = $_POST['real_tw3'] ?? [];
    $realTw4 = $_POST['real_tw4'] ?? [];
    $analisisKegiatan = $_POST['analisis_kegiatan'] ?? [];
    $analisisUpaya = $_POST['analisis_upaya'] ?? [];
    $analisisStrategi = $_POST['analisis_strategi'] ?? [];
    $analisisKendala = $_POST['analisis_kendala'] ?? [];
    $analisisSolusi = $_POST['analisis_solusi'] ?? [];

    $insert = db()->prepare(
        'INSERT INTO target_kinerja
         (tahun, unit, sasaran, indikator, satuan, tipe_indikator, sumber_data, bobot,
          target, target_tw1, target_tw2, target_tw3, target_tw4,
          dipa01, dipa04, real_tw1, real_tw2, real_tw3, real_tw4,
          analisis_kegiatan, analisis_upaya, analisis_strategi, analisis_kendala, analisis_solusi,
          user_id)
         VALUES
         (:tahun, :unit, :sasaran, :indikator, :satuan, :tipe_indikator, :sumber_data, :bobot,
          :target, :target_tw1, :target_tw2, :target_tw3, :target_tw4,
          :dipa01, :dipa04, :real_tw1, :real_tw2, :real_tw3, :real_tw4,
          :analisis_kegiatan, :analisis_upaya, :analisis_strategi, :analisis_kendala, :analisis_solusi,
          :user_id)'
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
            'dipa01' => num($dipa01[$i] ?? 0),
            'dipa04' => num($dipa04[$i] ?? 0),
            'real_tw1' => num($realTw1[$i] ?? 0),
            'real_tw2' => num($realTw2[$i] ?? 0),
            'real_tw3' => num($realTw3[$i] ?? 0),
            'real_tw4' => num($realTw4[$i] ?? 0),
            'analisis_kegiatan' => trim((string) ($analisisKegiatan[$i] ?? '')),
            'analisis_upaya' => trim((string) ($analisisUpaya[$i] ?? '')),
            'analisis_strategi' => trim((string) ($analisisStrategi[$i] ?? '')),
            'analisis_kendala' => trim((string) ($analisisKendala[$i] ?? '')),
            'analisis_solusi' => trim((string) ($analisisSolusi[$i] ?? '')),
        ];

        $id = (int) ($ids[$i] ?? 0);
        if ($id > 0) {
            $checkStmt = db()->prepare('SELECT * FROM target_kinerja WHERE id = :id');
            $checkStmt->execute(['id' => $id]);
            $existing = $checkStmt->fetch();
            if ($existing && can_manage_target_row($existing)) {
                $update->execute($payload + ['id' => $id]);
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
                <th>DIPA 01</th>
                <th>DIPA 04</th>
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
                        <input name="sasaran[]" value="<?= h((string) $row['sasaran']) ?>">
                    </td>
                    <td><input name="indikator[]" value="<?= h((string) $row['indikator']) ?>"></td>
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
                    <td><input type="number" step="0.01" name="dipa01[]" value="<?= h((string) $row['dipa01']) ?>"></td>
                    <td><input type="number" step="0.01" name="dipa04[]" value="<?= h((string) $row['dipa04']) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw1[]" value="<?= h((string) $row['real_tw1']) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw2[]" value="<?= h((string) $row['real_tw2']) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw3[]" value="<?= h((string) $row['real_tw3']) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw4[]" value="<?= h((string) $row['real_tw4']) ?>"></td>
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
                        <?php if ($row['id'] !== ''): ?>
                            <button class="danger target-delete" name="action" value="delete" onclick="this.form.delete_id.value='<?= h((string) $row['id']) ?>'">Hapus Baris</button>
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
