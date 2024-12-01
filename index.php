<?php
session_start();

require './database/db.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChinoPa-nya Spakol</title>
    <link rel="stylesheet" href="./homePage_SRC/style.css">
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
            <a href="./user.php">Appointment</a>
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
            <button type="submit" class="logout-btn" style="padding: 10px; background-color: #DEAA79; color: white; border: none; border-radius: 5px; border-style: solid; cursor: pointer;">Logout</button>
        </form>
    <?php else: ?>
        <a href="./login.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
            </svg>
        </a>
    <?php endif; ?>
</div>

    </header>

    <section class="hero" id="home">
        <img src="./homePage_SRC/young-woman-having-face-massage-relaxing-spa-salon.jpg" alt="Serene Spa Environment" class="hero-image">
        <div class="hero-content">
            <h1>Find Your Inner Peace</h1>
            <p>Unwind in Tranquility</p>
            <div class="hero-buttons">
                <a href="./booking.php"><button>Book Now</button></a>
                <a href="./service.php"><button>View Services</button></a>
            </div>
        </div>
    </section>

    <section class="services" id="services">
        <h2>Our Featured Services</h2>
        <div class="services-grid">
            <div class="service-card">
                <img src="./homePage_SRC/masahe.jpg" alt="Full Body Massage">
                <h3>Full Body Massage</h3>
                <p>Relieve stress and rejuvenate your body with our signature massage.</p>
                <p><strong>&#8369;500</strong></p>
                <a href="./booking.php"><button>Book Now</button></a>
            </div>
            <div class="service-card">
                <img src="./homePage_SRC/facial.jpg" alt="Relaxing Facial">
                <h3>Relaxing Facial</h3>
                <p>Rejuvenate your skin with our calming facial treatments.</p>
                <p><strong>&#8369;250</strong></p>
                <a href="./booking.php"><button>Book Now</button></a>
            </div>
            <div class="service-card">
                <img src="./homePage_SRC/rock.jpg" alt="Hot Stone Therapy">
                <h3>Hot Stone Therapy</h3>
                <p>Release tension and balance your energy with warm stone therapy.</p>
                <p><strong>&#8369;600</strong></p>
                <a href="./booking.php"><button>Book Now</button></a>
            </div>
        </div>
    </section>

    <section class="testimonials" id="testimonials">
        <h2>What Our Clients Say</h2>
        <div class="testimonials-slider">
            <div class="testimonial-card">
                <img src="./homePage_SRC/jaybee.jpg" alt="Client Testimonial">
                <p>"Best spa experience I've ever had in the world!."</p>
                <p><strong>★★★★★</strong></p>
            </div>
            <div class="testimonial-card">
                <img src="./homePage_SRC/jaybee.jpg" alt="Client Testimonial">
                <p>"Sobrang ganda nya OMG"</p>
                <p><strong>★★★★★</strong></p>
            </div>
        </div>
    </section>

    <footer class="cta">
        <?php
        if (isset($_SESSION['role'])) {
            if ($_SESSION['role'] == 'admin') {
                // If the user is an admin, show the Admin Dashboard button
                echo '<a href="admin.php"><button>Admin Dashboard</button></a>';
            } else {
                // If the user is not an admin, show the "Create Account" button
                echo '<a href="register.php"><button>Create Account</button></a>';
            }
        } else {
            // If the user is not logged in, just show the "Create Account" button
            echo '<a href="register.php"><button>Create Account</button></a>';
        }
        ?>

    <a href="booking.php"><button>Schedule Now</button></a>
    </footer>
</body>
</html>
