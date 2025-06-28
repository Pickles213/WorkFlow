<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type']; // either 'client' or 'freelancer'

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_hired = $_POST['date_hired'];
    $strengths = $_POST['strengths'];
    $bio = $_POST['bio'];

    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . "." . $ext;
        $upload_path = 'uploads/' . $filename;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path);
        $profile_picture = $upload_path;

        $stmt = $conn->prepare("UPDATE users SET profile_picture = ?, date_hired = ?, strengths = ?, bio = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $profile_picture, $date_hired, $strengths, $bio, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET date_hired = ?, strengths = ?, bio = ? WHERE id = ?");
        $stmt->bind_param("sssi", $date_hired, $strengths, $bio, $user_id);
    }

    $stmt->execute();
    $stmt->close();
    $success = "Profile updated!";
}

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
</head>
<body>
    <h2>Edit <?php echo ucfirst($user_type); ?> Profile</h2>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <p>
            <label>Profile Picture:</label><br>
            <?php if (!empty($user['profile_picture'])): ?>
                <img src="<?php echo $user['profile_picture']; ?>" alt="Profile" width="100"><br>
            <?php endif; ?>
            <input type="file" name="profile_picture">
        </p>

        <p>
            <label>Date Hired:</label><br>
            <input type="date" name="date_hired" value="<?php echo htmlspecialchars($user['date_hired']); ?>">
        </p>

        <p>
            <label>Strengths:</label><br>
            <textarea name="strengths" rows="3" cols="40"><?php echo htmlspecialchars($user['strengths']); ?></textarea>
        </p>

        <p>
            <label>Bio:</label><br>
            <textarea name="bio" rows="5" cols="40"><?php echo htmlspecialchars($user['bio']); ?></textarea>
        </p>

        <p>
            <button type="submit">Update Profile</button>
        </p>
    </form>

    <p><a href="<?php echo $user_type === 'client' ? 'client_dashboard.php' : 'freelancer_dashboard.php'; ?>">Back to Dashboard</a></p>
</body>
</html>
