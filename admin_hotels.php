<?php
session_start();
require_once 'db.php';

// Check if user is admin (for demo, assume user_id 1 is admin)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Handle add/edit hotel
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_hotel'])) {
    $name = $_POST['name'];
    $city = $_POST['city'];
    $rating = $_POST['rating'];
    $price_per_night = $_POST['price_per_night'];
    $image_url = $_POST['image_url'];
    $amenities = $_POST['amenities'];
    $description = $_POST['description'];

    try {
        $stmt = $conn->prepare("INSERT INTO hotels (name, city, rating, price_per_night, image_url, amenities, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $city, $rating, $price_per_night, $image_url, $amenities, $description]);
        echo "<script>window.location.href = 'admin_hotels.php';</script>";
        exit;
    } catch (PDOException $e) {
        $error = "Error adding hotel: " . $e->getMessage();
    }
}

// Fetch all hotels
$stmt = $conn->prepare("SELECT * FROM hotels");
$stmt->execute();
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Hotels</title>
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

        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .admin-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .admin-form input, .admin-form select, .admin-form textarea {
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .admin-form button {
            padding: 0.8rem;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .admin-form button:hover {
            background: #1557b0;
        }

        .error {
            color: #d32f2f;
            text-align: center;
            margin-bottom: 1rem;
        }

        .hotel-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .hotel-table th, .hotel-table td {
            border: 1px solid #ccc;
            padding: 0.8rem;
            text-align: left;
        }

        .hotel-table th {
            background: #1a73e8;
            color: white;
        }

        .hotel-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .hotel-table button {
            padding: 0.5rem;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .hotel-table button:hover {
            background: #1557b0;
        }

        @media (max-width: 768px) {
            .admin-container {
                margin: 1rem;
                padding: 1rem;
            }

            .hotel-table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin - Hotels.com Clone</h1>
        <p>Manage Hotels</p>
    </header>

    <div class="admin-container">
        <h2>Add New Hotel</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <input type="text" name="name" placeholder="Hotel Name" required>
            <input type="text" name="city" placeholder="City" required>
            <select name="rating" required>
                <option value="">Select Rating</option>
                <option value="1">1 Star</option>
                <option value="2">2 Stars</option>
                <option value="3">3 Stars</option>
                <option value="4">4 Stars</option>
                <option value="5">5 Stars</option>
            </select>
            <input type="number" name="price_per_night" placeholder="Price per Night" step="0.01" required>
            <input type="text" name="image_url" placeholder="Image URL" required>
            <input type="text" name="amenities" placeholder="Amenities (e.g., WiFi, Pool)" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <button type="submit" name="add_hotel">Add Hotel</button>
        </form>

        <h2>Existing Hotels</h2>
        <table class="hotel-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>City</th>
                    <th>Rating</th>
                    <th>Price/Night</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hotels as $hotel): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hotel['id']); ?></td>
                        <td><?php echo htmlspecialchars($hotel['name']); ?></td>
                        <td><?php echo htmlspecialchars($hotel['city']); ?></td>
                        <td><?php echo htmlspecialchars($hotel['rating']); ?> Stars</td>
                        <td>$<?php echo htmlspecialchars($hotel['price_per_night']); ?></td>
                        <td><button onclick="window.location.href='hotel_details.php?id=<?php echo $hotel['id']; ?>'">View</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
