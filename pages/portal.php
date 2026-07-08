<?php
declare(strict_types=1);

$slug = trim((string) ($_GET['slug'] ?? 'beranda'));
$pageData = site_page($slug);

if (!$pageData) {
    http_response_code(404);
    $pageData = [
        'title' => 'Halaman tidak ditemukan',
        'subtitle' => 'Konten belum tersedia',
        'body' => ['Halaman portal yang diminta belum tersedia.'],
    ];
}

render_header((string) $pageData['title']);
?>
<?php if ($slug === 'program-kerja-sop'): ?>
    <?php
    define('PROGRAM_KERJA_SOP_EMBEDDED', true);
    require __DIR__ . '/program-kerja-sop.php';
    render_footer();
    return;
    ?>
<?php endif; ?>
<?php if ($slug === 'penyusunan-anggaran'): ?>
    <?php
    define('PENYUSUNAN_ANGGARAN_EMBEDDED', true);
    require __DIR__ . '/penyusunan-anggaran.php';
    render_footer();
    return;
    ?>
<?php endif; ?>
<?php if ($slug === 'notifikasi'): ?>
    <?php
    define('IFKIN_EMBEDDED', true);
    require __DIR__ . '/ifkin.php';
    render_footer();
    return;
    ?>
<?php endif; ?>
<?php
$heroPages = ['tugas-dan-fungsi', 'revisi', 'hibah', 'e-monev-bappenas', 'manajemen-risiko', 'pojok-baca', 'baseline', 'pagu-indikatif', 'pagu-definitif', 'abt', 'evaluasi-akip'];
?>
<?php if (in_array($slug, $heroPages, true)): ?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&display=swap');

