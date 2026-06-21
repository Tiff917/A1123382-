<?php
session_start(); include 'db_config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("權限不足");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: admin_panel.php"); exit(); }

$action     = $_POST['action'] ?? '';
$product_id = intval($_POST['product_id'] ?? 0);
$redirect   = 'admin_panel.php?tab=' . ($_POST['redirect_tab'] ?? 'products');
if ($product_id <= 0) { header("Location: $redirect&err=無效商品ID"); exit(); }

switch ($action) {
    case 'update_price':
        $p = intval($_POST['price'] ?? 0);
        if ($p <= 0) { header("Location: $redirect&err=售價必須大於0"); exit(); }
        $s = $conn->prepare("UPDATE products SET price=? WHERE id=?"); $s->bind_param("ii",$p,$product_id); $s->execute();
        header("Location: $redirect&msg=售價已更新為 \$ $p"); break;
    case 'deactivate':
        $s = $conn->prepare("UPDATE products SET is_active=0 WHERE id=?"); $s->bind_param("i",$product_id); $s->execute();
        header("Location: $redirect&msg=商品已下架"); break;
    case 'activate':
        $s = $conn->prepare("UPDATE products SET is_active=1 WHERE id=?"); $s->bind_param("i",$product_id); $s->execute();
        header("Location: $redirect&msg=商品已重新上架"); break;
    case 'reset_status':
        $s = $conn->prepare("UPDATE products SET status='available' WHERE id=?"); $s->bind_param("i",$product_id); $s->execute();
        header("Location: $redirect&msg=商品狀態已恢復為販售中"); break;
    default:
        header("Location: $redirect&err=未知操作");
}
exit();
?>
