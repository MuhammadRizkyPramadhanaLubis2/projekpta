<?php
declare(strict_types=1);

// Fetch verified performance data from database
$pdo = db();
$tahun = year_value();
// Mengambil rata-rata realisasi dari seluruh user per indikator
$targets = db()->query("SELECT indikator,
    MAX(target) as target,
    AVG(real_tw1) as real_tw1, AVG(real_tw2) as real_tw2, AVG(real_tw3) as real_tw3, AVG(real_tw4) as real_tw4,
    AVG(target_tw1) as target_tw1, AVG(target_tw2) as target_tw2, AVG(target_tw3) as target_tw3, AVG(target_tw4) as target_tw4
    FROM target_kinerja WHERE tahun = $tahun GROUP BY indikator")->fetchAll(PDO::FETCH_ASSOC);

$iku1 = [0, 0, 0, 0];
$iku2 = [0, 0, 0, 0];
$anggaran = [0, 0, 0, 0];

$indicators = [];
$twData = [
    1 => ['target' => [], 'realisasi' => [], 'capaian' => []],
    2 => ['target' => [], 'realisasi' => [], 'capaian' => []],
    3 => ['target' => [], 'realisasi' => [], 'capaian' => []],
    4 => ['target' => [], 'realisasi' => [], 'capaian' => []]
];

foreach ($targets as $row) {
    if (strpos($row['indikator'], 'Tepat Waktu') !== false && strpos($row['indikator'], 'Kehadiran') === false) {
        $iku1 = [$row['real_tw1'], $row['real_tw2'], $row['real_tw3'], $row['real_tw4']];
    } elseif (strpos($row['indikator'], 'Kesalahan Administrasi') !== false) {
        $iku2 = [$row['real_tw1'], $row['real_tw2'], $row['real_tw3'], $row['real_tw4']];
    } elseif (strpos($row['indikator'], 'DIPA 01') !== false || strpos($row['indikator'], 'Anggaran DIPA') !== false) {
        $anggaran = [$row['real_tw1'], $row['real_tw2'], $row['real_tw3'], $row['real_tw4']];
    }

    $indText = (string) $row['indikator'];
    if (mb_strlen($indText) > 25) {
        $indText = mb_substr($indText, 0, 22) . '...';
    }
    $indicators[] = $indText;

    for ($i = 1; $i <= 4; $i++) {
        $targetVal = num($row['target_tw' . $i] ?? $row['target'] ?? 0);
        $realVal = num($row['real_tw' . $i] ?? 0);

        // Avoid using full achievement logic that requires 'tipe_indikator' for simple portal display
        // Just do standard calculation
        $achvVal = $targetVal > 0 ? min(120, ($realVal / $targetVal) * 100) : 0;

        $twData[$i]['target'][] = $targetVal;
        $twData[$i]['realisasi'][] = $realVal;
        $twData[$i]['capaian'][] = $achvVal;
    }
}

$activities = [
    ['icon' => 'ph-phone-call', 'title' => 'Rapat Pimpinan', 'desc' => 'Rapat evaluasi rutin pukul 10:00 WIB', 'color' => '#ef4444'],
    ['icon' => 'ph-ticket', 'title' => 'Pengajuan Revisi', 'desc' => 'Revisi DIPA diajukan ke Kanwil', 'color' => '#3b82f6'],
    ['icon' => 'ph-check-circle', 'title' => 'Penyelesaian Dokumen', 'desc' => 'Laporan triwulan selesai disusun', 'color' => '#10b981'],
    ['icon' => 'ph-envelope-simple', 'title' => 'Undangan Rapat', 'desc' => 'Undangan koordinasi Monev Bappenas', 'color' => '#8b5cf6'],
];

render_header('Diagram Hasil Capaian Kinerja');
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dashboard-container {
        padding: 0;
        background-color: transparent;
        font-family: 'Inter', sans-serif;
        font-style: normal;
        margin-top: 10px;
    }
    .dashboard-grid-top {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .dashboard-grid-bottom {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }
    .chart-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
    }
    .chart-card-header {
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .chart-wrapper {
        position: relative;
        flex-grow: 1;
        min-height: 250px;
    }
    .chart-wrapper-scrollable {
        overflow-x: auto;
        overflow-y: hidden;
        padding-bottom: 10px;
    }
    .chart-wrapper-scrollable .canvas-container {
        min-width: 1200px;
        position: relative;
        height: 350px;
    }
    .donut-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        min-height: 250px;
    }
    .donut-inner-text {
        position: absolute;
        text-align: center;
    }
    .donut-inner-text h3 {
        margin: 0;
        color: #10b981;
        font-size: 1.2rem;
        font-weight: 700;
    }
    .donut-inner-text p {
        margin: 0;
        font-size: 1.5rem;
        color: #10b981;
    }
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .activity-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 16px;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .activity-details h4 {
        margin: 0 0 4px 0;
        font-size: 0.95rem;
        color: #334155;
        font-weight: 700;
        font-style: normal;
    }
    .activity-details p {
        margin: 0;
        font-size: 0.8rem;
        color: #94a3b8;
        font-style: normal;
    }

    @media (max-width: 1024px) {
        .dashboard-grid-top, .dashboard-grid-bottom {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-grid-top">
        <div class="chart-card">
            <div class="chart-card-header">
                <span>Capaian Penyelesaian Perkara</span>
            </div>
            <div class="chart-wrapper">
                <canvas id="areaChart1"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <span>Capaian Kualitas Putusan</span>
            </div>
            <div class="chart-wrapper">
                <canvas id="areaChart2"></canvas>
            </div>
        </div>
    </div>

    <div class="dashboard-grid-bottom">
        <div class="chart-card">
            <div class="chart-card-header">
                <span>AKTIVITAS TERKINI</span>
                <i class="ph-bold ph-caret-down"></i>
            </div>
            <ul class="activity-list">
                <?php foreach ($activities as $act): ?>
                <li class="activity-item">
                    <div class="activity-icon" style="background-color: <?= h($act['color']) ?>;">
                        <i class="ph-fill <?= h($act['icon']) ?>"></i>
                    </div>
                    <div class="activity-details">
                        <h4><?= h($act['title']) ?></h4>
                        <p><?= h($act['desc']) ?></p>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="chart-card">
            <div class="chart-card-header">
                <span>RINGKASAN ANGGARAN</span>
                <i class="ph-bold ph-caret-down"></i>
            </div>
            <div class="donut-wrapper">
                <canvas id="donutChart"></canvas>
                <div class="donut-inner-text">
                    <h3>Anggaran</h3>
                    <p>27</p>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-card-header">
                <span>TREN KINERJA & ANGGARAN</span>
                <i class="ph-bold ph-caret-down"></i>
            </div>
            <div class="chart-wrapper">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>

    <div style="margin-top: 48px; margin-bottom: 24px;">
        <h3 style="color: #1e293b; font-size: 1.25rem; font-weight: bold; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Detail Capaian Seluruh Indikator Kinerja Per Triwulan</h3>
    </div>

    <?php for ($tw = 1; $tw <= 4; $tw++): ?>
        <div class="chart-card" style="margin-bottom: 24px;">
            <div class="chart-card-header" style="justify-content: center; font-size: 1rem; color: #0f766e;">
                <span>Capaian Kinerja Triwulan <?= array('', 'I', 'II', 'III', 'IV')[$tw] ?> <?= h((string)$tahun) ?></span>
            </div>
            <div class="chart-wrapper-scrollable">
                <div class="canvas-container" style="min-width: <?= count($indicators) * 150 ?>px;">
                    <canvas id="chartTw<?= $tw ?>"></canvas>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
Chart.register(ChartDataLabels);

const labelsAll = <?= json_encode($indicators) ?>;
const twDataAll = <?= json_encode($twData) ?>;

const detailOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } },
        datalabels: {
            anchor: 'end', align: 'top',
            formatter: function(value) { return Math.round(value * 100) / 100; },
            font: { weight: 'bold', size: 10 },
            color: function(context) { return context.dataset.backgroundColor; }
        }
    },
    scales: {
        y: { beginAtZero: true, max: 125, grid: { borderDash: [2, 4], color: '#f1f5f9' } },
        x: { grid: { display: false }, ticks: { maxRotation: 0, minRotation: 0, autoSkip: false, font: { size: 10 } } }
    },
    layout: { padding: { top: 20 } }
};

