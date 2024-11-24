<?php
require './database/db.php';
include 'db.php';

$queryServices = "SELECT * FROM services";
$stmtServices = $pdo->query($queryServices);
$services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

$queryBookings = "SELECT * FROM bookings";
$stmtBookings = $pdo->query($queryBookings);
$bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        $query = "INSERT INTO services (name, description, price, duration) VALUES (:name, :description, :price, :duration)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':name' => $_POST['name'],
            ':description' => $_POST['description'],
            ':price' => $_POST['price'],
            ':duration' => $_POST['duration']
        ]);
    }

    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $bookingId = $_POST['booking_id'];
        
        if ($action === 'approve') {
            $query = "UPDATE bookings SET status = 'confirmed' WHERE booking_id = :booking_id";
        } elseif ($action === 'cancel') {
            $query = "UPDATE bookings SET status = 'cancelled' WHERE booking_id = :booking_id";
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute([':booking_id' => $bookingId]);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./adminPage_SRC/admin.css">
</head>
<body>

<header>
    <div class="logo"><a href="#">SpaKol Admin</a></div>
    <nav>
        <a href="#manage-bookings">Manage Bookings</a>
        <a href="#manage-services">Manage Services</a>
        <a href="#therapist-schedule">Therapist Schedule</a>
        <a href="#payments-reports">Payments & Reports</a>
    </nav>
</header>

<main>
    <!-- Manage Bookings -->
    <section id="manage-bookings">
        <h2>Manage Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Therapist</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= $booking['booking_id'] ?></td>
                        <td><?= $booking['customer_name'] ?></td>
                        <td><?= $booking['service_name'] ?></td>
                        <td><?= $booking['therapist_name'] ?></td>
                        <td><?= $booking['date'] ?></td>
                        <td><?= $booking['status'] ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                <button type="submit" name="action" value="approve">Approve</button>
                                <button type="submit" name="action" value="cancel">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Manage Services -->
    <section id="manage-services">
        <h2>Manage Services</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= $service['name'] ?></td>
                        <td><?= $service['description'] ?></td>
                        <td>₱<?= number_format($service['price'], 2) ?></td>
                        <td><?= $service['duration'] ?> mins</td>
                        <td>
                            <a href="edit_service.php?id=<?= $service['service_id'] ?>">Edit</a>
                            <a href="delete_service.php?id=<?= $service['service_id'] ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Add New Service</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Service Name" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="number" name="price" placeholder="Price" required>
            <input type="number" name="duration" placeholder="Duration (mins)" required>
            <button type="submit" name="add_service">Add Service</button>
        </form>
    </section>

    <!-- Therapist Schedule Management -->
    <section id="therapist-schedule">
        <h2>Therapist Schedule</h2>
        <p>Use a calendar here (e.g., FullCalendar) to manage therapist availability.</p>
    </section>

    <!-- Payments & Reports -->
    <section id="payments-reports">
        <h2>Payments & Reports</h2>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Booking ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    // Fetch payments from the database
                    $queryPayments = "SELECT * FROM payments";
                    $stmtPayments = $pdo->query($queryPayments);
                    $payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($payments as $payment): 
                ?>
                    <tr>
                        <td><?= $payment['payment_id'] ?></td>
                        <td><?= $payment['booking_id'] ?></td>
                        <td>₱<?= number_format($payment['amount'], 2) ?></td>
                        <td><?= $payment['status'] ?></td>
                        <td><?= $payment['payment_date'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Reports</h3>
        <p>Generate visual reports here (e.g., Chart.js for bookings, earnings).</p>
    </section>
</main>

</body>
</html>