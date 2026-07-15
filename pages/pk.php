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
    save_document_meta($documentUser, $tahun, 'pk', $_POST);
    flash('Metadata dan Tanda Tangan Perjanjian Kinerja berhasil disimpan.');
    header('Location: index.php?page=pk&tahun=' . $tahun . ($selectedUserId > 0 ? '&user_id=' . $selectedUserId : ''));
    exit;
}

$owners = [];
if ($canViewAll) {
    $owners = db()->query('SELECT id, nama, role, unit FROM users WHERE status = "active" ORDER BY unit, role, nama')->fetchAll();
}

$query = 'SELECT tk.*, u.nama AS owner_nama, u.role AS owner_role, u.unit AS owner_unit
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
$meta = document_meta($documentUser, $tahun, 'pk');

// Handle Export CSV
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Perjanjian_Kinerja_' . $tahun . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['No', 'Sasaran Kinerja', 'Indikator Kinerja', 'Satuan', 'Tipe', 'Bobot', 'Target Tahunan']);
    foreach ($targets as $i => $target) {
        fputcsv($output, [
            $i + 1,
            $target['sasaran'],
            $target['indikator'],
            $target['satuan'] ?? '-',
            indicator_type_label((string) ($target['tipe_indikator'] ?? 'max')),
            $target['bobot'] ?? 1,
            $target['target']
        ]);
    }
    fclose($output);
    exit;
}

// Handle Export DOCX
$isDocx = ($_GET['export'] ?? '') === 'doc';
if ($isDocx) {
    header("Content-Type: application/vnd.ms-word");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("content-disposition: attachment;filename=Perjanjian_Kinerja_{$tahun}.doc");
}

if (!$isDocx) {
    render_header('Cetak Perjanjian Kinerja');
}
?>

