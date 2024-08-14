<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="icon" href="logo.ico">
    <title>聊天室注册</title>
    <style>
        #avatar-info {
            text-align: center;
            background-color: #f0f0f0;
            border-radius: 10px;
            padding: 10px;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        #avatar-info.show {
            opacity: 1;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <img src="https://cdn.pixabay.com/animation/2024/07/11/09/36/09-36-43-93_256.gif" alt="aiqiji">
        </div>
        <hr>
        <div class="content">
            <h1>注册</h1>

            <!-- 注册表单 -->
            <form action="register.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="file" name="avatar" id="avatar" accept="image/*" required onchange="previewAvatar(event)">
                    <img id="avatar-preview" src="https://cdn.pixabay.com/photo/2016/08/08/09/17/avatar-1577909_960_720.png" alt="上传头像" class="avatar-preview" onclick="document.getElementById('avatar').click()">
                    <p style="text-align: center;">头像选择</p>
                    <p id="avatar-info"></p>
                </div>
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">确认密码</label>
                    <input type="password" name="confirm-password" id="confirm-password" required>
                </div>
                <div class="form-group">
                    <input type="submit" value="注册">
                </div>
                <div class="form-group">
                    <button type="button" class="register-button" onclick="window.location.href='login.php'">登录</button>
                </div>
            </form>
        </div>
        <div class="footer">
            <p>&copy; 2024 AiQiji 版权所属.</p>
        </div>
        <!-- 模态框 -->
        <div id="messageModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <p id="modalMessage"></p>
            </div>
        </div>

        <script>
            function previewAvatar(event) {
                const reader = new FileReader();
                reader.onload = function() {
                    const output = document.getElementById('avatar-preview');
                    output.src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }

            // 获取模态框
            var modal = document.getElementById("messageModal");

            // 获取 <span> 元素，点击它可以关闭模态框
            var span = document.getElementsByClassName("close")[0];

            // 当用户点击 <span> (x)，关闭模态框
            span.onclick = function() {
                modal.style.display = "none";
            }

            // 当用户点击模态框外部，关闭模态框
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            // 显示模态框的函数
            function showModal(message) {
                document.getElementById("modalMessage").innerText = message;
                modal.style.display = "block";
            }
        </script>
        <script>
            function previewAvatar(event) {
                const file = event.target.files[0];
                if (file) {
                    // 预览图片
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('avatar-preview').src = e.target.result;
                    };
                    reader.readAsDataURL(file);

                    // 获取文件大小和格式
                    let fileSize;
                    if (file.size < 1024 * 1024) {
                        fileSize = (file.size / 1024).toFixed(2) + 'KB';
                    } else {
                        fileSize = (file.size / (1024 * 1024)).toFixed(2) + 'MB';
                    }
                    const fileType = file.type.split('/').pop();
                    const avatarInfo = `大小：<span style="color: blue;">${fileSize}</span> 格式：<span style="color: green;">.${fileType}</span>`;

                    // 更新页面上的信息
                    const avatarInfoElement = document.getElementById('avatar-info');
                    avatarInfoElement.innerHTML = avatarInfo;
                    avatarInfoElement.classList.add('show');
                }
            }
        </script>
    </div>
</body>
</html>

<?php
// 引入数据库连接文件
include 'db_link.php';

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];
    $avatar = $_FILES['avatar'];

    // 检查必填项是否为空
    if (empty($username)) {
        echo "<script>showModal('用户名没有填写');</script>";
        exit;
    }
    if (empty($password)) {
        echo "<script>showModal('密码没有填写');</script>";
        exit;
    }
    if (empty($confirmPassword)) {
        echo "<script>showModal('确认密码没有填写');</script>";
        exit;
    }
    if (empty($avatar['name'])) {
        echo "<script>showModal('请选择头像');</script>";
        exit;
    }

    // 检查密码是否匹配
    if ($password !== $confirmPassword) {
        echo "<script>showModal('密码不匹配');</script>";
        exit;
    }

    // 检查用户名是否为 admin
    if (strtolower($username) === 'admin') {
        echo "<script>showModal('该用户名不允许注册');</script>";
        exit;
    }

    // 检查用户名是否已存在
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>showModal('该用户名已存在');</script>";
        $stmt->close();
        exit;
    }
    $stmt->close();

    // 处理头像上传
    $targetDir = "static/images/avatar-img/";
    
    // 生成唯一的文件名，格式为 AiQiji-数字
    $uniqueNumber = time(); // 使用当前时间戳作为唯一数字
    $newFileName = "AiQiji-" . $uniqueNumber . "." . strtolower(pathinfo($avatar["name"], PATHINFO_EXTENSION));
    
    // 修改目标文件路径
    $targetFile = $targetDir . $newFileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // 检查文件是否为图像
    $check = getimagesize($avatar["tmp_name"]);
    if ($check === false) {
        echo "<script>showModal('文件不是图像');</script>";
        exit;
    }

    // 检查文件是否已经存在
    if (file_exists($targetFile)) {
        echo "<script>showModal('文件已存在');</script>";
        exit;
    }

    // 检查文件大小
    if ($avatar["size"] > 10000000) { // 10MB
        echo "<script>showModal('请上传小于10MB的照片');</script>";
        exit;
    }

    // 允许的文件格式
    $allowedFormats = ["jpg", "jpeg", "png", "gif", "bmp", "webp", "ico", "svg"];
    if (!in_array($imageFileType, $allowedFormats)) {
        echo "<script>showModal('只允许 JPG, JPEG, PNG, GIF, BMP, WEBP, ICO & SVG 格式的文件');</script>";
        exit;
    }

    // 检查目标目录是否存在，如果不存在则创建
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            echo "<script>showModal('无法创建目标目录');</script>";
            exit;
        }
    }

    // 检查目标目录是否有写入权限
    if (!is_writable($targetDir)) {
        echo "<script>showModal('抱歉，头像上传错误，请您告知管理员给头像存储目录加上写入权限！');</script>";
        exit;
    }

    // 尝试上传文件
    if (!move_uploaded_file($avatar["tmp_name"], $targetFile)) {
        echo "<script>showModal('上传文件时出错。错误信息: " . print_r(error_get_last(), true) . "');</script>";
        exit;
    }

    // 哈希密码
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 插入用户信息到数据库
    $stmt = $conn->prepare("INSERT INTO users (username, password, avatar_path) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashedPassword, $targetFile);

    if ($stmt->execute()) {
        echo "<script>showModal('恭喜您，您已注册成功！快去登陆吧！');</script>";
    } else {
        echo "<script>showModal('注册失败: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

$conn->close();
?>