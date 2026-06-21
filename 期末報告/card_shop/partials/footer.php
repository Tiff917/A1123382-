    </main>
    <?php if (is_logged_in() && !in_array(basename($_SERVER['PHP_SELF']), ['signin.php', 'register.php'], true)): ?>
        <nav class="bottom-tabs">
            <a class="<?= nav_is_active('index.php') ? 'is-active' : '' ?>" href="index.php">
                <span>首頁</span>
            </a>
            <a class="<?= nav_is_active('product_list.php') || nav_is_active('product.php') ? 'is-active' : '' ?>" href="product_list.php">
                <span>商品</span>
            </a>
            <a class="<?= nav_is_active('cart.php') || nav_is_active('checkout.php') ? 'is-active' : '' ?>" href="cart.php">
                <span>購物車</span>
            </a>
            <a class="<?= nav_is_active('member_center.php') ? 'is-active' : '' ?>" href="member_center.php">
                <span>會員</span>
            </a>
        </nav>
    <?php endif; ?>
</body>
</html>
