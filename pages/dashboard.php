<?php
declare(strict_types=1);

render_header('Menu APKIN RPA');

$sections = [
    'PRIMER / POKOK' => [
        ['Input Target Kinerja (TK)', 'target', null],
        ['Cetak Perjanjian Kinerja (PK)', 'pk', null],
        ['Cetak Rencana Aksi', 'renaksi', null],
        ['Cetak RKT & RKA', 'rkt_rka', null],
        ['Hitung Capaian Kinerja (HCK)', 'capaian', null],
        ['Evaluasi Kinerja (EvKin)', 'evaluasi', null],
    ],
    'SKUNDER' => [
        ['Program Kerja', 'modul', 'program-kerja'],
        ['Renstra', 'modul', 'renstra'],
        ['IKU', 'modul', 'iku'],
        ['Renaksi', 'modul', 'renaksi'],
        ['RKA-KL & Revisi', 'modul', 'rka-kl-revisi'],
        ['E-Monev Bappenas', 'modul', 'e-monev-bappenas'],
        ['Laporan Kinerja', 'modul', 'laporan-kinerja'],
        ['Manajemen Resiko', 'modul', 'manajemen-risiko'],
        ['Hibah & MoU', 'modul', 'hibah-mou'],
        ['Diagram Hasil Capaian Kinerja', 'modul', 'diagram-capaian'],
    ],
    'TERTIER' => [
        ['SOP', 'modul', 'sop'],
        ['Regulasi', 'modul', 'regulasi'],
        ['Artikel', 'modul', 'artikel'],
        ['Info & Pengumuman', 'modul', 'info-pengumuman'],
        ['LHE PA', 'modul', 'lhe-pa'],
        ['Upload TOR/KAK ABT/Baseline', 'modul', 'upload-tor-kak'],
        ['Tupoksi & Tim', 'modul', 'tupoksi-tim'],
    ],
    'KORELASI & KOORDINASI' => [
        ['Mahkamah Agung - Biro Humas', 'modul', 'biro-humas'],
        ['Mahkamah Agung - Bawas', 'modul', 'bawas'],
        ['Mahkamah Agung - Biro Perencanaan', 'modul', 'biro-perencanaan'],
        ['Badan Kepegawaian Negara', 'modul', 'bkn'],
        ['Kementerian Keuangan', 'modul', 'kemenkeu'],
        ['SIPP', 'modul', 'sipp'],
        ['E-SEMAR', 'modul', 'e-semar'],
        ['KOMDANAS', 'modul', 'komdanas'],
        ['MY ASN', 'modul', 'my-asn'],
        ['SAKTI', 'modul', 'sakti'],
        ['OMSPAN', 'modul', 'omspan'],
        ['SATUDJA', 'modul', 'satudja'],
        ['E-BIMA', 'modul', 'e-bima'],
        ['E-SADEWA', 'modul', 'e-sadewa'],
    ],
];

$user = current_user();
$profile = role_profile((string) $user['role']);
?>
<section class="panel summary-panel">
    <div>
        <h2><?= h((string) $profile['title']) ?></h2>
        <p class="muted"><?= h((string) $profile['scope']) ?></p>
    </div>
    <ul class="task-list">
        <?php foreach ($profile['checks'] as $task): ?>
            <li><?= h($task) ?></li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="job-grid">
    <article class="panel job-card">
        <h2>Data yang Dicek</h2>
        <ul class="clean-list">
            <?php foreach ($profile['checks'] as $item): ?>
                <li><?= h((string) $item) ?></li>
            <?php endforeach; ?>
        </ul>
    </article>
    <article class="panel job-card">
        <h2>Sumber Aplikasi</h2>
        <ul class="clean-list">
            <?php foreach ($profile['sources'] as $source): ?>
                <li><?= h((string) $source) ?></li>
            <?php endforeach; ?>
        </ul>
    </article>
    <article class="panel job-card">
        <h2>Output Wajib</h2>
        <ul class="clean-list">
            <?php foreach ($profile['outputs'] as $output): ?>
                <li><?= h((string) $output) ?></li>
            <?php endforeach; ?>
        </ul>
    </article>
</section>

<section class="panel analysis-rule">
    <strong>Aturan Analisis Sesuai Konsep</strong>
    <p><?= h((string) $profile['analysis_rule']) ?></p>
</section>

<section class="role-actions">
    <h2>Fitur Kerja Role Ini</h2>
    <div class="role-action-grid">
        <?php foreach ($profile['workflows'] as [$label, $targetPage, $slug, $description]): ?>
            <?php
            $url = 'index.php?page=' . urlencode($targetPage);
            if ($slug !== null) {
                $url .= '&slug=' . urlencode($slug);
            }
            ?>
            <a class="role-action-card" href="<?= h($url) ?>">
                <strong><?= h((string) $label) ?></strong>
                <span><?= h((string) $description) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="menu-grid">
    <div class="menu-intro">
        <h2>Referensi dan Modul Wajib Tampil</h2>
        <p class="muted">Bagian ini tetap tersedia sebagai referensi aplikasi, sedangkan fitur kerja utama mengikuti job role di atas.</p>
    </div>
    <?php foreach ($sections as $sectionTitle => $items): ?>
        <article class="menu-section">
            <h2><?= h($sectionTitle) ?></h2>
            <?php foreach ($items as [$label, $targetPage, $slug]): ?>
                <?php
                $url = 'index.php?page=' . urlencode($targetPage);
                if ($slug !== null) {
                    $url .= '&slug=' . urlencode($slug);
                }
                ?>
                <a href="<?= h($url) ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php render_footer(); ?>
