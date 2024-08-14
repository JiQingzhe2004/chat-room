<?php
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 获取会话中的 user_id
$user_id = $_SESSION['user_id'];

// 包含数据库连接文件
include 'db_link.php';

// 从数据库中获取用户的头像路径
$sql = "SELECT avatar_path FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("准备 SQL 语句失败: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $avatar_path = $user['avatar_path'];
} else {
    // 如果未找到用户，重定向到登录页面
    header("Location: login.php");
    exit;
}

// 处理发送信息的请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = isset($_POST['message']) ? $_POST['message'] : '';
    $imagePath = '';

    // 处理上传的图片
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = 'chat-' . time() . '.' . $imageExtension;
        $imageDir = 'static/images/chat-img';

        // 检查并创建目录
        if (!is_dir($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        $imagePath = $imageDir . '/' . $imageName;
        move_uploaded_file($imageTmpPath, $imagePath);
    }

    // 清除任何现有的输出缓冲区
    ob_clean();
    
    // 返回JSON响应
    header('Content-Type: application/json');

    $sql = "INSERT INTO messages (user_id, message, image_path, created_at, avatar_path) VALUES (?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'SQL 语句准备失败: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("isss", $user_id, $message, $imagePath, $avatar_path);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

// 关闭连接
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="icon" href="logo.ico">
    <title>AiQiji聊天室</title>
</head>
<body>

    <div class="container">
        <div class="header">
            <img src="https://cdn.pixabay.com/animation/2024/07/11/09/36/09-36-43-93_256.gif" alt="aiqiji">
        </div>
        <hr>
        <div class="chat-container">

            <div class="chat-box" id="chat-box">
                <!-- 退出登录按钮 -->

                <!-- 聊天消息将动态加载 -->
            </div>
            <div class="chat-input">
                <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="用户头像" class="user-avatar">
                <!-- 上传自定义图片 -->
                <input type="file" id="image-input" accept="image/*" onchange="previewImage(event)">
                <img id="image-preview" src="请上传.png" alt="上传图片" class="image-preview" onclick="document.getElementById('image-input').click()">
                <input type="text" id="message-input" placeholder="输入消息...">
                <button onclick="sendMessage()" id="send-button">发送</button>
            </div>
            <button onclick="window.location.href='logout.php'" class="logout-button fixed-top-right">退出登录</button>
        </div>
            <div class="footer">
                <p>宝贝，请注意言辞哦！</p>
            </div>
    </div>


                <script>
            function previewImage(event) {
                const reader = new FileReader();
                reader.onload = function(){
                    const output = document.getElementById('image-preview');
                    output.src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }
        
            function sendMessage() {
                console.log("sendMessage called"); // 调试日志
                const messageInput = document.getElementById('message-input');
                const message = messageInput.value;
                const imageInput = document.getElementById('image-input');
                const image = imageInput.files[0];
        
                if (!message && !image) {
                    alert("请输入消息或选择图片");
                    return;
                }
        
                const formData = new FormData();
                formData.append('message', message);
                if (image) {
                    formData.append('image', image);
                }
        
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'chat.php', true);
                xhr.onload = function () {
                    console.log("AJAX request completed"); // 调试日志
                    console.log("Response Text: ", xhr.responseText); // 输出响应内容
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                console.log("Message sent successfully"); // 调试日志
                                // 清空输入框
                                messageInput.value = '';
                                imageInput.value = '';
                                document.getElementById('image-preview').src = '请上传.png';
                                // 重新加载聊天消息并滚动到最新消息
                                loadMessages(function() {
                                    const chatBox = document.getElementById('chat-box');
                                    chatBox.style.scrollBehavior = 'smooth';
                                    chatBox.scrollTop = chatBox.scrollHeight;
                                    setTimeout(() => {
                                        chatBox.style.scrollBehavior = 'auto';
                                    }, 500); // 500ms 过渡时间
                                });
                            } else {
                                console.error('发送消息失败: ' + response.message); // 调试日志
                                alert('发送消息失败: ' + response.message);
                            }
                        } catch (e) {
                            console.error('解析响应失败: ', e); // 调试日志
                            alert('解析响应失败');
                        }
                    } else {
                        console.error('发送消息失败'); // 调试日志
                        alert('发送消息失败');
                    }
                };
                xhr.onerror = function () {
                    console.error('AJAX request failed'); // 调试日志
                    alert('发送消息失败');
                };
                xhr.send(formData);
            }
        
            function loadMessages(callback) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'load_messages.php', true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const messages = JSON.parse(xhr.responseText);
                            const chatBox = document.getElementById('chat-box');
                            
                            const shouldScroll = chatBox.scrollTop + chatBox.clientHeight === chatBox.scrollHeight;
                            
                            chatBox.innerHTML = ''; // 清空现有内容
            
                            messages.forEach(msg => {
                                const messageElement = document.createElement('div');
                                messageElement.className = msg.user_id == <?php echo $user_id; ?> ? 'chat-message-me' : 'chat-message-you';
                                
                                const bubbleElement = document.createElement('div');
                                bubbleElement.className = msg.user_id == <?php echo $user_id; ?> ? 'chat-bubbles-me' : 'chat-bubbles-you';
                                
                                const avatarElement = document.createElement('img');
                                avatarElement.src = msg.user_avatar_path;
                                avatarElement.className = 'user-avatar-chat';
                                
                                const messageContent = document.createElement('p');
                                messageContent.textContent = msg.message;
                                
                                const timeElement = document.createElement('div');
                                timeElement.className = 'chat-message-time';
                                timeElement.textContent = `发送时间: ${msg.created_at}`; 
            
                                // 创建一个新的元素来显示发送者的名字
                                const senderNameElement = document.createElement('div');
                                senderNameElement.className = 'chat-sender-name';
                                senderNameElement.textContent = `发送者: ${msg.username}`; // 假设 msg 对象中有 username 属性
            
                                // 将发送者的名字添加到气泡元素的顶部
                                bubbleElement.appendChild(senderNameElement); // 添加发送者的名字
                                bubbleElement.appendChild(avatarElement);
                                bubbleElement.appendChild(messageContent);

                                if (msg.image_path) {
                                    const imgElement = document.createElement('img');
                                    imgElement.src = msg.image_path;
                                    imgElement.className = 'chat-message-image';
                                    imgElement.style.cursor = 'pointer'; // 鼠标悬停时显示为指针
                                
                                    // 创建灯箱元素
                                    const lightbox = document.createElement('div');
                                    lightbox.className = 'lightbox';
                                    lightbox.onclick = function() {
                                        lightbox.classList.remove('show');
                                        setTimeout(() => {
                                            lightbox.style.display = 'none';
                                        }, 300); // 300ms 过渡时间
                                    };
                                
                                    // 创建灯箱图片元素
                                    const lightboxImg = document.createElement('img');
                                    lightboxImg.src = msg.image_path;
                                
                                    // 创建关闭按钮
                                    const closeButton = document.createElement('span');
                                    closeButton.className = 'lightbox-close';
                                    closeButton.innerHTML = '&times;';
                                    closeButton.onclick = function() {
                                        lightbox.classList.remove('show');
                                        setTimeout(() => {
                                            lightbox.style.display = 'none';
                                        }, 300); // 300ms 过渡时间
                                    };
                                
                                    lightbox.appendChild(lightboxImg);
                                    lightbox.appendChild(closeButton);
                                    document.body.appendChild(lightbox);
                                
                                    imgElement.onclick = function() {
                                        lightbox.style.display = 'flex';
                                        setTimeout(() => {
                                            lightbox.classList.add('show');
                                        }, 10); // 确保 display: flex; 已经应用
                                    };
                                
                                    bubbleElement.appendChild(imgElement);
                                }
            
                                bubbleElement.appendChild(timeElement); // 添加时间
                                messageElement.appendChild(bubbleElement);
                                chatBox.appendChild(messageElement);
                            });
            
                            if (shouldScroll) {
                                chatBox.style.scrollBehavior = 'smooth';
                                chatBox.scrollTop = chatBox.scrollHeight;
                                setTimeout(() => {
                                    chatBox.style.scrollBehavior = 'auto';
                                }, 500); // 500ms 过渡时间
                            }
            
                            if (callback) {
                                callback();
                            }
                            
                        } catch (e) {
                            console.error('解析响应失败: ', e); // 调试日志
                        }
                    } else {
                        console.error('加载消息失败'); // 调试日志
                    }
                };
                xhr.send();
            }
            
            // 在DOM加载完成后立即加载一次消息
            document.addEventListener('DOMContentLoaded', function() {
                loadMessages();
            });
        </script>

</body>
</html>
