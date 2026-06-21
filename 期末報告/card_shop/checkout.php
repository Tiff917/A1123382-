<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_once __DIR__ . '/mail_helpers.php';

require_login(['buyer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}

$checkoutMode = (string) ($_POST['checkout_mode'] ?? '');
$cartProducts = [];

if ($checkoutMode === 'cart') {
    $cartProducts = fetch_cart_products();
    if ($cartProducts === []) {
        set_flash('flash_error', '購物車是空的，先挑幾張喜歡的小卡再來結帳。');
        redirect('cart.php');
    }
} else {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

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
        set_flash('flash_error', '找不到這張小卡。');
        redirect('product_list.php');
    }

    $product['quantity'] = $quantity;
    $product['line_total'] = $quantity * (float) $product['price'];
    $cartProducts = [normalize_product_display($product)];
}

$pdo = db();
$pdo->beginTransaction();
$mailNotices = [];
$checkedOutProducts = [];
$grandTotal = 0.0;

try {
    foreach ($cartProducts as $product) {
        $productId = (int) $product['id'];
        $quantity = max(1, (int) ($product['quantity'] ?? 1));

        $stockStmt = $pdo->prepare(
            'SELECT p.*, u.display_name AS seller_name, u.email AS seller_email, u.phone AS seller_phone
             FROM products p
             INNER JOIN users u ON u.id = p.seller_id
             WHERE p.id = :id
             LIMIT 1'
        );
        $stockStmt->execute(['id' => $productId]);
        $liveProduct = $stockStmt->fetch();

        if (!$liveProduct || $liveProduct['status'] === 'sold_out' || (int) $liveProduct['stock'] < $quantity) {
            throw new RuntimeException('商品庫存不足或已售完。');
        }

        $liveProduct = normalize_product_display($liveProduct);
        $newStock = (int) $liveProduct['stock'] - $quantity;
        $newStatus = $newStock <= 0 ? 'sold_out' : 'active';
        $paidAt = date('Y-m-d H:i:s');
        $lineTotal = $quantity * (float) $liveProduct['price'];

        $orderStmt = $pdo->prepare(
            'INSERT INTO orders
                (product_id, buyer_id, seller_id, quantity, total_amount, status, created_at, paid_at)
             VALUES
                (:product_id, :buyer_id, :seller_id, :quantity, :total_amount, :status, NOW(), :paid_at)'
        );
        $orderStmt->execute([
            'product_id' => $productId,
            'buyer_id' => current_user()['id'],
            'seller_id' => $liveProduct['seller_id'],
            'quantity' => $quantity,
            'total_amount' => $lineTotal,
            'status' => 'paid',
            'paid_at' => $paidAt,
        ]);

        $orderId = (int) $pdo->lastInsertId();

        $updateStmt = $pdo->prepare(
            'UPDATE products
             SET stock = :stock,
                 status = :status,
                 updated_at = NOW(),
                 sold_at = :sold_at
             WHERE id = :id'
        );
        $updateStmt->execute([
            'stock' => $newStock,
            'status' => $newStatus,
            'sold_at' => $newStock <= 0 ? $paidAt : null,
            'id' => $productId,
        ]);

        $checkedOutProducts[] = [
            'order_id' => $orderId,
            'name' => $liveProduct['name'],
            'group_name' => $liveProduct['group_name'],
            'member_name' => $liveProduct['member_name'],
            'quantity' => $quantity,
            'total_amount' => $lineTotal,
        ];
        $grandTotal += $lineTotal;
    }

    $pdo->commit();

    $buyer = fetch_user_by_id((int) current_user()['id']);
    foreach ($checkedOutProducts as $index => $item) {
        $sourceProduct = $cartProducts[$index];
        $seller = fetch_user_by_id((int) $sourceProduct['seller_id']);
        if (!$buyer || !$seller) {
            continue;
        }

        $notice = send_order_notifications([
            'id' => $item['order_id'],
            'quantity' => $item['quantity'],
            'total_amount' => $item['total_amount'],
            'paid_at' => date('Y-m-d H:i:s'),
        ], $buyer, $seller, $sourceProduct);

        if ($notice) {
            $mailNotices[] = $notice;
        }
    }

    clear_cart();
} catch (Throwable $e) {
    $pdo->rollBack();
    set_flash('flash_error', '結帳時發生問題，請重新確認購物車後再試一次。');
    redirect('cart.php');
}

$pageTitle = '結帳完成 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <div class="section-head">
        <div>
            <h2 class="section-title">結帳完成</h2>
            <p class="muted">你的訂單已成立，現在可以回商品頁繼續逛，或前往會員中心查看紀錄。</p>
        </div>
        <span class="badge">已付款</span>
    </div>
</section>

<section class="app-section">
    <div class="simple-list">
        <?php foreach ($checkedOutProducts as $item): ?>
            <article class="list-row">
                <div>
                    <strong><?= h($item['name']) ?></strong>
                    <p class="muted-small"><?= h($item['group_name']) ?> ・ <?= h($item['member_name']) ?></p>
                    <p class="muted-small">訂單編號 #<?= (int) $item['order_id'] ?></p>
                </div>
                <div class="list-row-meta">
                    <span>x<?= (int) $item['quantity'] ?></span>
                    <strong><?= h(format_currency((float) $item['total_amount'])) ?></strong>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="app-section">
    <div class="simple-list">
        <div class="list-row">
            <strong>本次合計</strong>
            <strong><?= h(format_currency($grandTotal)) ?></strong>
        </div>
        <div class="list-row">
            <strong>通知狀態</strong>
            <span class="muted-small"><?= $mailNotices === [] ? '郵件通知正常處理中' : h(implode(' / ', $mailNotices)) ?></span>
        </div>
    </div>
</section>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">接下來</h3>
    </div>
    <div class="action-grid">
        <a class="button secondary action-chip" href="product_list.php">繼續購物</a>
        <a class="button secondary action-chip" href="cart.php">回購物車</a>
        <a class="button secondary action-chip" href="member_center.php">看訂單紀錄</a>
        <a class="button secondary action-chip" href="index.php">回首頁</a>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
