<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['admin']);

$stats = [
    'users' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'sellers' => (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn(),
    'buyers' => (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'buyer'")->fetchColumn(),
    'products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'sold_out' => (int) db()->query("SELECT COUNT(*) FROM products WHERE status = 'sold_out'")->fetchColumn(),
    'orders' => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'reviews' => (int) db()->query('SELECT COUNT(*) FROM reviews')->fetchColumn(),
];

$recentProducts = db()->query(
    'SELECT p.*, u.display_name
     FROM products p
     INNER JOIN users u ON u.id = p.seller_id
     ORDER BY p.created_at DESC
     LIMIT 6'
)->fetchAll();

$recentOrders = db()->query(
    'SELECT o.*, p.name AS product_name, p.group_name, p.member_name, b.display_name AS buyer_name, s.display_name AS seller_name
     FROM orders o
     INNER JOIN products p ON p.id = o.product_id
     INNER JOIN users b ON b.id = o.buyer_id
     INNER JOIN users s ON s.id = o.seller_id
     ORDER BY o.created_at DESC
     LIMIT 6'
)->fetchAll();

$pageTitle = '管理後台 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="panel">
    <span class="eyebrow">Admin overview</span>
    <h2>平台管理總覽</h2>
    <p class="muted">集中檢視會員、小卡、訂單、評價與 SOLD OUT 數量，方便快速確認平台運作狀況。</p>
    <div class="stat-grid">
        <article class="stat-card"><h3><?= $stats['users'] ?></h3><p>總會員數</p></article>
        <article class="stat-card"><h3><?= $stats['sellers'] ?></h3><p>賣家數</p></article>
        <article class="stat-card"><h3><?= $stats['buyers'] ?></h3><p>買家數</p></article>
        <article class="stat-card"><h3><?= $stats['products'] ?></h3><p>上架商品</p></article>
        <article class="stat-card"><h3><?= $stats['sold_out'] ?></h3><p>SOLD OUT</p></article>
        <article class="stat-card"><h3><?= $stats['orders'] ?></h3><p>訂單數</p></article>
        <article class="stat-card"><h3><?= $stats['reviews'] ?></h3><p>評價數</p></article>
    </div>
</section>

<section class="panel" style="margin-top: 24px;">
    <h2>最新商品</h2>
    <div class="review-list">
        <?php foreach ($recentProducts as $product): ?>
            <article class="review-card">
                <div class="badge-row" style="justify-content: space-between;">
                    <span class="badge"><?= h($product['group_name']) ?></span>
                    <span class="muted-small"><?= h($product['created_at']) ?></span>
                </div>
                <p><strong><?= h($product['name']) ?></strong></p>
                <p class="muted-small">賣家：<?= h($product['display_name']) ?> ｜ 狀態：<?= h(product_status_label((string) $product['status'], (int) $product['stock'])) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel" style="margin-top: 24px;">
    <h2>最新訂單</h2>
    <div class="review-list">
        <?php foreach ($recentOrders as $order): ?>
            <article class="review-card">
                <div class="badge-row" style="justify-content: space-between;">
                    <span class="badge"><?= h($order['group_name']) ?> <?= h($order['member_name']) ?></span>
                    <span class="muted-small"><?= h($order['created_at']) ?></span>
                </div>
                <p><strong><?= h($order['product_name']) ?></strong></p>
                <p class="muted-small">買家：<?= h($order['buyer_name']) ?> ｜ 賣家：<?= h($order['seller_name']) ?> ｜ 金額：<?= h(format_currency((float) $order['total_amount'])) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
