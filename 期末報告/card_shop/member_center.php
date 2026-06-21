<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login();

$sessionUser = current_user();
$user = fetch_user_by_id($sessionUser['id']);
$role = (string) ($user['role'] ?? 'buyer');

$orders = [];
if ($role === 'buyer') {
    $ordersStmt = db()->prepare(
        'SELECT o.*, p.name AS product_name
         FROM orders o
         INNER JOIN products p ON p.id = o.product_id
         WHERE o.buyer_id = :buyer_id
         ORDER BY o.created_at DESC'
    );
    $ordersStmt->execute(['buyer_id' => $sessionUser['id']]);
    $orders = $ordersStmt->fetchAll();
}

$pageTitle = '會員中心 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <div class="section-head">
        <div>
            <h2 class="section-title"><?= h($user['display_name']) ?></h2>
            <p class="muted-small">@<?= h($user['username']) ?></p>
        </div>
        <span class="badge"><?= h($role === 'seller' ? '賣家模式' : ($role === 'admin' ? '管理員模式' : '買家模式')) ?></span>
    </div>
    <p class="muted">這裡可以接續你的下一步：逛商品、查看購物車、整理會員資料，或回到賣家功能。</p>
</section>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">快速操作</h3>
    </div>
    <div class="action-grid">
        <a class="button secondary action-chip" href="index.php">回首頁</a>
        <a class="button secondary action-chip" href="product_list.php">逛商品</a>
        <a class="button secondary action-chip" href="cart.php">前往購物車</a>
        <?php if ($role === 'seller' || $role === 'admin'): ?>
            <a class="button secondary action-chip" href="seller_dashboard.php">賣家中心</a>
            <a class="button secondary action-chip" href="monthly_report.php">每月報表</a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
            <a class="button secondary action-chip" href="admin_dashboard.php">管理後台</a>
        <?php endif; ?>
    </div>
</section>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">會員資料</h3>
    </div>
    <form method="post" action="update_profile.php">
        <div class="field">
            <div class="field-head">
                <label for="display_name">暱稱</label>
                <span class="field-hint">目前正在輸入暱稱</span>
            </div>
            <input id="display_name" name="display_name" value="<?= h($user['display_name']) ?>" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="email">Email</label>
                <span class="field-hint">目前正在輸入 Email</span>
            </div>
            <input id="email" name="email" type="email" value="<?= h($user['email']) ?>" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="phone">手機</label>
                <span class="field-hint">目前正在輸入手機</span>
            </div>
            <input id="phone" name="phone" value="<?= h($user['phone'] ?? '') ?>">
        </div>
        <div class="field">
            <div class="field-head">
                <label for="favorite_group">常收團體</label>
                <span class="field-hint">目前正在輸入常收團體</span>
            </div>
            <input id="favorite_group" name="favorite_group" value="<?= h($user['favorite_group'] ?? '') ?>">
        </div>
        <div class="field">
            <div class="field-head">
                <label for="address">收件地址</label>
                <span class="field-hint">目前正在輸入地址</span>
            </div>
            <input id="address" name="address" value="<?= h($user['address'] ?? '') ?>">
        </div>
        <div class="field">
            <div class="field-head">
                <label for="new_password">新密碼</label>
                <span class="field-hint">留空就不修改</span>
            </div>
            <input id="new_password" name="new_password" type="password" minlength="6" placeholder="想換密碼時再輸入">
        </div>
        <button type="submit">儲存會員資料</button>
    </form>
</section>

<?php if ($role === 'buyer'): ?>
<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">最近訂單</h3>
    </div>
    <?php if ($orders === []): ?>
        <p class="muted">你還沒有訂單，現在可以先去逛商品把喜歡的小卡加入購物車。</p>
        <a class="button secondary" href="product_list.php">馬上去逛</a>
    <?php else: ?>
        <div class="simple-list">
            <?php foreach ($orders as $order): ?>
                <article class="list-row">
                    <div>
                        <strong><?= h($order['product_name']) ?></strong>
                        <p class="muted-small">下單時間 <?= h((string) $order['created_at']) ?></p>
                    </div>
                    <div class="list-row-meta">
                        <strong><?= h(format_currency((float) $order['total_amount'])) ?></strong>
                        <a class="button secondary compact-button" href="review.php?order_id=<?= (int) $order['id'] ?>">前往評價</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">帳號操作</h3>
    </div>
    <a class="button secondary" href="signout.php">登出</a>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
