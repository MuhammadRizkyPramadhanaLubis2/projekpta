<?php
declare(strict_types=1);

$user = current_user();
$role = (string) ($user['role'] ?? '');
$isEvaluator = in_array($role, ['Perencanaan', 'Admin'], true);
$isSatker = str_starts_with($role, 'Satker');
if (!$user || (!$isEvaluator && !$isSatker)) {
    flash('Halaman Evaluasi hanya tersedia untuk evaluator dan satuan kerja.', 'error');
    header('Location: index.php?page=dashboard');
    exit;
}

require __DIR__ . '/../app/evaluasi_akip_instrument.php';

function sakip_defaults(array $sections, string $channel): array
{
    $result = [];
    foreach ($sections as $section) {
        foreach ($section['subsections'] as $sub) {
            $isMandiri = $channel === 'mandiri';
            $result[$sub['code']] = [
                'jawaban' => $isMandiri ? $sub['satker_answer'] : $sub['evaluator_answer'],
                'nilai' => (float) ($isMandiri ? $sub['satker_score'] : $sub['evaluator_score']),
                'catatan' => $isMandiri ? '' : (string) $sub['notes'],
                'rekomendasi' => '',
                'kriteria' => [],
            ];
        }
    }
    return $result;
}

function sakip_summary(array $data): array
{
    $total = 0.0;
    foreach ($data as $item) {
        $total += (float) ($item['nilai'] ?? 0);
    }
    $grade = $total >= 90 ? 'AA' : ($total >= 80 ? 'A' : ($total >= 70 ? 'BB' : ($total >= 60 ? 'B' : ($total >= 50 ? 'CC' : ($total >= 30 ? 'C' : 'D')))));
    return ['total' => round($total, 2), 'grade' => $grade];
}

