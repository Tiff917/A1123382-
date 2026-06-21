<?php
session_start(); include 'db_config.php';
if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: signup.php"); exit(); }

$username  = trim($_POST['username'] ?? '');
$password  = trim($_POST['password'] ?? '');
$real_name = trim($_POST['real_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$birthday  = $_POST['birthday'] ?? '';
$role      = in_array($_POST['role'] ?? '', ['buyer','seller']) ? $_POST['role'] : 'buyer';
$favs      = isset($_POST['fav']) ? implode(',', $_POST['fav']) : '';

if (!$username || !$password || !$real_name || !$email || !$birthday) {
    echo "<script>alert('請填寫所有必要欄位'); history.back();</script>"; exit();
}
if (strlen($password) < 6) {
    echo "<script>alert('密碼至少6位'); history.back();</script>"; exit();
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username,password,real_name,email,birthday,role,favorite_groups) VALUES(?,?,?,?,?,?,?)");
$stmt->bind_param("sssssss", $username, $hash, $real_name, $email, $birthday, $role, $favs);

if ($stmt->execute()) {
    echo "<script>alert('註冊成功！請登入'); window.location.href='signin.php';</script>";
} else {
    if (strpos($conn->error,'Duplicate') !== false)
        echo "<script>alert('帳號已被使用，請換一個'); history.back();</script>";
    else
        echo "<script>alert('註冊失敗：{$conn->error}'); history.back();</script>";
}
?>
