<?php
declare(strict_types=1);

if (!defined('PROGRAM_KERJA_SOP_EMBEDDED')) {
    render_header('Program Kerja & SOP');
}
?>
<style>
    .shell > .topbar {
        display: none;
    }

    .program-sop-page {
        --pk-canvas: #eef3ef;
        --pk-paper: #ffffff;
        --pk-ink: #17231d;
        --pk-muted: #56675e;
        --pk-faint: #8c9a92;
        --pk-line: #e3eae5;
        --pk-green: #1f7a4d;
        --pk-green-deep: #0f3324;
        --pk-green-soft: #e9f3ed;
        --pk-green-mid: #cfe3d8;
        --pk-gold: #bf8f38;
        --pk-blue: #3b7fc4;
        margin: -40px;
        min-height: calc(100vh - 80px);
        background: var(--pk-canvas);
        color: var(--pk-ink);
    }

    .public-shell-mode .program-sop-page {
        margin: 0;
    }

    .program-sop-page * {
        box-sizing: border-box;
    }

    .program-sop-page a {
        text-decoration: none;
    }

    .pk-serif {
        font-family: Georgia, "Times New Roman", serif;
    }

    .pk-hero {
        position: relative;
        min-height: 430px;
        display: grid;
        align-items: center;
        overflow: hidden;
        color: #fff;
        background:
            linear-gradient(90deg, rgba(2, 21, 14, .96), rgba(15, 51, 36, .70), rgba(2, 21, 14, .86)),
            url('assets/gedung2.webp') center/cover;
    }

    .pk-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url('assets/batik_sumut.png');
        background-size: 320px;
        opacity: .12;
        mix-blend-mode: soft-light;
    }

    .pk-hero::after {
        content: "";
        position: absolute;
        inset: auto 0 0;
        height: 96px;
        background: linear-gradient(180deg, transparent, var(--pk-canvas));
    }

    .pk-hero-inner {
        position: relative;
        z-index: 1;
        width: min(980px, calc(100% - 48px));
        margin: 0 auto;
        padding: 72px 0 120px;
        text-align: center;
    }

    .pk-site {
        margin-bottom: 90px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .22em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .86);
    }

    .pk-kick {
        display: inline-flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 20px;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .34em;
        text-transform: uppercase;
    }

    .pk-kick::before,
    .pk-kick::after {
        content: "";
        width: 34px;
        height: 1px;
        background: rgba(255, 255, 255, .68);
    }

    .pk-hero h1 {
        margin: 0;
        font-size: clamp(42px, 6vw, 60px);
        line-height: 1.05;
        font-weight: 700;
        color: #fff;
        letter-spacing: 0;
        text-shadow: 0 18px 38px rgba(0, 0, 0, .38);
    }

    .pk-hero p {
        width: min(600px, 100%);
        margin: 24px auto 0;
        color: rgba(255, 255, 255, .95);
        font-size: 17px;
        line-height: 1.7;
    }

    .pk-stage {
        position: relative;
        z-index: 2;
        width: min(1180px, calc(100% - 48px));
        margin: -90px auto 0;
        padding-bottom: 38px;
    }

    .pk-card {
        overflow: hidden;
        border: 1px solid rgba(15, 51, 36, .08);
        border-radius: 22px;
        background: var(--pk-paper);
        box-shadow: 0 28px 70px rgba(13, 42, 29, .13);
    }

    .pk-tabs {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        padding: 18px;
        border-bottom: 1px solid var(--pk-line);
    }

    .pk-tab {
        border: 0;
        border-radius: 13px;
        background: transparent;
        color: #43564c;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        min-height: 62px;
        padding: 14px 18px;
        font: inherit;
        font-weight: 800;
        transition: .18s ease;
    }

    .pk-tab:hover {
        background: var(--pk-green-soft);
        color: var(--pk-green-deep);
    }

    .pk-tab.active {
        background: var(--pk-green);
        color: #fff;
        box-shadow: 0 12px 24px rgba(31, 122, 77, .24);
    }

    .pk-tab-number {
        color: currentColor;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 12px;
        opacity: .72;
        letter-spacing: .1em;
    }

    .pk-panel {
        display: none;
        padding: 56px 52px 62px;
    }

    .pk-panel.active {
        display: block;
    }

    .pk-panel-head {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: start;
        margin-bottom: 16px;
    }

    .pk-panel h2 {
        margin: 0;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 31px;
        font-weight: 400;
        color: #081b13;
        letter-spacing: 0;
    }

    .pk-meta {
        color: var(--pk-faint);
        font-size: 14px;
        white-space: nowrap;
    }

    .pk-panel-sub {
        width: min(620px, 100%);
        margin: 0 0 32px;
        color: var(--pk-muted);
        line-height: 1.7;
    }

    .pk-feature-grid,
    .pk-closing {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .pk-feature {
        position: relative;
        overflow: hidden;
        min-height: 230px;
        border-radius: 14px;
        padding: 42px 34px 30px;
        color: #fff;
        background:
            linear-gradient(180deg, rgba(2, 44, 34, .60), rgba(2, 44, 34, .92)),
            url('assets/batik_sumut.png') center/360px,
            linear-gradient(145deg, #0f3324, #1f7a4d);
        border: 1px solid rgba(255, 255, 255, .14);
        transition: .18s ease;
    }

    .pk-feature:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 34px rgba(9, 30, 20, .28);
    }

    .pk-year {
        font-size: 46px;
        line-height: 1;
        color: rgba(255, 255, 255, .83);
        text-shadow: 0 2px 18px rgba(0, 0, 0, .55);
    }

    .pk-feature h3,
    .pk-schedule-card h3,
    .pk-drive-card h3 {
        margin: 12px 0 0;
        font-size: 19px;
        color: inherit;
    }

    .pk-feature p,
    .pk-schedule-card p {
        margin: 10px 0 0;
        color: rgba(255, 255, 255, .86);
        line-height: 1.55;
    }

    .pk-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 22px;
        color: #96e0b8;
        font-weight: 800;
        font-size: 13px;
    }

    .pk-search {
        position: relative;
        display: flex;
        align-items: center;
        margin-bottom: 24px;
    }

    .pk-search i {
        position: absolute;
        left: 16px;
        color: var(--pk-faint);
        font-size: 20px;
    }

    .pk-search input {
        width: 100%;
        min-height: 48px;
        border: 1px solid var(--pk-line);
        border-radius: 999px;
        outline: none;
        background: #fbfdfb;
        color: var(--pk-ink);
        padding: 12px 18px 12px 48px;
        font: inherit;
        transition: .16s ease;
    }

    .pk-search input:focus {
        border-color: var(--pk-green);
        box-shadow: 0 0 0 3px var(--pk-green-soft);
        background: #fff;
    }

    .pk-sop-columns {
        columns: 2;
        column-gap: 38px;
    }

    .pk-sop-group {
        break-inside: avoid;
        margin-bottom: 24px;
    }

    .pk-group-caption {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 8px;
    }

    .pk-group-title {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 18px;
        color: #13221a;
    }

    .pk-tag {
        border-radius: 999px;
        padding: 3px 8px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .pk-tag.anggaran {
        color: var(--pk-green);
        background: var(--pk-green-soft);
    }

    .pk-tag.sakip {
        color: var(--pk-gold);
        background: #f8f0da;
    }

    .pk-tag.kinerja {
        color: var(--pk-blue);
        background: #e6f0fa;
    }

    .pk-sop-row {
        display: flex;
        align-items: baseline;
        gap: 11px;
        padding: 11px 4px;
        border-bottom: 1px solid var(--pk-line);
        color: var(--pk-ink);
        transition: .14s ease;
    }

    .pk-sop-row:hover {
        padding-left: 9px;
        background: linear-gradient(90deg, var(--pk-green-soft), transparent);
    }

    .pk-sop-number {
        flex: 0 0 28px;
        color: var(--pk-faint);
        font-family: Georgia, "Times New Roman", serif;
        font-size: 13px;
    }

    .pk-sop-title {
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1.35;
    }

    .pk-sumut {
        display: inline-flex;
        margin-left: 6px;
        border-radius: 999px;
        background: #f8f0da;
        color: var(--pk-gold);
        padding: 2px 6px;
        font-size: 8.5px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        vertical-align: 2px;
    }

    .pk-leader {
        flex: 1;
        min-width: 12px;
        border-bottom: 1.5px dotted var(--pk-green-mid);
        transform: translateY(-4px);
    }

    .pk-file-type {
        flex: 0 0 auto;
        color: var(--pk-faint);
        font-size: 10.5px;
        font-weight: 800;
    }

    .pk-empty {
        display: none;
        padding: 22px 0 0;
        color: var(--pk-faint);
        font-size: 14px;
    }

    .pk-schedule-card {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        padding: 30px;
        color: #fff;
        background: linear-gradient(150deg, var(--pk-green), var(--pk-green-deep));
    }

    .pk-schedule-card::after {
        content: "";
        position: absolute;
        right: -42px;
        top: -42px;
        width: 140px;
        height: 140px;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 50%;
    }

    .pk-drive-card {
        display: flex;
        flex-direction: column;
        justify-content: center;
        border: 1px solid var(--pk-line);
        border-radius: 16px;
        background: #fbfdfb;
        padding: 30px;
    }

    .pk-drive-card i {
        color: var(--pk-green);
        font-size: 34px;
    }

    .pk-drive-card p {
        margin: 10px 0 0;
        color: var(--pk-muted);
        line-height: 1.6;
    }

    .pk-drive-card .pk-link {
        color: var(--pk-green);
    }

    .pk-footer-note {
        width: min(1180px, calc(100% - 48px));
        margin: 0 auto;
        padding: 0 0 42px;
        color: var(--pk-faint);
        text-align: center;
        font-size: 12px;
    }

    .pk-footer-note b {
        color: var(--pk-muted);
    }

    @media (max-width: 980px) {
        .program-sop-page {
            margin: -24px;
        }

        .public-shell-mode .program-sop-page {
            margin: 0;
        }

        .pk-stage,
        .pk-footer-note {
            width: min(100% - 28px, 1180px);
        }
    }

    @media (max-width: 820px) {
        .pk-hero {
            min-height: 390px;
        }

        .pk-hero-inner {
            width: min(100% - 32px, 980px);
            padding-top: 54px;
        }

        .pk-site {
            margin-bottom: 54px;
            font-size: 11px;
        }

        .pk-tabs,
        .pk-feature-grid,
        .pk-closing {
            grid-template-columns: 1fr;
        }

        .pk-tab {
            justify-content: flex-start;
        }

        .pk-panel {
            padding: 32px 22px 38px;
        }

        .pk-panel-head {
            display: block;
        }

        .pk-meta {
            display: block;
            margin-top: 8px;
        }

        .pk-sop-columns {
            columns: 1;
        }
    }
</style>

<div class="program-sop-page">
    <header class="pk-hero">
        <div class="pk-hero-inner">
            <div class="pk-site">Pengadilan Tinggi Agama Medan</div>
            <div class="pk-kick">Sub Bagian Rencana Program &amp; Anggaran</div>
            <h1 class="pk-serif">Program Kerja &amp; SOP</h1>
            <p>Ruang dokumen perencanaan yang tertata - rencana kerja, prosedur baku, dan jadwal kegiatan dalam satu tempat yang tenang dan mudah ditelusuri.</p>
        </div>
    </header>

    <div class="pk-stage">
        <div class="pk-card">
            <div class="pk-tabs" role="tablist" aria-label="Kategori dokumen">
                <button class="pk-tab active" type="button" data-panel="program" role="tab" aria-selected="true">
                    <span class="pk-tab-number">01</span>
                    Program Kerja
                </button>
                <button class="pk-tab" type="button" data-panel="sop" role="tab" aria-selected="false">
                    <span class="pk-tab-number">02</span>
                    Standar Operasional (SOP)
                </button>
                <button class="pk-tab" type="button" data-panel="jadwal" role="tab" aria-selected="false">
                    <span class="pk-tab-number">03</span>
                    Jadwal &amp; Arsip
                </button>
            </div>

            <section class="pk-panel active" id="pk-panel-program" role="tabpanel">
                <div class="pk-panel-head">
                    <h2>Program Kerja</h2>
                    <span class="pk-meta">2 dokumen</span>
                </div>
                <p class="pk-panel-sub">Rencana kerja tahunan Sub Bagian Rencana Program dan Anggaran Pengadilan Tinggi Agama Medan.</p>
                <div class="pk-feature-grid">
                    <article class="pk-feature">
                        <div class="pk-year pk-serif">2024</div>
                        <h3>Program Kerja 2024</h3>
                        <p>Dokumen rencana kerja untuk tahun anggaran 2024.</p>
                        <a class="pk-link" href="#" aria-label="Buka Program Kerja 2024">Buka dokumen <span>&rarr;</span></a>
                    </article>
                    <article class="pk-feature">
                        <div class="pk-year pk-serif">2025</div>
                        <h3>Program Kerja 2025</h3>
                        <p>Pembaruan program kerja untuk tahun anggaran berjalan.</p>
                        <a class="pk-link" href="#" aria-label="Buka Program Kerja 2025">Buka dokumen <span>&rarr;</span></a>
                    </article>
                </div>
            </section>

            <section class="pk-panel" id="pk-panel-sop" role="tabpanel">
                <div class="pk-panel-head">
                    <h2>Standard Operating Procedure</h2>
                    <span class="pk-meta">14 dokumen</span>
                </div>
                <p class="pk-panel-sub">Kumpulan prosedur baku, dikelompokkan menurut tahapan siklus perencanaan dan anggaran.</p>
                <label class="pk-search">
                    <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                    <input id="pk-sop-search" type="search" placeholder="Cari prosedur..." autocomplete="off">
                </label>
                <div class="pk-sop-columns" id="pk-sop-list">
                    <div class="pk-sop-group">
                        <div class="pk-group-caption">
                            <span class="pk-group-title">Perencanaan Anggaran</span>
                            <span class="pk-tag anggaran">Anggaran</span>
                        </div>
                        <a class="pk-sop-row" href="#" data-search="penyusunan rencana anggaran">
                            <span class="pk-sop-number">01</span><span class="pk-sop-title">Penyusunan Rencana Anggaran</span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="rencana anggaran baseline se-sumut">
                            <span class="pk-sop-number">02</span><span class="pk-sop-title">Rencana Anggaran (Baseline)<span class="pk-sumut">se-Sumut</span></span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="penyusunan pagu indikatif">
                            <span class="pk-sop-number">03</span><span class="pk-sop-title">Penyusunan Pagu Indikatif</span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="pagu indikatif se-sumut">
                            <span class="pk-sop-number">04</span><span class="pk-sop-title">Pagu Indikatif<span class="pk-sumut">se-Sumut</span></span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="penyusunan pagu definitif">
                            <span class="pk-sop-number">05</span><span class="pk-sop-title">Penyusunan Pagu Definitif</span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="pagu definitif se-sumut">
                            <span class="pk-sop-number">06</span><span class="pk-sop-title">Pagu Definitif<span class="pk-sumut">se-Sumut</span></span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="anggaran biaya tambahan abt">
                            <span class="pk-sop-number">07</span><span class="pk-sop-title">Anggaran Biaya Tambahan (ABT)</span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="anggaran biaya tambahan abt se-sumut">
                            <span class="pk-sop-number">08</span><span class="pk-sop-title">Anggaran Biaya Tambahan (ABT)<span class="pk-sumut">se-Sumut</span></span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                    </div>

                    <div class="pk-sop-group">
                        <div class="pk-group-caption">
                            <span class="pk-group-title">Dokumen SAKIP</span>
                            <span class="pk-tag sakip">SAKIP</span>
                        </div>
                        <a class="pk-sop-row" href="#" data-search="permintaan dokumen sakip">
                            <span class="pk-sop-number">09</span><span class="pk-sop-title">Permintaan Dokumen SAKIP</span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="penyusunan dokumen sakip">
                            <span class="pk-sop-number">10</span><span class="pk-sop-title">Penyusunan Dokumen SAKIP</span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="penilaian evaluasi dokumen sakip se-sumut">
                            <span class="pk-sop-number">11</span><span class="pk-sop-title">Penilaian &amp; Evaluasi Dokumen SAKIP<span class="pk-sumut">se-Sumut</span></span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                    </div>

                    <div class="pk-sop-group">
                        <div class="pk-group-caption">
                            <span class="pk-group-title">Kinerja &amp; Monev</span>
                            <span class="pk-tag kinerja">Kinerja</span>
                        </div>
                        <a class="pk-sop-row" href="#" data-search="laporan e-monev bappenas">
                            <span class="pk-sop-number">12</span><span class="pk-sop-title">Laporan E-Monev Bappenas</span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="penyusunan program kerja">
                            <span class="pk-sop-number">13</span><span class="pk-sop-title">Penyusunan Program Kerja</span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                        <a class="pk-sop-row" href="#" data-search="pengukuran pengumpulan data kinerja">
                            <span class="pk-sop-number">14</span><span class="pk-sop-title">Pengukuran &amp; Pengumpulan Data Kinerja</span><span class="pk-leader"></span><span class="pk-file-type">PDF</span>
                        </a>
                    </div>
                </div>
                <div class="pk-empty" id="pk-sop-empty">Tidak ada prosedur yang cocok.</div>
            </section>

            <section class="pk-panel" id="pk-panel-jadwal" role="tabpanel">
                <div class="pk-panel-head">
                    <h2>Jadwal &amp; Arsip</h2>
                    <span class="pk-meta">Tahun 2025</span>
                </div>
                <p class="pk-panel-sub">Linimasa pelaksanaan kegiatan serta arsip lengkap seluruh berkas.</p>
                <div class="pk-closing">
                    <article class="pk-schedule-card">
                        <div class="pk-year pk-serif">2025</div>
                        <h3>Jadwal Kegiatan Tahun 2025</h3>
                        <p>Linimasa pelaksanaan kegiatan perencanaan program dan anggaran sepanjang tahun.</p>
                        <a class="pk-link" href="#" aria-label="Buka jadwal kegiatan 2025">Buka jadwal <span>&rarr;</span></a>
                    </article>
                    <article class="pk-drive-card">
                        <i class="ph-duotone ph-folder-open" aria-hidden="true"></i>
                        <h3>Folder Google Drive</h3>
                        <p>Seluruh berkas SOP dan program kerja tersimpan lengkap dan siap diunduh.</p>
                        <a class="pk-link" href="#" aria-label="Buka folder Google Drive">Buka folder <span>&rarr;</span></a>
                    </article>
                </div>
            </section>
        </div>
    </div>

    <div class="pk-footer-note">&copy; 2025 <b>Pengadilan Tinggi Agama Medan</b> - IKPA - Diperbarui 20 Juni 2024</div>
</div>

<script>
    (function () {
        const root = document.querySelector('.program-sop-page');
        if (!root) {
            return;
        }

        const tabs = root.querySelectorAll('.pk-tab');
        const panels = root.querySelectorAll('.pk-panel');
        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.panel;
                tabs.forEach((item) => {
                    const selected = item === tab;
                    item.classList.toggle('active', selected);
                    item.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
                panels.forEach((panel) => {
                    panel.classList.toggle('active', panel.id === 'pk-panel-' + target);
                });
            });
        });

        const search = root.querySelector('#pk-sop-search');
        const empty = root.querySelector('#pk-sop-empty');
        if (search && empty) {
            search.addEventListener('input', () => {
                const query = search.value.toLowerCase().trim();
                let visibleRows = 0;
                root.querySelectorAll('.pk-sop-group').forEach((group) => {
                    let visibleInGroup = 0;
                    group.querySelectorAll('.pk-sop-row').forEach((row) => {
                        const isMatch = !query || row.dataset.search.includes(query);
                        row.style.display = isMatch ? '' : 'none';
                        if (isMatch) {
                            visibleRows += 1;
                            visibleInGroup += 1;
                        }
                    });
                    group.style.display = visibleInGroup ? '' : 'none';
                });
                empty.style.display = query && visibleRows === 0 ? 'block' : 'none';
            });
        }
    }());
</script>
<?php
if (!defined('PROGRAM_KERJA_SOP_EMBEDDED')) {
    render_footer();
}
?>
