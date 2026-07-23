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
    flash('Metadata dan Tanda Tangan Rencana Aksi berhasil disimpan.');
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

$formatTargetValue = static function (array $target, int $quarter): string {
    $value = target_for_quarter($target, $quarter);
    $formatted = abs($value - round($value)) < 0.00001
        ? (string) (int) round($value)
        : rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
    return $formatted . ((string) ($target['satuan'] ?? '') === '%' ? '%' : '');
};

$approvalName = trim((string) ($meta['pihak2_nama'] ?? '')) ?: trim((string) ($meta['pihak1_nama'] ?? ''));
$approvalRole = trim((string) ($meta['pihak2_jabatan'] ?? '')) ?: trim((string) ($meta['pihak1_jabatan'] ?? ''));
$approvalSignature = trim((string) ($meta['pihak2_ttd'] ?? '')) ?: trim((string) ($meta['pihak1_ttd'] ?? ''));
$displayDate = '-';
if (!empty($meta['tanggal_surat'])) {
    $timestamp = strtotime((string) $meta['tanggal_surat']);
    if ($timestamp !== false) {
        $monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $displayDate = date('d', $timestamp) . ' ' . $monthNames[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}

// Handle Export CSV
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Rencana_Aksi_' . $tahun . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['No', 'Sasaran', 'Indikator', 'Satuan', 'Sumber Data', 'Target TW1', 'Target TW2', 'Target TW3', 'Target TW4', 'Aksi/Kegiatan', 'Jadwal', 'Keluaran', 'Dana']);
    foreach ($targets as $i => $target) {
        fputcsv($output, [
            $i + 1,
            $target['sasaran'],
            $target['indikator'],
            $target['satuan'] ?? '-',
            $target['sumber_data'] ?: implode(', ', $profile['sources']),
            target_for_quarter($target, 1),
            target_for_quarter($target, 2),
            target_for_quarter($target, 3),
            target_for_quarter($target, 4),
            'Validasi data dari ' . implode(', ', $profile['sources']) . ' dan tindak lanjut indikator.',
            'TW1 s.d. TW4 Tahun ' . $tahun,
            $profile['outputs'][0] ?? 'Laporan capaian kinerja triwulan.',
            (num($target['dipa01']) + num($target['dipa04']))
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
    header("content-disposition: attachment;filename=Rencana_Aksi_{$tahun}.doc");
}

if (!$isExport) {
    render_header('Cetak Rencana Aksi');
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
                        <?= format_user_label($owner['nama'] ?? '', $owner['role'] ?? '', false) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>
    <button type="submit" class="secondary">Tampilkan</button>
    <button type="button" onclick="printPdfSilent('index.php?page=renaksi&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf')" class="button" style="background:#475569; color:white; padding:8px 16px; border-radius:4px; text-decoration:none; border:none; cursor:pointer;">Cetak PDF</button>
    <a href="index.php?page=renaksi&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=doc" class="button" style="background:#1d4ed8; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;">Ekspor Word</a>
    <a href="index.php?page=renaksi&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=csv" class="button" style="background:#047857; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;">Ekspor CSV</a>
</form>

<section class="panel print-meta-form">
    <h2>Metadata Rencana Aksi</h2>
    <form method="post" class="document-form-grid" id="meta-form">
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
<?php else: ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<style>
@page WordSection1 {
    size: 841.9pt 595.3pt; /* A4 Landscape */
    mso-page-orientation: landscape;
    margin: 36.0pt 36.0pt 36.0pt 36.0pt;
}
div.WordSection1 { page: WordSection1; }
body { font-family: "Times New Roman", Times, serif; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { border: 1px solid #000; padding: 5px; text-align: left; vertical-align: top; }
th { background-color: #f6bc8b; font-weight: bold; text-align: center; vertical-align: middle; }
.center { text-align: center; }
.number { text-align: right; }
.strong { font-weight: bold; }
.indicator-band td { background-color: #fce8d7; font-weight: bold; }
.renaksi-title { text-align: center; font-size: 14pt; font-weight: bold; }
.renaksi-subtitle { text-align: center; font-size: 12pt; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; }
.renaksi-approval { font-size: 11pt; }
</style>
</head>
<body>
<div class="WordSection1">
<?php endif; ?>

<style>
/* Keep the browser print preview and the generated PDF on the same canvas. */
@page {
    size: A4 landscape;
    margin: 10mm;
}

.renaksi-document {
    width: min(1120px, 100%);
    max-width: none;
    box-sizing: border-box;
    margin: 24px auto;
    padding: 38px 42px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    box-shadow: 0 12px 32px rgba(15, 23, 42, .08);
    color: #111;
    font-family: Arial, Helvetica, sans-serif;
    background: #fff;
}
.renaksi-title {
    margin: 0;
    text-align: center;
    font-size: 18px;
    line-height: 1.25;
    font-weight: 700;
    letter-spacing: .01em;
}
.renaksi-subtitle {
    margin: 12px 0 28px;
    text-align: center;
    font-size: 17px;
    line-height: 1.25;
    font-weight: 700;
    text-transform: uppercase;
}
.renaksi-table {
    width: 100%;
    margin: 0 0 14px;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 10px;
    line-height: 1.25;
}
.renaksi-table th,
.renaksi-table td {
    border: 1px solid #111;
    padding: 5px 6px;
    vertical-align: top;
    overflow-wrap: anywhere;
}
.renaksi-table thead th {
    background: #f6bc8b;
    color: #111;
    text-align: center;
    vertical-align: middle;
    font-weight: 700;
}
.renaksi-table .center { text-align: center; vertical-align: middle; }
.renaksi-table .number { text-align: right; vertical-align: middle; white-space: nowrap; }
.renaksi-table .strong { font-weight: 700; }
.renaksi-table .indicator-band td {
    padding: 4px 6px;
    background: #fce8d7;
    font-weight: 700;
}
.renaksi-table .empty-row td { padding: 18px 8px; text-align: center; }
.renaksi-summary { margin-bottom: 16px; }
.renaksi-activities thead { display: table-header-group; }
.renaksi-activities tr { break-inside: avoid; page-break-inside: avoid; }
.renaksi-note { margin: 16px 0 0; font-size: 10px; line-height: 1.4; }
.renaksi-approval {
    font-size: 11px;
    line-height: 1.35;
    break-inside: avoid;
    page-break-inside: avoid;
}
.renaksi-approval .signature-space {
    min-height: 72px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.renaksi-approval .signature-space img { max-width: 150px; max-height: 72px; object-fit: contain; }
.renaksi-approval .name { font-weight: 700; text-decoration: underline; }
@media (max-width: 900px) {
    .renaksi-document { padding: 24px 18px; overflow-x: auto; }
    .renaksi-table { min-width: 920px; }
}
@media print {
    html, body { width: 100%; margin: 0 !important; padding: 0 !important; }
    .renaksi-document {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }
    .renaksi-title { font-size: 13pt; }
    .renaksi-subtitle { margin: 8px 0 18px; font-size: 12pt; }
    .renaksi-table { font-size: 8.5pt; }
    .renaksi-table th, .renaksi-table td { padding: 3.5px 5px; }
    .renaksi-summary, .renaksi-activities { break-after: auto; page-break-after: auto; }
    .renaksi-summary thead, .renaksi-activities thead { display: table-header-group; }
}
</style>

<section class="print-sheet renaksi-document">
    <h1 class="renaksi-title">RENCANA AKSI PERJANJIAN KINERJA TAHUN <?= h((string) $tahun) ?></h1>
    <h2 class="renaksi-subtitle"><?= h((string) $documentUser['unit']) ?></h2>

    <table class="renaksi-table renaksi-summary" border="1">
        <colgroup>
            <col style="width:4%">
            <?php if ($canViewAll): ?><col style="width:13%"><?php endif; ?>
            <col style="width:<?= $canViewAll ? '23' : '28' ?>%">
            <col style="width:<?= $canViewAll ? '40' : '48' ?>%">
            <col style="width:5%"><col style="width:5%"><col style="width:5%"><col style="width:5%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">No.</th>
                <?php if ($canViewAll): ?><th rowspan="2">PEMILIK</th><?php endif; ?>
                <th rowspan="2">SASARAN KEGIATAN</th>
                <th rowspan="2">INDIKATOR</th>
                <th colspan="4">TARGET</th>
            </tr>
            <tr><th>I</th><th>II</th><th>III</th><th>IV</th></tr>
        </thead>
        <tbody>
        <?php if (!$targets): ?>
            <tr class="empty-row"><td colspan="<?= $canViewAll ? '8' : '7' ?>">Belum ada target kinerja untuk tahun <?= h((string) $tahun) ?>.</td></tr>
        <?php endif; ?>
        <?php foreach ($targets as $i => $target): ?>
            <tr>
                <td class="center strong"><?= $i + 1 ?>.</td>
                <?php if ($canViewAll): ?>
                    <td><?= format_user_label($target['owner_nama'] ?? '', $target['owner_role'] ?? '', true) ?></td>
                <?php endif; ?>
                <td class="strong"><?= h((string) $target['sasaran']) ?></td>
                <td class="strong"><?= h((string) $target['indikator']) ?></td>
                <?php for ($quarter = 1; $quarter <= 4; $quarter++): ?>
                    <td class="center strong"><?= h($formatTargetValue($target, $quarter)) ?></td>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <table class="renaksi-table renaksi-activities" border="1">
        <colgroup>
            <col style="width:4%">
            <col style="width:29%">
            <col style="width:3.5%"><col style="width:3.5%"><col style="width:3.5%"><col style="width:3.5%">
            <col style="width:10%">
            <col style="width:18%">
            <col style="width:18%">
            <col style="width:7%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">AKSI/KEGIATAN</th>
                <th colspan="4">JADWAL<br>PELAKSANAAN</th>
                <th rowspan="2">KELUARAN</th>
                <th rowspan="2">PROGRAM</th>
                <th rowspan="2">KEGIATAN</th>
                <th rowspan="2">DANA<br>(Rp.)</th>
            </tr>
            <tr><th>I</th><th>II</th><th>III</th><th>IV</th></tr>
        </thead>
        <tbody>
        <?php if (!$targets): ?>
            <tr class="empty-row"><td colspan="10">Belum ada data rencana aksi yang dapat dicetak.</td></tr>
        <?php endif; ?>
        <?php foreach ($targets as $i => $target): ?>
            <?php
            $action = trim((string) ($target['analisis_kegiatan'] ?? ''));
            if ($action === '') $action = trim((string) ($target['analisis_upaya'] ?? ''));
            if ($action === '') $action = trim((string) ($target['analisis_strategi'] ?? ''));
            if ($action === '') $action = '-';
            $fund = num($target['dipa01'] ?? 0) + num($target['dipa04'] ?? 0);
            ?>
            <tr class="indicator-band"><td colspan="10"><?= h((string) $target['indikator']) ?></td></tr>
            <tr>
                <td class="center"><?= $i + 1 ?>.</td>
                <td><?= nl2br(h($action)) ?></td>
                <?php for ($quarter = 1; $quarter <= 4; $quarter++): ?>
                    <td class="center"><?= target_for_quarter($target, $quarter) != 0.0 ? '√' : '–' ?></td>
                <?php endfor; ?>
                <td class="center"><?= h((string) ($profile['outputs'][0] ?? '-')) ?></td>
                <td class="center">-</td>
                <td class="center">-</td>
                <td class="number"><?= $fund > 0 ? h(number_format($fund, 0, ',', '.')) : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (!empty($meta['catatan'])): ?>
        <p class="renaksi-note"><?= nl2br(h((string) $meta['catatan'])) ?></p>
    <?php endif; ?>

    <table border="0" width="100%" style="margin-top: 30px; page-break-inside: avoid;">
        <tr style="page-break-inside: avoid;">
            <td width="65%" style="border: none;"></td>
            <td width="35%" style="border: none; text-align: center; padding: 0;">
                <p style="margin: 0; padding: 0; page-break-after: avoid;">
                    <?= h((string) ($meta['lokasi'] ?: 'Medan')) ?>, <?= h($displayDate) ?><br>
                    <?= h($approvalRole ?: 'Pimpinan') ?>
                </p>
                <table border="0" cellspacing="0" cellpadding="0" width="100%" style="page-break-inside: avoid; page-break-before: avoid; page-break-after: avoid;">
                    <tr height="90">
                        <td align="center" valign="middle" style="border: none; padding: 0;">
                            <?php if ($approvalSignature !== ''): ?>
                                <?= get_signature_img_tag($approvalSignature, 150, 80, isset($isDocx) ? $isDocx : false) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <p style="margin: 0; padding: 0; font-weight: bold; text-decoration: underline; page-break-before: avoid;">
                    <?= h($approvalName ?: '-') ?>
                </p>
            </td>
        </tr>
    </table>
</section>

<?php if ($isExport): ?>
    <?php if ($isDocx): ?>
        </div>
    <?php endif; ?>
    <?php if ($isPdf): ?>
        <script>window.onload = function() { window.print(); };</script>
    <?php endif; ?>
</body>
</html>
<?php else: ?>
<?php render_footer(); ?>
<?php endif; ?>
