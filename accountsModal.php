<?php
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<div class="modal-overlay" id="accountInfoOverlay">
    <div class="modal">
        <h2>Account Information</h2>
        <div id="accountInfo">
            <p class="lg">Account Name: <?php echo htmlspecialchars($user['username']); ?></p>
            <p class="md">Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p class="md">Date created: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>

        </div>
        <div id="accountInfoButtons">
            <button type="button" class="modal-btn">Delete Account</button>
            <button type="button" class="modal-btn">Change Password</button>
            <button type="button" class="modal-btn" id="closeModal">Close</button>
        </div>
    </div>
</div>