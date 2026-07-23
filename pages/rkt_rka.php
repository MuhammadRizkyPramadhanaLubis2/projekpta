<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();
$canViewAll = user_can('view_all_targets');
$selectedUserId = $canViewAll ? (int) ($_GET['user_id'] ?? 0) : (int) $user['id'];
$profile = role_profile((string) $user['role']);
$documentUser = document_owner($user, $canViewAll, $selectedUserId);
$profile = role_profile((string) $documentUser['role']);
$rkaEnabled = false; // Source/data RKA dipertahankan untuk aktivasi berikutnya.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    save_document_meta($documentUser, $tahun, 'rkt_rka', $_POST);
    flash('Metadata dan Tanda Tangan RKT berhasil disimpan.');
    header('Location: index.php?page=rkt_rka&tahun=' . $tahun . ($selectedUserId > 0 ? '&user_id=' . $selectedUserId : ''));
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
$meta = document_meta($documentUser, $tahun, 'rkt_rka');

// Konsolidasikan target untuk cetak RKT unit. Saat administrator memilih semua
// pengguna, satu indikator dapat tersimpan pada lebih dari satu pemilik data.
// Cetakan RKT hanya menampilkan satu baris untuk indikator dan target yang sama.
$targetGroupsBySasaran = [];
$printedTargetKeys = [];
foreach ($targets as $target) {
    $sasaran = trim((string) ($target['sasaran'] ?? ''));
    $targetKey = implode('|', [
        $sasaran,
        trim((string) ($target['indikator'] ?? '')),
        trim((string) ($target['target'] ?? '')),
    ]);
    if (isset($printedTargetKeys[$targetKey])) {
        continue;
    }
    $printedTargetKeys[$targetKey] = true;

    if (!isset($targetGroupsBySasaran[$sasaran])) {
        $targetGroupsBySasaran[$sasaran] = [];
    }
    $targetGroupsBySasaran[$sasaran][] = $target;
}

$targetGroups = [];
foreach ($targetGroupsBySasaran as $sasaran => $items) {
    $targetGroups[] = ['sasaran' => $sasaran, 'items' => $items];
}

$tanggalCetak = trim((string) ($meta['tanggal_surat'] ?? ''));
if ($tanggalCetak !== '') {
    $timestampTanggalCetak = strtotime($tanggalCetak);
    if ($timestampTanggalCetak !== false) {
        $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggalCetak = date('d', $timestampTanggalCetak) . ' ' . $namaBulan[(int) date('n', $timestampTanggalCetak)] . ' ' . date('Y', $timestampTanggalCetak);
    }
}

$totalDipa01 = 0.0;
$totalDipa04 = 0.0;
foreach ($targets as $target) {
    $totalDipa01 += num($target['dipa01']);
    $totalDipa04 += num($target['dipa04']);
}

// Handle Export CSV
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=RKT_' . $tahun . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['No', 'Sasaran Kinerja', 'Indikator Kinerja', 'Satuan', 'Tipe', 'Bobot', 'Target']);
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
$isPdf = ($_GET['export'] ?? '') === 'pdf';
$isExport = $isDocx || $isPdf;
if ($isDocx) {
    header("Content-Type: application/vnd.ms-word");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("content-disposition: attachment;filename=RKT_{$tahun}.doc");
}

if (!$isExport) {
    render_header('Cetak RKT');
}
?>

