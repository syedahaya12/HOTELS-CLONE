<?php
session_start();
require_once 'db.php';

// Initialize variables
$hotels = [];
$no_results = '';
$bookings = [];
$destination = '';
$price_range = '';
$rating = '';
$checkin = '';
$checkout = '';

// Debug: Verify database connection and data
try {
    $stmt = $conn->prepare("SELECT city FROM hotels LIMIT 1");
    $stmt->execute();
    $sample_city = $stmt->fetchColumn();
    error_log("Sample city from database (hex): " . bin2hex($sample_city) . ", raw: " . $sample_city);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    $no_results = "Database connection failed. Check logs.";
}

// Fetch all hotels by default
try {
    $stmt = $conn->prepare("SELECT * FROM hotels");
    $stmt->execute();
    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Default hotel count: " . count($hotels));
} catch (PDOException $e) {
    $no_results = "Error fetching hotels: " . $e->getMessage();
}

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $destination = trim(strtolower($_POST['destination'] ?? ''));
    $checkin = $_POST['checkin'] ?? '';
    $checkout = $_POST['checkout'] ?? '';
    $price_range = $_POST['price_range'] ?? '';
    $rating = $_POST['rating'] ?? '';

    $query = "SELECT * FROM hotels WHERE 1=1";
    $params = [];

    if ($destination) {
        $query .= " AND LOWER(TRIM(city)) LIKE ?";
        $params[] = "%" . $destination . "%";
        error_log("Search query: $query, Param: $destination");
    }
    if ($price_range) {
        $query .= " AND price_per_night <= ?";
        $params[] = $price_range;
    }
    if ($rating) {
        $query .= " AND rating >= ?";
        $params[] = $rating;
    }

    try {
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Search result count for '$destination': " . count($hotels) . ", Query: $query, Params: " . implode(', ', $params));
        if (empty($hotels)) {
            $no_results = "No hotels found for '$destination'. Try a different city or fewer filters. Debug: Check logs.";
        }
    } catch (PDOException $e) {
        $no_results = "Search error: " . $e->getMessage();
        error_log("Search error: " . $e->getMessage());
    }
}

// Fetch user's bookings if logged in
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("SELECT b.*, h.name, h.city, h.price_per_night FROM bookings b JOIN hotels h ON b.hotel_id = h.id WHERE b.user_id = ? ORDER BY b.booking_date DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Booking fetch error: " . $e->getMessage());
    }
}