$tahun = year_value();
$satkerId = (int) ($_GET['user_id'] ?? $_POST['satker_id'] ?? 0);
if ($isSatker) {
    $satkerId = (int) $user['id'];
}
$mode = (string) ($_GET['mode'] ?? ($isSatker ? 'mandiri' : ($satkerId > 0 ? 'evaluator' : 'list')));
$allowedModes = $isEvaluator ? ['list', 'mandiri', 'evaluator', 'lhe'] : ['mandiri'];
if (!in_array($mode, $allowedModes, true)) {
    $mode = $isEvaluator ? 'list' : 'mandiri';
}
$canEditMandiri = $isSatker && $satkerId === (int) $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_assessment_ajax') {
    $channel = (string) ($_POST['channel'] ?? '');
    $type = (string) ($_POST['type'] ?? '');
    $code = trim((string) ($_POST['code'] ?? ''));
    $criterionIndex = max(0, (int) ($_POST['criterion_index'] ?? 0));
    $allowedType = ['jawaban', 'catatan', 'rekomendasi', 'criteria_note'];
    $authorized = ($channel === 'mandiri' && $canEditMandiri) || ($channel === 'evaluator' && $isEvaluator);
    if (!$authorized || $satkerId <= 0 || $code === '' || !in_array($type, $allowedType, true)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error']);
        exit;
    }
    $stmt = db()->prepare('SELECT data_mandiri, data_nilai FROM evaluasi_sakip WHERE tahun = :tahun AND satker_id = :satker_id');
    $stmt->execute(['tahun' => $tahun, 'satker_id' => $satkerId]);
    $existing = $stmt->fetch();
    $column = $channel === 'mandiri' ? 'data_mandiri' : 'data_nilai';
    $data = $existing && $existing[$column] ? json_decode((string) $existing[$column], true) : sakip_defaults($sections, $channel);
    $data[$code] ??= ['jawaban' => '', 'nilai' => 0, 'catatan' => '', 'rekomendasi' => '', 'kriteria' => []];
    if ($type === 'jawaban') {
        $data[$code]['jawaban'] = (string) ($_POST['value'] ?? '');
        $data[$code]['nilai'] = (float) ($_POST['score'] ?? 0);
    } elseif ($type === 'criteria_note') {
        $data[$code]['kriteria'][$criterionIndex]['catatan'] = trim((string) ($_POST['value'] ?? ''));
    } else {
        $data[$code][$type] = trim((string) ($_POST['value'] ?? ''));
    }
    $summary = sakip_summary($data);
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    if ($existing) {
        if ($channel === 'mandiri') {
            $save = db()->prepare('UPDATE evaluasi_sakip SET nilai_mandiri = :nilai, grade_mandiri = :grade, data_mandiri = :data, updated_at = CURRENT_TIMESTAMP WHERE tahun = :tahun AND satker_id = :satker_id');
        } else {
            $save = db()->prepare('UPDATE evaluasi_sakip SET nilai_akhir = :nilai, grade_akhir = :grade, data_nilai = :data, evaluator_id = :evaluator, updated_at = CURRENT_TIMESTAMP WHERE tahun = :tahun AND satker_id = :satker_id');
        }
    } else {
        if ($channel === 'mandiri') {
            $save = db()->prepare('INSERT INTO evaluasi_sakip (tahun, satker_id, nilai_mandiri, grade_mandiri, data_mandiri, status) VALUES (:tahun, :satker_id, :nilai, :grade, :data, "Penilaian Mandiri")');
        } else {
            $save = db()->prepare('INSERT INTO evaluasi_sakip (tahun, satker_id, evaluator_id, nilai_akhir, grade_akhir, data_nilai, status) VALUES (:tahun, :satker_id, :evaluator, :nilai, :grade, :data, "Evaluasi")');
        }
    }
    $params = ['tahun' => $tahun, 'satker_id' => $satkerId, 'nilai' => $summary['total'], 'grade' => $summary['grade'], 'data' => $json];
    if ($channel === 'evaluator') {
        $params['evaluator'] = (int) $user['id'];
    }
    $save->execute($params);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'total_score' => $summary['total'], 'grade' => $summary['grade']]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_evidence') {
    $subCode = trim((string) ($_POST['sub_code'] ?? ''));
    $criterionIndex = max(0, (int) ($_POST['criterion_index'] ?? 0));
    $file = $_FILES['evidence_file'] ?? null;
    if (!$canEditMandiri || $satkerId <= 0 || $subCode === '' || !$file || $file['error'] !== UPLOAD_ERR_OK) {
        flash('Dokumen belum dipilih atau Anda tidak memiliki akses.', 'error');
    } else {
        $extension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['pdf', 'doc', 'docx'], true)) {
            flash('Dokumen harus berformat PDF, DOC, atau DOCX.', 'error');
        } elseif ((int) $file['size'] > 10 * 1024 * 1024) {
            flash('Ukuran dokumen maksimal 10 MB.', 'error');
        } else {
            $uploadDir = dirname(__DIR__) . '/data/uploads/evaluasi-akip/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $storedName = 'evidence_' . $tahun . '_' . $satkerId . '_' . str_replace('.', '-', $subCode) . '_' . $criterionIndex . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $storedName)) {
                $save = db()->prepare('INSERT INTO evaluasi_sakip_dokumen (tahun, satker_id, sub_code, criterion_index, original_name, stored_name, uploaded_by) VALUES (:tahun, :satker_id, :sub_code, :criterion_index, :original_name, :stored_name, :uploaded_by)');
                $save->execute(['tahun' => $tahun, 'satker_id' => $satkerId, 'sub_code' => $subCode, 'criterion_index' => $criterionIndex, 'original_name' => basename((string) $file['name']), 'stored_name' => $storedName, 'uploaded_by' => (int) $user['id']]);
                flash('Dokumen kriteria berhasil ditambahkan.');
            }
        }
    }
    header('Location: index.php?page=evaluasi-akip&mode=mandiri&user_id=' . $satkerId . '&tahun=' . $tahun . '#sub-' . urlencode(str_replace('.', '-', $subCode)));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_lhe' && $isEvaluator) {
    $file = $_FILES['lhe_file'] ?? null;
    if ($satkerId > 0 && $file && $file['error'] === UPLOAD_ERR_OK && strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION)) === 'pdf') {
        $uploadDir = dirname(__DIR__) . '/data/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = 'lhe_' . $tahun . '_' . $satkerId . '_' . time() . '.pdf';
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $stmt = db()->prepare('SELECT id FROM evaluasi_sakip WHERE tahun = :tahun AND satker_id = :satker_id');
            $stmt->execute(['tahun' => $tahun, 'satker_id' => $satkerId]);
            if ($stmt->fetch()) {
                $save = db()->prepare('UPDATE evaluasi_sakip SET lhe_file = :file, updated_at = CURRENT_TIMESTAMP WHERE tahun = :tahun AND satker_id = :satker_id');
                $save->execute(['tahun' => $tahun, 'satker_id' => $satkerId, 'file' => $filename]);
            } else {
                $save = db()->prepare('INSERT INTO evaluasi_sakip (tahun, satker_id, evaluator_id, status, lhe_file) VALUES (:tahun, :satker_id, :evaluator, "Evaluasi", :file)');
                $save->execute(['tahun' => $tahun, 'satker_id' => $satkerId, 'evaluator' => (int) $user['id'], 'file' => $filename]);
            }
            flash('Dokumen LHE berhasil diunggah.');
        }
    } else {
        flash('Pilih dokumen LHE berformat PDF.', 'error');
    }
    header('Location: index.php?page=evaluasi-akip&mode=lhe&user_id=' . $satkerId . '&tahun=' . $tahun);
    exit;
}

