<?php
session_start();
require_once 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Initialize session data
$errors = $_SESSION['errors'] ?? [
    'username' => '',
    'email' => '',
    'password' => '',
    'general' => ''
];
$form_data = $_SESSION['form_data'] ?? [];

// Clear session data after retrieval
unset($_SESSION['errors'], $_SESSION['form_data']);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [
        'username' => '',
        'email' => '',
        'password' => '',
        'general' => ''
    ];

    // Validation
    if (strlen($username) < 3) {
        $errors['username'] = 'Name must be at least 3 characters';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (strlen($password) < 8 || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors['password'] = 'Password must be 8+ characters with at least 1 number and 1 special character';
    }

    // Process valid form
    if (empty(array_filter($errors))) {
        try {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $_SESSION['user_id'] = $insert_stmt->insert_id;
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                $errors['email'] = 'Email already registered';
            }
        } catch (Exception $e) {
            $errors['general'] = 'Registration failed. Please try again.';
        }
    }

    // Store data for redirect
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = ['username' => $username, 'email' => $email];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./Styles/main.css?v=1.0">
    <link rel="stylesheet" href="./Styles/signup.css?v=1.0">
    <title>FitLocker: Get Started</title>
</head>
<body>
    <main class="sign-form">
        <form method="post" class="column">
            <h2>Get Started!</h2>
            <?php if (!empty($errors['general'])): ?>
                <div class="error"><?= $errors['general'] ?></div>
            <?php endif; ?>

            <div class="inputs-section">
                <div class="field">
                    <label for="username">Name</label>
                    <input type="text" name="username" id="username" 
                           value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" 
                           placeholder="John Doe" required>
                    <?php if (!empty($errors['username'])): ?>
                        <div class="error"><?= $errors['username'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" 
                           value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" 
                           placeholder="johndoe8@gmail.com" required>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" 
                           placeholder="********" required>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="error"><?= $errors['password'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="other-info">
                <span class="terms-container">
                    <input type="checkbox" name="terms" id="terms" required>
                    <label for="terms">I have read the <a href="#">Terms and Conditions</a></label>
                </span>
                <button type="submit" class="btn btn-primary sh-md btn-md" id="create-account" >
                    Create Account
                </button>
                <div class="or">
                    <hr><p class="sm">OR</p><hr>
                </div>
                <button type="button" class="btn btn-md btn-secondary sh-md" 
                        onclick="window.location.href='login.php'">
                    Sign In
                </button>
            </div>
        </form>
        <img src="assets/img/measuring-img.png" alt="" class="get-started-img sh-lg">
    </main>


    <script src="./Scripts/signup.js?v=1.0"></script>
    <script src="./Scripts/script.js?v=1.0"></script>
</body>
</html>