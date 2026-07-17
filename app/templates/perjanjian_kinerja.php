<?php
/**
 * Template resmi Perjanjian Kinerja.
 * Disusun dari "Perjanjian Kinerja 2026" dan
 * "Format dan Lampiran Perjanjian Kinerja Individu".
 *
 * Variabel: $tahun, $documentUser, $profile, $meta, $pkExtra,
 * $targets, $canViewAll, $isDocx.
 */
$bulanIndonesia = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
$tanggalDokumen = trim((string)($meta['tanggal_surat'] ?? ''));
$tanggalObj = $tanggalDokumen !== '' ? DateTime::createFromFormat('Y-m-d', $tanggalDokumen) : false;
$tanggalIndonesia = $tanggalObj
    ? ((int)$tanggalObj->format('d')) . ' ' . $bulanIndonesia[(int)$tanggalObj->format('m')] . ' ' . $tanggalObj->format('Y')
    : $tanggalDokumen;
$lokasiDokumen = trim((string)($meta['lokasi'] ?? 'Medan')) ?: 'Medan';
$unitDokumen = trim((string)($documentUser['unit'] ?? 'Pengadilan Tinggi Agama Medan'));
if (strcasecmp($unitDokumen, 'PTA Medan') === 0) $unitDokumen = 'Pengadilan Tinggi Agama Medan';

$groupedTargets = [];
foreach ($targets as $target) {
    $sasaranKey = trim((string)($target['sasaran'] ?? '')) ?: 'Sasaran kinerja belum diisi';
    $groupedTargets[$sasaranKey][] = $target;
}
$formatAngkaTarget = static function (mixed $value): string {
    $number = num($value);
    return fmod($number, 1.0) === 0.0
        ? number_format($number, 0, ',', '.')
        : rtrim(rtrim(number_format($number, 2, ',', '.'), '0'), ',');
};
$formatTarget = static function (array $target) use ($formatAngkaTarget): string {
    $value = $formatAngkaTarget($target['target'] ?? 0);
    $satuan = trim((string)($target['satuan'] ?? ''));
    if (in_array(strtolower($satuan), ['persen','%'], true)) return $value . '%';
    return trim($value . ($satuan !== '' ? ' ' . $satuan : ''));
};

