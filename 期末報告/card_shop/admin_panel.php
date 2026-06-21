<?php
session_start(); include 'db_config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('此頁面僅限管理員'); window.location.href='index.php';</script>"; exit();
}

$tab = $_GET['tab'] ?? 'orders';

// 訂單資料
$orders = [];
if ($tab === 'orders') {
    $res = $conn->query("SELECT o.id, o.total_price, o.created_at, u.username AS buyer_name, COUNT(oi.id) AS item_count FROM orders o JOIN users u ON o.buyer_id=u.id JOIN order_items oi ON oi.order_id=o.id GROUP BY o.id ORDER BY o.created_at DESC");
    while ($r = $res->fetch_assoc()) $orders[] = $r;
}
// 訂單明細
$detail = [];
if ($tab === 'orders' && isset($_GET['order_id'])) {
    $oid = intval($_GET['order_id']);
    $stmt = $conn->prepare("SELECT oi.product_name, oi.price, oi.quantity, p.image_path, p.group_name FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");
    $stmt->bind_param("i",$oid); $stmt->execute();
    $detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
// 商品資料
$products = [];
if ($tab === 'products') {
    $kw = trim($_GET['q'] ?? '');
    if ($kw) {
        $like = "%$kw%";
        $stmt = $conn->prepare("SELECT p.*, u.username AS seller_name FROM products p JOIN users u ON p.seller_id=u.id WHERE p.name LIKE ? OR p.group_name LIKE ? ORDER BY p.created_at DESC");
        $stmt->bind_param("ss",$like,$like); $stmt->execute(); $res = $stmt->get_result();
    } else {
        $res = $conn->query("SELECT p.*, u.username AS seller_name FROM products p JOIN users u ON p.seller_id=u.id ORDER BY p.created_at DESC");
    }
    while ($r = $res->fetch_assoc()) $products[] = $r;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8"><title>管理員後台 | Card Shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display:block; background:var(--bg-color); }
        .wrap { max-width:1100px; margin:30px auto; padding:0 20px; }
        .hdr { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; }
        .hdr h1 { font-size:1.5rem; color:var(--primary-color); margin:0; }
        .tabs { display:flex; gap:10px; margin-bottom:22px; border-bottom:2px solid var(--secondary-color); padding-bottom:10px; }
        .tabs a { text-decoration:none; padding:8px 22px; border-radius:8px 8px 0 0; font-weight:600; font-size:.93rem; color:var(--text-color); background:var(--secondary-color); transition:.2s; }
        .tabs a.on, .tabs a:hover { background:var(--primary-color); color:#fff; }
        table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(194,163,142,.15); font-size:.88rem; }
        th { background:var(--secondary-color); padding:12px 14px; text-align:left; font-weight:700; }
        td { padding:10px 14px; border-bottom:1px solid var(--bg-color); vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:#fdf9f5; }
        .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.76rem; font-weight:bold; }
        .b-ok { background:#d4edda; color:#155724; } .b-sold { background:#f8d7da; color:#721c24; } .b-off { background:#e2e3e5; color:#383d41; }
        .btn { display:inline-block; padding:5px 12px; border:none; border-radius:6px; font-size:.8rem; font-weight:bold; cursor:pointer; text-decoration:none; transition:.2s; width:auto; margin-top:0; }
        .btn-p { background:var(--primary-color); color:#fff; } .btn-p:hover { background:var(--accent-color); }
        .btn-d { background:#e9534f; color:#fff; } .btn-d:hover { background:#c0392b; }
        .btn-s { background:#27ae60; color:#fff; } .btn-s:hover { background:#1e8449; }
        .btn-o { background:#e67e22; color:#fff; }
        .if { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
        .if input[type=number] { width:80px; height:30px; padding:0 8px; border:1px solid var(--secondary-color); border-radius:6px; font-size:.85rem; }
        .detail-box { background:#fff; border:1px solid var(--secondary-color); border-radius:12px; padding:20px; margin-top:18px; }
        .detail-box h3 { color:var(--primary-color); margin-top:0; }
        .search-bar { display:flex; gap:10px; margin-bottom:16px; }
        .search-bar input { height:36px; padding:0 12px; border:1px solid var(--secondary-color); border-radius:8px; width:260px; }
        .thumb { width:38px; height:51px; object-fit:cover; border-radius:4px; }
        .notice { padding:10px 16px; border-radius:8px; margin-bottom:16px; font-size:.92rem; }
        .n-ok { background:#d4edda; color:#155724; } .n-err { background:#f8d7da; color:#721c24; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hdr">
        <h1>🔑 管理員後台</h1>
        <div>
            <span style="margin-right:15px; font-size:.9rem;">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="index.php" class="btn btn-p">← 回賣場</a>
            <a href="logout.php" class="btn" style="background:#eee;color:#555; margin-left:6px;">登出</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?><div class="notice n-ok"><?php echo htmlspecialchars($_GET['msg']); ?></div><?php endif; ?>
    <?php if (isset($_GET['err'])): ?><div class="notice n-err"><?php echo htmlspecialchars($_GET['err']); ?></div><?php endif; ?>

    <div class="tabs">
        <a href="admin_panel.php?tab=orders" class="<?php echo $tab==='orders'?'on':''; ?>">📋 訂單管理</a>
        <a href="admin_panel.php?tab=products" class="<?php echo $tab==='products'?'on':''; ?>">🃏 商品管理</a>
    </div>

    <?php if ($tab === 'orders'): ?>
    <h3 style="margin-bottom:14px;">全部訂單（<?php echo count($orders); ?> 筆）</h3>
    <?php if (empty($orders)): ?><p style="color:#aaa;text-align:center;padding:40px;">尚無訂單</p>
    <?php else: ?>
    <table>
        <tr><th>訂單編號</th><th>買家帳號</th><th>件數</th><th>金額</th><th>時間</th><th>操作</th></tr>
        <?php foreach ($orders as $o): ?>
        <tr>
            <td><strong>#<?php echo $o['id']; ?></strong></td>
            <td><?php echo htmlspecialchars($o['buyer_name']); ?></td>
            <td><?php echo $o['item_count']; ?> 張</td>
            <td><strong>$ <?php echo number_format($o['total_price']); ?></strong></td>
            <td><?php echo $o['created_at']; ?></td>
            <td><a href="admin_panel.php?tab=orders&order_id=<?php echo $o['id']; ?>" class="btn btn-p">查看明細</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php if (!empty($detail)): ?>
    <div class="detail-box">
        <h3>訂單 #<?php echo intval($_GET['order_id']); ?> 明細</h3>
        <table>
            <tr><th>縮圖</th><th>商品名稱</th><th>團體</th><th>單價</th><th>數量</th><th>小計</th></tr>
            <?php foreach ($detail as $d): ?>
            <tr>
                <td><?php if($d['image_path']): ?><img src="<?php echo htmlspecialchars($d['image_path']); ?>" class="thumb"><?php endif; ?></td>
                <td><?php echo htmlspecialchars($d['product_name']); ?></td>
                <td><?php echo htmlspecialchars($d['group_name']??'—'); ?></td>
                <td>$ <?php echo number_format($d['price']); ?></td>
                <td><?php echo $d['quantity']; ?></td>
                <td>$ <?php echo number_format($d['price']*$d['quantity']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; endif; ?>

    <?php elseif ($tab === 'products'): ?>
    <div class="search-bar">
        <form method="GET" style="display:flex; gap:10px;">
            <input type="hidden" name="tab" value="products">
            <input type="text" name="q" placeholder="搜尋商品名稱或團體…" value="<?php echo htmlspecialchars($_GET['q']??''); ?>">
            <button type="submit" class="btn btn-p">搜尋</button>
            <a href="admin_panel.php?tab=products" class="btn" style="background:#eee;color:#555;">清除</a>
        </form>
    </div>
    <h3 style="margin-bottom:14px;">全部商品（<?php echo count($products); ?> 件）</h3>
    <?php if (empty($products)): ?><p style="color:#aaa;text-align:center;padding:40px;">沒有找到商品</p>
    <?php else: ?>
    <table>
        <tr><th>縮圖</th><th>商品名稱</th><th>團體</th><th>賣家</th><th>狀態</th><th>修改售價</th><th>上下架</th></tr>
        <?php foreach ($products as $p): ?>
        <tr>
            <td><img src="<?php echo htmlspecialchars($p['image_path']); ?>" class="thumb" onerror="this.style.display='none'"></td>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo htmlspecialchars($p['group_name']); ?></td>
            <td><?php echo htmlspecialchars($p['seller_name']); ?></td>
            <td><?php
                if ($p['is_active']==0) echo '<span class="badge b-off">已下架</span>';
                elseif ($p['status']==='sold_out') echo '<span class="badge b-sold">已售完</span>';
                else echo '<span class="badge b-ok">販售中</span>';
            ?></td>
            <td>
                <form class="if" action="admin_action.php" method="POST">
                    <input type="hidden" name="action" value="update_price">
                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="redirect_tab" value="products">
                    <input type="number" name="price" value="<?php echo $p['price']; ?>" min="1">
                    <button type="submit" class="btn btn-p">更新</button>
                </form>
            </td>
            <td style="white-space:nowrap;">
                <form class="if" action="admin_action.php" method="POST" style="margin-bottom:4px;">
                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="redirect_tab" value="products">
                    <?php if ($p['is_active']==1): ?>
                        <input type="hidden" name="action" value="deactivate">
                        <button type="submit" class="btn btn-d">下架</button>
                    <?php else: ?>
                        <input type="hidden" name="action" value="activate">
                        <button type="submit" class="btn btn-s">上架</button>
                    <?php endif; ?>
                </form>
                <?php if ($p['status']==='sold_out' && $p['is_active']==1): ?>
                <form class="if" action="admin_action.php" method="POST">
                    <input type="hidden" name="action" value="reset_status">
                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="redirect_tab" value="products">
                    <button type="submit" class="btn btn-o">恢復上架</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; endif; ?>
    </table>
    <?php endif; ?>
</div>
</body>
</html>
