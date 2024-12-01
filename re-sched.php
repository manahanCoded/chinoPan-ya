<?php
require './database/db.php'; 
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['appointment_id'])) {
    header("Location: login.php");
    exit();
}

$appointment_id = $_GET['appointment_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_date = $_POST['appointment_date'];
    $new_time = $_POST['appointment_time'];

    try {
        $stmt = $pdo->prepare("UPDATE Appointments SET appointment_date = ?, start_time = ?, status = 'pending' WHERE appointment_id = ? AND user_id = ?");
        $stmt->execute([$new_date, $new_time, $appointment_id, $_SESSION['user_id']]);
        $_SESSION['appointment_confirmation'] = "Appointment rescheduled successfully.";
        header("Location: user.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error rescheduling appointment: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment</title>
</head>

<body>
    <h1>Reschedule Appointment</h1>
    <?php if (isset($error)): ?>
        <p><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="appointment_date">New Date:</label>
        <input type="date" name="appointment_date" required>
        <label for="appointment_time">New Time:</label>
        <input type="time" name="appointment_time" required>
        <button type="submit">Reschedule</button>
    </form>
</body>

</html>
