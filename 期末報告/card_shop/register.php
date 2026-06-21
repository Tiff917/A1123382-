<?php
declare(strict_types=1);

require_once __DIR__ . '/check_remember.php';

if (is_logged_in()) {
    redirect('member_center.php');
}

$pageTitle = '註冊 | ' . APP_NAME;
require_once __DIR__ . '/partials/header.php';
?>
<section class="auth-block">
    <a class="back-link" href="signin.php">返回登入</a>
    <h2>註冊</h2>
    <p class="muted auth-copy">建立你的 T's cashop 帳號，之後就能收藏、下單、查看交易紀錄。</p>
    <form method="post" action="register_process.php" class="auth-form">
        <div class="field">
            <div class="field-head">
                <label for="display_name">暱稱</label>
                <span class="field-hint">目前正在輸入暱稱</span>
            </div>
            <input id="display_name" name="display_name" placeholder="想讓大家看到的名字" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="username">帳號</label>
                <span class="field-hint">目前正在輸入帳號</span>
            </div>
            <input id="username" name="username" placeholder="登入時會使用這個帳號" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="email">Email</label>
                <span class="field-hint">目前正在輸入 Email</span>
            </div>
            <input id="email" name="email" type="email" placeholder="用來接收訂單通知" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="phone">手機</label>
                <span class="field-hint">目前正在輸入手機</span>
            </div>
            <input id="phone" name="phone" placeholder="方便聯絡，可稍後再改">
        </div>
        <div class="field">
            <div class="field-head">
                <label for="favorite_group">常收團體</label>
                <span class="field-hint">目前正在輸入常收團體</span>
            </div>
            <input id="favorite_group" name="favorite_group" placeholder="例如 TXT">
        </div>
        <div class="field">
            <div class="field-head">
                <label for="address">收件地址</label>
                <span class="field-hint">目前正在輸入地址</span>
            </div>
            <input id="address" name="address" placeholder="可先留空，之後於會員中心補上">
        </div>
        <div class="field">
            <div class="field-head">
                <label for="role">身份</label>
                <span class="field-hint">目前正在選擇身份</span>
            </div>
            <select id="role" name="role" required>
                <option value="buyer">買家</option>
                <option value="seller">賣家</option>
            </select>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="password">密碼</label>
                <span class="field-hint">至少 6 個字元</span>
            </div>
            <input id="password" name="password" type="password" minlength="6" placeholder="請設定密碼" required>
        </div>
        <div class="field">
            <div class="field-head">
                <label for="password_confirm">確認密碼</label>
                <span class="field-hint">再次輸入密碼</span>
            </div>
            <input id="password_confirm" name="password_confirm" type="password" minlength="6" placeholder="再次輸入密碼" required>
        </div>
        <button type="submit">建立帳號</button>
    </form>
    <div class="auth-switch">
        <span>已經有帳號了？</span>
        <a href="signin.php">立即登入</a>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