$budgetRows = [];
foreach ($targets as $target) {
    $sasaran = trim((string)($target['sasaran'] ?? '')) ?: 'Kegiatan belum diisi';
    $amount = num($target['dipa01'] ?? 0) + num($target['dipa04'] ?? 0);
    if ($amount > 0) $budgetRows[$sasaran] = ($budgetRows[$sasaran] ?? 0) + $amount;
}
$hasBudget = count($budgetRows) > 0;
$signatureImage = static function (string $data): string {
    return $data !== '' ? get_signature_img_tag($data, 155, 75) : '<span class="pk-signature-space"></span>';
};
?>
<style>
.pk-official-wrap{margin-top:18px}.pk-preview-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:0 0 12px;padding:13px 15px;border:1px solid #bfdbfe;border-radius:12px;background:#eff6ff;color:#1e40af}.pk-preview-heading strong,.pk-preview-heading span{display:block}.pk-preview-heading span{margin-top:3px;font-size:.82rem}.pk-official{--pk-red:#b00000;color:#111}.pk-page{width:210mm;min-height:297mm;margin:0 auto 22px;padding:16mm 18mm 14mm;background:#fff;box-shadow:0 15px 40px rgba(15,23,42,.12);font-family:"Times New Roman",Times,serif;font-size:12pt;line-height:1.28}.pk-cover-title{text-align:center;font-weight:700;font-size:16pt;line-height:1.22;text-transform:uppercase}.pk-logo{display:block;width:27mm;height:27mm;object-fit:contain;margin:8mm auto 5mm}.pk-document-title{text-align:center;font-weight:700;font-size:15pt;margin:0 0 6mm;text-transform:uppercase}.pk-opening{margin:0 0 3mm;text-align:justify}.pk-party-table{width:100%;border:0;border-collapse:collapse;margin:0 0 3mm}.pk-party-table td{border:0;padding:.8mm 0;vertical-align:top}.pk-party-table td:first-child{width:25mm}.pk-party-table td:nth-child(2){width:6mm;text-align:center}.pk-statement{margin:2.5mm 0;text-align:justify}.pk-statement strong{font-weight:700}.pk-sign-grid{display:grid;grid-template-columns:1fr 1fr;gap:18mm;margin-top:5mm;break-inside:avoid}.pk-sign-box{text-align:left}.pk-sign-box.right{text-align:left}.pk-sign-box .pk-sign-title{font-weight:700;margin-bottom:1.5mm}.pk-sign-box .pk-sign-role{min-height:8mm}.pk-signature-space{display:block;height:20mm}.pk-sign-box img{margin:2mm 0!important;max-width:42mm!important;max-height:22mm!important}.pk-sign-name{font-weight:400}.pk-sign-nip{display:block;margin-top:1mm}.pk-annex-title{text-align:center;font-size:14pt;font-weight:700;line-height:1.25;text-transform:uppercase;margin:0 0 8mm}.pk-target-table,.pk-budget-table{width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:9.8pt;line-height:1.35}.pk-target-table th,.pk-target-table td,.pk-budget-table th,.pk-budget-table td{border:1px solid #111;padding:2.2mm;vertical-align:top}.pk-target-table th{background:var(--pk-red);color:#fff;text-align:center;font-weight:700}.pk-target-table .pk-no{width:9mm;text-align:center}.pk-target-table .pk-sasaran{width:36%}.pk-target-table .pk-indikator{width:49%}.pk-target-table .pk-target{width:14%;text-align:center}.pk-target-table tbody tr:nth-child(even) td{background:#f2f2f2}.pk-annex-sign{margin-top:10mm}.pk-budget-table th{background:var(--pk-red);color:#fff;text-align:center}.pk-budget-table td:first-child{width:10mm;text-align:center}.pk-budget-table td:last-child{width:45mm;text-align:right;white-space:nowrap}.pk-budget-total{font-weight:700}.pk-empty{padding:12mm!important;text-align:center;color:#64748b}.pk-source-note{margin-top:5mm;color:#64748b;font:8.5pt Arial,sans-serif;text-align:right}
@media(max-width:980px){.pk-page{width:100%;min-height:auto;padding:28px 22px}.pk-official-wrap{overflow-x:auto}.pk-sign-grid{gap:28px}.pk-target-table,.pk-budget-table{font-size:9pt}}
@media print{.pk-preview-heading{display:none!important}.pk-official-wrap{margin:0;overflow:visible}.pk-page{width:auto;min-height:0;margin:0;padding:0;box-shadow:none}.pk-page:not(:last-child){break-after:page;page-break-after:always}.pk-target-table thead,.pk-budget-table thead{display:table-header-group}.pk-target-table tr,.pk-budget-table tr,.pk-sign-grid{break-inside:avoid}.pk-source-note{display:none}@page{size:A4 portrait;margin:16mm 17mm}}
</style>

<?php if (!$isDocx): ?>
<div class="pk-preview-heading" id="pk-preview-start"><div><strong>Pratinjau Format Resmi Perjanjian Kinerja</strong><span>Pernyataan, lampiran target, dan lampiran anggaran dibuat dari data yang sudah tersimpan.</span></div><span><?= count($targets) ?> indikator · <?= $hasBudget ? 'anggaran tersedia' : 'tanpa lampiran anggaran' ?></span></div>
<?php endif; ?>

