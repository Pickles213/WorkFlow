<?php
require 'config.php';
session_start();

// Only allow clients to access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: login.php');
    exit();
}

$client_id = $_SESSION['user_id'];
$error = '';
$success = '';

// File upload directory
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $assigned_to = $_POST['assigned_to'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $status = 'pending';
    $file_attachment = null;

    // File Upload Handling
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_name = basename($_FILES['attachment']['name']);
        $file_size = $_FILES['attachment']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'zip', 'rar'];

        if (!in_array($file_ext, $allowed_ext)) {
            $error = "Invalid file type. Allowed: " . implode(", ", $allowed_ext);
        } elseif ($file_size > 5 * 1024 * 1024) { // 5MB limit
            $error = "File size must be under 5MB.";
        } else {
            $new_file_name = uniqid('file_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $new_file_name;
            if (move_uploaded_file($file_tmp, $file_path)) {
                $file_attachment = $new_file_name;
            } else {
                $error = "File upload failed.";
            }
        }
    }

    // If no errors, insert task
    if (empty($error) && !empty($title) && !empty($due_date) && !empty($priority) && !empty($assigned_to) && !empty($category)) {
        $stmt = $conn->prepare(
            "INSERT INTO tasks (title, due_date, priority, assigned_to, assigned_by, status, category, details, file_attachment, is_archived) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)"
        );
        $stmt->bind_param(
            "sssisssss", 
            $title, 
            $due_date, 
            $priority, 
            $assigned_to, 
            $client_id, 
            $status, 
            $category, 
            $details, 
            $file_attachment
        );

        if ($stmt->execute()) {
            $success = "Task successfully created!";
        } else {
            $error = "Database Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Get freelancers
$freelancers = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role = 'freelancer'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Task with File Upload</title>
    <style>
        body {
            font-family: sans-serif;
            background: linear-gradient(to right, #d7f5e8, #c0e3d0);
            margin: 40px;
        }
        .container {
            background-color: #fffdf6;
            padding: 30px;
            border-radius: 12px;
            max-width: 400px;
            margin: auto;
            box-shadow: 0px 0px 12px rgba(0,0,0,0.2);
        }
        label {
            font-weight: bold;
            color: #05552c;
        }
        input, select, textarea, button {
            display: block;
            margin: 8px 0 16px 0;
            width: 100%;
            padding: 10px;
            border: 2px solid #0a773f;
            border-radius: 8px;
            font-size: 14px;
        }
        input::placeholder, textarea::placeholder {
            color: #a0a0a0;
        }
        button {
            background-color: #0a773f;
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s ease;
            border: none;
        }
        button:hover {
            background-color: #095c33;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .success {
            color: green;
            font-weight: bold;
            margin-bottom: 12px;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            color: #05552c;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Create a New Task</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Task Title:</label>
        <input type="text" name="title" placeholder="Enter Title" required>

        <label>Freelancer:</label>
        <select name="assigned_to" required>
            <option value="">Select Freelancer</option>
            <?php while ($freelancer = mysqli_fetch_assoc($freelancers)): ?>
                <option value="<?= htmlspecialchars($freelancer['id']) ?>">
                    <?= htmlspecialchars($freelancer['fullname']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Due Date:</label>
        <input type="date" name="due_date" required>

        <label>Priority:</label>
        <select name="priority" required>
            <option value="">Select Priority</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>

        <label>Category:</label>
        <input type="text" name="category" placeholder="Enter Category" required>

        <label>Details:</label>
        <textarea name="details" rows="4" placeholder="Enter Details"></textarea>

        <label>Attach File (optional):</label>
        <input type="file" name="attachment">

        <button type="submit">Add Task</button>
    </form>

    <a href="client.php">⬅ Back to Dashboard</a>
</div>

</body>
</html>
