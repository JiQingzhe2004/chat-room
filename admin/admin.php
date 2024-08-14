<?php
session_start();

// 检查是否登录
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// 包含数据库连接文件
include '../db_link.php';

// 处理登出请求
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: admin-login.php");
    exit();
}

// 查询 site_logo
$sql = "SELECT site_logo,site_name FROM settings LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 获取结果
    $row = $result->fetch_assoc();
    $site_logo = $row['site_logo'];
} else {
    $site_logo = 'default_logo.ico'; // 如果没有结果，使用默认图标
}
if ($result->num_rows > 0) {
    // 获取结果
    $row = $result->fetch_assoc();
    $site_name = $row['site_name'];
} else {
    $site_name = 'AiQiji'; // 如果没有结果，使用默认标题
}
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员页面</title>
    <link rel="icon" href="../logo.ico" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 200px;
            height: auto;
            background-color: #007bff;
            color: #fff;
            padding: 20px;
            transition: width 0.3s;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-radius: 0 20px 20px 0;
        }
        .sidebar.collapsed {
            width: 0px;
        }
        .sidebar h2 {
            color: #fff;
            text-align: center;
        }
        .sidebar.collapsed h2,
        .sidebar.collapsed ul li a span,
        .sidebar.collapsed .logout-form {
            display: none;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
            width: 90%;
        }
        .sidebar ul li {
            margin: 5px 0;
            display: flex;
            justify-content: center;
            border-radius: 10px;
        }
        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
        }
        .sidebar ul li a:hover {
            background-color: #0056b3;
        }
        .sidebar .logout-form {
            margin-top: auto;
            width: 100%;
            text-align: center;
        }
        .sidebar .logout-form button {
            padding: 10px 20px;
            background-color: #ff4d4d;
            border: none;
            border-radius: 50px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        .sidebar .logout-form button:hover {
            background-color: #cc0000;
        }
        .container {
        flex-grow: 1;
        padding: 20px;
        background-color: #fff;
        border-radius: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        transition: margin-left 0.3s, width 0.3s;
        margin-left: 0px;
        }
        .sidebar.collapsed + .container {
            margin-left: 0px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .section {
            display: none;
            margin-bottom: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .section.active {
            display: block;
        }
        .section h2 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: #ffffff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e9e9e9;
        }
        .toggle-btn {
            position: absolute;
            top: 20px;
            left: 200px;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
            transition: left 0.3s;
        }
        .sidebar.collapsed .toggle-btn {
            left: 5px;
        }
        .zongshu {
            margin-bottom: 20px;
            background-color: #f2f2f2;
            padding: 10px;
            border-radius: 10px;
        }

        .edit-btn, .delete-btn {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            color: #fff;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.3s;
        }
        .edit-btn {
            background-color: #28a745;
        }
        .edit-btn:hover {
            background-color: #218838;
            transform: scale(1.05);
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .delete-btn:hover {
            background-color: #c82333;
            transform: scale(1.05);
        }


        .system-management-form {
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .system-management-form h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .system-management-form .form-group {
            margin-bottom: 15px;
        }
        
        .system-management-form .form-group-inline {
            display: flex;
            justify-content: space-between;
        }
        
        .system-management-form .form-group-inline .form-group {
            flex: 1;
            margin-right: 10px;
        }
        
        .system-management-form .form-group-inline .form-group:last-child {
            margin-right: 0;
        }
        
        .system-management-form .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .system-management-form .form-group input[type="text"],
        .system-management-form .form-group input[type="file"],
        .system-management-form .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 10px;
            outline: none;
        }
        
        .system-management-form .form-group button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 50px;
            cursor: pointer;
        }
        
        .system-management-form .form-group button:hover {
            background-color: #0056b3;
        }

        .form-group-inline {
            display: flex;
            justify-content: space-between;
        }
        
        .form-group-inline .form-group {
            flex: 1;
            margin-right: 10px;
        }
        
        .form-group-inline .form-group:last-child {
            margin-right: 0;
        }
        
        .custom-select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            appearance: none;
            background-color: #fff;
            position: relative;
        }
        
        .custom-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        
        .custom-select option {
            padding: 10px;
            transition: background-color 0.3s ease;
        }
        
        .custom-select option:hover {
            background-color: #f1f1f1;
        }
        
        /* 下拉菜单的样式 */
        .custom-select::-ms-expand {
            display: none;
        }
        
        .custom-select::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 10px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #000;
            transform: translateY(-50%);
            pointer-events: none;
        }
        
        .custom-select select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background: transparent;
            border: none;
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            outline: none;
            cursor: pointer;
        }
        .jqqd {
            text-align: center;
            margin-top: 20px;
        }
        .jqqd img {
            margin: 0 auto;
        }
        .form-container {
            position: relative;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 90%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            border-radius: 10px;
        }

        .overlay-message {
            color: white;
            font-size: 24px;
            text-align: center;
        }

        .disabled-form {
            pointer-events: none;
            opacity: 0.5;
        }
    </style>
    <script>
        function showSection(sectionId) {
            var sections = document.querySelectorAll('.section');
            sections.forEach(function(section) {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }
    
        document.addEventListener('DOMContentLoaded', function() {
            showSection('user-management'); // 默认显示用户管理
            var menuLinks = document.querySelectorAll('.sidebar ul li a');
            menuLinks.forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault(); // 阻止默认的锚点跳转行为
                    var sectionId = this.getAttribute('href').substring(1);
                    showSection(sectionId);
                });
            });
    
            var toggleBtn = document.querySelector('.toggle-btn');
            var sidebar = document.querySelector('.sidebar');
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
        });
    </script>
