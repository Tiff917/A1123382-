<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

$sellerId = (int) ($_GET['seller_id'] ?? 0);
if ($sellerId <= 0) {
    set_flash('flash_error', '找不到賣家資料。');
    redirect('index.php');
}

$sellerStmt = db()->prepare(
    "SELECT
        u.*,
        COUNT(DISTINCT p.id) AS total_products,
        COUNT(DISTINCT r.id) AS total_reviews,
        AVG(r.rating) AS avg_rating
     FROM users u
     LEFT JOIN products p ON p.seller_id = u.id
     LEFT JOIN reviews r ON r.seller_id = u.id
     WHERE u.id = :id AND u.role IN ('seller', 'admin')
     GROUP BY u.id
     LIMIT 1"
);
$sellerStmt->execute(['id' => $sellerId]);
$seller = $sellerStmt->fetch();

if (!$seller) {
    set_flash('flash_error', '找不到賣家資料。');
    redirect('index.php');
}

$productsStmt = db()->prepare(
    'SELECT
        p.*,
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
     FROM products p
     WHERE p.seller_id = :seller_id
     ORDER BY p.created_at DESC'
);
$productsStmt->execute(['seller_id' => $sellerId]);
$products = $productsStmt->fetchAll();

$reviewsStmt = db()->prepare(
    'SELECT r.*, u.display_name AS buyer_name
     FROM reviews r
     INNER JOIN users u ON u.id = r.buyer_id
     WHERE r.seller_id = :seller_id
     ORDER BY r.created_at DESC'
);
$reviewsStmt->execute(['seller_id' => $sellerId]);
$reviews = $reviewsStmt->fetchAll();

$pageTitle = '賣家主頁 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="panel">
    <div class="seller-header">
        <div>
            <span class="eyebrow">Seller profile</span>
            <h2><?= h($seller['display_name']) ?> 的賣場</h2>
            <p class="muted">平均評分：<?= h(stars_label((float) ($seller['avg_rating'] ?? 0))) ?> ｜ 評價數：<?= (int) $seller['total_reviews'] ?> ｜ 商品數：<?= (int) $seller['total_products'] ?></p>
            <div class="badge-row">
                <?php if (($seller['favorite_group'] ?? '') !== ''): ?>
                    <span class="badge">喜歡團體：<?= h($seller['favorite_group']) ?></span>
                <?php endif; ?>
                <?php if (($seller['phone'] ?? '') !== ''): ?>
                    <span class="badge">聯絡手機：<?= h($seller['phone']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="chart-box">
            <img src="gd_chart.php?seller_id=<?= (int) $sellerId ?>" alt="賣家星等圖表">
        </div>
    </div>
</section>

<section class="panel" style="margin-top: 24px;">
    <h2>賣家商品</h2>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <article class="product-card">
                <div class="product-cover">
                    <img src="<?= h(product_primary_image($product['primary_image'])) ?>" alt="<?= h($product['name']) ?>">
                </div>
                <div class="badge-row">
                    <span class="badge <?= $product['status'] === 'sold_out' || (int) $product['stock'] <= 0 ? 'sold-out' : '' ?>">
                        <?= h(product_status_label((string) $product['status'], (int) $product['stock'])) ?>
                    </span>
                    <span class="badge"><?= h($product['group_name']) ?></span>
                </div>
                <h3><?= h($product['name']) ?></h3>
                <p class="muted-small"><?= h($product['member_name']) ?> ｜ <?= h($product['album_name']) ?> ｜ <?= h($product['card_version']) ?></p>
                <p class="muted-small">價格：<?= h(format_currency((float) $product['price'])) ?> ｜ 庫存：<?= (int) $product['stock'] ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel" style="margin-top: 24px;">
    <h2>買家評價</h2>
    <div class="review-list">
        <?php if ($reviews === []): ?>
            <p class="muted">目前還沒有評價，完成交易後就會顯示在這裡。</p>
        <?php endif; ?>
        <?php foreach ($reviews as $review): ?>
            <article class="review-card">
                <div class="badge-row" style="justify-content: space-between;">
                    <span class="badge"><?= str_repeat('★', (int) $review['rating']) ?></span>
                    <span class="muted-small"><?= h($review['created_at']) ?></span>
                </div>
                <p><?= nl2br(h($review['comment'])) ?></p>
                <p class="muted-small">來自買家：<?= h($review['buyer_name']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
