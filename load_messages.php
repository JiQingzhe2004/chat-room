<?php
session_start();
include 'db_link.php';

$sql = "SELECT messages.id, messages.user_id, messages.message, messages.created_at, messages.image_path, users.username, users.avatar_path AS user_avatar_path 
        FROM messages 
        JOIN users ON messages.user_id = users.id 
        ORDER BY messages.created_at ASC";

$result = $conn->query($sql);
$messages = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($messages);

$conn->close();
?>