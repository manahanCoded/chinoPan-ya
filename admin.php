<?php
require './database/db.php'; // Include your database connection

$queryServices = "SELECT * FROM Services";
$stmtServices = $pdo->query($queryServices);
$services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

// Fetch bookings with filters for status
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'all';
$queryBookings = "
    SELECT 
        b.payment_id, 
        a.appointment_id, 
        b.amount, 
        b.payment_method, 
        b.payment_status, 
        b.payment_date, 
        a.appointment_date AS date, 
        a.start_time, 
        a.end_time, 
        u.full_name AS therapist_name,
        u2.full_name AS customer_name,
        s.service_name,
        a.status AS appointment_status
    FROM booking b
    JOIN Appointments a ON b.appointment_id = a.appointment_id
    JOIN Users u ON a.therapist_id = u.user_id
    JOIN Users u2 ON a.user_id = u2.user_id
    JOIN Services s ON a.service_id = s.service_id
";

if ($filterStatus !== 'all') {
    $queryBookings .= " WHERE a.status = :status";
}

$stmtBookings = $pdo->prepare($queryBookings);
if ($filterStatus !== 'all') {
    $stmtBookings->execute([':status' => $filterStatus]);
} else {
    $stmtBookings->execute();
}
$bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

// Handle POST requests for status updates
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $bookingId = $_POST['booking_id'];

    if ($action === 'approve') {
        $newStatus = 'confirmed';
    } elseif ($action === 'cancel') {
        $newStatus = 'canceled';
    }

    if (isset($newStatus)) {
        try {
            $pdo->beginTransaction();

            // Update booking table
            $queryBooking = "UPDATE booking SET payment_status = :status WHERE payment_id = :booking_id";
            $stmtBooking = $pdo->prepare($queryBooking);
            $stmtBooking->execute([ 
                ':status' => $newStatus,
                ':booking_id' => $bookingId
            ]);

            // Update Appointments table
            $queryAppointment = "
                UPDATE Appointments
                SET status = :status
                WHERE appointment_id = (
                    SELECT appointment_id FROM booking WHERE payment_id = :booking_id
                )";
            $stmtAppointment = $pdo->prepare($queryAppointment);
            $stmtAppointment->execute([
                ':status' => $newStatus,
                ':booking_id' => $bookingId
            ]);

            $pdo->commit();
            $message = "Booking successfully $action.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Error: " . $e->getMessage();
        }

        // Re-fetch updated bookings after the operation
        $stmtBookings->execute();
        $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);
    }
}


// Fetch services
$queryServices = "SELECT * FROM Services";
$stmtServices = $pdo->query($queryServices);
$services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

$message = '';

// Handle add, edit, and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        // Add a new service
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $duration = $_POST['duration'];

        $queryAdd = "INSERT INTO Services (service_name, description, price, duration) VALUES (:name, :description, :price, :duration)";
        $stmtAdd = $pdo->prepare($queryAdd);
        $stmtAdd->execute([':name' => $name, ':description' => $description, ':price' => $price, ':duration' => $duration]);

        $message = "Service added successfully!";
    } elseif (isset($_POST['edit_service'])) {
        // Edit an existing service
        $id = $_POST['service_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $duration = $_POST['duration'];

        $queryEdit = "UPDATE Services SET service_name = :name, description = :description, price = :price, duration = :duration WHERE service_id = :id";
        $stmtEdit = $pdo->prepare($queryEdit);
        $stmtEdit->execute([':name' => $name, ':description' => $description, ':price' => $price, ':duration' => $duration, ':id' => $id]);

        $message = "Service updated successfully!";
    } elseif (isset($_POST['delete_service'])) {
        // Delete a service
        $id = $_POST['service_id'];

        // Check if the service is referenced in appointments
        $queryCheck = "SELECT COUNT(*) FROM Appointments WHERE service_id = :id";
        $stmtCheck = $pdo->prepare($queryCheck);
        $stmtCheck->execute([':id' => $id]);
        $referenceCount = $stmtCheck->fetchColumn();

        if ($referenceCount > 0) {
            $message = "Cannot delete service. It is currently used in $referenceCount appointment(s).";
        } else {
            $queryDelete = "DELETE FROM Services WHERE service_id = :id";
            $stmtDelete = $pdo->prepare($queryDelete);
            $stmtDelete->execute([':id' => $id]);

            $message = "Service deleted successfully!";
        }
    }

    // Refresh the services list after any action
    $stmtServices = $pdo->query($queryServices);
    $services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);
}
$queryPayments = "
    SELECT 
        b.payment_id, 
        b.appointment_id, 
        b.amount, 
        b.payment_status, 
        b.payment_date 
    FROM booking b
