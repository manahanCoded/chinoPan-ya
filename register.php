<?php
require './database/db.php'; 
session_start();

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['full_name']) && !empty($_POST['email']) && !empty($_POST['phone_number']) && !empty($_POST['password'])) {
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];
        $password = $_POST['password'];

        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $error = "This email is already registered.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $pdo->prepare("
                    INSERT INTO Users (full_name, email, phone_number, password, role) 
                    VALUES (:full_name, :email, :phone_number, :password, 'customer')
                ");
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone_number', $phone_number);
                $stmt->bindParam(':password', $hashedPassword);

                if ($stmt->execute()) {
                    
                    $user_id = $pdo->lastInsertId();
                    $_SESSION['user_id'] = $user_id;

                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="./userPage_SRC/sign.css">
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>    
        <form method="POST" action="register.php">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required>
                
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            
            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" placeholder="Enter your phone number" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            
            <button type="submit">Register</button>
    </form>
    
    <p>Already have an account? <a href="logIn.php">Login here</a>.</p>
</div>
</body>
</html>