// Handle booking
$confirmation = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>window.location.href = 'login.php';</script>";
        exit;
    }
    $hotel_id = $_POST['hotel_id'];
    $user_id = $_SESSION['user_id'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];

    try {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, hotel_id, checkin_date, checkout_date, booking_date) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt->execute([$user_id, $hotel_id, $checkin, $checkout])) {
            echo "<script>window.location.href = 'booking_confirmation.php?hotel_id=$hotel_id';</script>";
            exit;
        } else {
            $confirmation = "Error in booking. Please try again.";
        }
    } catch (PDOException $e) {
        $confirmation = "Booking error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotels.com Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #ffcccb 0%, #d8b4e2 100%);
            color: #333;
            animation: fadeIn 1s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        header {
            background: #e6a8c7;
            color: white;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 15px 15px;
        }

        header h1 {
            font-size: 2.8rem;
            margin-bottom: 0.5rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .search-container {
            background: rgba(255, 204, 203, 0.9);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 850px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            animation: fadeIn 1.2s ease-in;
        }

        .search-container form {
            display: flex;
            flex-wrap: wrap;
            gap: 1.2rem;
        }

        .search-container input, .search-container select {
            padding: 0.9rem;
            border: 2px solid #e6a8c7;
            border-radius: 10px;
            flex: 1;
            min-width: 160px;
            background: #fff;
            transition: border-color 0.3s;
        }

        .search-container input:focus, .search-container select:focus {
            border-color: #d8b4e2;
            outline: none;
        }

        .search-container button {
            padding: 0.9rem 1.8rem;
            background: #e6a8c7;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .search-container button:hover {
            background: #d8b4e2;
            transform: scale(1.05);
        }

        .hotel-list, .booking-list {
            max-width: 1250px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .hotel-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
        }

        .hotel-card, .booking-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .hotel-card:hover, .booking-card:hover {
            animation: bounce 0.8s;
            transform: translateY(-5px);
        }

        .hotel-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }

        .hotel-card-content, .booking-card-content {
            padding: 1.2rem;
        }

        .hotel-card-content h3, .booking-card-content h3 {
            font-size: 1.6rem;
            margin-bottom: 0.6rem;
            color: #e6a8c7;
        }

        .hotel-card-content p, .booking-card-content p {
            margin-bottom: 0.6rem;
            color: #555;
        }

        .hotel-card-content .price, .booking-card-content .price {
            font-size: 1.3rem;
            color: #d8b4e2;
            font-weight: bold;
        }

        .hotel-card-content button, .booking-card-content button {
            width: 100%;
            padding: 0.9rem;
            background: #e6a8c7;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .hotel-card-content button:hover, .booking-card-content button:hover {
            background: #d8b4e2;
            transform: scale(1.05);
        }

        .booking-list h2 {
            color: #e6a8c7;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 2rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }

        .booking-card {
            margin-bottom: 1.5rem;
            background: rgba(216, 180, 226, 0.9);
        }

        .booking-card-content .dates {
            font-style: italic;
            color: #666;
            font-size: 0.9rem;
        }

        .message {
            max-width: 850px;
            margin: 2rem auto;
            padding: 1.2rem;
            border-radius: 10px;
            text-align: center;
            animation: fadeIn 1.2s ease-in;
        }

        .confirmation {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .no-results {
            background: #ffebee;
            color: #c62828;
        }

        @media (max-width: 768px) {
            .search-container form {
                flex-direction: column;
            }

            .hotel-list, .booking-list {
                padding: 0;
            }

            .hotel-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Hotels.com Clone</h1>
        <p>Find your perfect stay 💕</p>
    </header>

    <div class="search-container">
        <form method="POST" action="index.php">
            <input type="text" name="destination" placeholder="Destination 🌍" value="<?php echo htmlspecialchars($destination); ?>">
            <input type="date" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>" required>
            <input type="date" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>" required>
            <select name="price_range">
                <option value="">Select Price Range 💸</option>
                <option value="100" <?php echo $price_range == '100' ? 'selected' : ''; ?>>$100 or less</option>
                <option value="200" <?php echo $price_range == '200' ? 'selected' : ''; ?>>$200 or less</option>
                <option value="300" <?php echo $price_range == '300' ? 'selected' : ''; ?>>$300 or less</option>
            </select>
            <select name="rating">
                <option value="">Select Rating ⭐</option>
                <option value="3" <?php echo $rating == '3' ? 'selected' : ''; ?>>3 Stars & Above</option>
                <option value="4" <?php echo $rating == '4' ? 'selected' : ''; ?>>4 Stars & Above</option>
                <option value="5" <?php echo $rating == '5' ? 'selected' : ''; ?>>5 Stars</option>
            </select>
            <button type="submit" name="search">Search Hotels ✨</button>
        </form>
    </div>

    <?php if ($confirmation): ?>
        <div class="message confirmation">
            <?php echo htmlspecialchars($confirmation); ?>
        </div>
    <?php endif; ?>

    <?php if ($no_results): ?>
        <div class="message no-results">
            <?php echo htmlspecialchars($no_results); ?>
        </div>
    <?php endif; ?>

    <div class="hotel-list">
        <?php foreach ($hotels as $hotel): ?>
            <div class="hotel-card">
                <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                <div class="hotel-card-content">
                    <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                    <p><?php echo htmlspecialchars($hotel['city']); ?></p>
                    <p>Rating: <?php echo htmlspecialchars($hotel['rating']); ?> Stars</p>
                    <p class="price">$<?php echo htmlspecialchars($hotel['price_per_night']); ?>/night</p>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                        <input type="hidden" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
                        <input type="hidden" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
                        <button type="submit" name="book">Book Now 💖</button>
                    </form>
                    <button onclick="window.location.href='hotel_details.php?id=<?php echo $hotel['id']; ?>'">View Details 🌸</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (isset($_SESSION['user_id']) && !empty($bookings)): ?>
        <div class="booking-list">
            <h2>Your Bookings 🎀</h2>
            <?php foreach ($bookings as $booking): ?>
                <?php
                $checkin_date = new DateTime($booking['checkin_date']);
                $checkout_date = new DateTime($booking['checkout_date']);
                $nights = $checkin_date->diff($checkout_date)->days;
                $total_price = $nights * $booking['price_per_night'];
                ?>
                <div class="booking-card">
                    <div class="booking-card-content">
                        <h3><?php echo htmlspecialchars($booking['name']); ?></h3>
                        <p><?php echo htmlspecialchars($booking['city']); ?></p>
                        <p class="dates">Check-in: <?php echo $checkin_date->format('Y-m-d'); ?> - Check-out: <?php echo $checkout_date->format('Y-m-d'); ?> (<?php echo $nights; ?> nights)</p>
                        <p class="price">Total: $<?php echo number_format($total_price, 2); ?></p>
                        <button onclick="window.location.href='hotel_details.php?id=<?php echo $booking['hotel_id']; ?>'">View Details 🌸</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (isset($_SESSION['user_id']) && empty($bookings)): ?>
        <div class="message no-results">
            You have no bookings yet. Start by searching and booking a hotel! 💕
        </div>
    <?php endif; ?>

    <script>
        // Client-side form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const checkin = document.querySelector('input[name="checkin"]').value;
            const checkout = document.querySelector('input[name="checkout"]').value;
            const today = new Date().toISOString().split('T')[0];
            if (new Date(checkin) < new Date(today)) {
                alert('Check-in date cannot be in the past. 🌸');
                e.preventDefault();
            } else if (new Date(checkin) >= new Date(checkout)) {
                alert('Check-out date must be after check-in date. 💕');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
