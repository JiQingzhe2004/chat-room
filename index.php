<?php
session_start();

// 引入数据库配置文件
include 'db_config.php';

// 尝试连接到数据库
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// 检查连接
if ($conn->connect_error) {
    // 如果连接失败，跳转到 install.php
    header("Location: install.php");
    exit;
}

// 检查是否存在用户会话
if (isset($_SESSION['user_id'])) {
    // 如果用户已登录，跳转到 chat.php
    header("Location: chat.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="icon" href="logo.ico">
    <title>欢迎来到聊天室</title>
</head>
<body>

    <div class="container">
        <div class="header">
            <img src="https://cdn.pixabay.com/animation/2024/07/11/09/36/09-36-43-93_256.gif" alt="aiqiji">
        </div>
        <hr>
        <div class="content">
            <h1>欢迎来到聊天室</h1>
            <!-- 登录注册选项 -->
            <div class="btn-group">
                <a href="login.php" class="btn login">登录</a>
                <a href="register.php" class="btn register">注册</a>
            </div>
            <!-- 介绍内容 -->
            <div class="introduction">
                <p>
                    这是一个简单的聊天室，你可以在这里和朋友们聊天，分享你的心情。
                    <br>这里没有广告，没有繁琐的功能，只有简单的聊天。
                    <br>如果你喜欢这个聊天室，可以分享给你的朋友们。
                </p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; 2024 AiQiji 版权所属.</p>
        </div>
    </div>

    <script>
        // 检查 localStorage 或 sessionStorage 中是否有登录信息
        document.addEventListener('DOMContentLoaded', function() {
            const userId = localStorage.getItem('user_id') || sessionStorage.getItem('user_id');
            if (userId) {
                // 如果存在登录信息，跳转到 chat.php
                window.location.href = 'chat.php';
            }
        });
    </script>

</body>
</html>