<?php
require 'config.php';
session_start();

//  Only allow clients to access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: login.php');
    exit();
}

$client_id = $_SESSION['user_id'];
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $assigned_to = $_POST['assigned_to'] ?? '';
    $status = 'pending'; // default status

    if (empty($title) || empty($due_date) || empty($priority) || empty($assigned_to)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tasks (title, due_date, priority, assigned_to, assigned_by, status, is_archived) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sssiss", $title, $due_date, $priority, $assigned_to, $client_id, $status);

        if ($stmt->execute()) {
            header("Location: client.php?msg=created");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

//  Get list of freelancers for assignment dropdown
$freelancers = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role = 'freelancer'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Task</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        input, select, button { display: block; margin: 10px 0; padding: 8px; width: 300px; }
        .error { color: red; }
    </style>
</head>
<body>

<h2>Create a New Task</h2>

<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
    <label>Task Title:</label>
    <input type="text" name="title" required>

    <label>Due Date:</label>
    <input type="date" name="due_date" required>

    <label>Priority:</label>
    <select name="priority" required>
        <option value="">Select</option>
        <option value="high">High</option>
        <option value="medium">Medium</option>
        <option value="low">Low</option>
    </select>

    <label>Assign to Freelancer:</label>
    <select name="assigned_to" required>
        <option value="">Select</option>
        <?php while ($freelancer = mysqli_fetch_assoc($freelancers)): ?>
            <option value="<?= $freelancer['id'] ?>"><?= htmlspecialchars($freelancer['fullname']) ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Create Task</button>
</form>

<a href="client.php">⬅ Back to Dashboard</a>

</body>
</html>
