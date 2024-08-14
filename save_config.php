<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_password = $_POST['db_password'];
    $db_name = $_POST['db_name'];

    $config_content = "<?php\n";
    $config_content .= "\$db_host = '$db_host';\n";
    $config_content .= "\$db_user = '$db_user';\n";
    $config_content .= "\$db_password = '$db_password';\n";
    $config_content .= "\$db_name = '$db_name';\n";
    $config_content .= "?>";

    file_put_contents('db_config.php', $config_content);

    echo "配置已保存。";
} else {
    echo "无效的请求。";
}
?>