<?php
require './database/db.php'; 
session_start(); 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; 

try {
    // Fetch user details
    $stmt = $pdo->prepare("SELECT full_name, email, phone_number FROM Users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found.");
    }

    // Handle appointment cancellation
    if (isset($_POST['cancel_appointment_id'])) {
        $appointment_id = intval($_POST['cancel_appointment_id']);

        // Check if the appointment belongs to the user and is cancellable
        $stmt = $pdo->prepare("
            SELECT appointment_id, status 
            FROM Appointments 
            WHERE appointment_id = ? AND user_id = ? AND status = 'pending'
        ");
        $stmt->execute([$appointment_id, $user_id]);
        $appointment = $stmt->fetch();

        if ($appointment) {
            // Update the appointment status to 'cancelled'
            $updateStmt = $pdo->prepare("UPDATE Appointments SET status = 'cancelled' WHERE appointment_id = ?");
            $updateStmt->execute([$appointment_id]);

            $confirmationMessage = "Your appointment has been successfully cancelled.";
        } else {
            $confirmationMessage = "Unable to cancel the appointment. It may have already been processed.";
        }
    }

    // Fetch user bookings and associated reviews
    $stmtBookings = $pdo->prepare("
        SELECT 
            a.appointment_id,
            a.appointment_date, 
            a.start_time, 
            a.end_time, 
            s.service_name, 
            u.full_name AS therapist_name, 
            a.status,
            r.rating AS review_rating,
            r.comment AS review_comment
        FROM Appointments a
        JOIN Services s ON a.service_id = s.service_id
        JOIN Users u ON a.therapist_id = u.user_id
        LEFT JOIN Reviews r ON a.appointment_id = r.appointment_id
        WHERE a.user_id = ?
        ORDER BY a.appointment_date ASC, a.start_time ASC
    ");
    $stmtBookings->execute([$user_id]);
    $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="./userPage_SRC/user.css">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
</head>

<body>
    <header data-aos='zoom-in'>
        <div class="logo"><a href="#">SpaKol</a></div>
        <nav class="navbar">
            <a href="./index.php">Home</a>
            <a href="./service.php">Services</a>
            <a href="./booking.php">Booking</a>
            <a href="./user.php">Appointment</a>
        </nav>
        <div class="user-icon">
            <a href="./user.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
                </svg>
            </a>
        </div>
    </header>
    <div class="container" data-aos="fade-up-right">
        <div class="after-header">
            <div class="welcome-section">
                <h1>Welcome to SpaKol, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                <p>Relax, refresh, and rejuvenate with our premium services. Explore our offerings and manage your bookings below.</p>
                <button onclick="location.href='./service.php'" class="explore-button">Explore Services</button>
            </div>
        </div>
    </div>
        
    <div class="container" data-aos='fade-up'>
        <div class="center-bod">
            <div class="profile-container">
                <div class="profile-header">
                    <img src="./homePage_SRC/jaybee.jpg" alt="Profile Picture" class="profile-image">
                    <a href="./edit.php">
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
                <a href="booking.php"><button class="btn book">BOOK NOW</button></a>
            </div>
            <div class="design-2">
                <div class="appointment-container" data-aos='flip-right'>
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
                                <div>
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <a href="re-sched.php?appointment_id=<?php echo $booking['appointment_id']; ?>"><button class="reschedule">Reschedule</button></a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="cancel_appointment_id" value="<?php echo $booking['appointment_id']; ?>">
                                            <button type="submit" class="cancel">Cancel</button>
                                        </form>
                                    <?php elseif ($booking['status'] === 'completed'): ?>
                                        <?php if ($booking['review_rating']): ?>
                                            <div class="review-section styled-review">
                                                <div class="review-header">
                                                    <span class="review-user"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                                    <span class="review-rating">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <span class="<?php echo $i <= $booking['review_rating'] ? 'filled-star' : 'empty-star'; ?>">★</span>
                                                        <?php endfor; ?>
                                                    </span>
                                                </div>
                                                <p class="review-comment"><?php echo nl2br(htmlspecialchars($booking['review_comment'])); ?></p>
                                            </div>
                                        <?php else: ?>
                                            <a href="review.php?appointment_id=<?php echo $booking['appointment_id']; ?>">
                                                <button class="leave">Leave Review</button>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init();
    </script>
    
</body>

</html>
