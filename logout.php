<?php
session_start();

// 清除所有会话变量
$_SESSION = array();

// 如果存在会话 cookie，则删除它
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 销毁会话
session_destroy();

// 清除 localStorage 和 sessionStorage 中的登录信息
echo "<script>
    localStorage.removeItem('user_id');
    sessionStorage.removeItem('user_id');
    window.location.href = 'index.php';
</script>";
?>