<?php if (!$isDocx): ?>
<style>
/* Signature UI styles */
.signature-tabs { display: flex; gap: 8px; margin-bottom: 8px; }
.signature-tab { padding: 4px 12px; background: #e2e8f0; border-radius: 4px; cursor: pointer; font-size: 0.85rem; font-weight: 600; }
.signature-tab.active { background: #064e3b; color: #fff; }
.signature-panel { display: none; border: 1px solid #cbd5e1; border-radius: 4px; padding: 8px; background: #f8fafc; }
.signature-panel.active { display: block; }
.signature-canvas { border: 1px dashed #94a3b8; background: #fff; cursor: crosshair; display: block; margin-bottom: 8px; max-width: 100%; touch-action: none; }
.signature-preview { max-width: 150px; max-height: 80px; display: block; margin-top: 8px; }
.clear-canvas-btn { background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; font-weight: 600; }
</style>

<form method="get" class="toolbar">
    <input type="hidden" name="page" value="pk">
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
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; width: 100%; align-items: stretch; margin-top: 8px;">
        <button type="submit" class="secondary">Tampilkan</button>
        <button type="button" onclick="window.print()">Cetak PDF</button>
        <a href="index.php?page=pk&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=doc" class="button" style="background:#1d4ed8; color:white; padding:8px 16px; border-radius:4px; text-decoration:none; text-align: center; display: flex; justify-content: center; align-items: center; font-weight: 600;">Ekspor Word</a>
        <a href="index.php?page=pk&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=csv" class="button" style="background:#047857; color:white; padding:8px 16px; border-radius:4px; text-decoration:none; text-align: center; display: flex; justify-content: center; align-items: center; font-weight: 600;">Ekspor CSV</a>
    </div>
</form>

<section class="panel print-meta-form">
    <h2>Metadata Perjanjian Kinerja</h2>
    <form method="post" class="document-form-grid" id="meta-form">
        <input type="hidden" name="tahun" value="<?= h((string) $tahun) ?>">
        <input type="hidden" name="user_id" value="<?= h((string) $selectedUserId) ?>">
        <label>No. Surat <input name="no_surat" value="<?= h((string) $meta['no_surat']) ?>"></label>
        <label>Tanggal Surat <input type="date" name="tanggal_surat" value="<?= h((string) $meta['tanggal_surat']) ?>"></label>
        <label>Lokasi <input name="lokasi" value="<?= h((string) $meta['lokasi']) ?>"></label>
        <label>Nama Pihak I <input name="pihak1_nama" value="<?= h((string) $meta['pihak1_nama']) ?>"></label>
        <label>Jabatan Pihak I <input name="pihak1_jabatan" value="<?= h((string) $meta['pihak1_jabatan']) ?>"></label>
        <label>Nama Pihak II <input name="pihak2_nama" value="<?= h((string) $meta['pihak2_nama']) ?>"></label>
        <label>Jabatan Pihak II <input name="pihak2_jabatan" value="<?= h((string) $meta['pihak2_jabatan']) ?>"></label>
        <label>Catatan Dokumen <textarea name="catatan"><?= h((string) $meta['catatan']) ?></textarea></label>
        
        <!-- Pihak 1 TTD UI -->
        <div style="grid-column: 1 / -1; border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 8px;">
            <label>Tanda Tangan Pihak I</label>
            <input type="hidden" name="pihak1_ttd" id="pihak1_ttd" value="<?= h((string) $meta['pihak1_ttd']) ?>">
            <div class="signature-tabs">
                <div class="signature-tab active" onclick="switchTab('p1', 'canvas')">Tulis Tangan</div>
                <div class="signature-tab" onclick="switchTab('p1', 'upload')">Upload File</div>
            </div>
            <div id="p1-canvas-panel" class="signature-panel active">
                <canvas id="p1-canvas" class="signature-canvas" width="300" height="150"></canvas>
                <button type="button" class="clear-canvas-btn" onclick="clearCanvas('p1-canvas')">Hapus Coretan</button>
            </div>
            <div id="p1-upload-panel" class="signature-panel">
                <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
                    <input type="file" id="p1-file-input" accept="image/*" onchange="handleImageUpload(event, 'pihak1_ttd', 'p1-preview')" style="margin-bottom: 0; width: 100%;">
                    <button type="button" class="clear-canvas-btn" style="background: #ef4444; border: 1px solid #dc2626;" onclick="clearUpload('pihak1_ttd', 'p1-preview', 'p1-file-input')">Hapus File</button>
                </div>
                <img id="p1-preview" class="signature-preview" src="<?= !empty($meta['pihak1_ttd']) ? h((string) $meta['pihak1_ttd']) : '' ?>" style="<?= empty($meta['pihak1_ttd']) ? 'display:none;' : '' ?>">
            </div>
        </div>

        <!-- Pihak 2 TTD UI -->
        <div style="grid-column: 1 / -1; border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 8px;">
            <label>Tanda Tangan Pihak II</label>
            <input type="hidden" name="pihak2_ttd" id="pihak2_ttd" value="<?= h((string) $meta['pihak2_ttd']) ?>">
            <div class="signature-tabs">
                <div class="signature-tab active" onclick="switchTab('p2', 'canvas')">Tulis Tangan</div>
                <div class="signature-tab" onclick="switchTab('p2', 'upload')">Upload File</div>
            </div>
            <div id="p2-canvas-panel" class="signature-panel active">
                <canvas id="p2-canvas" class="signature-canvas" width="300" height="150"></canvas>
                <button type="button" class="clear-canvas-btn" onclick="clearCanvas('p2-canvas')">Hapus Coretan</button>
            </div>
            <div id="p2-upload-panel" class="signature-panel">
                <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
                    <input type="file" id="p2-file-input" accept="image/*" onchange="handleImageUpload(event, 'pihak2_ttd', 'p2-preview')" style="margin-bottom: 0; width: 100%;">
                    <button type="button" class="clear-canvas-btn" style="background: #ef4444; border: 1px solid #dc2626;" onclick="clearUpload('pihak2_ttd', 'p2-preview', 'p2-file-input')">Hapus File</button>
                </div>
                <img id="p2-preview" class="signature-preview" src="<?= !empty($meta['pihak2_ttd']) ? h((string) $meta['pihak2_ttd']) : '' ?>" style="<?= empty($meta['pihak2_ttd']) ? 'display:none;' : '' ?>">
            </div>
        </div>

        <button type="submit" onclick="saveSignatures()" style="grid-column: 1 / -1; margin-top: 16px;">Simpan Metadata & Tanda Tangan</button>
    </form>
</section>

<script>
function switchTab(prefix, tab) {
    document.querySelectorAll(`[onclick^="switchTab('${prefix}'"]`).forEach(el => el.classList.remove('active'));
    event.target.classList.add('active');
    
    document.getElementById(`${prefix}-canvas-panel`).classList.remove('active');
    document.getElementById(`${prefix}-upload-panel`).classList.remove('active');
    
    document.getElementById(`${prefix}-${tab}-panel`).classList.add('active');
}

// Canvas logic
const canvases = {
    'p1-canvas': { drawing: false, ctx: null, empty: true },
    'p2-canvas': { drawing: false, ctx: null, empty: true }
};

function initCanvas(id) {
    const canvas = document.getElementById(id);
    const ctx = canvas.getContext('2d');
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#000';
    canvases[id].ctx = ctx;

    const startPos = (e) => {
        canvases[id].drawing = true;
        canvases[id].empty = false;
        draw(e);
    };
    const endPos = () => {
        canvases[id].drawing = false;
        ctx.beginPath();
    };
    const draw = (e) => {
        if (!canvases[id].drawing) return;
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const x = ((e.clientX || e.touches?.[0].clientX) - rect.left) * scaleX;
        const y = ((e.clientY || e.touches?.[0].clientY) - rect.top) * scaleY;
        ctx.lineTo(x, y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x, y);
    };

    canvas.addEventListener('mousedown', startPos);
    canvas.addEventListener('mouseup', endPos);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('touchstart', (e) => { e.preventDefault(); startPos(e); }, {passive: false});
    canvas.addEventListener('touchend', endPos);
    canvas.addEventListener('touchmove', (e) => { e.preventDefault(); draw(e); }, {passive: false});
}

function clearCanvas(id) {
    const canvas = document.getElementById(id);
    const ctx = canvases[id].ctx;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    canvases[id].empty = true;
    document.getElementById(id === 'p1-canvas' ? 'pihak1_ttd' : 'pihak2_ttd').value = '';
}

function clearUpload(inputId, previewId, fileInputId) {
    document.getElementById(inputId).value = '';
    const fileInput = document.getElementById(fileInputId);
    if (fileInput) fileInput.value = '';
    const preview = document.getElementById(previewId);
    if (preview) {
        preview.src = '';
        preview.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initCanvas('p1-canvas');
    initCanvas('p2-canvas');
});

// Image processing for upload
function processImage(img, callback) {
    const MAX_WIDTH = 150;
    const MAX_HEIGHT = 80;
    let width = img.width;
    let height = img.height;

    // Calculate aspect ratio fit
    if (width > MAX_WIDTH || height > MAX_HEIGHT) {
        const ratio = Math.min(MAX_WIDTH / width, MAX_HEIGHT / height);
        width = width * ratio;
        height = height * ratio;
    }

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    
    // Fill white background to prevent black boxes in Word
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);
    
    ctx.drawImage(img, 0, 0, width, height);
    
    // Export as JPEG (no transparency issues, smaller file size)
    callback(canvas.toDataURL('image/jpeg', 0.9));
}

function handleImageUpload(e, inputId, previewId) {
    const file = e.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(event) {
        const img = new Image();
        img.onload = function() {
            processImage(img, function(processedDataUrl) {
                document.getElementById(inputId).value = processedDataUrl;
                const preview = document.getElementById(previewId);
                preview.src = processedDataUrl;
                preview.style.display = 'block';
            });
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

function getCanvasWithWhiteBg(canvasId) {
    const canvas = document.getElementById(canvasId);
    const tempCanvas = document.createElement('canvas');
    tempCanvas.width = canvas.width;
    tempCanvas.height = canvas.height;
    const ctx = tempCanvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
    ctx.drawImage(canvas, 0, 0);
    return tempCanvas.toDataURL('image/jpeg', 0.9);
}

function saveSignatures() {
    if (!canvases['p1-canvas'].empty && document.getElementById('p1-canvas-panel').classList.contains('active')) {
        document.getElementById('pihak1_ttd').value = getCanvasWithWhiteBg('p1-canvas');
    }
    if (!canvases['p2-canvas'].empty && document.getElementById('p2-canvas-panel').classList.contains('active')) {
        document.getElementById('pihak2_ttd').value = getCanvasWithWhiteBg('p2-canvas');
    }
}
</script>
<?php else: ?>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: "Times New Roman", Times, serif; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { border: 1px solid #000; padding: 5px; text-align: left; }
</style>
</head>
<body>
<?php endif; ?>

<section class="print-sheet">
    <h2 style="text-align: center;">PERJANJIAN KINERJA TAHUN <?= h((string) $tahun) ?></h2>
    <h3 style="text-align: center;"><?= h((string) $documentUser['unit']) ?> - <?= h(role_label((string) $documentUser['role'])) ?></h3>
    <p class="document-number" style="text-align: center;">
        Nomor: <?= h((string) ($meta['no_surat'] ?: '-')) ?>
    </p>

    <?php if (!$isDocx): ?>
    <div class="document-context">
        <div>
            <strong>Ruang Lingkup Kinerja</strong>
            <span><?= h((string) $profile['scope']) ?></span>
        </div>
        <div>
            <strong>Sumber Data/Realisasi</strong>
            <span><?= h(implode(', ', $profile['sources'])) ?></span>
        </div>
        <div>
            <strong>Output Wajib</strong>
            <span><?= h(implode('; ', $profile['outputs'])) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <p style="text-align: justify;">
        Dalam rangka mewujudkan manajemen pemerintahan yang efektif, transparan, dan akuntabel,
        <?= h(role_label((string) $documentUser['role'])) ?> menetapkan target kinerja sesuai
        tugas jabatan, sumber data aplikasi pendukung, dan indikator kinerja yang tercantum pada lampiran.
    </p>

    <p style="text-align: justify;">
        Perjanjian ini dibuat di <?= h((string) $meta['lokasi']) ?> pada tanggal
        <?= h((string) $meta['tanggal_surat']) ?> antara <?= h((string) $meta['pihak1_nama']) ?>
        selaku <?= h((string) $meta['pihak1_jabatan']) ?> dengan <?= h((string) $meta['pihak2_nama']) ?>
        selaku <?= h((string) $meta['pihak2_jabatan']) ?>.
    </p>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>No</th>
                <?php if ($canViewAll): ?>
                    <th>Pemilik</th>
                <?php endif; ?>
                <th>Sasaran Kinerja</th>
                <th>Indikator Kinerja</th>
                <th>Satuan</th>
                <th>Tipe</th>
                <th>Bobot</th>
                <th>Target Tahunan</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$targets): ?>
                <tr>
                    <td colspan="<?= $canViewAll ? '8' : '7' ?>">
                        Belum ada target kinerja. Indikator yang perlu dibuat untuk role ini:
                        <?= h(implode('; ', $profile['outputs'])) ?>.
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($targets as $i => $target): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <?php if ($canViewAll): ?>
                        <td>
                            <?= format_user_label($target['owner_nama'] ?? '', $target['owner_role'] ?? '', true) ?>
                        </td>
                    <?php endif; ?>
                    <td><?= h((string) $target['sasaran']) ?></td>
                    <td><?= h((string) $target['indikator']) ?></td>
                    <td><?= h((string) ($target['satuan'] ?? '-')) ?></td>
                    <td><?= h(indicator_type_label((string) ($target['tipe_indikator'] ?? 'max'))) ?></td>
                    <td><?= h((string) ($target['bobot'] ?? 1)) ?></td>
                    <td><?= h((string) $target['target']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <table style="width: 100%; border: none; margin-top: 40px;">
        <tr>
            <td style="width: 50%; text-align: center; border: none; vertical-align: bottom;">
                <strong>Pihak I</strong><br>
                <small><?= h((string) $meta['pihak1_jabatan']) ?></small><br>
                <?php if (!empty($meta['pihak1_ttd'])): ?>
                    <?= get_signature_img_tag((string) $meta['pihak1_ttd']) ?><br>
                <?php else: ?>
                    <br><br><br><br>
                <?php endif; ?>
                <span style="text-decoration: underline; font-weight: bold;"><?= h((string) $meta['pihak1_nama']) ?></span>
            </td>
            <td style="width: 50%; text-align: center; border: none; vertical-align: bottom;">
                <strong>Pihak II</strong><br>
                <small><?= h((string) $meta['pihak2_jabatan']) ?></small><br>
                <?php if (!empty($meta['pihak2_ttd'])): ?>
                    <?= get_signature_img_tag((string) $meta['pihak2_ttd']) ?><br>
                <?php else: ?>
                    <br><br><br><br>
                <?php endif; ?>
                <span style="text-decoration: underline; font-weight: bold;"><?= h((string) $meta['pihak2_nama']) ?></span>
            </td>
        </tr>
    </table>

    <?php if (!empty($meta['catatan'])): ?>
        <p class="muted" style="margin-top: 40px; font-size: 0.9em;"><?= nl2br(h((string) $meta['catatan'])) ?></p>
    <?php endif; ?>
</section>

<?php if ($isDocx): ?>
</body>
</html>
<?php else: ?>
<?php render_footer(); ?>
<?php endif; ?>
