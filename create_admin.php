<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>注册管理员账户</title>
    <link rel="icon" href="logo.ico">
    <link rel="stylesheet" href="static/css/create_admin_style.css">
</head>
<body>
    <div class="container">
        <h1>注册管理员账户</h1>
        <form method="POST" action="register_admin.php">
            <label for="username">用户名:</label>
            <input type="text" id="username" name="username" value="admin"><br>
            <label for="password">密码:</label>
            <input type="password" id="password" name="password" required><br>
            <label for="confirm_password">确认密码:</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br>
            <input type="submit" value="确定">
        </form>
        <div class="footer">
            <p>&copy; 2024 AiQiji 版权所属.</p>
        </div>

        <div class="ps">
            <p>请您到宝塔面板的此网站根目录下，将 static 文件夹下的 images 文件夹中的两个文件夹权限，给用户组和公共组的权限加上写入权限，否则用户注册会出错！</p>
        </div>
    </div>
</body>
</html>