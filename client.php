<?php
require 'config.php';
session_start();

// Check user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: login.php');
    exit();
}

$client_id = $_SESSION['user_id'];
$filter_status = $_GET['status'] ?? '';

// Get all active (not archived) tasks assigned by this client
$sql = "SELECT tasks.*, users.fullname AS freelancer_name
        FROM tasks
        JOIN users ON tasks.assigned_to = users.id
        WHERE tasks.assigned_by = $client_id AND tasks.is_archived = 0";

if ($filter_status) {
    $status_safe = mysqli_real_escape_string($conn, $filter_status);
    $sql .= " AND tasks.status = '$status_safe'";
}

$tasks = mysqli_query($conn, $sql);

// Task counters
$status_counts = [];
$status_query = mysqli_query(
    $conn,
    "SELECT status, COUNT(*) as count FROM tasks WHERE assigned_by = $client_id AND is_archived = 0 GROUP BY status"
);
while ($row = mysqli_fetch_assoc($status_query)) {
    $status_counts[$row['status']] = $row['count'];
}

// Priority color function
function priorityColor($priority)
{
    return match ($priority) {
        'high' => 'red',
        'medium' => 'orange',
        'low' => 'green',
        default => 'gray',
    };
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboar</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f0f7f3;
        }
        .task {
            border-left: 5px solid gray;
            background: white;
            padding: 15px;
            margin: 12px 0;
            border-radius: 8px;
            box-shadow: 0 3px 7px rgba(0,0,0,0.15);
        }
        .button {
            padding: 6px 12px;
            margin: 5px 5px 5px 0;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            display: inline-block;
        }
        .edit { background-color: orange; color: white; }
        .delete { background-color: red; color: white; }
        .create { background-color: green; color: white; }
        .msg {
            color: green;
            font-weight: bold;
            margin-bottom: 10px;
        }
        select, label {
            font-size: 14px;
        }
        a.download-link {
            color: blue;
            text-decoration: underline;
            margin-left: 5px;
        }
    </style>
</head>
<body>

<h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> (Client)</h2>

<!-- Success messages -->
<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'archived'): ?>
        <p class="msg">Task moved to trash.</p>
    <?php elseif ($_GET['msg'] === 'updated'): ?>
        <p class="msg">Task updated successfully.</p>
    <?php elseif ($_GET['msg'] === 'created'): ?>
        <p class="msg">Task created successfully.</p>
    <?php endif; ?>
<?php endif; ?>

<!-- Task Summary -->
<p><strong>Task Summary:</strong></p>
<ul>
    <li>Pending: <?= $status_counts['pending'] ?? 0 ?></li>
    <li>Accepted: <?= $status_counts['accepted'] ?? 0 ?></li>
    <li>In Progress: <?= $status_counts['in_progress'] ?? 0 ?></li>
    <li>Completed: <?= $status_counts['completed'] ?? 0 ?></li>
    <li>Declined: <?= $status_counts['declined'] ?? 0 ?></li>
</ul>

<!-- Controls -->
<a class="button create" href="create_task.php">+ Create New Task</a>
<a class="button" href="trash.php">🗑️ View Trash</a>

<!-- Filter by status -->
<form method="GET" style="margin-top: 10px;">
    <label for="status">Filter by Status:</label>
    <select name="status" onchange="this.form.submit()">
        <option value="">All</option>
        <?php foreach (['pending', 'accepted', 'in_progress', 'completed', 'declined'] as $s): ?>
            <option value="<?= $s ?>" <?= $filter_status === $s ? 'selected' : '' ?>>
                <?= ucfirst(str_replace('_', ' ', $s)) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<!-- Task list -->
<?php if (mysqli_num_rows($tasks) === 0): ?>
    <p>No tasks found.</p>
<?php else: ?>
    <?php while ($task = mysqli_fetch_assoc($tasks)): ?>
        <div class="task" style="border-color: <?= priorityColor($task['priority']) ?>;">
            <h3><?= htmlspecialchars($task['title']) ?></h3>
            <p><strong>Freelancer:</strong> <?= htmlspecialchars($task['freelancer_name']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $task['status']))) ?></p>
            <p><strong>Due Date:</strong> <?= htmlspecialchars($task['due_date']) ?></p>
            <p><strong>Priority:</strong> <?= htmlspecialchars(ucfirst($task['priority'])) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($task['category']) ?></p>

            <?php if (!empty($task['details'])): ?>
                <p><strong>Details:</strong> <?= nl2br(htmlspecialchars($task['details'])) ?></p>
            <?php endif; ?>

            <?php if (!empty($task['file_attachment'])): ?>
                <p><strong>Attachment:</strong>
                    <a class="download-link" href="uploads/<?= urlencode($task['file_attachment']) ?>" target="_blank">
                        <?= htmlspecialchars($task['file_attachment']) ?>
                    </a>
                </p>
            <?php endif; ?>

            <!-- Actions -->
            <a class="button edit" href="edit_task.php?id=<?= $task['id'] ?>">Edit</a>
            <a class="button delete" href="archive_task.php?id=<?= $task['id'] ?>"
               onclick="return confirm('Are you sure you want to move this task to Trash?')">
                Trash
            </a>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

</body>
</html>
