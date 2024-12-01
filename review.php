<?php
require './database/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$appointment_id = $_GET['appointment_id'] ?? null;

if (!$appointment_id) {
    die("Invalid appointment ID.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    try {
        // Insert the review into the database
        $stmt = $pdo->prepare("INSERT INTO Reviews (appointment_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$appointment_id, $user_id, $rating, $comment]);

        // Redirect back to the user dashboard with confirmation
        $_SESSION['appointment_confirmation'] = "Thank you for your review!";
        header("Location: user.php");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Review</title>
    <link rel="stylesheet" href="./userPage_SRC/user.css">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <style>
        /* Custom styles for the review form */
        .rating-section {
            display: flex;
            flex-direction: column;
            margin: 20px 0;
        }

        .rating-section label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .rating-section input[type="radio"] {
            display: none;
        }

        .rating-section label {
            cursor: pointer;
            font-size: 18px;
            padding: 10px;
            margin: 0 5px;
            background-color: #f0f0f0;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .rating-section input[type="radio"]:checked + label {
            background-color: #ffcd3c;
        }

        .rating-section label:hover {
            background-color: #ffcc00;
        }

        .comment textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #ffcd3c;
            border: none;
            padding: 10px 20px;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #ffbb00;
        }

        /* Optional additional styles for other fields */
        .container {
            padding: 20px;
            background-color: #f7f7f7;
            max-width: 800px;
            margin: 0 auto;
            border-radius: 10px;
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

    <div class="container">
        <h2>Leave a Review for Your Appointment</h2>
        <form method="POST" action="review.php?appointment_id=<?php echo $appointment_id; ?>">

            <div class="rating-section">
                <label for="rating">Rating:</label>
                <div>
                    <input type="radio" name="rating" value="1" id="star1"><label for="star1">&#9733;</label>
                    <input type="radio" name="rating" value="2" id="star2"><label for="star2">&#9733;</label>
                    <input type="radio" name="rating" value="3" id="star3"><label for="star3">&#9733;</label>
                    <input type="radio" name="rating" value="4" id="star4"><label for="star4">&#9733;</label>
                    <input type="radio" name="rating" value="5" id="star5"><label for="star5">&#9733;</label>
                </div>
            </div>

            <div class="comment">
                <label for="comment">Your Comment:</label>
                <textarea name="comment" id="comment" rows="4" placeholder="Write your review here..."></textarea>
            </div>

            <button type="submit">Submit Review</button>
        </form>
    </div>

</body>

</html>
