<?php
session_start();
require_once 'db.php';

$hotel = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    $hotel_id = $_POST['hotel_id'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default user for demo
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, hotel_id, checkin_date, checkout_date, booking_date) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt->execute([$user_id, $hotel_id, $checkin, $checkout])) {
        echo "<script>window.location.href = 'booking_confirmation.php?hotel_id=$hotel_id';</script>";
        exit;
    } else {
        $error = "Error in booking. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Details - Hotels.com Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
        }

        header {
            background: #1a73e8;
            color: white;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .hotel-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .hotel-details h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .hotel-details p {
            margin-bottom: 0.5rem;
            color: #555;
        }

        .hotel-details .price {
            font-size: 1.5rem;
            color: #1a73e8;
            font-weight: bold;
            margin: 1rem 0;
        }

        .booking-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
        }

        .booking-form input {
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .booking-form button {
            padding: 0.8rem;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .booking-form button:hover {
            background: #1557b0;
        }

        .error {
            color: #d32f2f;
            text-align: center;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1rem;
            }

            .hotel-image {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Hotels.com Clone</h1>
        <p>Hotel Details</p>
    </header>

    <div class="container">
        <?php if ($hotel): ?>
            <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-image">
            <div class="hotel-details">
                <h2><?php echo htmlspecialchars($hotel['name']); ?></h2>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($hotel['city']); ?></p>
                <p><strong>Rating:</strong> <?php echo htmlspecialchars($hotel['rating']); ?> Stars</p>
                <p><strong>Amenities:</strong> <?php echo htmlspecialchars($hotel['amenities']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($hotel['description']); ?></p>
                <p class="price">$<?php echo htmlspecialchars($hotel['price_per_night']); ?>/night</p>
                
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form method="POST" class="booking-form">
                    <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                    <input type="date" name="checkin" required>
                    <input type="date" name="checkout" required>
                    <button type="submit" name="book">Book Now</button>
                </form>
            </div>
        <?php else: ?>
            <p class="error">Hotel not found.</p>
        <?php endif; ?>
    </div>

    <script>
        // Client-side validation for booking form
        document.querySelector('.booking-form')?.addEventListener('submit', function(e) {
            const checkin = document.querySelector('input[name="checkin"]').value;
            const checkout = document.querySelector('input[name="checkout"]').value;
            if (new Date(checkin) >= new Date(checkout)) {
                alert('Check-out date must be after check-in date.');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
