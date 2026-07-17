<?php
declare(strict_types=1);

render_header('Menu Utama IKPA');

$sections = shared_workflow_groups();

$user = current_user();
$isAdmin = $user['role'] === 'Admin';
$profile = role_profile((string) $user['role']);
?>
<style>
    /* Super Aesthetic Dashboard Styles */
    .dashboard-hero {
        background: linear-gradient(135deg, #064e3b, #047857, #10b981);
        color: #fff;
        padding: 48px;
        border-radius: 28px;
        margin-bottom: 40px;
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.2);
        position: relative;
        overflow: hidden;
    }
    .dashboard-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        pointer-events: none;
        z-index: 0;
    }
    .dashboard-hero > * {
        position: relative;
        z-index: 1;
    }
    .dashboard-hero-title {
        font-size: 2.5rem;
        margin: 0 0 12px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .dashboard-hero-desc {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0 0 32px;
        max-width: 800px;
        line-height: 1.6;
    }
    .hero-actions {
        display: flex;
        gap: 16px;
    }
    .btn-portal-publik {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        color: #064e3b;
        padding: 16px 32px;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-portal-publik:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 28px rgba(0,0,0,0.15);
        color: #047857;
    }
    
    .section-title {
        font-size: 2rem;
        color: var(--primary-dark);
        margin-bottom: 24px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    
    /* Workflow Cards */
    .workflow-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 48px;
    }
    .workflow-card {
        background: #fff;
        border-radius: 20px;
        padding: 24px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 12px 24px -10px rgba(6, 78, 59, 0.08);
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }
    .workflow-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 30px -10px rgba(6, 78, 59, 0.15);
        border-color: rgba(16, 185, 129, 0.3);
    }
    .workflow-card-icon {
        width: 48px;
        height: 48px;
        background: #ecfdf5;
        color: #10b981;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 16px;
    }
    .workflow-title {
        font-size: 1.25rem;
        color: var(--primary-dark);
        font-weight: 700;
        margin-bottom: 8px;
    }
    .workflow-desc {
        font-size: 0.95rem;
        color: var(--muted);
        line-height: 1.6;
        flex: 1;
    }
    
    /* Tasks & Analysis Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 32px;
        margin-bottom: 48px;
    }
    .info-card {
        background: #fff;
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        height: 100%;
    }
    .info-card h3 {
        margin: 0 0 20px;
        color: var(--primary-dark);
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .info-card ul {
        margin: 0;
        padding-left: 20px;
        color: #334155;
        line-height: 1.7;
    }
    .info-card li {
        margin-bottom: 12px;
    }
    .info-card li:last-child {
        margin-bottom: 0;
    }

    /* Admin specific styles */
    .admin-stakeholder-section {
        margin-top: 64px;
    }
    .stakeholder-card {
        background: #fff;
        border-radius: 24px;
        padding: 32px;
        margin-bottom: 32px;
        border-left: 6px solid #10b981;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .stakeholder-header {
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .stakeholder-title {
        font-size: 1.5rem;
        color: var(--primary-dark);
        margin: 0 0 8px;
        font-weight: 800;
    }
    .stakeholder-scope {
        color: var(--muted);
        font-size: 1.05rem;
        margin: 0;
    }
    .stakeholder-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 24px;
    }
    
    .ref-link {
        display: block;
        padding: 14px 20px; 
        background: #f8fafc; 
        border-radius: 12px; 
        color: var(--primary-dark); 
        text-decoration: none; 
        font-weight: 600; 
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
    }
    .ref-link:hover {
        background: #fff; 
        color: var(--primary);
        border-color: var(--primary);
        box-shadow: 0 8px 16px rgba(16, 185, 129, 0.1);
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .dashboard-hero {
            padding: 24px;
        }
        .dashboard-hero-title {
            font-size: 1.8rem;
        }
        .dashboard-hero-desc {
            font-size: 1rem;
        }
        .hero-actions {
            flex-direction: column;
        }
    }
</style>

<div class="dashboard-hero">
    <h2 class="dashboard-hero-title">Selamat Datang, <?= h($user['nama']) ?></h2>
    <p class="dashboard-hero-desc">
        <?= h((string) $profile['scope']) ?>
        Gunakan dashboard ini untuk mengakses modul kerja yang menjadi tanggung jawab Anda, mengevaluasi kinerja, dan berkontribusi pada pencapaian Indikator Kinerja.
    </p>
    <div class="hero-actions">
        <a href="index.php?page=beranda" class="btn-portal-publik">
            <i class="ph-bold ph-globe"></i>
            Kunjungi Portal Publik
        </a>
    </div>
</div>

