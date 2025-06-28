<?php
require_once 'config.php';
session_start();

$error = [];
$errors = [];

if (isset($_POST['registerbtn'])) {
    $Fname = $_POST['Fname'];
    $email = $_POST['email'];
    $password = $_POST['pass'];
    $Cpass = $_POST['Cpass'];
    $user_type = $_POST['user'];

    // Check if user already exists
    $select = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $select);

    if (mysqli_num_rows($result) > 0) {
        $error[] = 'Email already exists!';
    } elseif ($password !== $Cpass) {
        $error[] = 'Passwords do not match!';
    } else {
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $Fname, $email, $hashedPass, $user_type);

        if ($insert->execute()) {
            header('Location: login.php');
            exit();
        } else {
            $error[] = "Registration failed: " . $insert->error;
        }
        $insert->close();
    }
}

if (isset($_POST['loginbtn'])) {
    $email = $_POST['login-email'];
    $password = $_POST['login-pass'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row && password_verify($password, $row['password'])) {
         $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['fullname'];
        $_SESSION['user_type'] = $row['role'];
       

        if ($row['role'] == 'freelancer') {
            header('Location: freelancer.php');
        } else {
            header('Location: client.php');
        }
        exit();
    } else {
        $errors[] = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Freelance Login</title>
    <style>
        body { font-family: sans-serif; background: #f5f5f5; }
        .container { width: 320px; margin: auto; padding-top: 60px; }
        form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input, select, button {
            display: block;
            margin: 10px 0;
            width: 100%;
            padding: 8px;
        }
        .toggle { text-align: center; margin-top: 10px; cursor: pointer; color: blue; }
        .error { color: red; font-size: 14px; margin: 5px 0; }
        h2 { text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <!-- Login Form -->
    <form method="POST">
        <h2>Login</h2>
        <?php if (!empty($errors)) foreach ($errors as $e) echo "<p class='error'>$e</p>"; ?>
        <input type="email" name="login-email" placeholder="Email" required>
        <input type="password" name="login-pass" placeholder="Password" required>
        <button name="loginbtn" type="submit">Login</button>
    </form>

    <!-- Toggle Link -->
    <div class="toggle" onclick="document.getElementById('signup').style.display='block'">Create Account</div>

    <!-- Signup Form -->
    <form id="signup" method="POST" style="display:none;">
        <h2>Sign Up</h2>
        <?php if (!empty($error)) foreach ($error as $e) echo "<p class='error'>$e</p>"; ?>
        <input type="text" name="Fname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="pass" placeholder="Password" required>
        <input type="password" name="Cpass" placeholder="Confirm Password" required>
        <select name="user" required>
            <option value="freelancer">Freelancer</option>
            <option value="client">Client</option>
        </select>
        <button name="registerbtn" type="submit">Register</button>
    </form>
</div>

</body>
</html>
