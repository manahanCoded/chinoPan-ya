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
        <div class="logo"><a href="#">SpaKol</a></div>
        <nav class="navbar">
            <a href="./index.html">Home</a>
            <a href="./service.html">Services</a>
            <a href="#contacts">Contact</a>
        </nav>
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
        <?php foreach ($services as $service): ?>
            <div class="service-card" data-type="<?= htmlspecialchars($service['service_type']); ?>" data-price="<?= $service['price']; ?>" data-duration="<?= $service['duration']; ?>">
                <img src="<?= htmlspecialchars($service['image_url']); ?>" alt="<?= htmlspecialchars($service['service_name']); ?>">
                <h3><?= htmlspecialchars($service['service_name']); ?></h3>
                <p><?= htmlspecialchars($service['description']); ?></p>
                <p class="price">₱<?= number_format($service['price'], 2); ?></p>
                <p class="duration">Duration: <?= $service['duration']; ?> mins</p>
                <a href="./booking.php?service_id=<?= $service['service_id']; ?>"><button>Book Now</button></a>
            </div>
        <?php endforeach; ?>
    </section>

<script src="./servicePage_SRC/services.js"></script>
</body>

</html>
