<?php
declare(strict_types=1);

render_header('Menu Aplikasi IKPA');

$sections = [
    'PRIMER / POKOK' => [
        ['Input Target Kinerja (TK)', 'target'],
        ['Cetak Perjanjian Kinerja (PK)', 'info'],
        ['Cetak Rencana Aksi', 'info'],
        ['Cetak RKT & RKA', 'info'],
        ['Hitung Capaian Kinerja (HCK)', 'capaian'],
        ['Evaluasi Kinerja (EvKin)', 'evaluasi'],
    ],
    'SKUNDER' => [
        ['Program Kerja', 'info'],
        ['Renstra', 'info'],
        ['IKU', 'info'],
        ['Renaksi', 'info'],
        ['RKA-KL & Revisi', 'info'],
        ['E-Monev Bappenas', 'info'],
        ['Laporan Kinerja', 'info'],
        ['Manajemen Resiko', 'info'],
        ['Hibah & MoU', 'info'],
        ['Diagram Hasil Capaian Kinerja', 'info'],
    ],
    'TERTIER' => [
        ['SOP', 'info'],
        ['Regulasi', 'info'],
        ['Artikel', 'info'],
        ['Info & Pengumuman', 'info'],
        ['LHE PA', 'info'],
        ['Upload TOR/KAK ABT/Baseline', 'info'],
        ['Tupoksi & Tim', 'info'],
    ],
    'KORELASI & KOORDINASI' => [
        ['Mahkamah Agung - Biro Humas', 'info'],
        ['Mahkamah Agung - Bawas', 'info'],
        ['Mahkamah Agung - Biro Perencanaan', 'info'],
        ['Badan Kepegawaian Negara', 'info'],
        ['Kementerian Keuangan', 'info'],
        ['SIPP', 'info'],
        ['E-SEMAR', 'info'],
        ['KOMDANAS', 'info'],
        ['MY ASN', 'info'],
        ['SAKTI', 'info'],
        ['OMSPAN', 'info'],
        ['SATUDJA', 'info'],
        ['E-BIMA', 'info'],
        ['E-SADEWA', 'info'],
    ],
];

$primaryLinks = [
    'Cetak Perjanjian Kinerja (PK)' => 'pk',
    'Cetak Rencana Aksi' => 'renaksi',
    'Cetak RKT & RKA' => 'rkt_rka',
];

$user = current_user();
$tasks = role_tasks((string) $user['role']);
?>
<section class="panel summary-panel">
    <div>
        <h2>Pengguna dan Tanggung Jawab</h2>
        <p class="muted">Konsep PDF membagi pengguna menjadi PTA Medan dan Satker PA, dengan analisis capaian sesuai role masing-masing.</p>
    </div>
    <ul class="task-list">
        <?php foreach ($tasks as $task): ?>
            <li><?= h($task) ?></li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="menu-grid">
    <?php foreach ($sections as $sectionTitle => $items): ?>
        <article class="menu-section">
            <h2><?= h($sectionTitle) ?></h2>
            <?php foreach ($items as [$label, $targetPage]): ?>
                <?php $page = $primaryLinks[$label] ?? $targetPage; ?>
                <a href="index.php?page=<?= h($page) ?>&title=<?= urlencode($label) ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php render_footer(); ?>
