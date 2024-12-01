<?php
session_start(); 
include './database/db.php'; 

// Redirect if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: user.php");
    exit();
}

// Initialize error variable
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if both email and password are provided
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        try {
            // Prepare SQL query to check if the email exists in the database
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Fetch user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if user exists and the password matches
            if ($user && password_verify($password, $user['password'])) {
                // Store user data in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username']; // Add username to session
                $_SESSION['role'] = $user['role']; // Add role to session

                // Redirect to user page or admin page based on the role
                if ($user['role'] == 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: user.php");
                }
                exit();
            } else {
                $error = "Invalid email or password."; // Error message for incorrect credentials
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage(); // Error handling for database issues
        }
    } else {
        $error = "Please provide both email and password."; // Error if fields are empty
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./userPage_SRC/sign.css">
</head>
<body>
    <div class="container">
        <h1 class="headd">LOGIN</h1>
        <h3>Welcome!</h3>

        <!-- Display error message if exists -->
        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Login form -->
        <form method="POST" action="logIn.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" autocomplete="email" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
            
            <button type="submit">Login</button>
        </form>

        <!-- Link to register page if user doesn't have an account -->
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        <a href="index.php" class="back-home">Go Back</a>
    </div>
</body>
</html>
