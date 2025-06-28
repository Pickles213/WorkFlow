<?php
require 'config.php'; // your DB connection
session_start();

if (!isset($_GET['id'])) {
    echo "Task ID is missing!";
    exit();
}

$task_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $description, $status, $task_id);

    if ($stmt->execute()) {
        header("Location: client.php");
        exit();
    } else {
        echo "Failed to update task.";
    }
}

// Fetch existing task data
$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();
?>

<h2>Edit Task</h2>
<form method="POST">
    <label>Title:</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>"><br><br>

    <label>Description:</label><br>
    <textarea name="description"><?= htmlspecialchars($task['description']) ?></textarea><br><br>

    <label>Status:</label><br>
    <select name="status">
        <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
        <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
    </select><br><br>

    <button type="submit">Update Task</button>
</form>