for(let tw = 1; tw <= 4; tw++) {
    new Chart(document.getElementById('chartTw' + tw).getContext('2d'), {
        type: 'bar',
        data: {
            labels: labelsAll,
            datasets: [
                { label: 'Target', data: twDataAll[tw].target, backgroundColor: '#0f766e', borderRadius: 2 },
                { label: 'Realisasi', data: twDataAll[tw].realisasi, backgroundColor: '#eab308', borderRadius: 2 },
                { label: 'Capaian', data: twDataAll[tw].capaian, backgroundColor: '#c2410c', borderRadius: 2 }
            ]
        },
        options: detailOptions
    });
}

// Data from PHP
const dataIKU1 = <?= json_encode($iku1) ?>;
const dataIKU2 = <?= json_encode($iku2) ?>;
const dataAnggaran = <?= json_encode($anggaran) ?>;
const labelsTW = ['Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'];

// Common Chart.js options
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.style = 'normal'; // Ensures no italics
Chart.defaults.color = '#94a3b8';
Chart.defaults.plugins.legend.display = false;
Chart.defaults.elements.line.tension = 0.4; // Smooth curves

const areaOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: { grid: { display: false } },
        y: {
            grid: { color: '#f1f5f9' },
            beginAtZero: true,
            max: 120
        }
    },
    plugins: {
        tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: 'rgba(255,255,255,0.9)',
            titleColor: '#334155',
            bodyColor: '#334155',
            borderColor: '#e2e8f0',
            borderWidth: 1
        }
    }
};

