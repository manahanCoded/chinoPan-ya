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
    <style>
        body {
    font-family: 'Poppins', sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

header {
    background-color: #DEAA79;
    padding: 15px 0;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-left: 20px;
    padding-right: 20px;
}

header .logo a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    font-size: 30px;
}

.navbar {
    flex: 1;
    display: flex;
    justify-content: center;
}

.navbar {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 15px;
}

.navbar  a {
    color: white;
    text-decoration: none;
    font-size: 18px;
}

.navbar a:hover {
    text-decoration: underline;
}

.user-icon a{
    font-size: 1.5rem;
    cursor: pointer;
    color: white;
}

.after-header {
    color: white;
    padding: 50px 20px;
    text-align: center;
}

.container1 {
    margin: 50px;
    padding: 12px;
    text-align: center;
}

.btn-sched {
    padding: 10px 20px;
    background-color: #DEAA79;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

.btn-sched:hover {
    background-color: #c58d63;
}

.hesoyam{
    width: 40%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    margin-bottom: 5px;
}

        
    </style>
</head>

<body>
<header>
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
    <div class='container1'>
        <h1>Reschedule Appointment</h1>
        <?php if (isset($error)): ?>
            <p><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <div>
                <label for="appointment_date">New Date:</label>
                <input class='hesoyam' type="date" name="appointment_date" required>
            </div>
            <div>
                <label for="appointment_time">New Time:</label>
                <input class='hesoyam' type="time" name="appointment_time" required><br>

            </div>
            <button class='btn-sched' type="submit">Reschedule</button>
        </form>
    </div>
</body>

</html>
