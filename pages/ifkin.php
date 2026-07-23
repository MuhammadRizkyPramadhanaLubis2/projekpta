<?php
declare(strict_types=1);

if (!defined('IFKIN_EMBEDDED')) {
    render_header('Informasi Kinerja');
}

$ifkinSites = [
    ['number'=>'01','title'=>'KemenPAN-RB','description'=>'Portal Kementerian Pendayagunaan Aparatur Negara dan Reformasi Birokrasi.','url'=>'https://menpan.go.id/','icon'=>'ph-buildings'],
    ['number'=>'02','title'=>'Bappenas','description'=>'Portal Kementerian Perencanaan Pembangunan Nasional/Bappenas.','url'=>'https://www.bappenas.go.id/','icon'=>'ph-chart-line-up'],
    ['number'=>'03','title'=>'KPK RI','description'=>'Portal Komisi Pemberantasan Korupsi Republik Indonesia.','url'=>'https://www.kpk.go.id/id','icon'=>'ph-shield-check'],
    ['number'=>'04','title'=>'Mahkamah Agung Republik Indonesia','description'=>'Portal utama Mahkamah Agung RI untuk informasi kelembagaan, berita, putusan, dan layanan publik.','url'=>'https://mahkamahagung.go.id/id','icon'=>'ph-scales'],
    ['number'=>'05','title'=>'Badan Urusan Administrasi Mahkamah Agung','description'=>'Informasi administrasi, perencanaan, organisasi, kepegawaian, dan layanan pendukung Mahkamah Agung RI.','url'=>'https://bua.mahkamahagung.go.id/','icon'=>'ph-buildings'],
    ['number'=>'06','title'=>'Direktorat Jenderal Badan Peradilan Agama','description'=>'Portal pembinaan, layanan, berita, regulasi, dan informasi peradilan agama se-Indonesia.','url'=>'https://badilag.mahkamahagung.go.id/','icon'=>'ph-bank'],
    ['number'=>'07','title'=>'Badan Pengawasan Mahkamah Agung','description'=>'Informasi pengawasan, disiplin, pengumuman, dan kebijakan integritas aparatur peradilan.','url'=>'https://bawas.mahkamahagung.go.id/','icon'=>'ph-eye'],
];
?>
<style>
    .shell > .topbar {
        display: none;
    }

    .ifkin-page {
        --ifkin-bg: #eef3ef;
        --ifkin-paper: #ffffff;
        --ifkin-ink: #11201a;
        --ifkin-muted: #5d6d65;
        --ifkin-faint: #94a19a;
        --ifkin-line: #e1eae5;
        --ifkin-green: #1f7a4d;
        --ifkin-green-deep: #0f3324;
        --ifkin-green-soft: #edf5f0;
        position: relative;
        overflow: hidden;
        margin: -40px;
        min-height: calc(100vh - 80px);
        color: var(--ifkin-ink);
        background: var(--ifkin-bg);
    }

    .public-shell-mode .ifkin-page {
        margin: 0;
    }

    .ifkin-page::before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 470px;
        background:
            linear-gradient(90deg, rgba(2, 21, 14, .96), rgba(15, 51, 36, .72), rgba(2, 21, 14, .90)),
            url('assets/gedung2.webp') center/cover;
        pointer-events: none;
    }

    .ifkin-page::after {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 470px;
        background:
            radial-gradient(ellipse at 50% 40%, rgba(16, 185, 129, .22), transparent 58%),
            linear-gradient(180deg, transparent 72%, var(--ifkin-bg) 100%),
            url('assets/batik_sumut.png') center/320px;
        opacity: .20;
        mix-blend-mode: soft-light;
        pointer-events: none;
    }

    .ifkin-wrap {
        position: relative;
        z-index: 2;
        width: min(1120px, 100%);
        margin: 0 auto;
        padding: 0 28px 48px;
    }

    .ifkin-hero {
        min-height: 390px;
        display: grid;
        place-items: center;
        padding: 80px 0 120px;
        text-align: center;
        color: #fff;
    }

    .ifkin-eyebrow {
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 999px;
        background: rgba(255, 255, 255, .10);
        color: #d7f7e4;
        padding: 7px 16px;
        box-shadow: 0 10px 24px -18px rgba(0, 0, 0, .7);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 2.4px;
        text-transform: uppercase;
    }

    .ifkin-eyebrow::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #9ee6bb;
        box-shadow: 0 0 0 4px rgba(158, 230, 187, .18);
        margin-right: 9px;
    }

    .ifkin-hero h1 {
        margin: 22px 0 0;
        color: #fff;
        font-size: clamp(42px, 6vw, 58px);
        font-weight: 800;
        letter-spacing: 0;
        line-height: 1.12;
        text-shadow: 0 18px 38px rgba(0, 0, 0, .38);
    }

    .ifkin-hero p {
        max-width: 620px;
        margin: 18px auto 0;
        color: rgba(255, 255, 255, .90);
        font-size: 16px;
        line-height: 1.7;
    }

    .ifkin-panel {
        margin-top: -76px;
        border: 1px solid rgba(15, 51, 36, .08);
        border-radius: 22px;
        background: var(--ifkin-paper);
        box-shadow: 0 28px 70px rgba(13, 42, 29, .13);
        overflow: hidden;
    }

    .ifkin-panel-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        padding: 34px 38px 26px;
        border-bottom: 1px solid var(--ifkin-line);
    }

    .ifkin-panel-head h2 {
        margin: 0;
        color: #081b13;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 31px;
        font-weight: 400;
        letter-spacing: 0;
    }

    .ifkin-panel-head p {
        max-width: 560px;
        margin: 8px 0 0;
        color: var(--ifkin-muted);
        line-height: 1.65;
    }

    .ifkin-count {
        flex: 0 0 auto;
        color: var(--ifkin-faint);
        font-size: 14px;
        font-weight: 700;
    }

    .ifkin-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        padding: 36px 38px 42px;
    }

    .ifkin-card {
        position: relative;
        overflow: hidden;
        min-height: 220px;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--ifkin-line);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff, #fcfefd);
        color: var(--ifkin-ink);
        padding: 30px;
        text-decoration: none;
        box-shadow: 0 1px 0 rgba(255, 255, 255, .7) inset, 0 24px 55px -38px rgba(7, 32, 22, .50);
        transition: .2s ease;
    }

    .ifkin-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 3px;
        background: linear-gradient(90deg, var(--ifkin-green), #3fae74);
        transform: scaleX(0);
        transform-origin: left;
        transition: .28s ease;
    }

    .ifkin-card::after {
        content: "";
        position: absolute;
        top: -54px;
        right: -54px;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(31, 122, 77, .12), rgba(63, 174, 116, .05) 45%, transparent 72%);
        pointer-events: none;
    }

    .ifkin-card:hover {
        transform: translateY(-4px);
        border-color: #cfe3d8;
        color: var(--ifkin-ink);
        box-shadow: 0 30px 58px -34px rgba(7, 32, 22, .58);
    }

    .ifkin-card:hover::before {
        transform: scaleX(1);
    }

    .ifkin-number {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, var(--ifkin-green), #2f9160);
        color: #fff;
        box-shadow: 0 10px 20px -10px rgba(31, 122, 77, .7), 0 1px 0 rgba(255, 255, 255, .3) inset;
        font-size: 14px;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }

    .ifkin-card h3 {
        margin: 22px 0 0;
        color: var(--ifkin-ink);
        font-size: 21px;
        font-weight: 800;
        letter-spacing: 0;
        line-height: 1.25;
        overflow-wrap: anywhere;
        hyphens: auto;
    }

    .ifkin-card p {
        margin: 10px 0 0;
        color: var(--ifkin-muted);
        font-size: 13.5px;
        line-height: 1.6;
    }

    .ifkin-url {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: auto;
        padding-top: 18px;
        border-top: 1px solid var(--ifkin-line);
        color: var(--ifkin-green);
        font-size: 12.5px;
        font-weight: 800;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .ifkin-card:hover .ifkin-url {
        gap: 12px;
    }

    @media (max-width: 980px) {
        .ifkin-page {
            margin: -24px;
        }

        .public-shell-mode .ifkin-page {
            margin: 0;
        }
    }

    @media (max-width: 760px) {
        .ifkin-wrap {
            padding: 0 18px 38px;
        }

        .ifkin-panel-head {
            display: block;
            padding: 28px 22px 22px;
        }

        .ifkin-count {
            display: block;
            margin-top: 10px;
        }

        .ifkin-grid {
            grid-template-columns: 1fr;
            padding: 24px 22px 30px;
        }
    }
.ifkin-card-disabled{opacity:.72;cursor:not-allowed}.ifkin-card-disabled:hover{transform:none;box-shadow:0 24px 55px -38px rgba(7,32,22,.5)}
</style>

<div class="ifkin-page">
    <div class="ifkin-wrap">
        <section class="ifkin-hero">
            <div>
                <div class="ifkin-eyebrow">IFKIN</div>
                <h1>Informasi Kinerja</h1>
                <p>Direktori cepat menuju website pemerintah dan peradilan yang menjadi rujukan informasi kinerja, pembinaan, administrasi, serta pengawasan.</p>
            </div>
        </section>

        <section class="ifkin-panel">
            <div class="ifkin-panel-head">
                <div>
                    <h2>Website Pemerintah</h2>
                    <p>Pilih salah satu tautan berikut untuk membuka sumber informasi resmi pada tab baru.</p>
                </div>
                <span class="ifkin-count"><?= count($ifkinSites) ?> website</span>
            </div>

            <div class="ifkin-grid">
                <?php foreach ($ifkinSites as $site): ?>
                    <?php if ($site['url'] !== ''): ?><a class="ifkin-card" href="<?= h($site['url']) ?>" target="_blank" rel="noopener noreferrer"><?php else: ?><div class="ifkin-card ifkin-card-disabled" aria-disabled="true"><?php endif; ?>
                        <span class="ifkin-number"><i class="ph <?= h((string)$site['icon']) ?>"></i></span>
                        <h3><?= h($site['title']) ?></h3><p><?= h($site['description']) ?></p>
                        <span class="ifkin-url"><?= $site['url'] !== '' ? h($site['url']) . ' ↗' : 'URL belum tersedia' ?></span>
                    <?php if ($site['url'] !== ''): ?></a><?php else: ?></div><?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
<?php
if (!defined('IFKIN_EMBEDDED')) {
    render_footer();
}
?>
