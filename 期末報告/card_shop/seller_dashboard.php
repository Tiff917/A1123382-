<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';
require_login(['seller', 'admin']);

$productsStmt = db()->prepare(
    'SELECT
        p.*,
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
     FROM products p
     WHERE p.seller_id = :seller_id
     ORDER BY p.created_at DESC'
);
$productsStmt->execute(['seller_id' => current_user()['id']]);
$products = $productsStmt->fetchAll();

$currentMonth = date('Y-m');
$summary = monthly_sales_summary((int) current_user()['id'], $currentMonth);

$pageTitle = '賣家 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="app-section">
    <div class="section-head">
        <div>
            <h2>賣家</h2>
            <p class="muted">本月 <?= (int) $summary['total_orders'] ?> 筆 / <?= (int) $summary['total_cards'] ?> 張 / <?= h(format_currency((float) $summary['total_revenue'])) ?></p>
        </div>
        <a class="button secondary" href="monthly_report.php">月報</a>
    </div>
</section>

<section class="app-section">
    <h3 class="section-title">上架</h3>
    <form method="post" action="upload_product.php" enctype="multipart/form-data">
        <div class="field two-col">
            <div>
                <label for="group_name">團體</label>
                <select id="group_name" name="group_name" required>
                    <option value="">選擇團體</option>
                    <?php foreach (group_name_options() as $group): ?>
                        <option value="<?= h($group) ?>"><?= h($group) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="member_name">成員</label>
                <input id="member_name" name="member_name" placeholder="例如 Hanni">
            </div>
        </div>

        <div class="field two-col">
            <div>
                <label for="album_name">專輯</label>
                <input id="album_name" name="album_name" placeholder="例如 Get Up">
            </div>
            <div>
                <label for="card_version">版本</label>
                <input id="card_version" name="card_version" placeholder="例如 weverse">
            </div>
        </div>

        <div class="field two-col">
            <div>
                <label for="card_code">編號</label>
                <input id="card_code" name="card_code" placeholder="例如 NJ-HANNI-003">
            </div>
            <div>
                <label for="name">商品名</label>
                <input id="name" name="name" required>
            </div>
        </div>

        <div class="field">
            <label for="description">說明</label>
            <textarea id="description" name="description" required></textarea>
        </div>

        <div class="field two-col">
            <div>
                <label for="price">價格</label>
                <input id="price" type="number" name="price" min="1" required>
            </div>
            <div>
                <label for="stock">數量</label>
                <input id="stock" type="number" name="stock" min="0" required>
            </div>
        </div>

        <div class="field">
            <label for="condition_tags">卡況標籤</label>
            <input id="condition_tags" name="condition_tags" placeholder="全新, 微痕, 已拆">
        </div>

        <div class="field">
            <label for="images">圖片</label>
            <input id="images" type="file" name="images[]" multiple accept="image/jpeg,image/png" required>
        </div>

        <div id="preview" class="preview-grid"></div>
        <button type="submit">上架</button>
    </form>
</section>

<section class="app-section">
    <div class="section-head">
        <h3 class="section-title">我的商品</h3>
    </div>

    <?php if ($products === []): ?>
        <p class="muted">目前還沒有商品。</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-item">
                    <div class="product-cover">
                        <img src="<?= h(product_primary_image($product['primary_image'])) ?>" alt="<?= h($product['name']) ?>">
                    </div>
                    <div class="product-meta">
                        <div class="badge-row">
                            <span class="badge"><?= h($product['group_name']) ?></span>
                            <span class="badge <?= $product['status'] === 'sold_out' || (int) $product['stock'] <= 0 ? 'sold-out' : '' ?>">
                                <?= h(product_status_label((string) $product['status'], (int) $product['stock'])) ?>
                            </span>
                        </div>
                        <h4><?= h($product['name']) ?></h4>
                        <p class="muted-small"><?= h($product['member_name']) ?> ・ <?= h($product['card_version']) ?></p>
                        <p class="muted-small"><?= h(format_currency((float) $product['price'])) ?> ・ 庫存 <?= (int) $product['stock'] ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
const input = document.getElementById('images');
const preview = document.getElementById('preview');

if (input && preview) {
    input.addEventListener('change', () => {
        preview.innerHTML = '';
        Array.from(input.files).slice(0, 5).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (event) => {
                const box = document.createElement('div');
                box.className = 'preview-item';

                const img = document.createElement('img');
                img.src = event.target.result;
                img.alt = file.name;
                box.appendChild(img);

                const badge = document.createElement('span');
                badge.className = 'badge';
                badge.textContent = index === 0 ? '主圖' : `圖 ${index + 1}`;
                box.appendChild(badge);

                preview.appendChild(box);
            };
            reader.readAsDataURL(file);
        });
    });
}
</script>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
