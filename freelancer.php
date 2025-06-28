<?php
require 'config.php';
session_start();

//  Check if logged in as a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: login.php');
    exit();
}
//  Get the logged-in freelancer's ID
$freelancer_id = $_SESSION['user_id'];

//  Query tasks assigned to this freelancer
$query = "SELECT * FROM tasks WHERE assigned_to = $freelancer_id";
$tasks = mysqli_query($conn, $query);

//  Optional: Check if query fails
if (!$tasks) {
    die("Query failed: " . mysqli_error($conn));
}

//  Function to assign color based on priority
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
            padding: 10px;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Freelancer)</h2>

<?php
if (mysqli_num_rows($tasks) === 0) {
    echo "<p>No tasks assigned to you yet.</p>";
} else {
    while ($task = mysqli_fetch_assoc($tasks)) {
        $color = priorityColor($task['priority']);
        echo "<div class='task' style='border-color: $color'>";
        echo "<h3>" . htmlspecialchars($task['title']) . "</h3>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($task['status']) . "</p>";
        echo "<p><strong>Due:</strong> " . htmlspecialchars($task['due_date']) . "</p>";
        echo "<p><strong>Priority:</strong> " . htmlspecialchars($task['priority']) . "</p>";

        // Show Accept/Decline buttons if pending
        if ($task['status'] === 'pending') {
            echo "<form method='POST' action='respond_task.php'>
                    <input type='hidden' name='task_id' value='{$task['id']}'>
                    <button name='accept'>Accept</button>
                    <button name='decline'>Decline</button>
                  </form>";
        }

        echo "</div>";
    }
}
?>

</body>
</html>
