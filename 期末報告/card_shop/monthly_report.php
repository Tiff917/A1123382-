<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['seller', 'admin']);

$month = preg_match('/^\d{4}-\d{2}$/', (string) ($_GET['month'] ?? '')) ? (string) $_GET['month'] : date('Y-m');
$summary = monthly_sales_summary((int) current_user()['id'], $month);
$orders = seller_monthly_orders((int) current_user()['id'], $month);
$averageOrderValue = (int) $summary['total_orders'] > 0
    ? ((float) $summary['total_revenue'] / (int) $summary['total_orders'])
    : 0.0;

$pageTitle = '每月銷售 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <div class="section-head">
        <div>
            <h2 class="section-title">每月銷售</h2>
            <p class="muted"><?= h($month) ?> 的銷售整理已經幫你彙整好，可以直接查看或匯出 PDF。</p>
        </div>
        <a class="button secondary compact-button" href="monthly_report_pdf.php?month=<?= h($month) ?>">下載 PDF</a>
    </div>
</section>

<section class="app-section">
    <form method="get" class="filter-inline">
        <div class="field" style="margin-bottom: 0; flex: 1;">
            <div class="field-head">
                <label for="month">報表月份</label>
                <span class="field-hint">切換不同月份查看</span>
            </div>
            <select id="month" name="month">
                <?php foreach (month_options(12) as $value => $label): ?>
                    <option value="<?= h($value) ?>" <?= $month === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="compact-button">更新</button>
    </form>
</section>

<section class="app-section">
    <div class="simple-list">
        <div class="list-row">
            <div>
                <strong>訂單數</strong>
                <p class="muted-small">本月完成的交易筆數</p>
            </div>
            <strong><?= (int) $summary['total_orders'] ?> 筆</strong>
        </div>
        <div class="list-row">
            <div>
                <strong>售出張數</strong>
                <p class="muted-small">本月賣出的小卡總數</p>
            </div>
            <strong><?= (int) $summary['total_cards'] ?> 張</strong>
        </div>
        <div class="list-row">
            <div>
                <strong>營收</strong>
                <p class="muted-small">本月累積收入</p>
            </div>
            <strong><?= h(format_currency((float) $summary['total_revenue'])) ?></strong>
        </div>
        <div class="list-row">
            <div>
                <strong>平均客單</strong>
                <p class="muted-small">每筆訂單平均成交金額</p>
            </div>
            <strong><?= h(format_currency($averageOrderValue)) ?></strong>
        </div>
    </div>
</section>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">銷售明細</h3>
    </div>
    <?php if ($orders === []): ?>
        <p class="muted">這個月份目前還沒有賣出紀錄，你可以回賣家中心繼續上架新商品。</p>
        <div class="action-grid">
            <a class="button secondary action-chip" href="seller_dashboard.php">回賣家中心</a>
            <a class="button secondary action-chip" href="product_list.php">查看前台商品</a>
        </div>
    <?php else: ?>
        <div class="simple-list">
            <?php foreach ($orders as $order): ?>
                <article class="list-row">
                    <div>
                        <strong><?= h($order['product_name']) ?></strong>
                        <p class="muted-small"><?= h($order['group_name']) ?> ・ <?= h($order['member_name']) ?></p>
                        <p class="muted-small">買家：<?= h($order['buyer_name']) ?> ・ 數量：<?= (int) $order['quantity'] ?></p>
                    </div>
                    <div class="list-row-meta">
                        <strong><?= h(format_currency((float) $order['total_amount'])) ?></strong>
                        <span class="muted-small"><?= h((string) $order['created_at']) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">接下來</h3>
    </div>
    <div class="action-grid">
        <a class="button secondary action-chip" href="seller_dashboard.php">回賣家中心</a>
        <a class="button secondary action-chip" href="monthly_report_pdf.php?month=<?= h($month) ?>">匯出 PDF</a>
        <a class="button secondary action-chip" href="member_center.php">回會員中心</a>
        <a class="button secondary action-chip" href="index.php">回首頁</a>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
