<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['buyer']);

$orderId = (int) ($_GET['order_id'] ?? 0);
$stmt = db()->prepare(
    'SELECT o.*, p.name AS product_name, p.group_name, p.member_name, u.display_name AS seller_name
     FROM orders o
     INNER JOIN products p ON p.id = o.product_id
     INNER JOIN users u ON u.id = o.seller_id
     WHERE o.id = :id AND o.buyer_id = :buyer_id
     LIMIT 1'
);
$stmt->execute([
    'id' => $orderId,
    'buyer_id' => current_user()['id'],
]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('flash_error', '找不到可評價的訂單。');
    redirect('member_center.php');
}

$pageTitle = '評價 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <h2>評價</h2>
    <p class="muted"><?= h($order['product_name']) ?> ・ <?= h($order['seller_name']) ?></p>

    <form method="post" action="add_review.php">
        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">

        <div class="field">
            <label for="rating">星等</label>
            <select id="rating" name="rating" required>
                <option value="">選擇星等</option>
                <option value="5">5 星</option>
                <option value="4">4 星</option>
                <option value="3">3 星</option>
                <option value="2">2 星</option>
                <option value="1">1 星</option>
            </select>
        </div>

        <div class="field">
            <label for="comment">留言</label>
            <textarea id="comment" name="comment" maxlength="500" required></textarea>
        </div>

        <button type="submit">送出</button>
    </form>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
