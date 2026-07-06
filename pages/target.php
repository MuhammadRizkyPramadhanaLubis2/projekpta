<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM target_kinerja WHERE id = :id AND unit = :unit');
        $stmt->execute([
            'id' => (int) ($_POST['delete_id'] ?? 0),
            'unit' => $user['unit'],
        ]);
        flash('Baris target kinerja dihapus.');
        header('Location: index.php?page=target&tahun=' . year_value());
        exit;
    }

    $ids = $_POST['id'] ?? [];
    $sasaran = $_POST['sasaran'] ?? [];
    $indikator = $_POST['indikator'] ?? [];
    $targets = $_POST['target'] ?? [];
    $dipa01 = $_POST['dipa01'] ?? [];
    $dipa04 = $_POST['dipa04'] ?? [];
    $realTw1 = $_POST['real_tw1'] ?? [];
    $realTw2 = $_POST['real_tw2'] ?? [];
    $realTw3 = $_POST['real_tw3'] ?? [];
    $realTw4 = $_POST['real_tw4'] ?? [];

    $insert = db()->prepare(
        'INSERT INTO target_kinerja
         (tahun, unit, sasaran, indikator, target, dipa01, dipa04, real_tw1, real_tw2, real_tw3, real_tw4)
         VALUES
         (:tahun, :unit, :sasaran, :indikator, :target, :dipa01, :dipa04, :real_tw1, :real_tw2, :real_tw3, :real_tw4)'
    );
    $update = db()->prepare(
        'UPDATE target_kinerja
         SET sasaran = :sasaran, indikator = :indikator, target = :target,
             dipa01 = :dipa01, dipa04 = :dipa04,
             real_tw1 = :real_tw1, real_tw2 = :real_tw2, real_tw3 = :real_tw3, real_tw4 = :real_tw4
         WHERE id = :id AND unit = :unit'
    );

    foreach ($sasaran as $i => $value) {
        $rowSasaran = trim((string) $value);
        $rowIndikator = trim((string) ($indikator[$i] ?? ''));
        if ($rowSasaran === '' && $rowIndikator === '') {
            continue;
        }

        $payload = [
            'unit' => $user['unit'],
            'sasaran' => $rowSasaran,
            'indikator' => $rowIndikator,
            'target' => num($targets[$i] ?? 0),
            'dipa01' => num($dipa01[$i] ?? 0),
            'dipa04' => num($dipa04[$i] ?? 0),
            'real_tw1' => num($realTw1[$i] ?? 0),
            'real_tw2' => num($realTw2[$i] ?? 0),
            'real_tw3' => num($realTw3[$i] ?? 0),
            'real_tw4' => num($realTw4[$i] ?? 0),
        ];

        $id = (int) ($ids[$i] ?? 0);
        if ($id > 0) {
            $update->execute($payload + ['id' => $id]);
        } else {
            $insert->execute($payload + ['tahun' => $tahun]);
        }
    }

    flash('Data target kinerja tersimpan.');
    header('Location: index.php?page=target&tahun=' . $tahun);
    exit;
}

$stmt = db()->prepare('SELECT * FROM target_kinerja WHERE tahun = :tahun AND unit = :unit ORDER BY id');
$stmt->execute(['tahun' => $tahun, 'unit' => $user['unit']]);
$rows = $stmt->fetchAll();
$rows[] = [
    'id' => '',
    'sasaran' => '',
    'indikator' => '',
    'target' => 0,
    'dipa01' => 0,
    'dipa04' => 0,
    'real_tw1' => 0,
    'real_tw2' => 0,
    'real_tw3' => 0,
    'real_tw4' => 0,
];

render_header('Input Target Kinerja');
?>
<form method="get" class="toolbar">
    <input type="hidden" name="page" value="target">
    <label>
        Tahun
        <input type="number" name="tahun" min="2020" max="2100" value="<?= h((string) $tahun) ?>">
    </label>
    <button type="submit" class="secondary">Tampilkan</button>
</form>

<form method="post" class="panel">
    <input type="hidden" name="tahun" value="<?= h((string) $tahun) ?>">
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Sasaran Kinerja</th>
                <th>Indikator Kinerja</th>
                <th>Target</th>
                <th>DIPA 01</th>
                <th>DIPA 04</th>
                <th>TW1</th>
                <th>TW2</th>
                <th>TW3</th>
                <th>TW4</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <input type="hidden" name="id[]" value="<?= h((string) $row['id']) ?>">
                        <input name="sasaran[]" value="<?= h((string) $row['sasaran']) ?>">
                    </td>
                    <td><input name="indikator[]" value="<?= h((string) $row['indikator']) ?>"></td>
                    <td><input type="number" step="0.01" name="target[]" value="<?= h((string) $row['target']) ?>"></td>
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
