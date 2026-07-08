<?php
declare(strict_types=1);

function site_nav(): array
{
    return [
        ['label' => 'Beranda', 'slug' => 'beranda'],
        ['label' => 'Revisi', 'slug' => 'revisi'],
        ['label' => 'IFKIN', 'slug' => 'notifikasi'],
        ['label' => 'Program Kerja & SOP', 'slug' => 'program-kerja-sop'],
        [
            'label' => 'Penyusunan Anggaran',
            'slug' => 'penyusunan-anggaran',
            'children' => [
                ['label' => 'Baseline', 'slug' => 'baseline'],
                ['label' => 'Pagu Indikatif', 'slug' => 'pagu-indikatif'],
                ['label' => 'Pagu Definitif', 'slug' => 'pagu-definitif'],
                ['label' => 'ABT', 'slug' => 'abt'],
            ],
        ],
        ['label' => 'Hibah', 'slug' => 'hibah'],
        ['label' => 'Manajemen Risiko', 'slug' => 'manajemen-risiko'],
        [
            'label' => 'SAKIP',
            'slug' => 'sakip',
            'children' => [
                ['label' => 'SAKIP PTA Medan', 'slug' => 'sakip-pta-medan'],
                ['label' => 'SAKIP PA', 'slug' => 'sakip-pa'],
            ],
        ],
        ['label' => 'Evaluasi AKIP', 'slug' => 'evaluasi-akip'],
        ['label' => 'e-Monev Bappenas', 'slug' => 'e-monev-bappenas'],
        ['label' => 'Monev Capaian Kinerja', 'slug' => 'monev-capaian-kinerja'],
        [
            'label' => 'Tugas dan Fungsi',
            'slug' => 'tugas-dan-fungsi',
            'children' => [
                ['label' => 'Squad', 'slug' => 'squad'],
            ],
        ],
        ['label' => 'Pojok Baca', 'slug' => 'pojok-baca'],
    ];
}