<?php if (!$isExport): ?>
<style>
/* Signature UI styles */
.signature-tabs { display: flex; gap: 8px; margin-bottom: 8px; }
.signature-tab { padding: 4px 12px; background: #e2e8f0; border-radius: 4px; cursor: pointer; font-size: 0.85rem; font-weight: 600; }
.signature-tab.active { background: #064e3b; color: #fff; }
.signature-panel { display: none; border: 1px solid #cbd5e1; border-radius: 4px; padding: 8px; background: #f8fafc; }
.signature-panel.active { display: block; }
.signature-canvas { border: 1px dashed #94a3b8; background: #fff; cursor: crosshair; display: block; margin-bottom: 8px; }
.signature-preview { max-width: 150px; max-height: 80px; display: block; margin-top: 8px; }
.clear-canvas-btn { background: #ef4444; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; cursor: pointer; }
</style>

<form method="get" class="toolbar">
    <input type="hidden" name="page" value="rkt_rka">
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
    <button type="submit" class="secondary">Tampilkan</button>
    <button type="button" onclick="printPdfSilent('index.php?page=rkt_rka&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf')" class="button" style="background:#475569; color:white; padding:8px 16px; border-radius:4px; text-decoration:none; border:none; cursor:pointer;">Cetak PDF</button>
    <a href="index.php?page=rkt_rka&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=doc" class="button" style="background:#1d4ed8; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;">Ekspor Word</a>
    <a href="index.php?page=rkt_rka&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=csv" class="button" style="background:#047857; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;">Ekspor CSV</a>
</form>

<section class="panel print-meta-form">
    <h2>Metadata RKT</h2>
    <form method="post" class="document-form-grid" id="meta-form">
        <input type="hidden" name="tahun" value="<?= h((string) $tahun) ?>">
        <input type="hidden" name="user_id" value="<?= h((string) $selectedUserId) ?>">
        <label>No. Surat <input name="no_surat" value="<?= h((string) $meta['no_surat']) ?>"></label>
        <label>Tanggal RKT <input type="date" name="tanggal_surat" value="<?= h((string) $meta['tanggal_surat']) ?>"><small class="muted">Tanggal ini tampil pada blok pengesahan cetakan RKT.</small></label>
        <label>Lokasi <input name="lokasi" value="<?= h((string) $meta['lokasi']) ?>"></label>
        <label>Nama Penyusun <input name="pihak1_nama" value="<?= h((string) $meta['pihak1_nama']) ?>"></label>
        <label>Jabatan Penyusun <input name="pihak1_jabatan" value="<?= h((string) $meta['pihak1_jabatan']) ?>"></label>
        <label>Nama Pimpinan <input name="pihak2_nama" value="<?= h((string) $meta['pihak2_nama']) ?>"></label>
        <label>Jabatan Pimpinan <input name="pihak2_jabatan" value="<?= h((string) $meta['pihak2_jabatan']) ?>"></label>
        <label>Catatan RKT <textarea name="catatan" placeholder="Catatan penyusunan Rencana Kerja Tahunan."><?= h((string) $meta['catatan']) ?></textarea></label>
        
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
        const x = (e.clientX || e.touches?.[0].clientX) - rect.left;
        const y = (e.clientY || e.touches?.[0].clientY) - rect.top;
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
<?php elseif ($isExport): ?>
<html>
<head>
<meta charset="utf-8">
</head>
<body>
<?php endif; ?>

<style>
.rkt-document { max-width: 160mm; margin: 0 auto; padding: 20mm 0; color: #000; font-family: "Nirmala UI", Arial, sans-serif; font-size: 11pt; line-height: 1.2; }
.rkt-title { margin: 0; color: #000; text-align: center; font-family: "Bookman Old Style", Georgia, serif; font-size: 14pt; font-weight: 700; line-height: 1.2; text-transform: uppercase; }
.rkt-title + .rkt-title { margin-bottom: 7mm; }
.rkt-table { width: 100%; border: 1px solid #f0b928; border-collapse: collapse; table-layout: fixed; }
.rkt-table th, .rkt-table td { border: 1px solid #f0b928; padding: 1.4mm 1.9mm; vertical-align: top; color: #000; }
.rkt-table th { border-color: #fff; background: #002060; color: #fff; text-align: center; vertical-align: middle; font-weight: 700; }
.rkt-table .rkt-no { width: 5.15%; text-align: center; vertical-align: middle; }
.rkt-table .rkt-sasaran { width: 35.72%; vertical-align: middle; }
.rkt-table .rkt-indikator { width: 50.64%; }
.rkt-table .rkt-target { width: 8.49%; text-align: center; vertical-align: middle; white-space: pre-line; }
.rkt-empty { padding: 8mm !important; text-align: center; }
.rkt-signature { width: 83mm; margin: 4mm 0 0 75mm; text-align: left; }
.rkt-signature p { margin: 0; }
.rkt-signature .rkt-sign-role { min-height: 6mm; font-weight: 700; }
.rkt-signature .rkt-sign-space { height: 14mm; line-height: 5mm; }
.rkt-signature img { display: block; max-width: 42mm !important; max-height: 22mm !important; margin: 1mm 0 !important; }
.rkt-signature .rkt-sign-name { font-weight: 700; text-decoration: underline; }
@media screen and (max-width: 760px) { .rkt-document { padding: 24px 0; font-size: 9pt; } .rkt-table { min-width: 650px; } .rkt-table-wrap { overflow-x: auto; } .rkt-signature { width: 58%; margin-left: 42%; } }
@media print { @page { size: A4 portrait; margin: 20mm 20mm 20mm 30mm; } .rkt-document { max-width: none; margin: 0; padding: 0; } .rkt-table thead { display: table-header-group; } .rkt-table tr { break-inside: avoid; page-break-inside: avoid; } }
</style>

<section class="print-sheet rkt-document">
    <h1 class="rkt-title">RENCANA KINERJA TAHUN ANGGARAN <?= h((string) $tahun) ?></h1>
    <h2 class="rkt-title"><?= h(mb_strtoupper((string) $documentUser['unit'], 'UTF-8')) ?></h2>

    <div class="rkt-table-wrap">
        <table class="rkt-table">
            <thead>
            <tr>
                <th>No</th>
                <th>Sasaran Kegiatan</th>
                <th>Indikator Kinerja</th>
                <th>Target</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$targets): ?>
                <tr>
                    <td colspan="4" class="rkt-empty">Belum ada target kinerja untuk tahun <?= h((string) $tahun) ?>.</td>
                </tr>
            <?php endif; ?>
            <?php $nomor = 1; ?>
            <?php foreach ($targetGroups as $group): ?>
                <?php foreach ($group['items'] as $itemIndex => $target): ?>
                    <tr>
                        <?php if ($itemIndex === 0): ?>
                            <td class="rkt-no" rowspan="<?= count($group['items']) ?>"><?= $nomor++ ?></td>
                            <td class="rkt-sasaran" rowspan="<?= count($group['items']) ?>"><?= h($group['sasaran']) ?></td>
                        <?php endif; ?>
                        <td class="rkt-indikator"><?= h((string) $target['indikator']) ?></td>
                        <td class="rkt-target"><?= h((string) $target['target']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="rkt-signature">
        <p><?= h((string) $meta['lokasi']) ?><?= $meta['lokasi'] !== '' && $tanggalCetak !== '' ? ', ' : '' ?><?= h($tanggalCetak) ?></p>
        <p>Pihak Pertama</p>
        <p class="rkt-sign-role"><?= h((string) $meta['pihak1_jabatan']) ?></p>
        <?php if (!empty($meta['pihak1_ttd'])): ?>
            <?= get_signature_img_tag((string) $meta['pihak1_ttd']) ?>
        <?php else: ?>
            <div class="rkt-sign-space" aria-hidden="true">&nbsp;<br>&nbsp;<br>&nbsp;</div>
        <?php endif; ?>
        <p class="rkt-sign-name"><?= h((string) $meta['pihak1_nama']) ?></p>
    </div>
</section>

<?php if ($isExport): ?>
    <?php if ($isPdf): ?>
        <script>window.onload = function() { window.print(); };</script>
    <?php endif; ?>
</body>
</html>
<?php else: ?>
<?php render_footer(); ?>
<?php endif; ?>