$satker = null;
$evaluation = null;
if ($satkerId > 0) {
    $stmt = db()->prepare('SELECT id, nama, unit FROM users WHERE id = :id');
    $stmt->execute(['id' => $satkerId]);
    $satker = $stmt->fetch();
    $stmt = db()->prepare('SELECT * FROM evaluasi_sakip WHERE tahun = :tahun AND satker_id = :satker_id');
    $stmt->execute(['tahun' => $tahun, 'satker_id' => $satkerId]);
    $evaluation = $stmt->fetch() ?: null;
}
$dataMandiri = $evaluation && $evaluation['data_mandiri'] ? json_decode((string) $evaluation['data_mandiri'], true) : sakip_defaults($sections, 'mandiri');
$dataEvaluator = $evaluation && $evaluation['data_nilai'] ? json_decode((string) $evaluation['data_nilai'], true) : sakip_defaults($sections, 'evaluator');
$summaryMandiri = sakip_summary($dataMandiri);
$summaryEvaluator = sakip_summary($dataEvaluator);

$criterionDocuments = [];
if ($satkerId > 0) {
    $stmt = db()->prepare('SELECT * FROM evaluasi_sakip_dokumen WHERE tahun = :tahun AND satker_id = :satker_id ORDER BY id');
    $stmt->execute(['tahun' => $tahun, 'satker_id' => $satkerId]);
    foreach ($stmt->fetchAll() as $document) {
        $criterionDocuments[(string) $document['sub_code']][(int) $document['criterion_index']][] = $document;
    }
}

$satkers = [];
if ($mode === 'list' && $isEvaluator) {
    $stmt = db()->prepare('SELECT u.id, u.nama, u.unit, es.nilai_mandiri, es.grade_mandiri, es.nilai_akhir, es.grade_akhir, es.status, es.lhe_file FROM users u LEFT JOIN evaluasi_sakip es ON u.id = es.satker_id AND es.tahun = :tahun WHERE u.status = "active" AND u.role LIKE "Satker%" ORDER BY u.unit, u.nama');
    $stmt->execute(['tahun' => $tahun]);
    $satkers = $stmt->fetchAll();
}

render_header('Evaluasi AKIP');
?>
<link rel="stylesheet" href="assets/evaluasi-akip.css?v=<?= time() ?>">
<div class="evakip">
<nav class="evakip-tabs" aria-label="Navigasi Evaluasi">
    <?php if ($isEvaluator): ?><a href="index.php?page=evaluasi-akip&tahun=<?= $tahun ?>" class="<?= $mode === 'list' ? 'active' : '' ?>"><i class="ph ph-list-bullets"></i> Daftar Evaluasi</a><?php endif; ?>
    <?php if ($satkerId > 0): ?>
        <a href="index.php?page=evaluasi-akip&mode=mandiri&user_id=<?= $satkerId ?>&tahun=<?= $tahun ?>" class="<?= $mode === 'mandiri' ? 'active' : '' ?>"><i class="ph ph-clipboard-text"></i> Penilaian Mandiri</a>
        <?php if ($isEvaluator): ?><a href="index.php?page=evaluasi-akip&mode=evaluator&user_id=<?= $satkerId ?>&tahun=<?= $tahun ?>" class="<?= $mode === 'evaluator' ? 'active' : '' ?>"><i class="ph ph-check-square"></i> Evaluasi Tingkat Banding</a><a href="index.php?page=evaluasi-akip&mode=lhe&user_id=<?= $satkerId ?>&tahun=<?= $tahun ?>" class="<?= $mode === 'lhe' ? 'active' : '' ?>"><i class="ph ph-file-pdf"></i> LHE</a><?php endif; ?>
    <?php endif; ?>
