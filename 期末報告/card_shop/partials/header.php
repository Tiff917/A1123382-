<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';
$currentPage = basename($_SERVER['PHP_SELF']);
$isAuthPage = in_array($currentPage, ['signin.php', 'register.php'], true);
$showBottomTabs = !$isAuthPage && is_logged_in();
$currentRole = (string) (current_user()['role'] ?? '');
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#f5efe8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="T's cashop">
    <title><?= h($pageTitle ?? APP_NAME) ?></title>
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="apple-touch-icon" href="assets/app-icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/app-icon-192.png">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= $isAuthPage ? 'auth-page' : 'app-page' ?>">
<header class="site-header <?= $isAuthPage ? 'compact-header' : '' ?>">
    <a class="brand" href="<?= is_logged_in() ? 'index.php' : 'signin.php' ?>">T's cashop</a>
    <?php if (!$isAuthPage && $currentRole === 'seller'): ?>
        <a class="top-link" href="seller_dashboard.php">賣家中心</a>
    <?php elseif (!$isAuthPage && $currentRole === 'admin'): ?>
        <a class="top-link" href="admin_dashboard.php">管理後台</a>
    <?php elseif (!$isAuthPage && !is_logged_in()): ?>
        <a class="top-link" href="register.php">註冊</a>
    <?php endif; ?>
</header>
<main class="page-shell <?= $isAuthPage ? 'auth-shell' : '' ?> <?= $showBottomTabs ? 'with-tabs' : '' ?>">
<?php if ($flash = pop_flash('flash_success')): ?>
    <div class="flash flash-success"><?= h($flash) ?></div>
<?php endif; ?>
<?php if ($flash = pop_flash('flash_error')): ?>
    <div class="flash flash-error"><?= h($flash) ?></div>
<?php endif; ?>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js').catch(() => {});
    });
}
</script>
