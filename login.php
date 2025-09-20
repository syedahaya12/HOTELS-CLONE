<?php
session_start();
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo "<script>window.location.href = 'index.php';</script>";
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}

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
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register - Hotels.com Clone</title>
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

        .auth-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }

        .auth-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .auth-form input {
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .auth-form button {
            padding: 0.8rem;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .auth-form button:hover {
            background: #1557b0;
        }

        .error {
            color: #d32f2f;
            text-align: center;
            margin-bottom: 1rem;
        }

        .toggle-link {
            text-align: center;
            color: #1a73e8;
            cursor: pointer;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .auth-container {
                margin: 1rem;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2 id="form-title">Login</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" class="auth-form" id="auth-form">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="email" name="email" placeholder="Email" id="email-field" style="display: none;">
            <button type="submit" name="login" id="submit-btn">Login</button>
        </form>
        <p class="toggle-link" onclick="toggleForm()">Don't have an account? Register</p>
    </div>

    <script>
        function toggleForm() {
            const title = document.getElementById('form-title');
            const emailField = document.getElementById('email-field');
            const submitBtn = document.getElementById('submit-btn');
            if (title.textContent === 'Login') {
                title.textContent = 'Register';
                emailField.style.display = 'block';
                submitBtn.name = 'register';
                submitBtn.textContent = 'Register';
                document.querySelector('.toggle-link').textContent = 'Already have an account? Login';
            } else {
                title.textContent = 'Login';
                emailField.style.display = 'none';
                submitBtn.name = 'login';
                submitBtn.textContent = 'Login';
                document.querySelector('.toggle-link').textContent = "Don't have an account? Register";
            }
        }
    </script>
</body>
</html>
