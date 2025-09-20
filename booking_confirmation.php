<?php
session_start();
require_once 'db.php';

$booking = null;
if (isset($_GET['hotel_id'])) {
    $stmt = $conn->prepare("SELECT b.*, h.name, h.city FROM bookings b JOIN hotels h ON b.hotel_id = h.id WHERE b.hotel_id = ? AND b.user_id = ? ORDER BY b.booking_date DESC LIMIT 1");
    $stmt->execute([$_GET['hotel_id'], isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Hotels.com Clone</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .confirmation-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .confirmation-container h2 {
            color: #1a73e8;
            margin-bottom: 1rem;
        }

        .confirmation-container p {
            margin-bottom: 0.5rem;
            color: #555;
        }

        .confirmation-container button {
            padding: 0.8rem 1.5rem;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 1rem;
            transition: background 0.3s;
        }

        .confirmation-container button:hover {
            background: #1557b0;
        }

        @media (max-width: 768px) {
            .confirmation-container {
                margin: 1rem;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <?php if ($booking): ?>
            <h2>Booking Confirmed!</h2>
            <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['name']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($booking['city']); ?></p>
            <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($booking['checkin_date']); ?></p>
            <p><strong>Check-out Date:</strong> <?php echo htmlspecialchars($booking['checkout_date']); ?></p>
            <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
            <button onclick="window.location.href = 'index.php'">Back to Home</button>
        <?php else: ?>
            <p style="color: #d32f2f;">No booking found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
