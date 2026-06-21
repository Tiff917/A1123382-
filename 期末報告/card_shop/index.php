<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

$stmt = db()->query(
    'SELECT
        p.*,
        u.display_name,
        u.id AS seller_user_id,
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
     FROM products p
     INNER JOIN users u ON u.id = p.seller_id
     WHERE p.id IN (1, 2)
     ORDER BY p.id ASC'
);
$products = array_map('normalize_product_display', $stmt->fetchAll());
$isBuyer = is_logged_in() && (current_user()['role'] ?? '') === 'buyer';

$pageTitle = APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section>
    <h2>T's cashop</h2>
    <p class="muted-small">首頁展示兩張真實拍攝的 TXT 小卡，現在可以直接加入購物車。</p>
</section>

<section class="products-grid">
    <?php foreach ($products as $product): ?>
        <article class="product-card">
            <a class="product-link" href="product.php?id=<?= (int) $product['id'] ?>">
                <div class="product-cover">
                    <img src="<?= h(product_primary_image($product['primary_image'])) ?>" alt="<?= h($product['name']) ?>">
                </div>
                <div class="product-meta">
                    <div class="badge-row">
                        <span class="badge"><?= h($product['group_name']) ?></span>
                        <span class="badge"><?= h(product_status_label((string) $product['status'], (int) $product['stock'])) ?></span>
                    </div>
                    <h3><?= h($product['name']) ?></h3>
                    <p class="muted-small"><?= h($product['member_name']) ?> ・ <?= h($product['card_version']) ?></p>
                    <div class="price"><?= h(format_currency((float) $product['price'])) ?></div>
                </div>
            </a>

            <div class="stack-row" style="margin-top: 14px;">
                <a class="button secondary" href="product.php?id=<?= (int) $product['id'] ?>">查看小卡</a>
                <?php if ($product['status'] === 'sold_out' || (int) $product['stock'] <= 0): ?>
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
        </article>
    <?php endforeach; ?>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