</nav>

<?php if ($mode === 'list'): ?>
<section class="evakip-heading"><div><span class="evakip-kicker">Evaluasi AKIP</span><h2>Daftar Penilaian Evaluasi Tingkat Banding</h2><p>Buka penilaian mandiri satker, lalu lakukan evaluasi pada data yang sama.</p></div><form method="get" class="evakip-year"><input type="hidden" name="page" value="evaluasi-akip"><label>Tahun Penilaian<select name="tahun" onchange="this.form.submit()"><?php for ($y=2020;$y<=2030;$y++): ?><option value="<?= $y ?>" <?= $y===$tahun?'selected':'' ?>><?= $y ?></option><?php endfor; ?></select></label></form></section>
<section class="evakip-card"><div class="evakip-table-wrap"><table class="evakip-table"><thead><tr><th>No</th><th>Satuan Kerja</th><th>Tahun</th><th>Mandiri</th><th>Evaluasi</th><th>LHE</th><th>Aksi</th></tr></thead><tbody><?php foreach ($satkers as $index=>$row): ?><tr><td><?= $index+1 ?></td><td><strong><?= h((string)$row['unit']) ?></strong><small><?= h((string)$row['nama']) ?></small></td><td><?= $tahun ?></td><td><span class="evakip-grade"><?= h((string)($row['grade_mandiri']?:'-')) ?></span><small><?= $row['nilai_mandiri']!==null?number_format((float)$row['nilai_mandiri'],2):'Belum diisi' ?></small></td><td><span class="evakip-grade"><?= h((string)($row['grade_akhir']?:'-')) ?></span><small><?= $row['nilai_akhir']!==null?number_format((float)$row['nilai_akhir'],2):'Belum dinilai' ?></small></td><td><?= $row['lhe_file']?'<span class="evakip-file-ok">Tersedia</span>':'<span class="evakip-file-empty">Belum ada</span>' ?></td><td><div class="evakip-row-actions"><a class="button secondary evakip-action" href="index.php?page=evaluasi-akip&mode=mandiri&user_id=<?= (int)$row['id'] ?>&tahun=<?= $tahun ?>">Lihat Mandiri</a><a class="button evakip-action" href="index.php?page=evaluasi-akip&mode=evaluator&user_id=<?= (int)$row['id'] ?>&tahun=<?= $tahun ?>">Evaluasi</a></div></td></tr><?php endforeach; ?></tbody></table></div></section>

<?php elseif ($mode === 'lhe'): ?>
<section class="evakip-heading"><div><span class="evakip-kicker"><?= h((string)($satker['unit']??'Satker')) ?> · <?= $tahun ?></span><h2>Laporan Hasil Evaluasi</h2><p>Unggah dan lihat status dokumen LHE.</p></div></section><div class="evakip-lhe-grid"><section class="evakip-card evakip-upload"><i class="ph ph-file-arrow-up"></i><h3>Unggah Dokumen LHE</h3><p>Gunakan PDF LHE final.</p><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="upload_lhe"><input type="hidden" name="satker_id" value="<?= $satkerId ?>"><input type="file" name="lhe_file" accept="application/pdf,.pdf" required><button>Unggah LHE</button></form></section><section class="evakip-card"><h3>Status Dokumen</h3><?php if ($evaluation&&$evaluation['lhe_file']): ?><div class="evakip-document"><span class="evakip-document-icon"><i class="ph ph-check"></i></span><div><strong>Dokumen tersedia</strong><small><?= h((string)$evaluation['lhe_file']) ?></small></div><a class="button secondary" target="_blank" href="data/uploads/<?= h((string)$evaluation['lhe_file']) ?>">Lihat PDF</a></div><?php else: ?><div class="evakip-document"><span class="evakip-document-icon empty"><i class="ph ph-clock"></i></span><div><strong>Belum ada dokumen</strong><small>LHE belum diunggah.</small></div></div><?php endif; ?></section></div>

