<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

$productId = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare(
    'SELECT
        p.*,
        u.display_name,
        u.id AS seller_user_id,
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
     FROM products p
     INNER JOIN users u ON u.id = p.seller_id
     WHERE p.id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = '找不到商品 | ' . APP_NAME;
    require_once __DIR__ . '/partials/header.php';
    ?>
    <section class="app-section">
        <h2 class="section-title">找不到這張小卡</h2>
        <p class="muted-small">這張商品可能已下架，先回商品列表看看其他小卡。</p>
        <div class="stack-row" style="margin-top: 18px;">
            <a class="button secondary" href="product_list.php">返回商品列表</a>
        </div>
    </section>
    <?php
    require_once __DIR__ . '/partials/footer.php';
    exit;
}

$product = normalize_product_display($product);
$isBuyer = is_logged_in() && (current_user()['role'] ?? '') === 'buyer';
$isAvailable = $product['status'] !== 'sold_out' && (int) $product['stock'] > 0;

$pageTitle = $product['name'] . ' | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <div class="product-cover" style="aspect-ratio: 1 / 1.15; margin-bottom: 18px;">
        <img src="<?= h(product_primary_image($product['primary_image'])) ?>" alt="<?= h($product['name']) ?>">
    </div>
    <div class="badge-row">
        <span class="badge"><?= h($product['group_name']) ?></span>
        <span class="badge"><?= h(product_status_label((string) $product['status'], (int) $product['stock'])) ?></span>
    </div>
    <h2 class="section-title" style="margin-top: 14px;"><?= h($product['name']) ?></h2>
    <p class="muted-small"><?= h($product['member_name']) ?> ・ <?= h($product['card_version']) ?></p>
    <div class="price" style="margin-top: 10px;"><?= h(format_currency((float) $product['price'])) ?></div>
</section>

<section class="app-section">
    <div class="simple-list">
        <div class="list-row">
            <span>卡況</span>
            <strong><?= h($product['condition_tags']) ?></strong>
        </div>
        <div class="list-row">
            <span>專輯</span>
            <strong><?= h($product['album_name']) ?></strong>
        </div>
        <div class="list-row">
            <span>賣家</span>
            <strong><?= h($product['display_name']) ?></strong>
        </div>
        <div class="list-row">
            <span>庫存</span>
            <strong><?= max(0, (int) $product['stock']) ?></strong>
        </div>
    </div>

    <p class="muted-small" style="margin-top: 16px;"><?= h($product['description']) ?></p>

    <div class="stack-row" style="margin-top: 18px;">
        <a class="button secondary" href="product_list.php">返回繼續購物</a>
        <?php if (!$isAvailable): ?>
            <button type="button" disabled>SOLD OUT</button>
        <?php elseif ($isBuyer): ?>
            <form method="post" action="add_to_cart.php" style="flex: 1;">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="return_to" value="cart.php">
                <button type="submit">馬上加入購物車</button>
            </form>
        <?php else: ?>
            <a class="button" href="signin.php">登入後加入購物車</a>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
