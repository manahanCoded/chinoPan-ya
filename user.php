<?php
require './database/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$upcomingStmt = $pdo->prepare("SELECT * FROM Appointments WHERE user_id = :user_id AND status IN ('pending', 'confirmed') ORDER BY appointment_date, start_time");
$upcomingStmt->bindParam(':user_id', $user_id);
$upcomingStmt->execute();
$upcomingAppointments = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

$completedStmt = $pdo->prepare("SELECT * FROM Appointments WHERE user_id = :user_id AND status = 'completed' ORDER BY appointment_date DESC");
$completedStmt->bindParam(':user_id', $user_id);
$completedStmt->execute();
$completedAppointments = $completedStmt->fetchAll(PDO::FETCH_ASSOC);

$promoStmt = $pdo->prepare("SELECT * FROM Promotions WHERE CURDATE() BETWEEN start_date AND end_date");
$promoStmt->execute();
$promotions = $promoStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="./userPage_SRC/user.css">
</head>

<body>
    <header class="navbar">
        <div class="logo">SpaKol</div>
        <nav>
                <a href="./index.php">Home</a>
                <a href="./service.php">Services</a>
                <a href="./booking.php">Booking</a>
        </nav>
        <div class="user-icon">
            <a href="./user.php"><svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor"
                    class="bi bi-person-fill" viewBox="0 0 16 16">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
                </svg></a>
        </div>
    </header>

    <div class="container">
        <div class="center-bod">
            <div class="profile-container">
                <div class="profile-header">
                    <img src="profile-picture.jpg" alt="Profile Picture" class="profile-image">
                    <a href="account.php">
                        <button class="edit-button">Edit</button>
                    </a>
                </div>
                <div class="profile-details">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="design-1">
                <button class="btn book">BOOK NOW</button>
            </div>
            <div class="design-2">
                <div class="appointment-container">
                    <h2>Upcoming Appointments</h2>
                    <?php foreach ($upcomingAppointments as $appointment): ?>
                        <div class="appointment-item">
                            <div>DATE: <?php echo $appointment['appointment_date']; ?> TIME: <?php echo $appointment['start_time']; ?> </div>
                            <div>
                                <a href=""><button class="reschedule">Reschedule</button></a>
                                <a href=""><button class="cancel">Cancel</button></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="appointment-container">
                    <h2>Completed Appointments</h2>
                    <?php foreach ($completedAppointments as $appointment): ?>
                        <div class="appointment-item">
                            <div>DATE: <?php echo $appointment['appointment_date']; ?> TIME: <?php echo $appointment['start_time']; ?></div>
                            <div>
                                <a href=""><button class="leave">Leave Review</button></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="promotions-rewards">
            <h2>Promotions and Rewards</h2>
            <?php foreach ($promotions as $promo): ?>
                <div class="promotion">
                    <h3><?php echo htmlspecialchars($promo['promo_code']); ?>: <?php echo htmlspecialchars($promo['description']); ?></h3>
                    <p>Valid until <?php echo $promo['end_date']; ?> with a discount of <?php echo $promo['discount_percent']; ?>%.</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>