<?php else: $isMandiriMode=$mode==='mandiri'; $channel=$isMandiriMode?'mandiri':'evaluator'; $currentData=$isMandiriMode?$dataMandiri:$dataEvaluator; $currentSummary=$isMandiriMode?$summaryMandiri:$summaryEvaluator; $editable=$isMandiriMode?$canEditMandiri:$isEvaluator; ?>
<section class="evakip-heading"><div><span class="evakip-kicker"><?= h((string)($satker['unit']??'Satker')) ?> · <?= $tahun ?></span><h2><?= $isMandiriMode?'Penilaian Mandiri':'Evaluasi Tingkat Banding' ?></h2><p><?= $isMandiriMode?'Isi jawaban, catatan, dan dokumen bukti secara bertahap.':'Bandingkan jawaban mandiri dengan bukti, lalu isi analisis dan rekomendasi evaluator.' ?></p></div><div class="evakip-total"><small><?= $isMandiriMode?'Nilai Mandiri':'Nilai Evaluator' ?></small><strong id="evakip-total-value"><?= number_format($currentSummary['total'],2) ?></strong><span id="evakip-grade-value"><?= h($currentSummary['grade']) ?></span></div></section>
<?php if ($isMandiriMode && !$editable): ?><div class="evakip-view-note"><i class="ph ph-eye"></i> Mode lihat. Data Penilaian Mandiri hanya dapat diubah oleh satuan kerja.</div><?php endif; ?>
<div class="evakip-layout"><aside class="evakip-index"><?php foreach($sections as $section): ?><a href="#komponen-<?= h($section['code']) ?>"><span><?= h($section['code']) ?></span><?= h($section['title']) ?></a><?php endforeach; ?></aside><div class="evakip-components">
<?php foreach($sections as $section): ?><section class="evakip-component" id="komponen-<?= h($section['code']) ?>"><header><div><span>Komponen <?= h($section['code']) ?></span><h3><?= h($section['title']) ?></h3></div><div><small>Bobot <?= h($section['weight']) ?></small><strong><?= h($isMandiriMode?$section['weight']:$section['evaluator']) ?></strong></div></header>
<?php foreach($section['subsections'] as $sub): $mandiri=$dataMandiri[$sub['code']]??[]; $eval=$dataEvaluator[$sub['code']]??[]; $active=$currentData[$sub['code']]??[]; ?><details class="evakip-sub" id="sub-<?= h(str_replace('.','-',$sub['code'])) ?>" open><summary><span class="evakip-code"><?= h($sub['code']) ?></span><span><strong><?= h($sub['title']) ?></strong><small>Bobot <?= h($sub['weight']) ?></small></span><span class="evakip-sub-score"><small><?= $isMandiriMode?'Mandiri':'Mandiri → Evaluator' ?></small><b><?= h((string)($mandiri['jawaban']??'-')) ?> · <?= number_format((float)($mandiri['nilai']??0),2) ?><?= !$isMandiriMode?' → '.h((string)($eval['jawaban']??'-')).' · '.number_format((float)($eval['nilai']??0),2):'' ?></b></span></summary><div class="evakip-sub-body">
<?php if ($isMandiriMode): ?><div class="evakip-input-row"><label>Jawaban Mandiri<select class="evakip-assessment-answer" data-channel="mandiri" data-code="<?= h($sub['code']) ?>" data-weight="<?= h($sub['weight']) ?>" <?= !$editable?'disabled':'' ?>><?php foreach(['AA','A','BB','B','CC','C','D','E'] as $option): ?><option value="<?= $option ?>" <?= $option===($active['jawaban']??'')?'selected':'' ?>><?= $option ?></option><?php endforeach; ?></select></label><label>Nilai<input class="evakip-assessment-score" data-channel="mandiri" data-code="<?= h($sub['code']) ?>" value="<?= number_format((float)($active['nilai']??0),2,'.','') ?>" readonly></label><label>Catatan Mandiri<textarea class="evakip-assessment-text" data-channel="mandiri" data-type="catatan" data-code="<?= h($sub['code']) ?>" rows="2" <?= !$editable?'readonly':'' ?>><?= h((string)($active['catatan']??'')) ?></textarea></label></div>
<?php else: ?><div class="evakip-compare"><div class="evakip-compare-card mandiri"><span>Penilaian Mandiri</span><strong><?= h((string)($mandiri['jawaban']??'-')) ?> · <?= number_format((float)($mandiri['nilai']??0),2) ?></strong><p><?= h((string)($mandiri['catatan']??'Belum ada catatan mandiri.')) ?></p></div><div class="evakip-compare-card evaluator"><span>Evaluasi</span><div class="evakip-evaluator-inputs"><label>Jawaban<select class="evakip-assessment-answer" data-channel="evaluator" data-code="<?= h($sub['code']) ?>" data-weight="<?= h($sub['weight']) ?>"><?php foreach(['AA','A','BB','B','CC','C','D','E'] as $option): ?><option value="<?= $option ?>" <?= $option===($eval['jawaban']??'')?'selected':'' ?>><?= $option ?></option><?php endforeach; ?></select></label><label>Nilai<input class="evakip-assessment-score" data-channel="evaluator" data-code="<?= h($sub['code']) ?>" value="<?= number_format((float)($eval['nilai']??0),2,'.','') ?>" readonly></label></div><label>Catatan Evaluator<textarea class="evakip-assessment-text" data-channel="evaluator" data-type="catatan" data-code="<?= h($sub['code']) ?>" rows="2"><?= h((string)($eval['catatan']??'')) ?></textarea></label><label>Rekomendasi<textarea class="evakip-assessment-text" data-channel="evaluator" data-type="rekomendasi" data-code="<?= h($sub['code']) ?>" rows="2"><?= h((string)($eval['rekomendasi']??'')) ?></textarea></label></div></div><?php endif; ?>
<div class="evakip-criteria-head"><span>No</span><span>Kriteria dan Dokumen</span><span><?= $isMandiriMode?'Catatan Kriteria':'Analisis Evaluator' ?></span></div>
<?php foreach($sub['criteria'] as $criterionIndex=>$criterion): $mandiriCriterion=$mandiri['kriteria'][$criterionIndex]['catatan']??''; $evalCriterion=$eval['kriteria'][$criterionIndex]['catatan']??$criterion['note']; ?><div class="evakip-criterion"><span><?= $criterionIndex+1 ?></span><div><p><?= h($criterion['text']) ?></p><small class="evakip-required-label"><?= $isMandiriMode ? 'Dokumen yang diminta' : 'Bukti Penilaian Mandiri' ?></small><div class="evakip-evidence"><?php foreach($criterion['evidence'] as $evidence): ?><span><?= h($evidence) ?></span><?php endforeach; ?></div><div class="evakip-uploaded-docs"><?php foreach($criterionDocuments[$sub['code']][$criterionIndex]??[] as $document): ?><a href="data/uploads/evaluasi-akip/<?= h((string)$document['stored_name']) ?>" target="_blank"><i class="ph ph-file"></i><?= h((string)$document['original_name']) ?></a><?php endforeach; ?></div><?php if ($isMandiriMode&&$editable): ?><form class="evakip-evidence-upload" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="upload_evidence"><input type="hidden" name="satker_id" value="<?= $satkerId ?>"><input type="hidden" name="sub_code" value="<?= h($sub['code']) ?>"><input type="hidden" name="criterion_index" value="<?= $criterionIndex ?>"><label class="evakip-file-picker"><i class="ph ph-paperclip"></i><span>Tambah dokumen</span><input type="file" name="evidence_file" accept=".pdf,.doc,.docx" required></label><button class="evakip-upload-button">Unggah</button></form><?php endif; ?></div><textarea class="evakip-criterion-analysis" data-channel="<?= $channel ?>" data-type="criteria_note" data-code="<?= h($sub['code']) ?>" data-index="<?= $criterionIndex ?>" rows="3" <?= !$editable?'readonly':'' ?>><?= h((string)($isMandiriMode?$mandiriCriterion:$evalCriterion)) ?></textarea></div><?php endforeach; ?>
</div></details><?php endforeach; ?></section><?php endforeach; ?></div></div>
<?php endif; ?>
</div>
<div class="evakip-preview-modal" id="evakip-preview-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="evakip-preview-title">
    <div class="evakip-preview-modal__backdrop" data-preview-close></div>
    <section class="evakip-preview-modal__dialog">
        <header><div><span>BUKTI PENILAIAN MANDIRI</span><strong id="evakip-preview-title">Preview dokumen</strong></div><button type="button" class="evakip-preview-modal__close" data-preview-close aria-label="Tutup preview"><i class="ph ph-x"></i></button></header>
        <iframe id="evakip-preview-frame" title="Preview dokumen bukti"></iframe>
    </section>
