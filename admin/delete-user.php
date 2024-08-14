<?php
session_start();

// 检查是否登录
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// 包含数据库连接文件
include '../db_link.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // 删除用户
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        echo "<script>alert('删除用户时出错1'); window.location.href = 'admin.php';</script>";
        exit();
    }

    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        echo "<script>alert('用户已成功删除'); window.location.href = 'admin.php';</script>";
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo "<script>alert('删除用户时出错2: " . addslashes($stmt->error) . "'); window.location.href = 'admin.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>