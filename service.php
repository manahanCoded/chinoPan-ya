<?php
require './database/db.php';


$typeFilter = $_GET['service_type'] ?? 'all';
$priceFilter = $_GET['price'] ?? 'all';
$durationFilter = $_GET['duration'] ?? 'all';
$sortBy = $_GET['sort'] ?? 'price';


$query = "SELECT * FROM Services WHERE 1=1";
$params = [];


if ($typeFilter !== 'all') {
    $query .= " AND service_type = :service_type";
    $params[':service_type'] = $typeFilter;
}

if ($priceFilter !== 'all') {
    if ($priceFilter === 'low') {
        $query .= " AND price <= 1000";
    } elseif ($priceFilter === 'medium') {
        $query .= " AND price > 1000 AND price <= 2000";
    } elseif ($priceFilter === 'high') {
        $query .= " AND price > 2000";
    }
}

if ($durationFilter !== 'all') {
    if ($durationFilter === 'short') {
        $query .= " AND duration <= 60";
    } elseif ($durationFilter === 'medium') {
        $query .= " AND duration > 60 AND duration <= 90";
    } elseif ($durationFilter === 'long') {
        $query .= " AND duration > 90";
    }
}

if ($sortBy === 'price') {
    $query .= " ORDER BY price ASC";
} elseif ($sortBy === 'duration') {
    $query .= " ORDER BY duration ASC";
}


$stmt = $pdo->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Massage Services</title>
    <link rel="stylesheet" href="./servicePage_SRC/services.css">
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
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        try {
            $stmt = $pdo->prepare("SELECT email FROM Users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_email = $user['email'] ?? "User";
        } catch (PDOException $e) {
            $user_email = "User";
        }
        ?>
        <span><?php echo htmlspecialchars($user_email); ?></span>
        <form action="logout.php" method="post" style="display:inline;">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    <?php else: ?>
        <a href="register.php">Register</a>
        <a href="./login.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
            </svg>
        </a>
    <?php endif; ?>
</div>

    </header>

    <section class="filters">
        <form method="GET" action="">
            <div class="filter-options">
                <select id="service-type-filter" name="service_type">
                    <option value="all" <?php if ($typeFilter === 'all') echo 'selected'; ?>>All Services</option>
                    <option value="foot-massage" <?php if ($typeFilter === 'foot-massage') echo 'selected'; ?>>Foot Massage</option>
                    <option value="aromatherapy-massage" <?php if ($typeFilter === 'aromatherapy-massage') echo 'selected'; ?>>Aromatherapy Massage</option>
                    <option value="head-massage" <?php if ($typeFilter === 'head-massage') echo 'selected'; ?>>Head, Neck & Shoulder Massage</option>
                    <option value="add-ons" <?php if ($typeFilter === 'add-ons') echo 'selected'; ?>>Add Ons</option>
                    <option value="full-body-massage" <?php if ($typeFilter === 'full-body-massage') echo 'selected'; ?>>Full Body Massage</option>
                    <option value="relaxing-facial" <?php if ($typeFilter === 'relaxing-facial') echo 'selected'; ?>>Relaxing Facial</option>
                    <option value="hot-stone-therapy" <?php if ($typeFilter === 'hot-stone-therapy') echo 'selected'; ?>>Hot Stone Therapy</option>
                </select>

                <select id="price-filter" name="price">
                    <option value="all" <?php if ($priceFilter === 'all') echo 'selected'; ?>>All Prices</option>
                    <option value="low" <?php if ($priceFilter === 'low') echo 'selected'; ?>>₱0 - ₱1000</option>
                    <option value="medium" <?php if ($priceFilter === 'medium') echo 'selected'; ?>>₱1001 - ₱2000</option>
                    <option value="high" <?php if ($priceFilter === 'high') echo 'selected'; ?>>₱2001+</option>
                </select>

                <select id="duration-filter" name="duration">
                    <option value="all" <?php if ($durationFilter === 'all') echo 'selected'; ?>>All Durations</option>
                    <option value="short" <?php if ($durationFilter === 'short') echo 'selected'; ?>>Under 1 hour</option>
                    <option value="medium" <?php if ($durationFilter === 'medium') echo 'selected'; ?>>1 hour - 1.5 hours</option>
                    <option value="long" <?php if ($durationFilter === 'long') echo 'selected'; ?>>1.5 hours+</option>
                </select>

                <select id="sort-filter" name="sort">
                    <option value="price" <?php if ($sortBy === 'price') echo 'selected'; ?>>Sort by Price</option>
                    <option value="duration" <?php if ($sortBy === 'duration') echo 'selected'; ?>>Sort by Duration</option>
                </select>

                <button type="submit">Filter</button>
            </div>
        </form>
    </section>

    <section id="service-cards" class="service-cards">
    <?php 
    $image_names = ['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg', '7.jpg']; 
    $index = 0;
    foreach ($services as $service): 
        if ($index >= count($image_names)) {
            $index = 0; 
        }

        $service_price = isset($service['price']) ? number_format($service['price'], 2) : '0.00';
        $service_duration = isset($service['duration']) ? htmlspecialchars($service['duration']) : 'N/A';
        $service_type = isset($service['service_type']) ? htmlspecialchars($service['service_type']) : 'undefined';
        $service_name = isset($service['service_name']) ? htmlspecialchars($service['service_name']) : 'No Name';
        $service_description = isset($service['description']) ? htmlspecialchars($service['description']) : 'No Description';
    ?>
        <div class="service-card" data-type="<?= $service_type; ?>" data-price="<?= $service_price; ?>" data-duration="<?= $service_duration; ?>">
            <img src="./servicePage_SRC/<?= htmlspecialchars($image_names[$index]); ?>" alt="<?= $service_name; ?>">
            <h3><?= $service_name; ?></h3>
            <p><?= $service_description; ?></p>
            <p class="price">₱<?= $service_price; ?></p>
            <p class="duration">Duration: <?= $service_duration; ?> mins</p>
            <a href="./booking.php?service_id=<?= htmlspecialchars($service['service_id']); ?>"><button>Book Now</button></a>
        </div>
    <?php 
    $index++;
    endforeach; 
    ?>
</section>


<script src="./servicePage_SRC/services.js"></script>
</body>

</html>