<section>
    <h2 class="section-title">Ringkasan Alur Kerja Jabatan</h2>
    <div class="workflow-grid">
        <?php foreach ($profile['workflows'] as [$label, $targetPage, $slug, $description]): ?>
            <?php
            $url = 'index.php?page=' . urlencode($targetPage);
            if ($slug !== null) {
                $url .= '&slug=' . urlencode($slug);
            }
            ?>
            <a class="workflow-card" href="<?= h($url) ?>">
                <div class="workflow-card-icon">
                    <i class="ph-bold ph-caret-circle-right"></i>
                </div>
                <span class="workflow-title"><?= h((string) $label) ?></span>
                <span class="workflow-desc"><?= h((string) $description) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php if (!$isAdmin): ?>
    <!-- Tampilan Dashboard Spesifik Role Biasa -->
    <div class="info-grid">
        <div class="info-card">
            <h3><i class="ph-fill ph-check-circle"></i> Tugas Utama (Checks)</h3>
            <ul>
                <?php foreach ($profile['checks'] as $item): ?>
                    <li><?= h((string) $item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="info-card">
            <h3><i class="ph-fill ph-database"></i> Sumber Aplikasi</h3>
            <ul>
                <?php foreach ($profile['sources'] as $source): ?>
                    <li><?= h((string) $source) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="info-card">
            <h3><i class="ph-fill ph-export"></i> Output Wajib</h3>
            <ul>
                <?php foreach ($profile['outputs'] as $output): ?>
                    <li><?= h((string) $output) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="info-card" style="background: #fff8f1; border-color: #ffd8a8; margin-bottom: 48px;">
        <h3 style="color: var(--gold);"><i class="ph-fill ph-warning-circle"></i> Aturan Analisis EvKin Sesuai Konsep IKPA</h3>
        <p style="font-size: 1.15rem; line-height: 1.7; margin: 0; color: #b45309;">
            <?= h((string) $profile['analysis_rule']) ?>
        </p>
    </div>
<?php else: ?>
    <!-- Tampilan Admin Khusus Menjelaskan Seluruh Stakeholder -->
    <section class="admin-stakeholder-section">
        <div style="margin-bottom: 32px;">
            <h2 class="section-title">Pemahaman Konsep IKPA (Semua Role)</h2>
            <p class="site-lead">Sebagai Administrator, Anda dapat melihat rincian tugas dan tanggung jawab dari seluruh pemangku kepentingan (stakeholder) di PTA Medan maupun Satuan Kerja PA, sesuai dengan Konsep Aplikasi IKPA.</p>
        </div>

        <?php 
        $allProfiles = get_all_role_profiles(); 
        foreach ($allProfiles as $roleKey => $roleData): 
            if ($roleKey === 'Admin') continue; // Skip admin from the list of roles
        ?>
            <div class="stakeholder-card">
                <div class="stakeholder-header">
                    <h3 class="stakeholder-title"><?= h($roleData['title']) ?></h3>
                    <p class="stakeholder-scope"><?= h($roleData['scope']) ?></p>
                </div>
                
                <div class="info-card" style="background: #f8fafc; border: 1px dashed #cbd5e1; margin-bottom: 24px; padding: 20px;">
                    <strong style="display: block; color: var(--gold); margin-bottom: 8px;"><i class="ph-fill ph-warning-circle"></i> Aturan Analisis EvKin</strong>
                    <span style="color: #475569; line-height: 1.6;"><?= h($roleData['analysis_rule']) ?></span>
                </div>

                <div class="stakeholder-grid">
                    <div>
                        <strong style="display: block; margin-bottom: 12px; color: var(--primary-dark);"><i class="ph-fill ph-check-circle"></i> Tugas Utama</strong>
                        <ul style="margin: 0; padding-left: 20px; color: #475569; font-size: 0.95rem;">
                            <?php foreach ($roleData['checks'] as $task): ?>
                                <li style="margin-bottom: 6px;"><?= h($task) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <strong style="display: block; margin-bottom: 12px; color: var(--primary-dark);"><i class="ph-fill ph-export"></i> Output Wajib</strong>
                        <ul style="margin: 0; padding-left: 20px; color: #475569; font-size: 0.95rem;">
                            <?php foreach ($roleData['outputs'] as $out): ?>
                                <li style="margin-bottom: 6px;"><?= h($out) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <strong style="display: block; margin-bottom: 12px; color: var(--primary-dark);"><i class="ph-fill ph-database"></i> Sumber Aplikasi</strong>
                        <ul style="margin: 0; padding-left: 20px; color: #475569; font-size: 0.95rem;">
                            <?php foreach ($roleData['sources'] as $src): ?>
                                <li style="margin-bottom: 6px;"><?= h($src) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<section class="page-panel" style="margin-bottom: 0;">
    <div style="margin-bottom: 32px;">
        <h2 class="section-title" style="margin-bottom: 10px;">Referensi Aplikasi dan Modul</h2>
        <p class="site-lead" style="margin: 0;">Bagian ini tetap tersedia sebagai referensi keseluruhan aplikasi dan untuk melakukan navigasi lintas sistem.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px;">
        <?php foreach ($sections as $sectionTitle => $items): ?>
            <div style="background: #fff; padding: 24px; border-radius: 20px; border: 1px solid #e2e8f0;">
                <h3 style="color: #64748b; margin: 0 0 20px; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 800;">
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
