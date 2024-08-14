<?php
session_start();

// 检查是否登录
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// 包含数据库连接文件
include '../db_link.php';

// 初始化错误信息数组
$errors = [];

// 处理网站名称
if (isset($_POST['site_name'])) {
    $siteName = trim($_POST['site_name']);
    if (empty($siteName)) {
        $errors[] = "网站名称不能为空。";
    }
} else {
    $errors[] = "网站名称未设置。";
}

// 处理密码格式
if (isset($_POST['password_format'])) {
    $passwordFormat = $_POST['password_format'];
} else {
    $errors[] = "密码格式未设置。";
}

// 处理允许用户注册
if (isset($_POST['allow_registration'])) {
    $allowRegistration = $_POST['allow_registration'] == '1' ? 1 : 0;
} else {
    $errors[] = "允许用户注册未设置。";
}

// 处理网站 Logo
if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
    $logoTmpPath = $_FILES['site_logo']['tmp_name'];
    $logoName = $_FILES['site_logo']['name'];
    $logoExtension = strtolower(pathinfo($logoName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($logoExtension, $allowedExtensions)) {
        $uploadDir = '../static/images/';
        $destPath = $uploadDir . 'logo.' . $logoExtension; // 更名为 logo，保留原格式
        if (!move_uploaded_file($logoTmpPath, $destPath)) {
            $errors[] = "文件上传失败。";
        }
    } else {
        $errors[] = "不支持的文件类型。";
    }
}

// 如果没有错误，更新数据库
if (empty($errors)) {
    $sql = "UPDATE settings SET site_name = ?, password_format = ?, allow_registration = ?";
    if (isset($destPath)) {
        $sql .= ", site_logo = ?";
    }
    $sql .= " WHERE id = 1";

    $stmt = $conn->prepare($sql);
    if (isset($destPath)) {
        $stmt->bind_param("ssis", $siteName, $passwordFormat, $allowRegistration, $destPath);
    } else {
        $stmt->bind_param("ssi", $siteName, $passwordFormat, $allowRegistration);
    }

    if ($stmt->execute()) {
        echo "设置更新成功。";
    } else {
        echo "设置更新失败：" . $stmt->error;
    }

    $stmt->close();
} else {
    foreach ($errors as $error) {
        echo $error . "<br>";
    }
}

$conn->close();
?>