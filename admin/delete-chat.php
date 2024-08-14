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
    $messageId = intval($_GET['id']);

    // 查询要删除的消息的图片路径
    $sql = "SELECT image_path FROM messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $messageId);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();

    // 删除消息
    $sql = "DELETE FROM messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $messageId);

    if ($stmt->execute()) {
        // 删除图片文件
        if (!empty($imagePath) && file_exists("../" . $imagePath)) {
            unlink("../" . $imagePath);
        }
        echo "<script>alert('消息已成功删除'); window.location.href = 'admin.php';</script>";
    } else {
        echo "<script>alert('删除消息时出错'); window.location.href = 'admin.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>