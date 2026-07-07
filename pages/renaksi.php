<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();
$canViewAll = user_can('view_all_targets');
$selectedUserId = $canViewAll ? (int) ($_GET['user_id'] ?? 0) : (int) $user['id'];
$profile = role_profile((string) $user['role']);
$documentUser = document_owner($user, $canViewAll, $selectedUserId);
$profile = role_profile((string) $documentUser['role']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    save_document_meta($documentUser, $tahun, 'renaksi', $_POST);
    flash('Metadata Rencana Aksi tersimpan.');
    header('Location: index.php?page=renaksi&tahun=' . $tahun . ($selectedUserId > 0 ? '&user_id=' . $selectedUserId : ''));
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
$targets = $stmt->fetchAll();
$meta = document_meta($documentUser, $tahun, 'renaksi');

render_header('Cetak Rencana Aksi');
?>
<form method="get" class="toolbar">
    <input type="hidden" name="page" value="renaksi">
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
    <button type="button" onclick="window.print()">Cetak</button>
</form>

<section class="panel print-meta-form">
    <h2>Metadata Rencana Aksi</h2>
    <form method="post" class="document-form-grid">
        <input type="hidden" name="tahun" value="<?= h((string) $tahun) ?>">
        <input type="hidden" name="user_id" value="<?= h((string) $selectedUserId) ?>">
        <label>No. Surat <input name="no_surat" value="<?= h((string) $meta['no_surat']) ?>"></label>
        <label>Tanggal <input type="date" name="tanggal_surat" value="<?= h((string) $meta['tanggal_surat']) ?>"></label>
        <label>Lokasi <input name="lokasi" value="<?= h((string) $meta['lokasi']) ?>"></label>
        <label>Nama Penanggung Jawab <input name="pihak1_nama" value="<?= h((string) $meta['pihak1_nama']) ?>"></label>
        <label>Jabatan <input name="pihak1_jabatan" value="<?= h((string) $meta['pihak1_jabatan']) ?>"></label>
        <label>Nama Pimpinan <input name="pihak2_nama" value="<?= h((string) $meta['pihak2_nama']) ?>"></label>
        <label>Jabatan Pimpinan <input name="pihak2_jabatan" value="<?= h((string) $meta['pihak2_jabatan']) ?>"></label>
        <label>Catatan Jadwal/Kegiatan <textarea name="catatan"><?= h((string) $meta['catatan']) ?></textarea></label>
        <button type="submit">Simpan Metadata</button>
    </form>
</section>

<section class="print-sheet">
    <h2>RENCANA AKSI TAHUN <?= h((string) $tahun) ?></h2>
    <h3><?= h((string) $documentUser['unit']) ?> - <?= h(role_label((string) $documentUser['role'])) ?></h3>
    <p class="document-number">Nomor: <?= h((string) ($meta['no_surat'] ?: '-')) ?></p>

    <div class="document-context">
        <div>
            <strong>Ruang Lingkup</strong>
            <span><?= h((string) $profile['scope']) ?></span>
        </div>
        <div>
            <strong>Sumber Realisasi</strong>
            <span><?= h(implode(', ', $profile['sources'])) ?></span>
        </div>
        <div>
            <strong>Aturan Analisis</strong>
            <span><?= h((string) $profile['analysis_rule']) ?></span>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>No</th>
                <?php if ($canViewAll): ?>
                    <th>Pemilik</th>
                <?php endif; ?>
                <th>Sasaran</th>
                <th>Indikator</th>
                <th>Satuan</th>
                <th>Sumber Data</th>
                <th>Target TW1</th>
                <th>Target TW2</th>
                <th>Target TW3</th>
                <th>Target TW4</th>
                <th>Aksi/Kegiatan</th>
                <th>Jadwal</th>
                <th>Keluaran</th>
                <th>Dana</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$targets): ?>
                <tr>
                    <td colspan="<?= $canViewAll ? '14' : '13' ?>">
                        Belum ada target kinerja. Buat rencana aksi untuk output role:
                        <?= h(implode('; ', $profile['outputs'])) ?>.
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($targets as $i => $target): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <?php if ($canViewAll): ?>
                        <td>
                            <?= h((string) ($target['owner_nama'] ?? '-')) ?>
                            <br><small><?= h(role_label((string) ($target['owner_role'] ?? ''))) ?></small>
                        </td>
                    <?php endif; ?>
                    <td><?= h((string) $target['sasaran']) ?></td>
                    <td><?= h((string) $target['indikator']) ?></td>
                    <td><?= h((string) ($target['satuan'] ?? '-')) ?></td>
                    <td><?= h((string) ($target['sumber_data'] ?: implode(', ', $profile['sources']))) ?></td>
                    <td><?= h((string) target_for_quarter($target, 1)) ?></td>
                    <td><?= h((string) target_for_quarter($target, 2)) ?></td>
                    <td><?= h((string) target_for_quarter($target, 3)) ?></td>
                    <td><?= h((string) target_for_quarter($target, 4)) ?></td>
                    <td>Validasi data dari <?= h(implode(', ', $profile['sources'])) ?> dan tindak lanjut indikator.</td>
                    <td>TW1 s.d. TW4 Tahun <?= h((string) $tahun) ?></td>
                    <td><?= h((string) ($profile['outputs'][0] ?? 'Laporan capaian kinerja triwulan.')) ?></td>
                    <td><?= h((string) (num($target['dipa01']) + num($target['dipa04']))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($meta['catatan'])): ?>
        <p class="muted"><?= nl2br(h((string) $meta['catatan'])) ?></p>
    <?php endif; ?>

    <p class="document-place-date"><?= h((string) $meta['lokasi']) ?>, <?= h((string) $meta['tanggal_surat']) ?></p>
    <div class="signature-grid">
        <div class="signature-box">
            <strong>Penanggung Jawab</strong>
            <span><?= h((string) $meta['pihak1_nama']) ?></span>
            <small><?= h((string) $meta['pihak1_jabatan']) ?></small>
        </div>
        <div class="signature-box">
            <strong>Mengetahui</strong>
            <span><?= h((string) $meta['pihak2_nama']) ?></span>
            <small><?= h((string) $meta['pihak2_jabatan']) ?></small>
        </div>
    </div>
</section>
<?php render_footer(); ?>
