<?php
session_start();

// 检查是否存在 admin_id 会话变量
if (!isset($_SESSION['admin_id'])) {
    // 如果未登录，重定向到 admin-login.php
    header("Location: admin-login.php");
    exit();
} else {
    // 如果已登录，重定向到 admin.php
    header("Location: admin.php");
    exit();
}
?>