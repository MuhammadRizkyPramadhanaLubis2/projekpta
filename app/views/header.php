<?php
// Variabel ini biasanya disiapkan oleh render_header(). Nilai awal membuat
// partial tetap aman jika dibuka atau dianalisis secara terpisah oleh editor.
$title = isset($title) ? (string) $title : 'IKPA';

/** @var array{nama: string, role: string, unit: string, username: string}|null $user */
$user = $user ?? null;

/** @var array{type: string, message: string}|null $flash */
$flash = $flash ?? null;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> - IKPA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Lexend:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/app.css?v=<?= time() ?>">
</head>
<body>

<?php 
$isLogin = ($_GET['page'] ?? '') === 'login'; 
$currentPage = $_GET['page'] ?? 'beranda';
$isPublicPage = in_array($currentPage, ['beranda', 'portal', 'info'], true);
?>

<input type="checkbox" id="sidebar-toggle" class="sidebar-toggle" hidden>

<?php if (!$isLogin): ?>
<header class="app-header">
    <label for="sidebar-toggle" class="sidebar-open-btn-top">
        <i class="ph ph-list"></i>
    </label>
    <a href="index.php?page=beranda" class="brand">
        <img src="assets/logo_pta.png" alt="Logo PTA Medan">
        <div>
            <strong style="display: block; font-size: 1.1rem; color: #fff;" title="Aplikasi Kinerja Rencana Program Anggaran">IKPA</strong>
            <small style="color: #a7f3d0;">PTA Medan</small>
        </div>
    </a>
    <div class="header-right">
        <?php if ($user): ?>
            <div class="header-profile" tabindex="0">
                <div class="header-profile-text">
                    <strong><?= h($user['nama']) ?></strong>
                    <small><?= h(role_label((string) $user['role'])) ?></small>
                </div>
                <div class="header-profile-avatar">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <div class="header-dropdown">
                    <a href="index.php?page=logout" class="text-danger"><i class="ph ph-sign-out"></i> Keluar</a>
                </div>
            </div>
        <?php else: ?>
            <?php if (($_GET['page'] ?? '') !== 'login'): ?>
                <a href="index.php?page=login" class="button" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); color: #fff; box-shadow: none;">Masuk</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</header>


