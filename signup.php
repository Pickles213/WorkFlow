<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($password !== $confirm) {
        die("Passwords do not match.");
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $fullname, $email, $hashed, $role);

    if ($stmt->execute()) {
        echo "Signup successful. <a href='index.php'>Login here</a>.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
</head>
<body>
    <h2>Signup Form</h2>
    <form action="login.php" method="POST">
        <label>Full Name:</label><br>
        <input type="text" name="fullname" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm" required><br><br>

        <label>Role:</label><br>
        <select name="role" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <input type="submit" value="Sign Up">
    </form>
</body>
</html>