</head>
<body>
    <div class="sidebar">
 
        <h2>管理菜单</h2>
        <button class="toggle-btn">☰</button>
        <ul>
            <li><a href="#user-management"><span>用户管理</span></a></li>
            <li><a href="#chat-management"><span>聊天信息管理</span></a></li>
            <li><a href="#system-management"><span>系统管理</span></a></li>
        </ul>
        <form class="logout-form" method="post">
            <button type="submit" name="logout">登出</button>
        </form>
    </div>
    <div class="container">
            <h1>管理员页面</h1>

            <div class="section" id="user-management">
        <h2>管理用户账号</h2>
        <?php
        // 查询用户总数
        $sql = "SELECT COUNT(*) as total_users FROM users";
        $result = $conn->query($sql);
        $total_users = $result->fetch_assoc()['total_users'];
        ?>
        <p class="zongshu">总用户数: <?php echo $total_users; ?></p>
        <!-- 在这里添加管理用户账号的功能 -->
        <table class="modern-table">
            <thead>
                <tr>
                    <th>用户ID</th>
                    <th>用户名</th>
                    <th>操作</th>
                </tr>
            </thead>
                <tbody>
                    <?php
                    // 查询用户数据
                    $sql = "SELECT id, username FROM users";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['username'] . "</td>";
                            echo "<td><a class='edit-btn' href='edit-user.php?id=" . $row['id'] . "'>编辑</a>  <a class='delete-btn' href='delete-user.php?id=" . $row['id'] . "'>删除</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>没有用户数据</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="section" id="chat-management">
            <h2>管理聊天数据</h2>
            <?php
            // 查询聊天总数
            $sql = "SELECT COUNT(*) as total_chats FROM messages";
            $result = $conn->query($sql);
            $total_chats = $result->fetch_assoc()['total_chats'];
            ?>
            <p  class="zongshu">总聊天数: <?php echo $total_chats; ?></p>
            <!-- 在这里添加管理聊天数据的功能 -->
            <table>
                <tr>
                    <th>聊天ID</th>
                    <th>用户名</th>
                    <th>消息内容</th>
                    <th>时间</th>
                    <th>图片</th>
                    <th>头像</th>
                    <th>操作</th>
                </tr>
                <?php
                // 查询聊天数据
                $sql = "SELECT messages.id, users.username, messages.message, messages.created_at, messages.image_path, messages.avatar_path 
                        FROM messages 
                        JOIN users ON messages.user_id = users.id";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . format_message($row['message']) . "</td>";
                        echo "<td>" . $row['created_at'] . "</td>";
                        echo "<td>";
                        if (!empty($row['image_path'])) {
                            echo "<img src='../" . htmlspecialchars($row['image_path']) . "' alt='图片' style='max-width: 100px; border-radius: 8px;'>";
                        } else {
                            echo "无图片";
                        }
                        echo "</td>";
                        echo "<td>";
                        if (!empty($row['avatar_path'])) {
                            echo "<img src='../" . htmlspecialchars($row['avatar_path']) . "' alt='头像' style='max-width: 50px; border-radius: 50%;'>";
                        } else {
                            echo "无头像";
                        }
                        echo "</td>";
                        echo "<td><a class='delete-btn' href='delete-chat.php?id=" . $row['id'] . "'>删除</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>没有聊天数据</td></tr>";
                }
                
                // 定义判断是否是 URL 的函数
                function is_url($text) {
                    return filter_var($text, FILTER_VALIDATE_URL);
                }
                
                // 定义格式化消息的函数
                function format_message($message) {
                    if (is_url($message)) {
                        return '<a href="' . htmlspecialchars($message) . '" style="color: blue;">' . htmlspecialchars($message) . '</a>';
                    } else {
                        return htmlspecialchars($message);
                    }
                }
                ?>
            </table>
        </div>

    <?php
    $sql = "SELECT site_name, password_format, allow_registration, site_logo FROM settings WHERE id = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $currentSettings = $result->fetch_assoc();
    } else {
        $currentSettings = [
            'site_name' => '',
            'password_format' => 'format1',
            'allow_registration' => 1,
            'site_logo' => ''
        ];
    }

    // 处理表单提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                $uploadDir = '../static/images/avatar-img/';
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
                $successMessage = "设置更新成功。";
            } else {
                $errors[] = "设置更新失败：" . $stmt->error;
            }
        
            $stmt->close();
        }
        $conn->close();
    }
    ?>
        
        <div class="section" id="system-management">
            <h2>系统管理</h2>

            <div class="form-container">
        <!-- 覆盖层 -->
        <div class="overlay">
            <div class="overlay-message">暂未开启</div>
        </div>

        <!-- 表单 -->
        <form class="system-management-form disabled-form" action="admin.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="site-logo">网站 Logo:</label>
                <input type="file" id="site-logo" name="site_logo">
            </div>
            <div class="form-group">
                <label for="site-name">网站名称:</label>
                <input type="text" id="site-name" name="site_name" value="<?php echo htmlspecialchars($currentSettings['site_name']); ?>">
            </div>
            <div class="form-group-inline">
                <div class="form-group">
                    <label for="password-format">密码格式:</label>
                    <select id="password-format" name="password_format" class="custom-select">
                        <option value="format1" <?php echo $currentSettings['password_format'] == 'format1' ? 'selected' : ''; ?>>
                            必须有英语大小写和至少8位密码组成
                        </option>
                        <option value="format2" <?php echo $currentSettings['password_format'] == 'format2' ? 'selected' : ''; ?>>
                            至少6位密码和字母和数字组合
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="allow-registration">允许用户注册:</label>
                    <select id="allow-registration" name="allow_registration" class="custom-select">
                        <option value="1" <?php echo $currentSettings['allow_registration'] ? 'selected' : ''; ?>>是</option>
                        <option value="0" <?php echo !$currentSettings['allow_registration'] ? 'selected' : ''; ?>>否</option>
                    </select>
                </div>
            </div>
                <div class="form-group">
                    <button type="submit">保存设置</button>
                </div>
            </form>
            <div class="jqqd"><p>该表单暂时不可用！<br>代码正在火速拼凑中，敬请期待！</p>
            <img src="敬请期待.gif" alt="敬请期待..." style="width: 60px;"></div>
        </div>

    </div>
</body>
</html>