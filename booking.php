<?php
require './database/db.php';
require './database/fetch_booking.php';

$services = getServices($pdo);
$specialists = getTherapists($pdo); // Fetch spa specialists
$confirmationMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service'];
    $specialist_id = $_POST['specialist'];
    $appointment_date = $_POST['appointment_date'];
    $time_slot = $_POST['time_slot'];
    $payment_method = $_POST['payment_method'];
    $promo_code = $_POST['promo_code'] ?? null;

    // Validate therapist exists and has 'therapist' role
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE user_id = :user_id AND role = 'therapist'");
    $stmt->execute([':user_id' => $specialist_id]);
    $therapistExists = $stmt->fetchColumn();

    if (!$therapistExists) {
        $confirmationMessage = "Error: Selected therapist does not exist or is not a valid therapist.";
    } else {
        try {
            // Fetch the price of the selected service
            $stmt = $pdo->prepare("SELECT price, duration FROM Services WHERE service_id = :service_id");
            $stmt->execute([':service_id' => $service_id]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if service exists
            if (!$service) {
                throw new Exception("Selected service not found.");
            }

            $service_price = $service['price'];
            $duration = $service['duration']; // Duration in minutes

            // Calculate end time (assuming service duration in minutes)
            $end_time = date("H:i", strtotime("+$duration minutes", strtotime($time_slot)));

            // Insert the appointment into the Appointments table
            $stmt = $pdo->prepare("
                INSERT INTO Appointments 
                (user_id, service_id, therapist_id, appointment_date, start_time, end_time, status) 
                VALUES (:user_id, :service_id, :therapist_id, :appointment_date, :start_time, :end_time, 'pending')
            ");
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'], // Get the user ID from session
                ':service_id' => $service_id,
                ':therapist_id' => $specialist_id,
                ':appointment_date' => $appointment_date,
                ':start_time' => $time_slot,
                ':end_time' => $end_time
            ]);

            // Set the confirmation message in session
            $_SESSION['appointment_confirmation'] = "Appointment Confirmed! Your spa appointment has been booked successfully.";

            // Redirect to user page
            header("Location: user.php");
            exit();
        } catch (PDOException $e) {
            $confirmationMessage = "Error: " . $e->getMessage();
        } catch (Exception $e) {
            $confirmationMessage = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spa Booking Page</title>
    <link rel="stylesheet" href="./bookingPage_SRC/booking.css">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
</head>
<body>
    <header class="header">
        <div class="logo"><a href="#">SpaKol</a></div>
        <nav class="navbar">
            <a href="./index.php">Home</a>
            <a href="./service.php">Services</a>
            <a href="./booking.php">Booking</a>
        </nav>
        <div class="user-icon">
            <a href="./user.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                </svg>
            </a>
        </div>
    </header>

    <section id="booking-page">
        <div class="container">
            <?php if (!empty($confirmationMessage)): ?>
                <!-- Confirmation Message -->
                <div class="confirmation-message">
                    <h1><?= $confirmationMessage ?></h1>
                </div>
            <?php else: ?>
                <!-- Booking Form -->
                <form action="" method="POST">
                    <!-- Step 1 -->
                    <div class="step">
                        <h2>Step 1: Select Service and Specialist</h2>
                        <label for="service">Spa Service:</label>
                        <select id="service" name="service">
                            <?php foreach ($services as $service): ?>
                                <option value="<?= $service['service_id']; ?>">
                                    <?= $service['service_name']; ?> - ₱<?= number_format($service['price'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="specialist">Spa Specialist:</label>
                        <select id="specialist" name="specialist">
                            <?php foreach ($specialists as $specialist): ?>
                                <option value="<?= $specialist['user_id']; ?>">
                                    <?= $specialist['full_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Step 2 -->
                    <div class="step">
                        <h2>Step 2: Choose Date and Time</h2>
                        <label for="appointment-date">Select Date:</label>
                        <input type="date" id="appointment-date" name="appointment_date" required>
                        
                        <label for="time-slot">Available Time Slots:</label>
                        <select id="time-slot" name="time_slot">
                            <option value="09:00">09:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="14:00">02:00 PM</option>
                        </select>
                    </div>

                    <!-- Step 3 -->
                    <div class="step">
                        <h2>Step 3: Confirmation and Payment</h2>
                        <label for="payment-method">Payment Method:</label>
                        <select id="payment-method" name="payment_method">
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="paypal">PayPal</option>
                        </select>

                        <label for="promo-code">Promo Code:</label>
                        <input type="text" id="promo-code" name="promo_code" placeholder="Enter Promo Code">
                        
                        <button type="submit" class="confirm-btn">Confirm Appointment</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>