function site_pages(): array
{
    $budgetLaw = [
        'Peraturan Pemerintah Nomor 6 Tahun 2023 tentang Penyusunan Rencana Kerja dan Anggaran.',
        'Peraturan Mahkamah Agung Nomor 7 Tahun 2015 tentang Organisasi dan Tata Kerja Kepaniteraan dan Kesekretariatan Peradilan.',
        'Peraturan Menteri Keuangan Nomor 62 Tahun 2023 tentang Perencanaan Anggaran, Pelaksanaan Anggaran, serta Akuntansi dan Pelaporan Keuangan.',
        'Peraturan Direktur Jenderal Anggaran Nomor PER-4/AG/2022 tentang petunjuk teknis penyusunan dan penelaahan RKA-K/L.',
    ];

    $sakipLaw = [
        'Peraturan Presiden Nomor 29 Tahun 2014 tentang Sistem Akuntabilitas Kinerja Pemerintah.',
        'Permen PANRB Nomor 88 Tahun 2021 tentang Evaluasi Akuntabilitas Kinerja Instansi Pemerintah.',
        'Keputusan Sekretaris Mahkamah Agung Nomor 2049 Tahun 2022 tentang Pedoman Pelaksanaan SAKIP.',
        'Keputusan Sekretaris Mahkamah Agung Nomor 878 Tahun 2022 tentang Pedoman Evaluasi AKIP.',
    ];

    return [
        'beranda' => [
            'title' => 'IKPA',
            'subtitle' => 'Informasi Kinerja Program dan Anggaran',
            'lead' => 'Pengadilan Tinggi Agama Medan - Sub Bagian Perencanaan Program dan Anggaran.',
            'body' => [
                'IKPA adalah sistem aplikasi yang membantu Sub Bagian Perencanaan Program dan Anggaran dan satuan kerja Pengadilan Agama dalam melayani tugas pokok dan fungsi penyusunan anggaran, penyusunan SAKIP, monitoring capaian kinerja, dan referensi proses pelaksanaan bidang program, akuntabilitas kinerja, serta evaluasi kinerja PTA Medan dan PA se-Sumatera Utara.',
            ],
            'cards' => [
                ['Program Kerja', 'program-kerja-sop'],
                ['Baseline', 'baseline'],
                ['Pagu Indikatif', 'pagu-indikatif'],
                ['Pagu Definitif', 'pagu-definitif'],
                ['Revisi Anggaran', 'revisi'],
                ['SAKIP', 'sakip'],
                ['Evaluasi AKIP', 'evaluasi-akip'],
                ['e-Monev Bappenas', 'e-monev-bappenas'],
                ['ABT', 'abt'],
                ['Hibah', 'hibah'],
                ['Manajemen Risiko', 'manajemen-risiko'],
                ['Pojok Baca', 'pojok-baca'],
            ],
        ],
        'revisi' => [
            'title' => 'Revisi',
            'subtitle' => 'Dasar hukum revisi anggaran',
            'list' => array_merge($budgetLaw, ['Juknis Penyusunan RKA Tahun 2024.']),
            'sections' => [
                ['title' => 'RKAKL DIPA 01 (401777)', 'iframe' => 'https://drive.google.com/embeddedfolderview?id=1SmCw8I1yE5mAVKJiZhBm0HBUGddO0aUQ#list', 'url' => 'https://drive.google.com/drive/folders/1SmCw8I1yE5mAVKJiZhBm0HBUGddO0aUQ'],
                ['title' => 'RKAKL DIPA 04 (401778)', 'iframe' => 'https://drive.google.com/embeddedfolderview?id=1QannGitiB_w1GCgon_IIsT0Aq7fJTlIe#list', 'url' => 'https://drive.google.com/drive/folders/1QannGitiB_w1GCgon_IIsT0Aq7fJTlIe'],
            ],
        ],
        'notifikasi' => [
            'title' => 'Informasi Kinerja',
            'subtitle' => 'Direktori website pemerintah dan peradilan',
            'body' => ['Rujukan cepat menuju sumber informasi kinerja, kebijakan, pembinaan, dan pengawasan lembaga peradilan.'],
        ],
        'program-kerja-sop' => [
            'title' => 'Program Kerja & SOP',
            'subtitle' => 'Dokumen kerja dan standar operasional prosedur',
            'sections' => [
                ['title' => 'Program Kerja', 'items' => ['Program Kerja PTA Medan 2024.', 'Program Kerja Tahun 2025.']],
                ['title' => 'Standard Operating Procedure (SOP)', 'items' => ['SOP Sub Bagian Rencana Program dan Anggaran PTA Medan.']],
                ['title' => 'Jadwal Kegiatan', 'items' => ['Jadwal pelaksanaan kegiatan Sub Bagian Perencanaan Program & Anggaran Tahun 2025.']],
            ],
        ],
        'penyusunan-anggaran' => [
            'title' => 'Penyusunan Anggaran',
            'subtitle' => 'Baseline, Pagu Indikatif, Pagu Definitif, dan ABT',
            'cards' => [
                ['Baseline', 'baseline'],
                ['Pagu Indikatif', 'pagu-indikatif'],
                ['Pagu Definitif', 'pagu-definitif'],
                ['ABT', 'abt'],
            ],
        ],
        'baseline' => [
            'title' => 'Usulan Anggaran',
            'subtitle' => 'Baseline',
            'lead' => 'Dasar hukum penyusunan rencana anggaran baseline.',
            'list' => $budgetLaw,
        ],
        'pagu-indikatif' => [
            'title' => 'Pagu Indikatif',
            'subtitle' => 'Dasar hukum penyusunan pagu indikatif',
            'list' => array_merge($budgetLaw, ['Juknis Penyusunan RKA Tahun 2024.']),
        ],
        'pagu-definitif' => [
            'title' => 'Pagu Definitif',
            'subtitle' => 'Dasar hukum penyusunan pagu definitif',
            'list' => array_merge($budgetLaw, ['Juknis Penyusunan RKA Tahun 2024.']),
        ],
        'abt' => [
            'title' => 'Anggaran Biaya Tambahan (ABT)',
            'subtitle' => 'Dasar hukum penyusunan ABT',
            'list' => $budgetLaw,
        ],
        'hibah' => [
            'title' => 'Hibah',
            'subtitle' => 'Dasar hukum hibah',
            'list' => [
                'Peraturan Menteri Keuangan RI Nomor 99 Tahun 2017 tentang Administrasi Pengelolaan Hibah.',
                'Peraturan Menteri Keuangan RI Nomor 182 Tahun 2017 tentang Pengelolaan Rekening Milik Satuan Kerja.',
                'Peraturan Menteri Keuangan RI Nomor 82 Tahun 2022 tentang perubahan pengelolaan hibah dari pemerintah pusat kepada pemerintah daerah.',
                'Peraturan Mahkamah Agung Nomor 5 Tahun 2022 tentang Administrasi Pengelolaan Hibah.',
            ],
        ],
        'manajemen-risiko' => [
            'title' => 'Manajemen Risiko',
            'subtitle' => 'Manajemen Risiko PTA Medan',
            'body' => [
                'Manajemen Risiko adalah proses yang sistematis dan terstruktur yang didukung oleh budaya, untuk mengidentifikasi dan mengelola risiko organisasi guna memberikan keyakinan yang memadai dalam pencapaian sasaran instansi.',
                'Penerapan Manajemen Risiko di Pengadilan Tinggi Agama Medan bertujuan untuk meminimalkan dampak negatif yang mungkin timbul serta mengoptimalkan setiap peluang yang ada demi terwujudnya visi dan misi peradilan yang agung.'
            ],
            'sections' => [
                ['title' => 'Buku Manajemen Risiko PTA Medan', 'iframe' => 'https://drive.google.com/file/d/1MPQ-gbT74GuHyMHkzxktr1Nlvtslg3bh/preview', 'url' => 'https://drive.google.com/file/d/1MPQ-gbT74GuHyMHkzxktr1Nlvtslg3bh/view', 'iframeHeight' => '650px'],
                ['title' => 'Folder Dokumen Manajemen Risiko', 'iframe' => 'https://drive.google.com/embeddedfolderview?id=1lTiQRjt1wsamN-0z_DDdhjSAC2nLjk_e#list', 'url' => 'https://drive.google.com/drive/folders/1lTiQRjt1wsamN-0z_DDdhjSAC2nLjk_e'],
                ['title' => 'Penyampaian Dokumen Manajemen Risiko', 'iframe' => 'https://docs.google.com/forms/d/e/1FAIpQLScCQ_4QF5XRACmrLC8l2HHyIj_XaTF4fXY6ATf_a3x6-ISiWA/viewform?embedded=true', 'url' => 'https://docs.google.com/forms/d/e/1FAIpQLScCQ_4QF5XRACmrLC8l2HHyIj_XaTF4fXY6ATf_a3x6-ISiWA/viewform', 'iframeHeight' => '650px'],
            ],
        ],
        'sakip' => [
            'title' => 'SAKIP',
            'subtitle' => 'Sistem Akuntabilitas Kinerja Instansi Pemerintah',
            'body' => [
                'SAKIP adalah rangkaian sistematik dari aktivitas, alat, dan prosedur untuk penetapan, pengukuran, pengumpulan data, pengklasifikasian, pengikhtisaran, dan pelaporan kinerja instansi pemerintah.',
                'Penyelenggaraan SAKIP terdiri atas perencanaan kinerja, pengukuran dan pengelolaan data kinerja, pelaporan kinerja, serta reviu dan evaluasi kinerja.',
            ],
            'cards' => [
                ['SAKIP PTA Medan', 'sakip-pta-medan'],
                ['SAKIP PA Sewilayah PTA Medan', 'sakip-pa'],
            ],
            'list' => $sakipLaw,
        ],
        'sakip-pta-medan' => [
            'title' => 'SAKIP PTA Medan',
            'subtitle' => 'Dokumen SAKIP Pengadilan Tinggi Agama Medan',
            'sections' => [
                ['title' => 'Tahun 2021-2025', 'items' => ['Rancangan Renstra 2025-2029.', 'Reviu Indikator Kinerja Utama.', 'Rencana Kinerja Tahun 2024.', 'Revisi Rencana Kinerja Tahun 2025.', 'Rencana Kinerja Tahun 2026.', 'Perjanjian Kinerja Tahun 2025.', 'Rencana Aksi Kinerja Tahun 2025.', 'Laporan Kinerja 2024.']],
            ],
        ],
        'sakip-pa' => [
            'title' => 'SAKIP PA',
            'subtitle' => 'Dasar hukum penyusunan dokumen SAKIP satuan kerja',
            'list' => $sakipLaw,
        ],
        'evaluasi-akip' => [
            'title' => 'Evaluasi AKIP',
            'subtitle' => 'Sistem Evaluasi & Monitoring Akuntabilitas Kinerja',
            'body' => [
                'Evaluasi Akuntabilitas Kinerja Instansi Pemerintah (AKIP) merupakan wujud nyata komitmen Mahkamah Agung RI dalam menerapkan manajemen berbasis kinerja yang transparan, efektif, dan akuntabel.',
                'Penguatan akuntabilitas kinerja merupakan salah satu strategi yang dilaksanakan dalam rangka mempercepat pelaksanaan Reformasi Birokrasi, untuk mewujudkan pemerintahan yang bersih dan akuntabel, pemerintahan yang kapabel, serta meningkatnya kualitas pelayanan publik kepada masyarakat. Sesuai Peraturan Presiden Nomor 29 Tahun 2014 tentang Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP), yang merupakan rangkaian sistematik dari berbagai aktivitas, alat, dan prosedur yang dirancang untuk tujuan penetapan dan pengukuran, pengumpulan data, pengklasifikasian, pengikhtisaran, dan pelaporan kinerja pada instansi pemerintah, dalam rangka pertanggungjawaban dan peningkatan kinerja instansi pemerintah.',
                'Untuk mengetahui sejauh mana implementasi SAKIP dilaksanakan, serta untuk mendorong peningkatan pencapaian kinerja yang tepat sasaran dan berorientasi hasil, maka perlu dilakukan evaluasi akuntabilitas kinerja.',
                'Evaluasi Akuntabilitas Kinerja pada Mahkamah Agung RI dan Badan Peradilan di bawahnya difokuskan untuk peningkatan mutu penerapan manajemen berbasis kinerja (Sistem AKIP) dalam rangka mewujudkan Unit Kerja Eselon I, Pengadilan Tingkat Banding dan Pengadilan Tingkat Pertama yang berorientasi pada hasil (result oriented government).',
                'Pelaksanaan evaluasi akuntabilitas kinerja harus dilakukan dengan sebaik-baiknya, secara efektif dan efisien. Untuk mendukung hal tersebut diperlukan suatu sistem aplikasi yang dapat meningkatkan kualitas pelaksanaan evaluasi akuntabilitas kinerja di lingkungan Badan Pengawasan Mahkamah Agung RI.',
                'Aplikasi Sistem Evaluasi Dan Monitoring Akuntabilitas Kinerja (SEMAR) merupakan sarana evaluasi secara elektronik bagi evaluator pada Badan Pengawasan Mahkamah Agung RI.'
            ],
            'list' => array_merge($sakipLaw, ['Keputusan Kepala Badan Pengawasan MARI Nomor 90 Tahun 2023 tentang penggunaan aplikasi SEMAR.']),
            'sections' => [
                [
                    'title' => 'Aplikasi SEMAR (Pengisian LKE AKIP)', 
                    'url' => 'https://bawasmari.mahkamahagung.go.id/seMAr/login', 
                    'description' => 'Akses sistem evaluasi elektronik untuk melakukan pengisian Lembar Kerja Evaluasi (LKE) AKIP secara terpusat.', 
                    'custom_icon' => '<div style="font-family: Arial, sans-serif; text-align: center; line-height: 1; display: flex; align-items: baseline; letter-spacing: -0.5px;"><span style="font-size: 23px; font-weight: 900; color: #f59e0b; font-style: italic; text-shadow: 1px 1px 0px rgba(0,0,0,0.1);">S</span><span style="font-size: 19px; font-weight: 800; color: #1e293b;">e</span><span style="font-size: 21px; font-weight: 900; color: #1e293b; margin-left: -1px;">M</span><span style="font-size: 19px; font-weight: 800; color: #1e293b;">A</span><span style="font-size: 19px; font-weight: 800; color: #1e293b;">r</span></div>'
                ],
                [
                    'title' => 'Laporan Hasil Evaluasi (LHE) PTA Medan', 
                    'iframe' => 'https://drive.google.com/embeddedfolderview?id=1lTiQRjt1wsamN-0z_DDdhjSAC2nLjk_e#list', // Placeholder reusing manajemen-risiko folder as example since none provided
                    'url' => 'https://drive.google.com/drive/folders/1lTiQRjt1wsamN-0z_DDdhjSAC2nLjk_e',
                    'iframeHeight' => '450px'
                ],
                [
                    'title' => 'Tindak Lanjut Hasil Evaluasi (TLHE) PTA Medan', 
                    'iframe' => 'https://drive.google.com/embeddedfolderview?id=1z7q2TTJTkcH8U6ldPJmQuOQW1jrmPA9t#list', // Placeholder reusing emonev folder as example since none provided
                    'url' => 'https://drive.google.com/drive/folders/1z7q2TTJTkcH8U6ldPJmQuOQW1jrmPA9t',
                    'iframeHeight' => '450px'
                ],
                [
                    'title' => 'Tindak Lanjut Hasil Evaluasi Satker', 
                    'iframe' => 'https://drive.google.com/embeddedfolderview?id=1RHZkGulgNTaVpZT3fhnw4p3mOihGmY3N#list', // Placeholder reusing another emonev folder since none provided
                    'url' => 'https://drive.google.com/drive/folders/1RHZkGulgNTaVpZT3fhnw4p3mOihGmY3N',
                    'iframeHeight' => '450px'
                ],
                [
                    'title' => 'Form Pelaporan TLHE Satuan Kerja', 
                    'url' => 'https://docs.google.com/forms/d/e/1FAIpQLScCQ_4QF5XRACmrLC8l2HHyIj_XaTF4fXY6ATf_a3x6-ISiWA/viewform', // Placeholder reusing manajemen-risiko form as example since none provided
                    'description' => 'Formulir online pelaporan Tindak Lanjut Hasil Evaluasi AKIP untuk seluruh Satuan Kerja wilayah hukum PTA Medan.', 
                    'custom_icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="50" height="50"><path fill="#673AB7" d="M12,44h24c2.209,0,4-1.791,4-4V16L28,4H12c-2.209,0-4,1.791-4,4v32C8,42.209,9.791,44,12,44z"></path><path fill="#5E35B1" d="M28,4v12h12L28,4z"></path><path fill="#FFF" d="M15 18H33V22H15zM15 26H33V30H15zM15 34H27V38H15z"></path></svg>'
                ]
            ],
        ],
        'e-monev-bappenas' => [
            'title' => 'e-Monev Bappenas',
            'subtitle' => 'Pelaporan e-Monev Bappenas PTA Medan',
            'sections' => [
                ['title' => 'Laporan 401777', 'iframe' => 'https://drive.google.com/embeddedfolderview?id=1z7q2TTJTkcH8U6ldPJmQuOQW1jrmPA9t#list', 'url' => 'https://drive.google.com/drive/folders/1z7q2TTJTkcH8U6ldPJmQuOQW1jrmPA9t'],
                ['title' => 'Laporan 401778', 'iframe' => 'https://drive.google.com/embeddedfolderview?id=1RHZkGulgNTaVpZT3fhnw4p3mOihGmY3N#list', 'url' => 'https://drive.google.com/drive/folders/1RHZkGulgNTaVpZT3fhnw4p3mOihGmY3N'],
                ['title' => 'Portal e-Monev Bappenas', 'url' => 'https://e-monev.bappenas.go.id/portal/masuk', 'description' => 'Akses langsung ke halaman portal sistem e-Monev Bappenas untuk melakukan pelaporan elektronik instansi secara terpusat.', 'icon' => 'ph-globe-hemisphere-west'],
            ],
            'list' => [
                'Peraturan Pemerintah Nomor 39 Tahun 2006 tentang Tata Cara Pengendalian dan Evaluasi Pelaksanaan Rencana Pembangunan.',
                'Peraturan Pemerintah Nomor 17 Tahun 2017 tentang Sinkronisasi Proses Perencanaan dan Penganggaran Pembangunan Nasional.',
                'Keputusan Sekretaris Mahkamah Agung Nomor 2049 Tahun 2022 tentang Pedoman Pelaksanaan SAKIP.',
            ],
        ],
        'monev-capaian-kinerja' => [
            'title' => 'Monev Capaian Kinerja',
            'subtitle' => 'Monitoring capaian kinerja per bulan dan per triwulan',
            'sections' => [
                [
                    'title' => 'Data Monev Capaian Kinerja PTA Medan', 
                    'iframe' => 'https://docs.google.com/spreadsheets/d/1hyK7zGeuAsCLbxI5-ysrT251zEmLcA6KLsD3l0Carlc/htmlembed?widget=true&headers=false', 
                    'url' => 'https://docs.google.com/spreadsheets/d/1hyK7zGeuAsCLbxI5-ysrT251zEmLcA6KLsD3l0Carlc/edit?usp=sharing',
                    'iframeHeight' => '650px'
                ],
                [
                    'title' => 'Monitoring Capaian Kinerja per Bulan PA se-Sumatera Utara',
                    'iframe' => 'https://docs.google.com/forms/d/e/1FAIpQLScTfNp_6XorSKN3FOQAZeayYMwh7WmRI6PXPUgN_EO1bbKWbA/viewform?embedded=true',
                    'url' => 'https://docs.google.com/forms/d/e/1FAIpQLScTfNp_6XorSKN3FOQAZeayYMwh7WmRI6PXPUgN_EO1bbKWbA/viewform',
                    'iframeHeight' => '650px'
                ]
            ],
        ],
        'tugas-dan-fungsi' => [
            'title' => 'Tugas dan Fungsi',
            'subtitle' => 'Sub Bagian Rencana Program dan Anggaran PTA Medan',
            'body' => [
                'Sub Bagian Perencanaan Program dan Anggaran mempunyai tugas melaksanakan penyiapan bahan perencanaan program dan anggaran, pelaksanaan program dan anggaran, pemantauan, evaluasi, dokumentasi, serta penyusunan laporan.',
                'Dasar: PERMA Nomor 7 Tahun 2015 Pasal 301.',
            ],
            'list' => [
                'Pelaksanaan dokumen usulan anggaran baseline.',
                'Penyusunan anggaran indikatif dan definitif/alokasi.',
                'Penyusunan Rencana Penggunaan Anggaran.',
                'Pengusulan revisi anggaran PTA Medan dan satker PA se-Sumatera Utara.',
                'Pengusulan dan monitoring ABT.',
                'Penyusunan laporan e-Monev Bappenas.',
                'Penyusunan dokumen SAKIP PTA Medan.',
                'Penyusunan LKE AKIP PTA Medan.',
                'Penelaahan dokumen SAKIP dan LKE AKIP satuan kerja.',
                'Pembinaan dan pengawasan ke Pengadilan Agama.',
                'Penyusunan Program Kerja.',
                'Pelaksanaan monev Zona Integritas Area IV.',
            ],
            'sections' => [
                ['title' => 'Pohon Kinerja PTA Medan', 'iframe' => 'https://drive.google.com/file/d/1TJ4-D7IvBbxl_k-3uho9PJ9Gbr9rQjsI/preview', 'url' => 'https://drive.google.com/file/d/1TJ4-D7IvBbxl_k-3uho9PJ9Gbr9rQjsI/view'],
            ],
        ],
        'squad' => [
            'title' => 'RPA Squad',
            'subtitle' => 'Tim Sub Bagian Rencana Program dan Anggaran',
            'sections' => [
                ['title' => 'Tim', 'items' => ['Muhammad Syahrur Ramadhan, S.H., M.H. - Kepala Sub Bagian Rencana Program dan Anggaran.', 'Sri Melda Sitorus, S.H. - Klerek/Penata Layanan Operasional.', 'Maulida Arumdhani, S.Sos. - Klerek/Penelaah Teknis Kebijakan.', 'Ika Nindya Kartika, S.E. - Perencana Ahli Pertama.', 'Syahlil Fadli, S.Kom. - Staf/Admin.']],
            ],
        ],
        'pojok-baca' => [
            'title' => 'Pojok Baca',
            'subtitle' => 'Kumpulan Regulasi dan Artikel',
            'body' => [
                'Halaman ini menyediakan kumpulan peraturan, petunjuk teknis, dan artikel terkait pelaksanaan tugas dan fungsi perencanaan program dan anggaran.'
            ],
            'sections' => [
                [
                    'title' => 'REGULASI', 
                    'iframe' => 'https://drive.google.com/embeddedfolderview?id=1DbB2N0JM2StiMUOwC6CVZySZdp-cn9kt#list', 
                    'url' => 'https://drive.google.com/drive/folders/1DbB2N0JM2StiMUOwC6CVZySZdp-cn9kt'
                ],
                [
                    'title' => 'ARTIKEL', 
                    'iframe' => 'https://drive.google.com/embeddedfolderview?id=1UgJyf2hNrbSJ1au274ohSC2ELb3NpAEN#list', 
                    'url' => 'https://drive.google.com/drive/folders/1UgJyf2hNrbSJ1au274ohSC2ELb3NpAEN'
                ],
            ],
        ],
    ];
}

function site_page(string $slug): ?array
{
    $pages = site_pages();
    return $pages[$slug] ?? null;
}