<div class="pk-official-wrap"><div class="pk-official">
<section class="pk-page pk-statement-page">
    <div class="pk-cover-title">Pernyataan Perjanjian Kinerja<br><?= h($unitDokumen) ?></div>
    <img class="pk-logo" src="assets/logo_pta.png" alt="Logo <?= h($unitDokumen) ?>">
    <h2 class="pk-document-title">Perjanjian Kinerja Tahun <?= h((string)$tahun) ?></h2>
    <p class="pk-opening">Dalam rangka mewujudkan manajemen pemerintahan yang efektif, transparan dan akuntabel serta berorientasi pada hasil, kami yang bertanda tangan di bawah ini:</p>
    <table class="pk-party-table"><tr><td>Nama</td><td>:</td><td><strong><?= h((string)$meta['pihak1_nama']) ?></strong></td></tr><tr><td>Jabatan</td><td>:</td><td><?= h((string)$meta['pihak1_jabatan']) ?></td></tr></table>
    <p class="pk-opening">Selanjutnya disebut <strong>Pihak Pertama.</strong></p>
    <table class="pk-party-table"><tr><td>Nama</td><td>:</td><td><strong><?= h((string)$meta['pihak2_nama']) ?></strong></td></tr><tr><td>Jabatan</td><td>:</td><td><?= h((string)$meta['pihak2_jabatan']) ?></td></tr></table>
    <p class="pk-opening">Selaku atasan langsung pihak pertama, selanjutnya disebut <strong>Pihak Kedua.</strong></p>
    <p class="pk-statement"><strong>Pihak pertama</strong> berjanji akan mewujudkan target kinerja yang seharusnya sesuai lampiran perjanjian ini, dalam rangka mencapai target kinerja jangka menengah seperti yang telah ditetapkan dalam dokumen perencanaan. Keberhasilan dan kegagalan pencapaian target kinerja tersebut menjadi tanggung jawab kami.</p>
    <p class="pk-statement"><strong>Pihak kedua</strong> akan melakukan supervisi yang diperlukan serta akan melakukan evaluasi terhadap capaian kinerja dari perjanjian ini dan mengambil tindakan yang diperlukan dalam rangka pemberian penghargaan dan sanksi.</p>
    <div class="pk-sign-grid">
        <div class="pk-sign-box"><div class="pk-sign-title">Pihak Kedua</div><div class="pk-sign-role"><?= h((string)$meta['pihak2_jabatan']) ?></div><?= $signatureImage((string)($meta['pihak2_ttd'] ?? '')) ?><div class="pk-sign-name"><?= h((string)$meta['pihak2_nama']) ?></div><?php if (($pkExtra['pihak2_nip'] ?? '') !== ''): ?><span class="pk-sign-nip">NIP. <?= h((string)$pkExtra['pihak2_nip']) ?></span><?php endif; ?></div>
        <div class="pk-sign-box right"><div><?= h($lokasiDokumen) ?>, <?= h($tanggalIndonesia) ?></div><div class="pk-sign-title">Pihak Pertama</div><div class="pk-sign-role"><?= h((string)$meta['pihak1_jabatan']) ?></div><?= $signatureImage((string)($meta['pihak1_ttd'] ?? '')) ?><div class="pk-sign-name"><?= h((string)$meta['pihak1_nama']) ?></div><?php if (($pkExtra['pihak1_nip'] ?? '') !== ''): ?><span class="pk-sign-nip">NIP. <?= h((string)$pkExtra['pihak1_nip']) ?></span><?php endif; ?></div>
    </div>
</section>

