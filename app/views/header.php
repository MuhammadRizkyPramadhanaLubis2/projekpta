<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> - Aplikasi IKPA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Lexend:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/app.css?v=<?= time() ?>">
</head>
<body>

<?php $isLogin = ($_GET['page'] ?? '') === 'login'; ?>

<input type="checkbox" id="sidebar-toggle" class="sidebar-toggle" hidden>

<?php if (!$isLogin): ?>
<header class="app-header">
    <label for="sidebar-toggle" class="sidebar-open-btn-top">
        <i class="ph ph-list"></i>
    </label>
    <a href="index.php?page=beranda" class="brand">
        <img src="assets/logo_pta.png" alt="Logo PTA Medan">
        <div>
            <strong style="display: block; font-size: 1.1rem; color: #fff;">Aplikasi IKPA</strong>
            <small style="color: #a7f3d0;">PTA Medan</small>
        </div>
    </a>
    <div class="header-right">
        <?php if ($user): ?>
            <div class="header-profile" tabindex="0">
                <div class="header-profile-text">
                    <strong><?= h($user['nama']) ?></strong>
                    <small><?= h($user['role']) ?></small>
                </div>
                <div class="header-profile-avatar">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <div class="header-dropdown">
                    <a href="index.php?page=logout" class="text-danger"><i class="ph ph-sign-out"></i> Logout</a>
                </div>
            </div>
        <?php else: ?>
            <?php if (($_GET['page'] ?? '') !== 'login'): ?>
                <a href="index.php?page=login" class="button" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); color: #fff; box-shadow: none;">Login</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</header>


<aside class="sidebar">
    <nav class="nav">
        <?php if (!$user): ?>
            <?php foreach (site_nav() as $item): ?>
                <?php if (!empty($item['children'])): ?>
                    <?php 
                    $isParentActive = (($_GET['page'] ?? '') === 'portal' && ($_GET['slug'] ?? '') === $item['slug']);
                    $isAnyChildActive = false;
                    foreach ($item['children'] as $child) {
                        if (($_GET['page'] ?? '') === 'portal' && ($_GET['slug'] ?? '') === $child['slug']) {
                            $isAnyChildActive = true;
                        }
                    }
                    $isOpen = $isParentActive || $isAnyChildActive;
                    $parentUrl = 'index.php?page=portal&slug=' . urlencode($item['slug']);
                    ?>
                    <div class="nav-group <?= $isOpen ? 'open' : '' ?>">
                        <div class="nav-parent <?= $isParentActive ? 'active' : '' ?>">
                            <a href="<?= h($parentUrl) ?>"><?= h($item['label']) ?></a>
                            <span class="nav-caret-toggle" onclick="this.closest('.nav-group').classList.toggle('open');">
                                <i class="ph-bold ph-caret-down nav-caret"></i>
                            </span>
                        </div>
                        <div class="nav-children-wrapper">
                            <div class="nav-children">
                                <?php foreach ($item['children'] as $child): ?>
                                    <?php 
                                    $childUrl = 'index.php?page=portal&slug=' . urlencode($child['slug']); 
                                    $isChildActive = ($_GET['page'] ?? '') === 'portal' && ($_GET['slug'] ?? '') === $child['slug'];
                                    ?>
                                    <a href="<?= h($childUrl) ?>" class="<?= $isChildActive ? 'active' : '' ?>"><?= h($child['label']) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php 
                    $url = $item['slug'] === 'beranda' ? 'index.php?page=beranda' : 'index.php?page=portal&slug=' . urlencode($item['slug']); 
                    $isActive = (($_GET['page'] ?? '') === 'portal' && ($_GET['slug'] ?? '') === $item['slug']) || (($_GET['page'] ?? 'beranda') === 'beranda' && $item['slug'] === 'beranda');
                    ?>
                    <a href="<?= h($url) ?>" class="<?= $isActive ? 'active' : '' ?>"><?= h($item['label']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <a href="index.php?page=beranda">Portal Publik</a>
            <a href="index.php?page=dashboard" class="<?= ($_GET['page'] ?? '') === 'dashboard' ? 'active' : '' ?>">Menu Utama</a>
            <a href="index.php?page=target" class="<?= ($_GET['page'] ?? '') === 'target' ? 'active' : '' ?>">Input Target Kinerja</a>
            <a href="index.php?page=pk" class="<?= ($_GET['page'] ?? '') === 'pk' ? 'active' : '' ?>">Perjanjian Kinerja</a>
            <a href="index.php?page=renaksi" class="<?= ($_GET['page'] ?? '') === 'renaksi' ? 'active' : '' ?>">Rencana Aksi</a>
            <a href="index.php?page=rkt_rka" class="<?= ($_GET['page'] ?? '') === 'rkt_rka' ? 'active' : '' ?>">RKT & RKA</a>
            <a href="index.php?page=capaian" class="<?= ($_GET['page'] ?? '') === 'capaian' ? 'active' : '' ?>">Hitung Capaian</a>
            <a href="index.php?page=evaluasi" class="<?= ($_GET['page'] ?? '') === 'evaluasi' ? 'active' : '' ?>">Evaluasi Kinerja</a>
        <?php endif; ?>
    </nav>

</aside>
<?php endif; ?>

<main class="shell <?= $isLogin ? 'login-shell-mode' : (!$user ? 'public-shell-mode' : '') ?>">

    <?php if ($user): ?>
        <header class="topbar">
            <div>
                <h1><?= h($title) ?></h1>
                <p><?= h($user['unit']) ?> | <?= h($user['username']) ?></p>
            </div>
        </header>
    <?php endif; ?>

    <?php if ($flash): ?>
        <div class="alert <?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>
