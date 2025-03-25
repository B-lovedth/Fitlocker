<?php
session_start();
require_once 'db_connect.php';

$errors = [];
$remember = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        // Check user exists
        $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['user_id'] = $user['user_id'];

                // Remember me functionality
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + 60 * 60 * 24 * 30; // 30 days
                    
                    setcookie('remember_token', $token, $expiry, '/');
                    $conn->query("UPDATE users SET remember_token = '$token' WHERE user_id = {$user['user_id']}");
                }

                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
        }
    }
}

// Handle "Forgot Password" request
if (isset($_GET['forgot_password'])) {
    $email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 1) {
            // Implement password reset logic here
            // Generate token, send email, etc.
            $errors[] = "Password reset link sent to your email";
        } else {
            $errors[] = "Email not found";
        }
    } else {
        $errors[] = "Invalid email address";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLocker: Login</title>
    <style>
        .error { color: red; margin: 10px 0; }
        .container { max-width: 1200px; margin: 0 auto; }
        .wrapper { display: flex; gap: 2rem; }
        .left img { max-width: 600px; }
        form { display: flex; flex-direction: column; gap: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="wrapper">
            <div class="left">
                <img src="images/login.jpg" alt="Login illustration">
            </div>
            <div class="right">
                <h1>Welcome Back</h1>
                
                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <?php foreach ($errors as $error): ?>
                            <p><?= $error ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="post">
                    <input type="email" name="email" placeholder="Email" 
                           value="<?= htmlspecialchars($email ?? '') ?>" required>
                    
                    <input type="password" name="password" placeholder="Password" required>
                    
                    <div>
                        <span>
                            <input type="checkbox" name="remember" id="remember" <?= $remember ? 'checked' : '' ?>>
                            <label for="remember">Remember me</label>
                        </span>
                        <a href="reset-password.php">Forgot password?</a>
                    </div>
                    
                    <button type="submit">Sign In</button>
                    
                    <div class="or-separator">
                        <hr>
                        <span>OR</span>
                        <hr>
                    </div>
                    
                    <a href="google-auth.php" class="google-btn">
                        Sign in with Google
                    </a>
                    
                    <div>
                        Don't have an account? 
                        <a href="signup.php">Create one!</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>