<section class="pk-page pk-page-break pk-target-page">
    <h2 class="pk-annex-title">Perjanjian Kinerja Tahun <?= h((string)$tahun) ?><br><?= h($unitDokumen) ?></h2>
    <table class="pk-target-table"><thead><tr><th class="pk-no">No.</th><th class="pk-sasaran">Sasaran Kegiatan</th><th class="pk-indikator">Indikator Kinerja</th><th class="pk-target">Target</th></tr></thead><tbody>
    <?php if (!$groupedTargets): ?><tr><td class="pk-empty" colspan="4">Belum ada data Target Kinerja untuk tahun <?= h((string)$tahun) ?>.</td></tr><?php endif; ?>
    <?php $sasaranNo=1; foreach ($groupedTargets as $sasaran => $items): foreach ($items as $indicatorIndex => $target): ?>
        <tr><?php if ($indicatorIndex===0): ?><td class="pk-no" rowspan="<?= count($items) ?>"><?= $sasaranNo ?>.</td><td rowspan="<?= count($items) ?>"><?= h($sasaran) ?></td><?php endif; ?><td><?= h($sasaranNo . '.' . ($indicatorIndex+1) . '  ' . (string)$target['indikator']) ?></td><td class="pk-target"><?= h($formatTarget($target)) ?></td></tr>
    <?php endforeach; $sasaranNo++; endforeach; ?>
    </tbody></table>
    <?php if (!$hasBudget): ?><div class="pk-sign-grid pk-annex-sign"><div class="pk-sign-box"><div class="pk-sign-title">Pihak Kedua</div><div class="pk-sign-role"><?= h((string)$meta['pihak2_jabatan']) ?></div><?= $signatureImage((string)($meta['pihak2_ttd'] ?? '')) ?><div><?= h((string)$meta['pihak2_nama']) ?></div><?php if (($pkExtra['pihak2_nip'] ?? '') !== ''): ?><span>NIP. <?= h((string)$pkExtra['pihak2_nip']) ?></span><?php endif; ?></div><div class="pk-sign-box"><div><?= h($lokasiDokumen) ?>, <?= h($tanggalIndonesia) ?></div><div class="pk-sign-title">Pihak Pertama</div><div class="pk-sign-role"><?= h((string)$meta['pihak1_jabatan']) ?></div><?= $signatureImage((string)($meta['pihak1_ttd'] ?? '')) ?><div><?= h((string)$meta['pihak1_nama']) ?></div><?php if (($pkExtra['pihak1_nip'] ?? '') !== ''): ?><span>NIP. <?= h((string)$pkExtra['pihak1_nip']) ?></span><?php endif; ?></div></div><?php endif; ?>
</section>

<?php if ($hasBudget): ?>
<section class="pk-page pk-page-break pk-budget-page">
    <h2 class="pk-annex-title">Lampiran Anggaran Perjanjian Kinerja Tahun <?= h((string)$tahun) ?><br><?= h($unitDokumen) ?></h2>
    <table class="pk-budget-table"><thead><tr><th>No.</th><th>Kegiatan</th><th>Anggaran</th></tr></thead><tbody><?php $budgetNo=1;$budgetTotal=0.0;foreach($budgetRows as $activity=>$amount):$budgetTotal+=$amount;?><tr><td><?= $budgetNo++ ?>.</td><td><?= h($activity) ?></td><td>Rp <?= h(number_format($amount,0,',','.')) ?>,-</td></tr><?php endforeach; ?><tr class="pk-budget-total"><td colspan="2">Total Anggaran</td><td>Rp <?= h(number_format($budgetTotal,0,',','.')) ?>,-</td></tr></tbody></table>
    <div class="pk-sign-grid pk-annex-sign"><div class="pk-sign-box"><div class="pk-sign-title">Pihak Kedua</div><div class="pk-sign-role"><?= h((string)$meta['pihak2_jabatan']) ?></div><?= $signatureImage((string)($meta['pihak2_ttd'] ?? '')) ?><div><?= h((string)$meta['pihak2_nama']) ?></div><?php if (($pkExtra['pihak2_nip'] ?? '') !== ''): ?><span>NIP. <?= h((string)$pkExtra['pihak2_nip']) ?></span><?php endif; ?></div><div class="pk-sign-box"><div><?= h($lokasiDokumen) ?>, <?= h($tanggalIndonesia) ?></div><div class="pk-sign-title">Pihak Pertama</div><div class="pk-sign-role"><?= h((string)$meta['pihak1_jabatan']) ?></div><?= $signatureImage((string)($meta['pihak1_ttd'] ?? '')) ?><div><?= h((string)$meta['pihak1_nama']) ?></div><?php if (($pkExtra['pihak1_nip'] ?? '') !== ''): ?><span>NIP. <?= h((string)$pkExtra['pihak1_nip']) ?></span><?php endif; ?></div></div>
</section>
<?php endif; ?>
</div></div>
