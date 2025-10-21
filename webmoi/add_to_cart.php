<?php

require_once 'includes/config.php'; 

function check_csrf_token($token) {
    if (!isset($_SESSION)) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}
if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
    exit('Token CSRF không hợp lệ');
}

$id = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));

$stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: index.php'); exit; }

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id] += $qty;
} else {
    $_SESSION['cart'][$id] = $qty;
}
header('Location: cart.php');
