<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "<script>alert('密码和确认密码不匹配，请重新输入。'); window.history.back();</script>";
    } else {
        // 包含数据库连接文件
        include 'db_link.php';

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('管理员账户注册成功！'); window.location.href = 'index.php';</script>";
        } else {
            echo "错误: " . $sql . "<br>" . $conn->error;
        }

        $conn->close();
    }
}
?>