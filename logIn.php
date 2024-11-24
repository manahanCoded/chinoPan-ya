<?php
session_start(); 
include './database/db.php'; // Include database connection

// Redirect user to home page if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = ""; // Initialize error message

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate if both email and password are provided
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        try {
            // Prepare SQL statement to find user by email
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password and redirect if valid
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                header("Location: index.php"); // Redirect to home page
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please provide both email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./userPage_SRC/user.css">
</head>
<body>
    <h1>Login</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="logIn.php">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" placeholder="Enter your email" autocomplete="email" required>
    
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
    
    <button type="submit">Login</button>
</form>

</body>
</html>