<aside class="sidebar">
    <nav class="nav">
        <?php if ($user): ?>
            <a href="index.php?page=dashboard" class="<?= ($_GET['page'] ?? '') === 'dashboard' ? 'active' : '' ?>">Menu Utama</a>
        <?php else: ?>
            <a href="index.php?page=beranda" class="<?= ($_GET['page'] ?? '') === 'beranda' ? 'active' : '' ?>">Beranda</a>
        <?php endif; ?>
        <span class="nav-heading">Kertas Kerja</span>
            <?php foreach (shared_workflow_groups() as $groupLabel => $items): ?>
                <?php
                $isGroupActive = false;
                foreach ($items as $item) {
                    [, $targetPage, $slug] = $item;
                    if (($_GET['page'] ?? '') === $targetPage && ($slug === null || ($_GET['slug'] ?? '') === $slug)) {
                        $isGroupActive = true;
                        break;
                    }

                    foreach (($item[3] ?? []) as [, $childTargetPage, $childSlug]) {
                        if (($_GET['page'] ?? '') === $childTargetPage && ($childSlug === null || ($_GET['slug'] ?? '') === $childSlug)) {
                            $isGroupActive = true;
                            break 2;
                        }
                    }
                }
                ?>
                <div class="nav-group <?= $isGroupActive ? 'open' : '' ?>">
                    <div class="nav-parent <?= $isGroupActive ? 'active' : '' ?>" onclick="this.closest('.nav-group').classList.toggle('open');">
                        <span class="nav-group-label"><?= h($groupLabel) ?></span>
                        <span class="nav-caret-toggle" aria-label="Buka menu <?= h($groupLabel) ?>">
                            <i class="ph-bold ph-caret-down nav-caret"></i>
                        </span>
                    </div>
                    <div class="nav-children-wrapper">
                        <div class="nav-children">
                            <?php foreach ($items as $item): ?>
                                <?php
                                [$label, $targetPage, $slug] = $item;
                                $children = $item[3] ?? [];
                                $url = 'index.php?page=' . urlencode((string) $targetPage);
                                if ($slug !== null) {
                                    $url .= '&slug=' . urlencode((string) $slug);
                                }
                                $isActive = ($_GET['page'] ?? '') === $targetPage && ($slug === null || ($_GET['slug'] ?? '') === $slug);
                                ?>
                                <?php if ($children): ?>
                                    <?php
                                    $isChildActive = false;
                                    foreach ($children as [, $childTargetPage, $childSlug]) {
                                        if (($_GET['page'] ?? '') === $childTargetPage && ($childSlug === null || ($_GET['slug'] ?? '') === $childSlug)) {
                                            $isChildActive = true;
                                            break;
                                        }
                                    }
                                    $isSubgroupOpen = $isActive || $isChildActive;
                                    ?>
                                    <div class="nav-subgroup <?= $isSubgroupOpen ? 'open' : '' ?>">
                                        <div class="nav-subgroup-parent <?= ($isActive || $isChildActive) ? 'active' : '' ?>">
                                            <a href="<?= h($url) ?>" class="<?= $isActive ? 'active' : '' ?>"><?= h((string) $label) ?></a>
                                            <span class="nav-subgroup-toggle" aria-label="Buka submenu <?= h((string) $label) ?>" onclick="this.closest('.nav-subgroup').classList.toggle('open');">
                                                <i class="ph-bold ph-caret-down nav-sub-caret"></i>
                                            </span>
                                        </div>
                                        <div class="nav-subchildren-wrapper">
                                            <div class="nav-subchildren">
                                                <?php foreach ($children as [$childLabel, $childTargetPage, $childSlug]): ?>
                                                    <?php
                                                    $childUrl = 'index.php?page=' . urlencode((string) $childTargetPage);
                                                    if ($childSlug !== null) {
                                                        $childUrl .= '&slug=' . urlencode((string) $childSlug);
                                                    }
                                                    $isChildItemActive = ($_GET['page'] ?? '') === $childTargetPage && ($childSlug === null || ($_GET['slug'] ?? '') === $childSlug);
                                                    ?>
                                                    <a href="<?= h($childUrl) ?>" class="<?= $isChildItemActive ? 'active' : '' ?>"><?= h((string) $childLabel) ?></a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <a href="<?= h($url) ?>" class="<?= $isActive ? 'active' : '' ?>"><?= h((string) $label) ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (user_can('manage_users')): ?>
                <span class="nav-heading">Administrasi</span>
                <a href="index.php?page=users" class="<?= ($_GET['page'] ?? '') === 'users' ? 'active' : '' ?>">Manajemen Pengguna</a>
            <?php endif; ?>
    </nav>

</aside>
<?php endif; ?>

<main class="shell <?= $isLogin ? 'login-shell-mode' : ($isPublicPage ? 'public-shell-mode' : '') ?> <?= defined('PORTAL_MODULE_PAGE') ? 'portal-module-shell' : '' ?>">

    <?php if ($user && !$isPublicPage && !defined('HIDE_PAGE_TOPBAR')): ?>
        <header class="topbar">
            <div>
                <h1><?= h($title) ?></h1>
                <p><?= h($user['unit']) ?> | <?= h(role_label((string) $user['role'])) ?> | <?= h($user['username']) ?></p>
            </div>
        </header>
    <?php endif; ?>

    <?php if ($flash): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: '<?= h($flash['type']) === 'error' ? 'error' : 'success' ?>',
                    title: <?= json_encode($flash['message']) ?>
                });
            });
        </script>
    <?php endif; ?>
