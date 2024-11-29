<?php 
require './database/db.php'; // Include your database connection
session_start();  

// Fetch services
$queryServices = "SELECT * FROM Services";
$stmtServices = $pdo->query($queryServices);
$services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

// Fetch appointments with filters for status
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'all';
$queryAppointments = "
    SELECT 
        a.appointment_id, 
        a.appointment_date AS date, 
        a.start_time, 
        a.end_time, 
        a.status AS appointment_status, 
        COALESCE(u.full_name, 'N/A') AS therapist_name,
        COALESCE(u2.full_name, 'N/A') AS customer_name,
        COALESCE(s.service_name, 'N/A') AS service_name
    FROM Appointments a
    LEFT JOIN Users u ON a.therapist_id = u.user_id
    LEFT JOIN Users u2 ON a.user_id = u2.user_id
    LEFT JOIN Services s ON a.service_id = s.service_id
";

if ($filterStatus !== 'all') {
    $queryAppointments .= " WHERE a.status = :status";
}

// Order by appointment date in descending order
$queryAppointments .= " ORDER BY a.appointment_date DESC";

$stmtAppointments = $pdo->prepare($queryAppointments);

if ($filterStatus !== 'all') {
    $stmtAppointments->execute([':status' => $filterStatus]);
} else {
    $stmtAppointments->execute();
}
$appointments = $stmtAppointments->fetchAll(PDO::FETCH_ASSOC);

// Handle POST requests for status updates
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $appointmentId = $_POST['appointment_id'] ?? null;

    if ($action === 'approve') {
        $newStatus = 'confirmed';
    } elseif ($action === 'cancel') {
        $newStatus = 'canceled';
    }

    if (isset($newStatus)) {
        try {
            $queryUpdateAppointment = "
                UPDATE Appointments
                SET status = :status
                WHERE appointment_id = :appointment_id
            ";
            $stmtUpdateAppointment = $pdo->prepare($queryUpdateAppointment);
            $stmtUpdateAppointment->execute([
                ':status' => $newStatus,
                ':appointment_id' => $appointmentId
            ]);

            $message = "Appointment successfully $action.";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }

        // Re-fetch updated appointments after the operation
        $stmtAppointments->execute();
        $appointments = $stmtAppointments->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Fetch services again after potential POST operations
$stmtServices = $pdo->query($queryServices);
$services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

// Handle service actions (add, edit, delete)
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

// Fetch payments for reports
$queryPayments = "
    SELECT 
        p.payment_id, 
        p.appointment_id, 
        p.amount, 
        p.payment_status, 
        p.payment_date 
    FROM Payments p
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
        <a href="#payments-reports">Payments & Reports</a>
    </nav>
</header>

<main>
    <!-- Manage Bookings -->
    <section id="manage-bookings">
        <h2>Manage Appointments</h2>
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
                    <th>Appointment ID</th>
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
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= $appointment['appointment_id'] ?></td>
                        <td><?= htmlspecialchars($appointment['customer_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['therapist_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['date']) ?></td>
                        <td><?= htmlspecialchars($appointment['start_time']) ?> - <?= htmlspecialchars($appointment['end_time']) ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_status']) ?></td>
                        <td>
                            <form method="POST" action="#manage-bookings" style="display:inline;">
                                <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                                <button name="action" value="approve">Approve</button>
                                <button name="action" value="cancel">Cancel</button>
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
        <form method="POST" action="#manage-services">
            <h3>Add or Edit a Service</h3>
            <input type="hidden" name="service_id">
            <label for="name">Service Name:</label>
            <input type="text" name="name" required>
            <label for="description">Description:</label>
            <textarea name="description" required></textarea>
            <label for="price">Price:</label>
            <input type="number" name="price" step="0.01" required>
            <label for="duration">Duration (in mins):</label>
            <input type="number" name="duration" required>
            <button type="submit" name="add_service">Add Service</button>
            <button type="submit" name="edit_service">Edit Service</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Service ID</th>
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
                        <td><?= $service['service_id'] ?></td>
                        <td><?= htmlspecialchars($service['service_name']) ?></td>
                        <td><?= htmlspecialchars($service['description']) ?></td>
                        <td><?= htmlspecialchars($service['price']) ?></td>
                        <td><?= htmlspecialchars($service['duration']) ?> mins</td>
                        <td>
                            <form method="POST" action="#manage-services" style="display:inline;">
                                <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                                <button name="delete_service" onclick="return confirm('Are you sure you want to delete this service?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Payments Reports -->
    <section id="payments-reports">
        <h2>Payments & Reports</h2>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Appointment ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= $payment['payment_id'] ?></td>
                        <td><?= $payment['appointment_id'] ?></td>
                        <td><?= htmlspecialchars($payment['amount']) ?></td>
                        <td><?= htmlspecialchars($payment['payment_status']) ?></td>
                        <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<footer>
    <p>&copy; 2024 SpaKol Admin Panel</p>
</footer>

</body>
</html>
