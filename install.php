<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_password = $_POST['db_password'];
    $db_name = $_POST['db_name'];

    // 尝试连接到数据库
    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

    if ($conn->connect_error) {
        // 如果连接失败，显示错误信息
        echo "<script>alert('输入信息有误，请检查。'); window.history.back();</script>";
    } else {
        // 创建数据表和列的 SQL 语句
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            avatar_path VARCHAR(255) DEFAULT NULL
        );

        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            image_path VARCHAR(255) DEFAULT NULL,
            avatar_path VARCHAR(255) DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );

        ALTER TABLE messages
        DROP FOREIGN KEY messages_ibfk_1;

        ALTER TABLE messages
        ADD CONSTRAINT messages_ibfk_1
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE;

        CREATE TABLE settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            site_logo VARCHAR(255),
            site_name VARCHAR(255),
            password_format VARCHAR(255),
            allow_registration BOOLEAN
        );
        ";

        if ($conn->multi_query($sql) === TRUE) {
            // 保存配置文件
            $config_content = "<?php\n";
            $config_content .= "\$db_host = '$db_host';\n";
            $config_content .= "\$db_user = '$db_user';\n";
            $config_content .= "\$db_password = '$db_password';\n";
            $config_content .= "\$db_name = '$db_name';\n";
            $config_content .= "?>";

            file_put_contents('db_config.php', $config_content);

            // 显示成功信息并跳转到创建管理员账户的界面
            echo "<div class='countdown-container'>
                    <div id='countdown'></div>
                  </div>
                  <script>
                    var countdown = 5;
                    var countdownElement = document.getElementById('countdown');
                    countdownElement.innerHTML = '将在 ' + countdown + ' 秒后跳转至创建管理员账户的界面';
                    var interval = setInterval(function() {
                        countdown--;
                        countdownElement.innerHTML = '将在 ' + countdown + ' 秒后跳转至创建管理员账户的界面';
                        if (countdown <= 0) {
                            clearInterval(interval);
                            window.location.href = 'create_admin.php';
                        }
                    }, 1000);
                  </script>";
        } else {
            echo "<script>alert('创建数据表时出错: " . $conn->error . "'); window.history.back();</script>";
        }
    }

    $conn->close();
} else {
    // 显示输入表单
    echo '
    <!DOCTYPE html>
    <html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>安装</title>
        <link rel="icon" href="logo.ico">
        <link rel="stylesheet" href="static/css/install_style.css">
    </head>
    <body>
        <div class="container">
            <h1>聊天室安装</h1>
            <form method="POST" action="">
                <label for="db_host">数据库主机:</label>
                <input type="text" id="db_host" name="db_host" value="localhost"><br>
                <label for="db_user">数据库用户名:</label>
                <input type="text" id="db_user" name="db_user" required><br>
                <label for="db_password">数据库密码:</label>
                <input type="password" id="db_password" name="db_password" required><br>
                <label for="db_name">数据库名称:</label>
                <input type="text" id="db_name" name="db_name" required><br>
                <input type="submit" value="提交">
            </form>
                <div class="footer">
                    <p>&copy; 2024 AiQiji 版权所属.</p>
                </div>
        </div>
    </body>
    </html>
    ';
}
?>