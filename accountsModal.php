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

<div class="account-modal-overlay" id="accountInfoOverlay">
    <div class="account-modal sh-lg">
        <h2>Account Information</h2>
        <hr>
        <div id="accountInfo">
            <h4>Account Name: <?php echo htmlspecialchars($user['username']); ?></h4>
            <p class="md">Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p class="md">Date created: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>

        </div>
        <button type="button" class="account-modal-btn btn btn-sm btn-secondary sh-sm fill" id="closeAccountModal">Close</button>
        
    </div>
</div>