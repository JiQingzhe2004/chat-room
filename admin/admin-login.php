<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
    <link rel="stylesheet" href="css/admin-login-style.css">
    <link rel="icon" href="../logo.ico" type="image/x-icon">
</head>
<body>
<div class="login-container">
        <h2>管理员登录</h2>
        <form action="admin-login.php" method="post">
            <div>
                <label for="username">用户名:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">密码:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <button type="submit">登录</button>
            </div>
        </form>
        <div class="footer">
            <p>&copy; 2024 AiQiji 版权所属.</p>
        </div>
    </div>
</body>
</html>

<?php
// admin-login.php

// 数据库连接配置
include '../db_link.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 包含数据库连接文件
    include '../db_link.php';

    // 查询管理员信息的数据库表为 'users'
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("预处理语句失败: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        // 验证密码
        if (password_verify($password, $admin['password'])) {
            // 设置会话变量
            $_SESSION['admin_id'] = $admin['id'];
            // 登录成功，重定向到管理员主页
            header("Location: admin.php");
            exit();
        } else {
            // 密码错误
            echo "<p style='color: red;'>用户名或密码错误</p>";
        }
    } else {
        // 用户名不存在
        echo "<p style='color: red;'>用户名或密码错误</p>";
    }

    $stmt->close();
}

$conn->close();
?>