<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to reply");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $topic_id = $_POST['topic_id'];
    $content = $_POST['content'];

    $sql = "INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $topic_id, $_SESSION['user_id'], $content);
    $stmt->execute();
    $stmt->close();

    // Update user's last activity
    $update_sql = "UPDATE users SET last_activity = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $_SESSION['user_id']);
    $update_stmt->execute();
    $update_stmt->close();

    header("Location: topic.php?id=" . $topic_id);
    exit();
}