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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelance Login</title>
    <link rel="stylesheet" href="styles/login_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

<div class="login-container">
    <!-- Login Form -->
    <h2>LOGIN</h2>
    <form method="POST", class="login-form">
        <div class="input-group">
                <input type="email" name="login-email" placeholder="Email" required>
        </div>
        <div class="input-group password-group">
            <input type="password" name="login-pass" placeholder="Password" required>
            <span class="password-toggle" onclick="togglePasswordVisibility()">
                <i class="fas fa-eye-slash" id="password-icon"></i>
            </span>
        </div>
        <button name="loginbtn" type="submit" class="login-button">Log In</button>
        <?php if (!empty($errors)) foreach ($errors as $e) echo "<p class='error'>$e</p>"; ?>
    </form>

    <div class="links">
            <p>Don't have an account? <a href="#">Sign Up</a></p>
            <p><a href="#">Forgot Password?</a></p>
    </div>

    <!-- Toggle Link -->
    <div class="links" onclick="document.getElementById('signup').style.display='block'">Create Account</div>

    <!-- Signup Form -->
    <form id="signup" method="POST" style="display:none;">
        <h2>Sign Up</h2>
        <?php if (!empty($error)) foreach ($error as $e) echo "<p class='error'>$e</p>"; ?>

        <div class="input-group">
            <input type="text" name="Fname" placeholder="Full Name" required>
        </div>

        <div class="input-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-group password-group">
            <input type="password" name="pass" placeholder="Password" required>
            <input type="password" name="Cpass" placeholder="Confirm Password" required>
            </span>
        </div>
        
        <select name="user" required>
            <option value="freelancer">Freelancer</option>
            <option value="client">Client</option>
        </select>
        <button name="registerbtn" type="submit">Register</button>
    </form>
</div>

</body>
</html>
