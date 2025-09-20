<?php
session_start();
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);
        echo "<script>window.location.href = 'login.php';</script>";
        exit;
    } catch (PDOException $e) {
        $error = "Registration failed: " . ($e->getCode() == 23000 ? "Username or email already exists." : $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Hotels.com Clone</title>
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

        .signup-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }

        .signup-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #1a73e8;
        }

        .signup-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .signup-form input {
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .signup-form button {
            padding: 0.8rem;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .signup-form button:hover {
            background: #1557b0;
        }

        .error {
            color: #d32f2f;
            text-align: center;
            margin-bottom: 1rem;
        }

        .login-link {
            text-align: center;
            color: #1a73e8;
            cursor: pointer;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .signup-container {
                margin: 1rem;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" class="signup-form">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Sign Up</button>
        </form>
        <p class="login-link" onclick="window.location.href='login.php'">Already have an account? Log in</p>
    </div>

    <script>
        // JavaScript for redirection (already handled in form and link)
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
