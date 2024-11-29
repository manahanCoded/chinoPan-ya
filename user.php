<?php
require './database/db.php'; // Include the PDO database connection
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: logIn.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Retrieve the logged-in user ID

try {
    // Fetch user details from the database
    $stmt = $pdo->prepare("SELECT full_name, email, phone_number FROM Users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found.");
    }

    // Fetch bookings (both upcoming and completed) for the user
    $stmtBookings = $pdo->prepare("
        SELECT 
            a.appointment_date, 
            a.start_time, 
            a.end_time, 
            s.service_name, 
            u.full_name AS therapist_name, 
            a.status 
        FROM Appointments a
        JOIN Services s ON a.service_id = s.service_id
        JOIN Users u ON a.therapist_id = u.user_id
        WHERE a.user_id = ?
        ORDER BY a.appointment_date ASC, a.start_time ASC
    ");
    $stmtBookings->execute([$user_id]);
    $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Show confirmation message after booking
$confirmationMessage = $_SESSION['appointment_confirmation'] ?? '';
unset($_SESSION['appointment_confirmation']); // Remove message after showing it
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
            <a href="./user.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
                </svg>
            </a>
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
                <!-- All Bookings -->
                <div class="appointment-container">
                    <h2>Your Bookings</h2>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <div class="appointment-item">
                                <div>
                                    <strong>Service:</strong> <?php echo htmlspecialchars($booking['service_name']); ?><br>
                                    <strong>Therapist:</strong> <?php echo htmlspecialchars($booking['therapist_name']); ?><br>
                                    <strong>Date:</strong> <?php echo htmlspecialchars($booking['appointment_date']); ?><br>
                                    <strong>Time:</strong> <?php echo htmlspecialchars($booking['start_time']); ?> - <?php echo htmlspecialchars($booking['end_time']); ?><br>
                                    <strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                </div>
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <div>
                                        <a href=""><button class="reschedule">Reschedule</button></a>
                                        <a href=""><button class="cancel">Cancel</button></a>
                                    </div>
                                <?php elseif ($booking['status'] === 'completed'): ?>
                                    <div>
                                        <a href=""><button class="leave">Leave Review</button></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No bookings found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($confirmationMessage)): ?>
            <div class="confirmation-message">
                <h1><?= $confirmationMessage ?></h1>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