.tf-hero {
    position: relative;
    overflow: hidden;
    background-image:
        linear-gradient(90deg, rgba(2, 21, 14, 0.96), rgba(15, 51, 36, 0.72), rgba(2, 21, 14, 0.90)),
        url('assets/gedung1.webp');
    background-size: cover;
    background-position: center;
    padding: 120px 20px 160px;
    text-align: center;
    color: #fff;
}
.tf-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse at 50% 42%, rgba(16, 185, 129, 0.22) 0%, rgba(16, 185, 129, 0.08) 28%, transparent 58%),
        radial-gradient(ellipse at center, transparent 0%, transparent 42%, rgba(1, 18, 12, 0.54) 100%);
    pointer-events: none;
    z-index: 1;
}
.tf-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, transparent 68%, #eef3ef 100%),
        url('assets/batik_sumut.png') center/320px;
    opacity: 0.16;
    mix-blend-mode: soft-light;
    pointer-events: none;
    z-index: 2;
}
.tf-hero > * {
    position: relative;
    z-index: 3;
}
.tf-hero-subtitle {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.25em;
    color: #a7f3d0;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    font-weight: 600;
}
.tf-hero h1 {
    font-family: 'Merriweather', serif;
    font-size: clamp(2.5rem, 5vw, 4.5rem);
    font-weight: 700;
    margin: 0 0 24px;
    text-shadow: 0 4px 20px rgba(0,0,0,0.4);
    letter-spacing: -0.01em;
    text-transform: uppercase;
}
.tf-hero p {
    font-size: 1.15rem;
    max-width: 680px;
    margin: 0 auto;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.7;
    font-weight: 300;
}
.tf-container {
    max-width: 1000px;
    margin: -100px auto 80px;
    position: relative;
    z-index: 10;
    padding: 0 24px;
}
.tf-card {
    background: #fff;
    border-radius: 28px;
    padding: 48px;
    box-shadow: 0 24px 50px rgba(6, 78, 59, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.1);
}
.tf-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 24px;
    margin-bottom: 32px;
}
.tf-header h2 {
    font-family: 'Merriweather', serif;
    font-size: 1.75rem;
    color: #064e3b;
    margin: 0;
    font-weight: 700;
}
.tf-header .badge {
    background: #ecfdf5;
    color: #059669;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.05em;
}
.tf-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 16px;
}
.tf-list li {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 24px;
    border: 1px solid #f1f5f9;
    border-radius: 20px;
    background: #fafaf9;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.tf-list li:hover {
    background: #fff;
    border-color: #10b981;
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.1);
    transform: translateY(-3px);
}
.tf-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: #d1fae5;
    color: #047857;
    font-weight: 800;
    border-radius: 12px;
    font-size: 0.95rem;
    flex-shrink: 0;
}
.tf-content {
    color: #334155;
    font-size: 1.1rem;
    line-height: 1.6;
    margin-top: 5px;
    font-weight: 500;
}
.tf-cta {
    background: linear-gradient(135deg, #064e3b, #047857);
    border-radius: 20px;
    padding: 32px 40px;
    margin-top: 48px;
    margin-bottom: 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    box-shadow: 0 12px 30px rgba(4, 120, 87, 0.2);
    color: #fff;
    flex-wrap: wrap;
}
.tf-cta-content {
    display: flex;
    align-items: center;
    gap: 24px;
    flex: 1 1 300px;
}
.tf-cta-icon {
    width: 64px;
    height: 64px;
    background: rgba(255,255,255,0.15);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.tf-cta-btn {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #fff;
    color: #064e3b;
    padding: 16px 32px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.05rem;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    white-space: nowrap;
}
.tf-cta-btn:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 25px rgba(0,0,0,0.2);
}
@media (max-width: 768px) {
    .tf-cta {
        flex-direction: column;
        align-items: stretch;
        padding: 24px;
        text-align: center;
    }
    .tf-cta-content {
        flex-direction: column;
        text-align: center;
    }
    .tf-cta-btn {
        width: 100%;
        box-sizing: border-box;
    }
}
</style>

<div class="tf-hero">
    <div class="tf-hero-subtitle">
        <?= in_array($slug, ['revisi', 'hibah', 'e-monev-bappenas', 'manajemen-risiko', 'baseline', 'pagu-indikatif', 'pagu-definitif', 'abt', 'evaluasi-akip']) ? 'Informasi Kinerja Program Dan Anggaran' : 'Sub Bagian Rencana Program & Anggaran' ?>
    </div>
    <h1><?= h((string) $pageData['title']) ?></h1>
    <?php if (!empty($pageData['body'])): ?>
        <p><?= h((string) $pageData['body'][0]) ?></p>
    <?php endif; ?>
</div>

<div class="tf-container">
    <div class="tf-card">
        <?php if (!empty($pageData['body']) && count($pageData['body']) > 1): ?>
            <div style="margin-bottom: 48px; color: #334155; line-height: 1.8; font-size: 1.1rem; text-align: justify; max-width: 850px; margin-left: auto; margin-right: auto; padding: 0 20px;">
                <?php 
                    for ($i = 1; $i < count($pageData['body']); $i++) {
                        echo '<p style="margin-top: 0; margin-bottom: 24px;">' . h((string) $pageData['body'][$i]) . '</p>';
                    }
                ?>
            </div>
            <hr style="border: 0; height: 1px; background: #e2e8f0; margin-bottom: 48px;">
        <?php endif; ?>
        
        <?php if ($slug === 'revisi'): ?>
        <!-- Google Form Embed for Revisi -->
        <div style="background: #fff; border-radius: 12px; margin-bottom: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <iframe src="https://docs.google.com/forms/d/e/1FAIpQLSfizQBrP99UN1U4sRDhgxkrbvcyvAI_MkJUH987_TgaXim5Zw/viewform?embedded=true" width="100%" height="650" frameborder="0" marginheight="0" marginwidth="0">Memuat…</iframe>
        </div>
        <div style="text-align: right; margin-bottom: 48px;">
            <a href="https://docs.google.com/forms/d/e/1FAIpQLSfizQBrP99UN1U4sRDhgxkrbvcyvAI_MkJUH987_TgaXim5Zw/viewform" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; background: #064e3b; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: background 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Buka di Tab Baru
            </a>
        </div>
        <?php elseif ($slug === 'hibah'): ?>
        <!-- Google Form Embed for Hibah -->
        <div style="background: #fff; border-radius: 12px; margin-bottom: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <iframe src="https://docs.google.com/forms/d/e/1FAIpQLSf5t-JFsQTG3wSAHChrjfvNOXXMC30cxLR4rU-tRVe4UfpDqg/viewform?embedded=true" width="100%" height="650" frameborder="0" marginheight="0" marginwidth="0">Memuat…</iframe>
        </div>
        <div style="text-align: right; margin-bottom: 48px;">
            <a href="https://docs.google.com/forms/d/e/1FAIpQLSf5t-JFsQTG3wSAHChrjfvNOXXMC30cxLR4rU-tRVe4UfpDqg/viewform" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; background: #064e3b; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: background 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Buka di Tab Baru
            </a>
        </div>
        <?php elseif ($slug === 'baseline'): ?>
        <!-- Google Form Embed for Baseline -->
        <div style="background: #fff; border-radius: 12px; margin-bottom: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <iframe src="https://docs.google.com/forms/d/e/1FAIpQLScqU1NGkTpqJBHEQnBC1dMI1CcGOA23C4hv6LBqqBEwwaskRw/viewform?embedded=true" width="100%" height="650" frameborder="0" marginheight="0" marginwidth="0">Memuat…</iframe>
        </div>
        <div style="text-align: right; margin-bottom: 48px;">
            <a href="https://docs.google.com/forms/d/e/1FAIpQLScqU1NGkTpqJBHEQnBC1dMI1CcGOA23C4hv6LBqqBEwwaskRw/viewform" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; background: #064e3b; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: background 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Buka di Tab Baru
            </a>
        </div>
        <?php elseif ($slug === 'pagu-indikatif'): ?>
        <!-- Google Form Embed for Pagu Indikatif -->
        <div style="background: #fff; border-radius: 12px; margin-bottom: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <iframe src="https://docs.google.com/forms/d/e/1FAIpQLSfbdAbfdhx6HxLmIYpTa5SSgNe72Wbz-VExK2RcTsjjEbBL1A/viewform?embedded=true" width="100%" height="650" frameborder="0" marginheight="0" marginwidth="0">Memuat…</iframe>
        </div>
        <div style="text-align: right; margin-bottom: 48px;">
            <a href="https://docs.google.com/forms/d/e/1FAIpQLSfbdAbfdhx6HxLmIYpTa5SSgNe72Wbz-VExK2RcTsjjEbBL1A/viewform" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; background: #064e3b; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: background 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Buka di Tab Baru
            </a>
        </div>
        <?php elseif ($slug === 'pagu-definitif'): ?>
        <!-- Google Form Embed for Pagu Definitif -->
        <div style="background: #fff; border-radius: 12px; margin-bottom: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <iframe src="https://docs.google.com/forms/d/e/1FAIpQLSdBxLJ45DAJoJ_NYf-gKmaIJ1RYcrh1H0mK1zj5t5bZTFtM0Q/viewform?embedded=true" width="100%" height="650" frameborder="0" marginheight="0" marginwidth="0">Memuat…</iframe>
        </div>
        <div style="text-align: right; margin-bottom: 48px;">
            <a href="https://docs.google.com/forms/d/e/1FAIpQLSdBxLJ45DAJoJ_NYf-gKmaIJ1RYcrh1H0mK1zj5t5bZTFtM0Q/viewform" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; background: #064e3b; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: background 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Buka di Tab Baru
            </a>
        </div>
        <?php elseif ($slug === 'abt'): ?>
        <!-- Google Form Embed for ABT -->
        <div style="background: #fff; border-radius: 12px; margin-bottom: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <iframe src="https://docs.google.com/forms/d/e/1FAIpQLSek6TE9QyCYqQy_SEoGig04dcSr8MKTvu7w0LcSk-4OdHImyg/viewform?embedded=true" width="100%" height="650" frameborder="0" marginheight="0" marginwidth="0">Memuat…</iframe>
        </div>
        <div style="text-align: right; margin-bottom: 48px;">
            <a href="https://docs.google.com/forms/d/e/1FAIpQLSek6TE9QyCYqQy_SEoGig04dcSr8MKTvu7w0LcSk-4OdHImyg/viewform" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; background: #064e3b; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: background 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Buka di Tab Baru
            </a>
        </div>
        <?php endif; ?>

        <?php if (!empty($pageData['list'])): ?>
            <div class="tf-header">
                <?php 
                    $headerTitle = 'Daftar Tugas dan Fungsi';
                    if (in_array($slug, ['revisi', 'hibah', 'e-monev-bappenas', 'manajemen-risiko', 'pojok-baca', 'baseline', 'pagu-indikatif', 'pagu-definitif', 'abt', 'evaluasi-akip'])) {
                        $headerTitle = h((string) $pageData['subtitle']);
                    }
                    
                    $badgeText = 'TUGAS';
                    if (in_array($slug, ['revisi', 'hibah', 'e-monev-bappenas', 'manajemen-risiko', 'pojok-baca', 'baseline', 'pagu-indikatif', 'pagu-definitif', 'abt', 'evaluasi-akip'])) {
                        $badgeText = 'REGULASI';
                    }
                ?>
                <h2><?= $headerTitle ?></h2>
                <span class="badge"><?= count($pageData['list']) ?> <?= $badgeText ?></span>
            </div>
            
            <ul class="tf-list">
                <?php foreach ($pageData['list'] as $index => $item): ?>
                    <li>
                        <div class="tf-number"><?= sprintf('%02d', $index + 1) ?></div>
                        <div class="tf-content"><?= h((string) $item) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <?php foreach (($pageData['sections'] ?? []) as $section): ?>
            <?php if (empty($section['iframe']) && empty($section['items']) && !empty($section['url'])): ?>
                <!-- Render CTA Box and skip tf-header -->
                <div class="tf-cta">
                    <div class="tf-cta-content">
                        <?php if (!empty($section['custom_icon'])): ?>
                            <div class="tf-cta-icon" style="background: #fff; width: 80px; height: 80px; border-radius: 20px; box-sizing: border-box; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                <?= $section['custom_icon'] ?>
                            </div>
                        <?php elseif (!empty($section['icon']) && $slug === 'e-monev-bappenas'): ?>
                            <div class="tf-cta-icon" style="background: #fff; width: 80px; height: 80px; border-radius: 20px; padding: 5px; box-sizing: border-box;">
                                <img src="assets/logo_emonev.png" alt="e-Monev Bappenas" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                        <?php elseif (!empty($section['icon'])): ?>
                            <div class="tf-cta-icon">
                                <i class="ph-duotone <?= h($section['icon']) ?>" style="font-size: 36px; color: #fff;"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h3 style="margin: 0 0 8px 0; font-size: 1.6rem; color: #fff; font-family: 'Merriweather', serif; font-weight: 700; letter-spacing: -0.02em;"><?= h((string) $section['title']) ?></h3>
                            <?php if (!empty($section['description'])): ?>
                                <p style="margin: 0; color: #a7f3d0; font-size: 1.05rem; line-height: 1.6; max-width: 550px;"><?= h((string) $section['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?= h($section['url']) ?>" target="_blank" class="tf-cta-btn">
                        Kunjungi Portal <i class="ph-bold ph-arrow-square-out" style="font-size: 20px;"></i>
                    </a>
                </div>
                <?php continue; ?>
            <?php endif; ?>

            <div class="tf-header" style="margin-top: 48px;">
                <h2><?= h((string) $section['title']) ?></h2>
            </div>
            <?php if (!empty($section['iframe'])): ?>
                <?php $iHeight = !empty($section['iframeHeight']) ? h($section['iframeHeight']) : '450px'; ?>
                <div style="width: 100%; height: <?= $iHeight ?>; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 8px 16px rgba(0,0,0,0.05); margin-bottom: 20px;">
                    <iframe src="<?= h($section['iframe']) ?>" width="100%" height="100%" style="border: none;" allow="autoplay"></iframe>
                </div>
                <?php if (!empty($section['url'])): ?>
                    <div style="text-align: right; margin-bottom: 32px;">
                        <a href="<?= h($section['url']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; background: #064e3b; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: background 0.2s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            Buka di Tab Baru
                        </a>
                    </div>
                <?php endif; ?>
            <?php elseif (!empty($section['items'])): ?>
                <ul class="tf-list">
                    <?php foreach ($section['items'] as $index => $item): ?>
                        <li>
                            <div class="tf-number" style="background: #f1f5f9; color: #64748b;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            </div>
                            <div class="tf-content"><?= h((string) $item) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<?php else: ?>
<section class="site-section">
    <div class="page-panel">
        <h2 style="font-size: 2.5rem; margin-bottom: 8px;"><?= h((string) $pageData['title']) ?></h2>
    <?php if (!empty($pageData['subtitle'])): ?>
        <p class="site-lead"><?= h((string) $pageData['subtitle']) ?></p>
    <?php endif; ?>
    <?php if (!empty($pageData['lead'])): ?>
        <p class="site-lead"><?= h((string) $pageData['lead']) ?></p>
    <?php endif; ?>

    <?php foreach (($pageData['body'] ?? []) as $paragraph): ?>
        <p><?= h((string) $paragraph) ?></p>
    <?php endforeach; ?>

    <?php if (!empty($pageData['cards'])): ?>
        <?php
        $iconMap = [
            'program-kerja-sop' => ['icon' => 'ph-chart-pie-slice', 'bg' => 'linear-gradient(135deg, #0ea5e9, #2563eb)', 'shadow' => 'rgba(14, 165, 233, 0.4)'],
            'baseline' => ['icon' => 'ph-hand-coins', 'bg' => 'linear-gradient(135deg, #10b981, #059669)', 'shadow' => 'rgba(16, 185, 129, 0.4)'],
            'pagu-indikatif' => ['icon' => 'ph-buildings', 'bg' => 'linear-gradient(135deg, #f59e0b, #d97706)', 'shadow' => 'rgba(245, 158, 11, 0.4)'],
            'pagu-definitif' => ['icon' => 'ph-bank', 'bg' => 'linear-gradient(135deg, #ef4444, #dc2626)', 'shadow' => 'rgba(239, 68, 68, 0.4)'],
            'revisi' => ['icon' => 'ph-arrows-clockwise', 'bg' => 'linear-gradient(135deg, #8b5cf6, #6d28d9)', 'shadow' => 'rgba(139, 92, 246, 0.4)'],
            'sakip' => ['icon' => 'ph-gear-six', 'bg' => 'linear-gradient(135deg, #14b8a6, #0f766e)', 'shadow' => 'rgba(20, 184, 166, 0.4)'],
            'evaluasi-akip' => ['icon' => 'ph-shield-check', 'bg' => 'linear-gradient(135deg, #f43f5e, #e11d48)', 'shadow' => 'rgba(244, 63, 94, 0.4)'],
            'e-monev-bappenas' => ['custom' => true],
            'abt' => ['icon' => 'ph-list-plus', 'bg' => 'linear-gradient(135deg, #3b82f6, #1d4ed8)', 'shadow' => 'rgba(59, 130, 246, 0.4)'],
            'hibah' => ['icon' => 'ph-handshake', 'bg' => 'linear-gradient(135deg, #ec4899, #be185d)', 'shadow' => 'rgba(236, 72, 153, 0.4)'],
            'manajemen-risiko' => ['icon' => 'ph-warning-octagon', 'bg' => 'linear-gradient(135deg, #f97316, #ea580c)', 'shadow' => 'rgba(249, 115, 22, 0.4)'],
            'pojok-baca' => ['icon' => 'ph-book-open-text', 'bg' => 'linear-gradient(135deg, #64748b, #475569)', 'shadow' => 'rgba(100, 116, 139, 0.4)'],
        ];
        ?>
        <div class="site-card-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 24px; padding: 16px 0;">
            <?php foreach ($pageData['cards'] as [$label, $cardSlug]): ?>
                <?php $mapping = $iconMap[$cardSlug] ?? ['icon' => 'ph-folder', 'bg' => 'linear-gradient(135deg, #94a3b8, #64748b)', 'shadow' => 'rgba(148, 163, 184, 0.4)']; ?>
                <a class="site-card" href="index.php?page=portal&slug=<?= h($cardSlug) ?>" style="border: none; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01); background: #fff; border-radius: 20px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                    
                    <?php if (isset($mapping['custom'])): ?>
                        <!-- Custom e-Monev Logo CSS replica -->
                        <div style="width: 80px; height: 80px; margin-bottom: 24px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fff; border-radius: 24px; box-shadow: 0 8px 16px rgba(0,0,0,0.06), inset 0 2px 4px rgba(255,255,255,0.8); border: 1px solid #f1f5f9;">
                            <div style="position: relative; font-family: 'Arial', sans-serif; font-weight: 900; font-size: 15px; color: #0ea5e9; letter-spacing: -0.5px; display: flex; align-items: center; margin-left: 10px; margin-top: 5px;">
                                <div style="position: absolute; left: -16px; top: -3px; width: 28px; height: 28px; background: #0ea5e9; border-radius: 50%; z-index: 1;"></div>
                                <span style="position: relative; z-index: 2; color: #f59e0b; font-size: 34px; font-style: italic; font-family: Georgia, serif; margin-right: 1px; margin-top: -6px; text-shadow: 1px 0 0 #fff, -1px 0 0 #fff, 0 1px 0 #fff, 0 -1px 0 #fff;">e</span>
                                <span style="position: relative; z-index: 2; margin-top: 2px;">MONEV</span>
                                <span style="position: absolute; top: -10px; right: -12px; background: #0ea5e9; color: #fff; font-size: 7px; padding: 2px 4px; border-radius: 6px; z-index: 3;">PP 39</span>
                            </div>
                            <div style="font-size: 10px; font-weight: 900; color: #fff; background: #0ea5e9; padding: 2px 8px; border-radius: 12px; margin-top: 4px; letter-spacing: 1px;">BAPPENAS</div>
                        </div>
                    <?php else: ?>
                        <!-- Modern Gradient Icon -->
                        <div style="width: 80px; height: 80px; margin-bottom: 24px; border-radius: 24px; background: <?= $mapping['bg'] ?>; display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 12px 20px -8px <?= $mapping['shadow'] ?>, inset 0 2px 4px rgba(255,255,255,0.3);">
                            <i class="ph-duotone <?= $mapping['icon'] ?>" style="font-size: 42px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></i>
                        </div>
                    <?php endif; ?>
                    
                    <span style="font-weight: 700; color: #1e293b; font-size: 1.1rem; letter-spacing: -0.01em;"><?= h($label) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <style>
            .site-card:hover {
                transform: translateY(-8px) scale(1.02);
                box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1), 0 10px 15px -5px rgba(0, 0, 0, 0.05) !important;
            }
            .site-card-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 24px;
            }
        </style>
    <?php endif; ?>

    <?php if (!empty($pageData['list'])): ?>
        <ol class="content-list">
            <?php foreach ($pageData['list'] as $item): ?>
                <li><?= h((string) $item) ?></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <?php foreach (($pageData['sections'] ?? []) as $section): ?>
        <article class="content-section">
            <h3><?= h((string) $section['title']) ?></h3>
            <?php if (!empty($section['iframe'])): ?>
                <div style="width: 100%; height: 600px; margin-top: 16px; margin-bottom: 16px; border-radius: 8px; overflow: hidden; border: 1px solid var(--border);">
                    <iframe src="<?= h($section['iframe']) ?>" width="100%" height="100%" style="border: none;" allow="autoplay"></iframe>
                </div>
                <?php if (!empty($section['url'])): ?>
                    <div style="text-align: right; margin-bottom: 24px;">
                        <a href="<?= h($section['url']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; background: var(--primary); color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            Buka di Tab Baru
                        </a>
                    </div>
                <?php endif; ?>
            <?php elseif (!empty($section['items'])): ?>
                <ul class="content-list">
                    <?php foreach ($section['items'] as $item): ?>
                        <li><?= h((string) $item) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<?php render_footer(); ?>