// Area Chart 1
new Chart(document.getElementById('areaChart1').getContext('2d'), {
    type: 'line',
    data: {
        labels: labelsTW,
        datasets: [{
            label: 'Realisasi (%)',
            data: dataIKU1,
            backgroundColor: 'rgba(52, 211, 153, 0.5)', // Greenish
            borderColor: 'rgba(52, 211, 153, 1)',
            fill: true,
        },
        {
            label: 'Target Dasar',
            data: [100, 100, 100, 100],
            backgroundColor: 'rgba(250, 204, 21, 0.3)', // Yellowish
            borderColor: 'rgba(250, 204, 21, 1)',
            fill: true,
        }]
    },
    options: areaOptions
});

// Area Chart 2
new Chart(document.getElementById('areaChart2').getContext('2d'), {
    type: 'line',
    data: {
        labels: labelsTW,
        datasets: [{
            label: 'Realisasi (%)',
            data: dataIKU2,
            backgroundColor: 'rgba(244, 63, 94, 0.4)', // Pink/Red
            borderColor: 'rgba(244, 63, 94, 1)',
            fill: true,
        },
        {
            label: 'Tren Optimal',
            data: [90, 95, 98, 100],
            backgroundColor: 'rgba(250, 204, 21, 0.4)', // Yellow
            borderColor: 'rgba(250, 204, 21, 1)',
            fill: true,
        }]
    },
    options: areaOptions
});

// Donut Chart
new Chart(document.getElementById('donutChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Realisasi', 'Sisa'],
        datasets: [{
            data: [dataAnggaran[3], 100 - dataAnggaran[3]],
            backgroundColor: ['#10b981', '#f43f5e', '#facc15', '#3b82f6'],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
            tooltip: { enabled: true }
        }
    }
});

// Line Chart
new Chart(document.getElementById('lineChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: labelsTW,
        datasets: [
            {
                label: 'IKU 01',
                data: dataIKU1,
                borderColor: '#0ea5e9',
                backgroundColor: '#0ea5e9',
                fill: false,
                borderWidth: 2
            },
            {
                label: 'IKU 02',
                data: dataIKU2,
                borderColor: '#facc15',
                backgroundColor: '#facc15',
                fill: false,
                borderWidth: 2
            },
            {
                label: 'Anggaran',
                data: dataAnggaran,
                borderColor: '#f43f5e',
                backgroundColor: '#f43f5e',
                fill: false,
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false } },
            y: { grid: { display: false } }
        },
        plugins: {
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(255,255,255,0.9)',
                titleColor: '#334155',
                bodyColor: '#334155',
                borderColor: '#e2e8f0',
                borderWidth: 1
            }
        }
    }
});
</script>

<?php render_footer(); ?>