</div>
<script>
(function(){const multipliers={AA:1,A:.9,BB:.8,B:.7,CC:.6,C:.5,D:.4,E:0};function save(channel,type,code,value,score,index){const data=new FormData();data.append('action','save_assessment_ajax');data.append('satker_id','<?= $satkerId ?>');data.append('channel',channel);data.append('type',type);data.append('code',code);data.append('value',value);data.append('score',score||0);data.append('criterion_index',index||0);fetch('index.php?page=evaluasi-akip&tahun=<?= $tahun ?>',{method:'POST',body:data}).then(r=>r.json()).then(result=>{if(result.status==='success'){const total=document.getElementById('evakip-total-value'),grade=document.getElementById('evakip-grade-value');if(total)total.textContent=Number(result.total_score).toFixed(2);if(grade)grade.textContent=result.grade;}});}document.querySelectorAll('.evakip-assessment-answer').forEach(el=>el.addEventListener('change',function(){const score=(Number(this.dataset.weight)*(multipliers[this.value]||0)).toFixed(2);const input=document.querySelector('.evakip-assessment-score[data-channel="'+this.dataset.channel+'"][data-code="'+this.dataset.code+'"]');if(input)input.value=score;save(this.dataset.channel,'jawaban',this.dataset.code,this.value,score,0);}));document.querySelectorAll('.evakip-assessment-text').forEach(el=>el.addEventListener('change',function(){save(this.dataset.channel,this.dataset.type,this.dataset.code,this.value,0,0);}));document.querySelectorAll('.evakip-criterion-analysis').forEach(el=>el.addEventListener('change',function(){save(this.dataset.channel,'criteria_note',this.dataset.code,this.value,0,this.dataset.index);}));document.querySelectorAll('.evakip-file-picker input').forEach(input=>input.addEventListener('change',function(){const picker=this.closest('.evakip-file-picker'),label=picker&&picker.querySelector('span');if(this.files[0]&&label){label.textContent=this.files[0].name;picker.classList.add('has-file');}}));})();
</script>
<script>
(function () {
    const modal = document.getElementById('evakip-preview-modal');
    const frame = document.getElementById('evakip-preview-frame');
    const title = document.getElementById('evakip-preview-title');
    if (!modal || !frame || !title) return;

    document.querySelectorAll('.evakip-uploaded-docs').forEach((container) => {
        if (container.children.length) return;
        const criterion = container.closest('.evakip-criterion');
        const evidence = criterion && criterion.querySelector('.evakip-evidence span');
        const documentName = evidence ? evidence.textContent.trim() : 'Dokumen bukti penilaian mandiri';
        container.innerHTML = '<article class="evakip-evidence-document evakip-evidence-document--dummy"><span class="evakip-evidence-document__icon"><i class="ph ph-file-text"></i></span><div><strong>' + documentName + '</strong><small>Dokumen contoh · siap dipreview</small></div><button type="button" class="evakip-preview-button" data-preview-url="assets/dummy-evaluasi-akip.html" data-preview-title="' + documentName.replace(/&/g, '&amp;').replace(/\"/g, '&quot;') + '"><i class="ph ph-eye"></i> Preview</button></article>';
    });

    const close = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        frame.removeAttribute('src');
    };
    document.querySelectorAll('[data-preview-close]').forEach((button) => button.addEventListener('click', close));
    document.querySelectorAll('.evakip-preview-button').forEach((button) => button.addEventListener('click', () => {
        title.textContent = button.dataset.previewTitle || 'Preview dokumen';
        frame.src = button.dataset.previewUrl;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });
}());
</script>
<?php render_footer(); ?>
