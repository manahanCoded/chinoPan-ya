<?php
require './database/db.php';
require './database/fetch_data.php';

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

    try {
      
        $stmt = $pdo->prepare("INSERT INTO Appointments (user_id, therapist_id, service_id, appointment_date, start_time, end_time, status) 
            VALUES (:user_id, :specialist_id, :service_id, :appointment_date, :start_time, :end_time, 'pending')");
        $stmt->execute([
            ':user_id' => 1, 
            ':specialist_id' => $specialist_id,
            ':service_id' => $service_id,
            ':appointment_date' => $appointment_date,
            ':start_time' => $time_slot,
            ':end_time' => date("H:i", strtotime("+1 hour", strtotime($time_slot))),
        ]);

        $appointment_id = $pdo->lastInsertId();

        $transaction_id = uniqid('txn_');

        $stmt = $pdo->prepare("INSERT INTO Payments (appointment_id, amount, payment_method, payment_status, transaction_id, payment_date) 
            VALUES (:appointment_id, :amount, :payment_method, 'unpaid', :transaction_id, NOW())");
        $stmt->execute([
            ':appointment_id' => $appointment_id,
            ':amount' => 50.00,
            ':payment_method' => $payment_method,
            ':transaction_id' => $transaction_id,
        ]);

        $confirmationMessage = "Appointment Confirmed! Your spa appointment has been booked successfully. Transaction ID: $transaction_id.";
    } catch (PDOException $e) {
        $confirmationMessage = "Error: " . $e->getMessage();
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
        <div class="logo">
            <a href="#">SpaKol</a>
        </div>
        <nav class="navbar">
                <a href="./index.php">Home</a>
                <a href="./service.php">Services</a>
                <a href="./booking.php">Booking</a>
        </nav>
        <div class="user-icon">
            <a href="./user.php"><svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
            </svg></a>
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
                                    <?= $service['service_name']; ?> - $<?= $service['price']; ?>
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