";
$stmtPayments = $pdo->query($queryPayments);
$payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);

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
    <section id="manage-bookings">
        <h2>Manage Bookings</h2>
        <?php if (!empty($message)): ?>
            <p style="color: green;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="GET">
            <label for="status-filter">Filter by Status:</label>
            <select name="status" id="status-filter" onchange="this.form.submit()">
                <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All</option>
                <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $filterStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="canceled" <?= $filterStatus === 'canceled' ? 'selected' : '' ?>>Canceled</option>
            </select>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Therapist</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= $booking['payment_id'] ?></td>
                        <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                        <td><?= htmlspecialchars($booking['service_name']) ?></td>
                        <td><?= htmlspecialchars($booking['therapist_name']) ?></td>
                        <td><?= htmlspecialchars($booking['date']) ?></td>
                        <td><?= htmlspecialchars($booking['start_time']) ?> - <?= htmlspecialchars($booking['end_time']) ?></td>
                        <td><?= htmlspecialchars($booking['appointment_status']) ?></td>
                        <td>
                        <form method="POST" action="#manage-bookings" style="display: inline;">
                            <input type="hidden" name="booking_id" value="<?= $booking['payment_id'] ?>">
                            <button type="submit" name="action" value="approve">Approve</button>
                        </form>
                        <form method="POST" action="#manage-bookings" style="display: inline;">
                            <input type="hidden" name="booking_id" value="<?= $booking['payment_id'] ?>">
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
    <?php if (!empty($message)): ?>
        <p style="color: green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
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
                    <form method="POST">
                        <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                        <td><input type="text" name="name" value="<?= htmlspecialchars($service['service_name']) ?>" required></td>
                        <td><input type="text" name="description" value="<?= htmlspecialchars($service['description']) ?>" required></td>
                        <td><input type="number" name="price" value="<?= htmlspecialchars($service['price']) ?>" step="0.01" required></td>
                        <td><input type="number" name="duration" value="<?= htmlspecialchars($service['duration']) ?>" required></td>
                        <td>
                            <button type="submit" name="edit_service">Edit</button>
                            <button type="submit" name="delete_service">Delete</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Add New Service</h3>
    <form method="POST">
        <input type="text" name="name" placeholder="Service Name" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="number" name="price" placeholder="Price" step="0.01" required>
        <input type="number" name="duration" placeholder="Duration (mins)" required>
        <button type="submit" name="add_service">Add Service</button>
    </form>
</section>

    <section id="therapist-schedule">
        <h2>Therapist Schedule</h2>
        <form method="POST">
            <label for="therapist">Therapist:</label>
            <select name="therapist_id" id="therapist" required>
                <!-- Populate with therapist data -->
            </select>
            <label for="date">Date:</label>
            <input type="date" name="available_date" id="date" required>
            <label for="start-time">Start Time:</label>
            <input type="time" name="start_time" id="start-time" required>
            <label for="end-time">End Time:</label>
            <input type="time" name="end_time" id="end-time" required>
            <button type="submit" name="add_availability">Add Availability</button>
        </form>
    </section>

    <!-- Payments & Reports -->
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
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= htmlspecialchars($payment['payment_id']) ?></td>
                        <td><?= htmlspecialchars($payment['appointment_id']) ?></td>
                        <td>₱<?= number_format($payment['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($payment['payment_status']) ?></td>
                        <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

        <h3>Reports</h3>
        <canvas id="reportChart"></canvas>
        <script>
            const ctx = document.getElementById('reportChart').getContext('2d');
            const reportChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Bookings', 'Earnings', 'Satisfaction'],
                    datasets: [{
                        label: 'Report Data',
                        data: [100, 20000, 85], // Replace with dynamic data
                        backgroundColor: ['#ff6384', '#36a2eb', '#ffce56']
                    }]
                }
            });
        </script>
    </section>
</main>

</body>
</html>
