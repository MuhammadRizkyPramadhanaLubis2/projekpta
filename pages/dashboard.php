<?php
declare(strict_types=1);

render_header('Menu Utama APKIN RPA');

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
<style>
    /* Tambahan style khusus dashboard agar lebih rapi */
    .dashboard-hero {
        background: var(--gradient-hero); 
        color: #fff; 
        border: none; 
        padding: 40px; 
        border-radius: 24px;
        margin-bottom: 40px;
        box-shadow: 0 10px 40px rgba(16, 185, 129, 0.15);
    }
    .dashboard-hero h2 {
        font-size: 2.25rem; 
        margin: 0 0 10px; 
        color: #fff;
    }
    .dashboard-hero p {
        font-size: 1.15rem; 
        color: rgba(255,255,255,0.9); 
        margin: 0 0 24px;
    }
    .dashboard-hero-task-list {
        background: rgba(255,255,255,0.1); 
        backdrop-filter: blur(12px); 
        padding: 24px; 
        border-radius: 16px; 
        border: 1px solid rgba(255,255,255,0.2);
    }
    .dashboard-grid-3 {
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
        gap: 24px; 
        margin-bottom: 40px;
    }
    .ref-link {
        display: block;
        padding: 12px 16px; 
        background: #f8fafc; 
        border-radius: 12px; 
        color: var(--accent); 
        text-decoration: none; 
        font-weight: 500; 
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid transparent;
    }
    .ref-link:hover {
        background: #fff; 
        color: var(--primary-dark);
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
        transform: translateY(-2px);
    }
    .workflow-card {
        min-height: 120px;
    }
    .workflow-card span.workflow-title {
        font-size: 1.25rem; 
        margin-bottom: 8px; 
        display: block; 
        color: var(--primary-dark);
    }
    .workflow-card span.workflow-desc {
        font-size: 0.95rem; 
        color: var(--muted); 
        font-weight: 400;
        line-height: 1.5;
    }
</style>

<div class="dashboard-hero">
    <h2><?= h((string) $profile['title']) ?></h2>
    <p><?= h((string) $profile['scope']) ?></p>
    
    <div class="dashboard-hero-task-list">
        <strong style="display: block; margin-bottom: 12px; font-size: 1.1rem;">Kertas Kerja (Tugas Utama):</strong>
        <ul style="margin: 0; padding-left: 20px; line-height: 1.7; color: #fff;">
            <?php foreach ($profile['checks'] as $task): ?>
                <li><?= h($task) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<section style="margin-bottom: 40px;">
    <h2 style="font-size: 2rem; color: var(--accent); margin-bottom: 8px; font-weight: 800;">Fitur Kerja Role Ini</h2>
    <p class="site-lead" style="margin-bottom: 24px;">Akses cepat ke menu dan aksi utama yang menjadi tanggung jawab Anda.</p>
    <div class="site-card-grid" style="margin-top: 0;">
        <?php foreach ($profile['workflows'] as [$label, $targetPage, $slug, $description]): ?>
            <?php
            $url = 'index.php?page=' . urlencode($targetPage);
            if ($slug !== null) {
                $url .= '&slug=' . urlencode($slug);
            }
            ?>
            <a class="site-card workflow-card" href="<?= h($url) ?>">
                <span class="workflow-title"><?= h((string) $label) ?></span>
                <span class="workflow-desc"><?= h((string) $description) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<div class="dashboard-grid-3">
    <div class="content-section" style="margin-top: 0; display: flex; flex-direction: column;">
        <h3>Data yang Dicek</h3>
        <ul class="content-list" style="margin-top: 0; flex: 1;">
            <?php foreach ($profile['checks'] as $item): ?>
                <li><?= h((string) $item) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="content-section" style="margin-top: 0; display: flex; flex-direction: column;">
        <h3>Sumber Aplikasi</h3>
        <ul class="content-list" style="margin-top: 0; flex: 1;">
            <?php foreach ($profile['sources'] as $source): ?>
                <li><?= h((string) $source) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="content-section" style="margin-top: 0; display: flex; flex-direction: column;">
        <h3>Output Wajib</h3>
        <ul class="content-list" style="margin-top: 0; flex: 1;">
            <?php foreach ($profile['outputs'] as $output): ?>
                <li><?= h((string) $output) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<section class="page-panel" style="background: #fff8f1; border-color: #ffd8a8;">
    <h3 style="color: var(--gold); margin: 0 0 16px; font-size: 1.5rem; display: flex; align-items: center; gap: 8px;">
        <i class="ph-fill ph-warning-circle"></i> Aturan Analisis Sesuai Konsep
    </h3>
    <p style="font-size: 1.1rem; line-height: 1.7; margin: 0; color: #b45309;"><?= h((string) $profile['analysis_rule']) ?></p>
</section>

<section class="page-panel" style="margin-bottom: 0;">
    <div style="margin-bottom: 32px;">
        <h2 style="margin: 0 0 10px; font-size: 2rem; color: var(--accent); font-weight: 800;">Referensi dan Modul Wajib Tampil</h2>
        <p class="site-lead" style="margin: 0;">Bagian ini tetap tersedia sebagai referensi aplikasi, sedangkan fitur kerja utama mengikuti job role di atas.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 32px;">
        <?php foreach ($sections as $sectionTitle => $items): ?>
            <div>
                <h3 style="color: var(--primary-dark); margin: 0 0 20px; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
                    <?= h($sectionTitle) ?>
                </h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($items as [$label, $targetPage, $slug]): ?>
                        <?php
                        $url = 'index.php?page=' . urlencode($targetPage);
                        if ($slug !== null) {
                            $url .= '&slug=' . urlencode($slug);
                        }
                        ?>
                        <a href="<?= h($url) ?>" class="ref-link">
                            <?= h($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php render_footer(); ?>
