<?php
include './database/db.php';

session_start();  

require './database/db.php'; 


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user['role'] === 'admin') {
    header("Location: admin-dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update-profile'])) {

    $full_name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone'];


    $sql = "UPDATE Users SET full_name = :full_name, email = :email, phone_number = :phone_number, updated_at = NOW() WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone_number', $phone_number);
    $stmt->bindParam(':user_id', $user_id);
    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Failed to update profile.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change-password'])) {
    $old_password = $_POST['old-password'];
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];

    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM Users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($old_password, $user_data['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql = "UPDATE Users SET password = :password, updated_at = NOW() WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_id', $user_id);

            if ($stmt->execute()) {
                $message = "Password changed successfully!";
            } else {
                $message = "Failed to change password.";
            }
        } else {
            $message = "Old password is incorrect.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="./userPage_SRC/user.css">
</head>

<body>
    <header class="navbar">
        <div class="logo">SpaKol</div>
        <nav>
            <a href="home.html">Home</a>
            <a href="#about">About</a>
            <a href="services.html">Services</a>
            <a href="#contacts">Contact</a>
        </nav>
        <div class="user-icon">
            <a href="./profile.php"><svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
            </svg></a>
        </div>
    </header>
    <div class="container">
        <h1>Account Settings</h1>

        <?php
        if (isset($message)) {
            echo "<p class='message'>$message</p>";
        }
        ?>

        <div class="profile">
            <h2>Edit Profile</h2>
            <form id="profile-form" action="profile.php" method="POST">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" value="<?php echo $user['full_name']; ?>" required>

                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo $user['email']; ?>" required>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" value="<?php echo $user['phone_number']; ?>" required>

                <button type="submit" name="update-profile">Save Changes</button>
            </form>

            <h2>Change Password</h2>
            <form action="profile.php" method="POST">
                <label for="old-password">Old Password</label>
                <input type="password" id="old-password" name="old-password" placeholder="Enter your old password" required>

                <label for="new-password">New Password</label>
                <input type="password" id="new-password" name="new-password" placeholder="Enter your new password" required>

                <label for="confirm-password">Confirm New Password</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your new password" required>

                <button type="submit" name="change-password">Change Password</button>
            </form>
        </div>
    </div>
</body>

</html>
