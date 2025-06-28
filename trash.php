<?php
require 'config.php';
session_start();

//  Only allow logged-in clients
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: login.php');
    exit();
}

$client_id = $_SESSION['user_id'];

//  Get archived tasks for this client
$sql = "SELECT tasks.*, users.fullname AS freelancer_name
        FROM tasks
        JOIN users ON tasks.assigned_to = users.id
        WHERE tasks.assigned_by = $client_id AND tasks.is_archived = 1";

$tasks = mysqli_query($conn, $sql);

//  Color function
function priorityColor($priority) {
    return match ($priority) {
        'high' => 'red',
        'medium' => 'orange',
        'low' => 'green',
        default => 'gray'
    };
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trash - Archived Tasks</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f9f9f9; }
        .task {
            border-left: 5px solid gray;
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .button {
            display: inline-block;
            padding: 6px 10px;
            margin: 5px 5px 0 0;
            font-size: 14px;
            border-radius: 4px;
            text-decoration: none;
        }
        .restore { background-color: green; color: white; }
        .delete { background-color: red; color: white; }
        .back { background-color: gray; color: white; }
        .msg { color: green; margin-bottom: 10px; font-weight: bold; }
    </style>
</head>
<body>

<h2>🗑️ Archived Tasks (Trash)</h2>

<!--  Success message -->
<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'deleted'): ?>
        <p class="msg"> Task permanently deleted.</p>
    <?php elseif ($_GET['msg'] === 'restored'): ?>
        <p class="msg"> Task successfully restored.</p>
    <?php endif; ?>
<?php endif; ?>

<a class="button back" href="client.php">⬅ Back to Dashboard</a>

<?php if (mysqli_num_rows($tasks) === 0): ?>
    <p>No archived tasks found.</p>
<?php else: ?>
    <?php while ($task = mysqli_fetch_assoc($tasks)): ?>
        <div class="task" style="border-color: <?= priorityColor($task['priority']) ?>;">
            <h3><?= htmlspecialchars($task['title']) ?></h3>
            <p><strong>Freelancer:</strong> <?= htmlspecialchars($task['freelancer_name']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($task['status']) ?></p>
            <p><strong>Due:</strong> <?= htmlspecialchars($task['due_date']) ?></p>
            <p><strong>Priority:</strong> <?= htmlspecialchars($task['priority']) ?></p>

            <!--  Actions -->
            <a class="button restore" href="restore_task.php?id=<?= $task['id'] ?>" onclick="return confirm('Restore this task?')">Restore</a>
            <a class="button delete" href="delete_task.php?id=<?= $task['id'] ?>" onclick="return confirm('Permanently delete this task? This cannot be undone.')">Delete Permanently</a>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

</body>
</html>
