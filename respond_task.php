<?php
require 'config.php';
session_start();

$task_id = $_POST['task_id'];
if (isset($_POST['accept'])) {
    mysqli_query($conn, "UPDATE tasks SET status = 'accepted' WHERE id = $task_id");
} elseif (isset($_POST['decline'])) {
    mysqli_query($conn, "UPDATE tasks SET status = 'declined' WHERE id = $task_id");
}
header('Location: freelancer.php');
exit();
