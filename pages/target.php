<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();
$canViewAll = user_can('view_all_targets');
$selectedUserId = $canViewAll ? (int) ($_GET['user_id'] ?? $_POST['user_id'] ?? 0) : (int) $user['id'];
$profile = role_profile((string) $user['role']);

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
    $targetTw1 = $_POST['target_tw1'] ?? [];
    $targetTw2 = $_POST['target_tw2'] ?? [];
    $targetTw3 = $_POST['target_tw3'] ?? [];
    $targetTw4 = $_POST['target_tw4'] ?? [];
    $dipa01 = $_POST['dipa01'] ?? [];
    $dipa04 = $_POST['dipa04'] ?? [];
    $realTw1 = $_POST['real_tw1'] ?? [];
    $realTw2 = $_POST['real_tw2'] ?? [];
    $realTw3 = $_POST['real_tw3'] ?? [];
    $realTw4 = $_POST['real_tw4'] ?? [];

    $insert = db()->prepare(
        'INSERT INTO target_kinerja
         (tahun, unit, sasaran, indikator, satuan, tipe_indikator, sumber_data, bobot,
          target, target_tw1, target_tw2, target_tw3, target_tw4,
          dipa01, dipa04, real_tw1, real_tw2, real_tw3, real_tw4, user_id)
         VALUES
         (:tahun, :unit, :sasaran, :indikator, :satuan, :tipe_indikator, :sumber_data, :bobot,
          :target, :target_tw1, :target_tw2, :target_tw3, :target_tw4,
          :dipa01, :dipa04, :real_tw1, :real_tw2, :real_tw3, :real_tw4, :user_id)'
    );
    $update = db()->prepare(
        'UPDATE target_kinerja
         SET sasaran = :sasaran, indikator = :indikator,
             satuan = :satuan, tipe_indikator = :tipe_indikator, sumber_data = :sumber_data, bobot = :bobot,
             target = :target, target_tw1 = :target_tw1, target_tw2 = :target_tw2,
             target_tw3 = :target_tw3, target_tw4 = :target_tw4,
             dipa01 = :dipa01, dipa04 = :dipa04,
             real_tw1 = :real_tw1, real_tw2 = :real_tw2, real_tw3 = :real_tw3, real_tw4 = :real_tw4,
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
            'target_tw1' => num($targetTw1[$i] ?? 0),
            'target_tw2' => num($targetTw2[$i] ?? 0),
            'target_tw3' => num($targetTw3[$i] ?? 0),
            'target_tw4' => num($targetTw4[$i] ?? 0),
            'dipa01' => num($dipa01[$i] ?? 0),
            'dipa04' => num($dipa04[$i] ?? 0),
            'real_tw1' => num($realTw1[$i] ?? 0),
            'real_tw2' => num($realTw2[$i] ?? 0),
            'real_tw3' => num($realTw3[$i] ?? 0),
            'real_tw4' => num($realTw4[$i] ?? 0),
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
                <th>Target TW1</th>
                <th>Target TW2</th>
                <th>Target TW3</th>
                <th>Target TW4</th>
                <th>DIPA 01</th>
                <th>DIPA 04</th>
                <th>Realisasi TW1</th>
                <th>Realisasi TW2</th>
                <th>Realisasi TW3</th>
                <th>Realisasi TW4</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
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
                    <td><input name="satuan[]" value="<?= h((string) ($row['satuan'] ?? '')) ?>" placeholder="% / perkara / nilai"></td>
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
                    <td><input name="sumber_data[]" value="<?= h((string) ($row['sumber_data'] ?? '')) ?>" placeholder="<?= h(implode(', ', $profile['sources'])) ?>"></td>
                    <td><input type="number" step="0.01" name="target[]" value="<?= h((string) $row['target']) ?>"></td>
                    <td><input type="number" step="0.01" name="target_tw1[]" value="<?= h((string) ($row['target_tw1'] ?? 0)) ?>"></td>
                    <td><input type="number" step="0.01" name="target_tw2[]" value="<?= h((string) ($row['target_tw2'] ?? 0)) ?>"></td>
                    <td><input type="number" step="0.01" name="target_tw3[]" value="<?= h((string) ($row['target_tw3'] ?? 0)) ?>"></td>
                    <td><input type="number" step="0.01" name="target_tw4[]" value="<?= h((string) ($row['target_tw4'] ?? 0)) ?>"></td>
                    <td><input type="number" step="0.01" name="dipa01[]" value="<?= h((string) $row['dipa01']) ?>"></td>
                    <td><input type="number" step="0.01" name="dipa04[]" value="<?= h((string) $row['dipa04']) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw1[]" value="<?= h((string) $row['real_tw1']) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw2[]" value="<?= h((string) $row['real_tw2']) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw3[]" value="<?= h((string) $row['real_tw3']) ?>"></td>
                    <td><input type="number" step="0.01" name="real_tw4[]" value="<?= h((string) $row['real_tw4']) ?>"></td>
                    <td>
                        <?php if ($row['id'] !== ''): ?>
                            <button class="danger" name="action" value="delete" onclick="this.form.delete_id.value='<?= h((string) $row['id']) ?>'">Hapus</button>
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
<?php render_footer(); ?>
