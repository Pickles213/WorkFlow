<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: login.php');
    exit();
}

$task_id = $_GET['id'] ?? null;

if ($task_id) {
    $task_id = intval($task_id); // sanitize

    $sql = "UPDATE tasks SET is_archived = 1 WHERE id = $task_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: client.php?msg=archived");
        exit();
    } else {
        echo "Error archiving task: " . mysqli_error($conn);
    }
} else {
    echo "Invalid Task ID.";
}
?>
