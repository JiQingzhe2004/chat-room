<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="icon" href="logo.ico">
    <title>聊天室登录</title>
</head>
<body>

    <div class="container">
        <div class="header">
            <img src="https://cdn.pixabay.com/animation/2024/07/11/09/36/09-36-43-93_256.gif" alt="aiqiji">
        </div>
        <hr>
        <div class="content">
            <h1>登录</h1>

            <!-- 登录表单 -->
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-group">
                    <input type="submit" value="登录">
                </div>
                <div class="form-group">
                    <button type="button" class="register-button" onclick="window.location.href='register.php'">注册</button>
                </div>
            </form>
        </div>
        <div class="footer">
            <p>&copy; 2024 AiQiji 版权所属.</p>
    </div>

</body>
</html>

<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 包含数据库连接文件
    include 'db_link.php';

    // 查询用户信息的数据库表为 'users'
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // 验证密码
        if (password_verify($password, $user['password'])) {
            // 设置会话变量
            $_SESSION['user_id'] = $user['id'];
            // 跳转到 chat.php
            header("Location: chat.php");
            exit;
        } else {
            echo "<p>用户名或密码错误！</p>";
        }
    } else {
        echo "<p>未找到该用户！<br>请先注册！<br><a href='register.php' class='btn1 register'>点击注册</a></p>";
    }

    // 关闭连接
    $stmt->close();
    $conn->close();
}
?>