<?php
// 引入配置文件
include 'db_config.php';

// 连接到数据库
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
?>