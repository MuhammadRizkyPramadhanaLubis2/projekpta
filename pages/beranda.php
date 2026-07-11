<?php
declare(strict_types=1);

$pageData = site_page('beranda');
render_header('IKPA');
?>
<section class="site-hero" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; align-items: center;">
    <div style="z-index: 2; position: relative;">
        <h1 class="hero-title"><?= h((string) $pageData['title']) ?></h1>
        <p class="hero-subtitle"><?= h((string) $pageData['subtitle']) ?></p>
        <p style="margin-bottom: 2rem; font-size: 1.15rem; max-width: 600px; opacity: 0.9; line-height: 1.6;">
            <?= h((string) $pageData['lead']) ?>
        </p>
        <a href="index.php?page=portal&slug=program-kerja-sop" class="button" style="padding: 14px 32px; font-size: 1.1rem; border-radius: 50px; margin-bottom: 12px; display: inline-flex; align-items: center; gap: 8px;">
            Mulai Eksplorasi <i class="ph-bold ph-arrow-right"></i>
        </a>
    </div>
    <div style="z-index: 2; position: relative; display: flex; justify-content: center;">
        <img src="assets/gedung1.webp" alt="Gedung PTA Medan" style="width: 100%; max-width: 450px; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); border: 4px solid rgba(255,255,255,0.15);">
    </div>
</section>

<section class="site-section">
    <div class="page-panel" style="margin-bottom: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; align-items: center;">
        <div>
            <?php foreach ($pageData['body'] as $paragraph): ?>
                <p style="font-size: 1.1rem; line-height: 1.7; color: var(--text); margin-bottom: 16px;"><?= h((string) $paragraph) ?></p>
            <?php endforeach; ?>
        </div>
        <div>
            <img src="assets/gedung2.webp" alt="Gedung PTA Medan" style="width: 100%; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        </div>
    </div>

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
                        <div style="width: 80px; height: 80px; margin-bottom: 24px; display: flex; align-items: center; justify-content: center; background: #fff; border-radius: 24px; box-shadow: 0 8px 16px rgba(0,0,0,0.06), inset 0 2px 4px rgba(255,255,255,0.8); border: 1px solid #f1f5f9; padding: 5px; box-sizing: border-box;">
                            <img src="assets/logo_emonev.png" alt="e-Monev Bappenas" style="max-width: 100%; max-height: 100%; object-fit: contain;">
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
</section>
<?php render_footer(); ?>
