<?php
declare(strict_types=1);

$user = current_user();
$tahun = year_value();
$canViewAll = user_can('view_all_targets');
$selectedUserId = $canViewAll ? (int) ($_GET['user_id'] ?? 0) : (int) $user['id'];
$profile = role_profile((string) $user['role']);
$documentUser = document_owner($user, $canViewAll, $selectedUserId);
$profile = role_profile((string) $documentUser['role']);

/**
 * Metadata tambahan PK disimpan sebagai JSON pada kolom catatan yang sudah ada.
 * Dengan cara ini NIP dapat dipakai tanpa mengubah struktur database.
 */
function pk_meta_extras(string $raw): array
{
    $defaults = ['pihak1_nip'=>'', 'pihak2_nip'=>'', 'catatan'=>''];
    $decoded = json_decode($raw, true);
    if (is_array($decoded) && (int)($decoded['_pk_version'] ?? 0) >= 1) {
        return array_merge($defaults, array_intersect_key($decoded, $defaults));
    }
    $defaults['catatan'] = $raw;
    return $defaults;
}

function pk_meta_pack(array $post): string
{
    return json_encode([
        '_pk_version'=>1,
        'pihak1_nip'=>trim((string)($post['pihak1_nip'] ?? '')),
        'pihak2_nip'=>trim((string)($post['pihak2_nip'] ?? '')),
        'catatan'=>trim((string)($post['catatan'] ?? '')),
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pkPayload = $_POST;
    $pkPayload['catatan'] = pk_meta_pack($_POST);
    save_document_meta($documentUser, $tahun, 'pk', $pkPayload);
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
$pkExtra = pk_meta_extras((string)($meta['catatan'] ?? ''));

$pihakPertamaJabatan = [
    'Wakil Ketua PTA Medan',
    'Panitera PTA Medan',
    'Sekretaris PTA Medan',
    'Kepala Bagian Perencanaan dan Anggaran',
    'Kepala Bagian Umum dan Keuangan',
    'Panitera Muda Hukum',
    'Panitera Muda Banding',
    'Kasubag Perencanaan Program dan Anggaran',
    'Kasubag Kepegawaian dan TI',
    'Kasubag Keuangan dan Pelaporan',
    'Kasubag Tata Usaha dan Rumah Tangga',
];
$pihakKeduaJabatan = $pihakPertamaJabatan;
$pihakKeduaJabatan[0] = 'Ketua PTA Medan';


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
$isPdf = ($_GET['export'] ?? '') === 'pdf';
$isExport = $isDocx || $isPdf;
if ($isDocx) {
    header("Content-Type: application/vnd.ms-word");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("content-disposition: attachment;filename=Perjanjian_Kinerja_{$tahun}.doc");
}

if (!$isExport) {
    render_header('Cetak Perjanjian Kinerja');
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
.signature-canvas { border: 1px dashed #94a3b8; background: #fff; cursor: crosshair; display: block; margin-bottom: 8px; max-width: 100%; touch-action: none; }
.signature-preview { max-width: 150px; max-height: 80px; display: block; margin-top: 8px; }
.clear-canvas-btn { background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; font-weight: 600; }
.pk-page-actions{display:grid;grid-template-columns:repeat(5,minmax(145px,1fr));gap:8px;width:100%;margin-top:8px}.pk-page-actions .button,.pk-page-actions button{display:flex;align-items:center;justify-content:center;gap:7px;text-align:center;text-decoration:none}.pk-word-button{background:#1d4ed8!important;color:#fff!important}.pk-csv-button{background:#047857!important;color:#fff!important}.pk-meta-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px}.pk-meta-heading span{color:#047857;font-size:.72rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.pk-meta-heading h2{margin:5px 0}.pk-meta-heading p{margin:0;color:#64748b}.pk-meta-heading>div:last-child{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid #b7e7cf;border-radius:10px;background:#ecfdf5;color:#047857;font-size:.76rem;font-weight:800;white-space:nowrap}.pk-full-field{grid-column:1/-1}@media(max-width:1050px){.pk-page-actions{grid-template-columns:repeat(3,minmax(140px,1fr))}}@media(max-width:680px){.pk-page-actions{grid-template-columns:1fr}.pk-meta-heading{display:block}.pk-meta-heading>div:last-child{margin-top:12px}.pk-full-field{grid-column:auto}}
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
    <div class="pk-page-actions">
        <button type="submit" class="secondary"><i class="ph ph-funnel"></i> Tampilkan</button>
        <button type="button" class="secondary" onclick="document.getElementById('pk-preview-start')?.scrollIntoView({behavior:'smooth'})"><i class="ph ph-eye"></i> Lihat Pratinjau</button>
        <button type="button" onclick="window.print()"><i class="ph ph-printer"></i> Cetak / Simpan PDF</button>
        <a href="index.php?page=pk&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=doc" class="button pk-word-button"><i class="ph ph-file-doc"></i> Ekspor Word</a>
        <a href="index.php?page=pk&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=csv" class="button pk-csv-button"><i class="ph ph-file-csv"></i> Ekspor CSV</a>
    </div>
</form>

<section class="panel print-meta-form pk-meta-panel">
    <div class="pk-meta-heading"><div><span>Format Resmi 2026</span><h2>Metadata Perjanjian Kinerja</h2><p>Isi pihak yang menandatangani. Pernyataan, lampiran target, dan lampiran anggaran dibuat otomatis.</p></div><div><i class="ph ph-seal-check"></i> Nomor Surat tidak digunakan</div></div>
    <form method="post" class="document-form-grid" id="meta-form">
        <input type="hidden" name="tahun" value="<?= h((string) $tahun) ?>">
        <input type="hidden" name="user_id" value="<?= h((string) $selectedUserId) ?>">
        <label>Tanggal Surat <input type="date" name="tanggal_surat" value="<?= h((string) $meta['tanggal_surat']) ?>"></label>
        <label>Lokasi <input name="lokasi" value="<?= h((string) $meta['lokasi']) ?>"></label>
        <label>Nama Pihak Pertama <input name="pihak1_nama" required value="<?= h((string) $meta['pihak1_nama']) ?>"></label>
        <label>Jabatan Pihak Pertama
            <select name="pihak1_jabatan" required>
                <option value="" disabled <?= trim((string) $meta['pihak1_jabatan']) === '' ? 'selected' : '' ?>>Pilih jabatan</option>
                <?php foreach ($pihakPertamaJabatan as $jabatan): ?>
                    <option value="<?= h($jabatan) ?>" <?= (string) $meta['pihak1_jabatan'] === $jabatan ? 'selected' : '' ?>><?= h($jabatan) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>NIP Pihak Pertama <input name="pihak1_nip" inputmode="numeric" value="<?= h((string) $pkExtra['pihak1_nip']) ?>" placeholder="Opsional untuk PK individu"></label>
        <label>Nama Pihak Kedua <input name="pihak2_nama" required value="<?= h((string) $meta['pihak2_nama']) ?>"></label>
        <label>Jabatan Pihak Kedua
            <select name="pihak2_jabatan" required>
                <option value="" disabled <?= trim((string) $meta['pihak2_jabatan']) === '' ? 'selected' : '' ?>>Pilih jabatan</option>
                <?php foreach ($pihakKeduaJabatan as $jabatan): ?>
                    <option value="<?= h($jabatan) ?>" <?= (string) $meta['pihak2_jabatan'] === $jabatan ? 'selected' : '' ?>><?= h($jabatan) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>NIP Pihak Kedua <input name="pihak2_nip" inputmode="numeric" value="<?= h((string) $pkExtra['pihak2_nip']) ?>" placeholder="Opsional untuk PK individu"></label>
        <label class="pk-full-field">Catatan Dokumen <textarea name="catatan" placeholder="Catatan internal, tidak ditampilkan pada naskah resmi."><?= h((string) $pkExtra['catatan']) ?></textarea></label>
        
        <!-- Pihak 1 TTD UI -->
        <div style="grid-column: 1 / -1; border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 8px;">
            <label>Tanda Tangan Pihak Pertama</label>
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
            <label>Tanda Tangan Pihak Kedua</label>
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
<?php elseif ($isExport): ?>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: "Times New Roman", Times, serif; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { border: 1px solid #000; padding: 5px; text-align: left; }
@page { margin: 20mm; }
</style>
</head>
<body>
<?php endif; ?>

<?php require __DIR__ . '/../app/templates/perjanjian_kinerja.php'; ?>

<?php if ($isExport): ?>
    <?php if ($isPdf): ?>
        <script>window.onload = function() { window.print(); };</script>
    <?php endif; ?>
</body>
</html>
<?php else: ?>
<?php render_footer(); ?>
<?php endif; ?>
