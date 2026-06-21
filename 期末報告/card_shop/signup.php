<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>會員註冊 | Card Shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root { --label-width:80px; --input-width:260px; }
        .fc { width:calc(var(--label-width)+var(--input-width)+35px); margin:0 auto; }
        .fi { display:flex; align-items:center; margin-bottom:14px; }
        .fi label { width:var(--label-width); font-size:.93rem; font-weight:600; margin-right:15px; flex-shrink:0; }
        .fi input, .fi select { flex:1; max-width:var(--input-width); height:40px; margin-bottom:0!important; }
        .checkbox-group { flex:1; max-width:var(--input-width); display:flex; flex-wrap:wrap; gap:8px; }
        .chip { display:flex; align-items:center; gap:5px; background:var(--secondary-color); border-radius:20px; padding:5px 12px; cursor:pointer; font-size:.83rem; }
        .chip input { width:14px; height:14px; margin:0; flex-shrink:0; }
        .role-sel { display:flex; gap:20px; flex:1; max-width:var(--input-width); }
        .role-sel label { display:flex; align-items:center; gap:6px; font-weight:normal; cursor:pointer; width:auto; }
        .btn-r { display:flex; justify-content:flex-end; margin-top:10px; }
        .btn-r button { width:var(--input-width); }
    </style>
</head>
<body>
<div class="container" style="max-width:500px;">
    <h2>📝 會員註冊</h2>
    <div class="fc">
    <form action="signup_process.php" method="POST">
        <div class="fi"><label>帳號</label><input type="text" name="username" placeholder="登入帳號" required></div>
        <div class="fi"><label>密碼</label><input type="password" name="password" placeholder="至少6位" required></div>
        <div class="fi"><label>姓名</label><input type="text" name="real_name" placeholder="真實姓名" required></div>
        <div class="fi"><label>Email</label><input type="email" name="email" placeholder="email@example.com" required></div>
        <div class="fi"><label>生日</label><input type="date" name="birthday" required></div>
        <div class="fi" style="align-items:flex-start;">
            <label style="padding-top:6px;">身分</label>
            <div class="role-sel">
                <label><input type="radio" name="role" value="buyer" checked> 買家</label>
                <label><input type="radio" name="role" value="seller"> 賣家</label>
            </div>
        </div>
        <div class="fi" style="align-items:flex-start;">
            <label style="padding-top:5px;">關注團體</label>
            <div class="checkbox-group">
                <?php foreach(['SEVENTEEN','TXT','BTS','TWICE','NewJeans'] as $g): ?>
                <label class="chip">
                    <input type="checkbox" name="fav[]" value="<?php echo $g; ?>"> <?php echo $g; ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="btn-r"><button type="submit">確認註冊</button></div>
        <p style="text-align:right; margin-top:10px; font-size:.88rem;">已有帳號？<a href="signin.php">立即登入</a></p>
    </form>
    </div>
</div>
</body>
</html>
