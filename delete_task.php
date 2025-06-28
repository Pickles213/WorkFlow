<?php
require 'config.php';
session_start();

//  Allow only logged-in clients
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header("Location: login.php");
    exit();
}

$task_id = $_GET['id'] ?? null;

if ($task_id) {
    $task_id = intval($task_id); // sanitize input

    //  Double-check ownership (optional but safer)
    $client_id = $_SESSION['user_id'];
    $check = mysqli_query($conn, "SELECT * FROM tasks WHERE id = $task_id AND assigned_by = $client_id");

    if (mysqli_num_rows($check) === 1) {
        $delete = mysqli_query($conn, "DELETE FROM tasks WHERE id = $task_id");

        if ($delete) {
            header("Location: trash.php?msg=deleted");
            exit();
        } else {
            echo "Error deleting task: " . mysqli_error($conn);
        }
    } else {
        echo "❌ Task not found or you do not have permission.";
    }
} else {
    echo "❌ Invalid Task ID.";
}
?>
