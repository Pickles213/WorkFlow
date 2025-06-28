<?php
require 'config.php';
session_start();

// ✅ Check if logged in as a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: login.php');
    exit();
}

$freelancer_id = $_SESSION['user_id'];
$filter_status = $_GET['status'] ?? '';

// ✅ Query for task status summary
$status_counts = [];
$status_stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM tasks WHERE assigned_to = ? GROUP BY status");
$status_stmt->bind_param('i', $freelancer_id);
$status_stmt->execute();
$result = $status_stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}
$status_stmt->close();

// ✅ Query for tasks
if ($filter_status) {
    $task_stmt = $conn->prepare("SELECT * FROM tasks WHERE assigned_to = ? AND status = ?");
    $task_stmt->bind_param('is', $freelancer_id, $filter_status);
} else {
    $task_stmt = $conn->prepare("SELECT * FROM tasks WHERE assigned_to = ?");
    $task_stmt->bind_param('i', $freelancer_id);
}
$task_stmt->execute();
$tasks = $task_stmt->get_result();

// ✅ Priority color helper
function priorityColor($priority) {
    return match($priority) {
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
    <title>Freelancer Dashboard</title>
    <style>
        .task {
            border-left: 5px solid gray;
            padding: 15px;
            margin: 10px 0;
            background: #f9f9f9;
        }
        .filter, .summary {
            margin: 20px 0;
        }
        button {
            margin-right: 10px;
        }
    </style>
</head>
<body>

<h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> (Freelancer)</h2>

<!-- ✅ Task Status Summary -->
<div class="summary">
    <h3>Task Summary:</h3>
    <ul>
        <?php 
        $statuses = ['pending', 'accepted', 'in_progress', 'completed', 'declined'];
        foreach ($statuses as $status) {
            $count = $status_counts[$status] ?? 0;
            echo "<li>" . ucfirst(str_replace('_', ' ', $status)) . ": $count</li>";
        }
        ?>
    </ul>
</div>

<!-- ✅ Filter by Task Status -->
<div class="filter">
    <form method="GET">
        <label for="status">Filter Tasks:</label>
        <select name="status" onchange="this.form.submit()">
            <option value="">All</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= $status ?>" <?= $filter_status === $status ? 'selected' : '' ?>>
                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- ✅ Task Display -->
<?php
if ($tasks->num_rows === 0) {
    echo "<p>No tasks found.</p>";
} else {
    while ($task = $tasks->fetch_assoc()) {
        $color = priorityColor($task['priority']);
        echo "<div class='task' style='border-color: $color'>";
        echo "<h3>" . htmlspecialchars($task['title']) . "</h3>";
        echo "<p><strong>Description:</strong> " . htmlspecialchars($task['description']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($task['status']) . "</p>";
        echo "<p><strong>Due:</strong> " . htmlspecialchars($task['due_date']) . "</p>";
        echo "<p><strong>Priority:</strong> " . htmlspecialchars($task['priority']) . "</p>";
        echo "<p><strong>Category:</strong> " . htmlspecialchars($task['category']) . "</p>";
        echo "<p><strong>Details:</strong> " . htmlspecialchars($task['details']) . "</p>";

        // ✅ File attachment
        if (!empty($task['file_attachment'])) {
            echo "<p><a href='uploads/" . htmlspecialchars($task['file_attachment']) . "' download>Download Attachment</a></p>";
        }

        // ✅ Accept/Decline buttons for pending tasks
        if ($task['status'] === 'pending') {
            echo "<form method='POST' action='respond_task.php' style='margin-top:10px;'>
                    <input type='hidden' name='task_id' value='" . intval($task['id']) . "'>
                    <button name='accept'>Accept</button>
                    <button name='decline'>Decline</button>
                  </form>";
        }

        echo "</div>";
    }
}
$task_stmt->close();
?>

</body>
</html>
