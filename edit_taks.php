<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: login.php');
    exit();
}

$task_id = $_GET['id'] ?? null;
$error = '';
$success = false;

if ($task_id) {
    $task_id = intval($task_id);
    $result = mysqli_query($conn, "SELECT * FROM tasks WHERE id = $task_id");
    $task = mysqli_fetch_assoc($result);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_title = $_POST['title'] ?? '';
        if (empty($new_title)) {
            $error = "Title cannot be empty.";
        } else {
            $stmt = $conn->prepare("UPDATE tasks SET title = ? WHERE id = ?");
            $stmt->bind_param("si", $new_title, $task_id);
            if ($stmt->execute()) {
                header("Location: client.php?msg=updated");
                exit();
            } else {
                $error = "Update failed.";
            }
        }
    }
} else {
    echo "Invalid Task ID.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Edit Task</title></head>
<body>
    <h2>Edit Task</h2>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <form method="POST">
        <label>Task Title:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
        <button type="submit">Update</button>
    </form>
    <a href="client.php">⬅ Back</a>
</body>
</html>
