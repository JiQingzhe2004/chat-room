<?php
session_start();

// 检查是否登录
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// 包含数据库连接文件
include '../db_link.php';

// 获取用户ID
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 初始化错误消息
$error_message = "";

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $avatar_path = '';

    // 检查必填字段
    if (empty($username) || empty($password)) {
        $error_message = "用户名和密码不能为空。";
    } else {
        // 处理头像上传
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../static/images/avatar-img/';
            $avatar_path = $upload_dir . basename($_FILES['avatar']['name']);
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                $error_message = "头像上传失败。";
            }
        } else {
            $avatar_path = $_POST['current_avatar'];
        }

        if (empty($error_message)) {
            // 更新用户信息
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET username = ?, password = ?, avatar_path = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $username, $hashed_password, $avatar_path, $user_id);

            if ($stmt->execute()) {
                header("Location: admin.php");
                exit();
            } else {
                $error_message = "更新用户信息时出错。";
            }
        }
    }
}

// 获取用户当前信息
$sql = "SELECT username, avatar_path FROM users WHERE id = ?"; // 从 users 表中选择 username 和 avatar_path 字段
$stmt = $conn->prepare($sql); // 准备 SQL 语句
$stmt->bind_param("i", $user_id); // 绑定参数，将 $user_id 作为查询条件
$stmt->execute(); // 执行查询
$result = $stmt->get_result(); // 获取查询结果

if ($result->num_rows == 0) { // 检查是否有结果
    echo "用户不存在。"; // 如果没有结果，输出 "用户不存在。"
    exit(); // 退出脚本
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑用户</title>
    <link rel="icon" href="../logo.ico" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            /* 上下居中 */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        h1 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            border-radius: 10px;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"], input[type="file"] {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            border: none;
            border-radius: 50px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
        .custom-file-upload {
            display: inline-block;
            padding: 6px 12px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            font-size: 16px;
            text-align: center;
        }
        
        .custom-file-upload input[type="file"] {
            display: none;
        }
        .avatar-preview {
            margin-bottom: 15px;
        }
        .avatar-preview img {
            width: 60px;
            height: 60px;
            border-radius: 50px;
            object-fit: cover; /* 保持图像比例并填满容器 */
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 50px;
            margin-top: 10px;
            text-align: center;
            transition: background-color 0.3s, transform 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }

                .custom-file-upload {
                    display: inline-block;
                    padding: 6px 12px;
                    cursor: pointer;
                    background-color: #007bff;
                    color: white;
                    border-radius: 4px;
                    font-size: 16px;
                    text-align: center;
                }
                
                .custom-file-upload input[type="file"] {
                    display: none;
                }
                
                .avatar-preview {
                    margin-top: 10px;
                    text-align: center;
                    position: relative;
                    display: inline-block;
                }
                
                .avatar-preview img {
                    width: 100px;
                    height: 100px;
                    border-radius: 50%;
                    object-fit: cover;
                }
                
                .avatar-info {
                    background-color: #f8f9fa;
                    padding: 10px;
                    border-radius: 4px;
                    display: inline-block;
                    text-align: left;
                }
                
                .avatar-info p {
                    margin: 5px 0;
                }
    </style>
    <script>
        function previewAvatar(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatarPreview = document.getElementById('avatarPreview');
                    avatarPreview.src = e.target.result;
                    avatarPreview.style.display = 'block';
        
                    const avatarInfo = document.getElementById('avatarInfo');
                    document.getElementById('fileName').textContent = file.name;
                    document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(2) + ' KB';
                    document.getElementById('fileType').textContent = file.type;
                    avatarInfo.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>编辑用户</h1>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label for="username">用户名</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="password">密码</label>
            <input type="password" id="password" name="password" required>

            <label for="avatar">头像</label>
            <label for="avatar" class="custom-file-upload">
                <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewAvatar(event)">
                选择头像
            </label>
            <div class="avatar-preview">
                <img id="avatarPreview" src="../<?php echo htmlspecialchars($user['avatar_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="当前头像">
            </div>
            <input type="hidden" name="current_avatar" value="<?php echo htmlspecialchars($user['avatar_path']); ?>">
            
            <div class="avatar-preview">
                <img id="avatarPreview" src="#" alt="预览头像" style="display: none;">
                <div id="avatarInfo" class="avatar-info" style="display: none;">
                    <p>文件名: <span id="fileName"></span></p>
                    <p>文件大小: <span id="fileSize"></span></p>
                    <p>文件类型: <span id="fileType"></span></p>
                </div>
            </div>
            <button type="submit">保存更改</button>
            <a class="btn" href="admin.php">返回</a>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>