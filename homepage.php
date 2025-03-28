<?php
// Start the session
session_start();

// Include database connection
require_once 'db_connect.php'; // Assuming you have this file or will create it

// Check if user is logged in and fetch user data (optional, for validation)
$is_logged_in = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $is_logged_in = true;
    } else {
        // If user_id doesn't exist in DB, invalidate session
        unset($_SESSION['user_id']);
        $is_logged_in = false;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FitLocker</title>
    <link rel="stylesheet" href="./Styles/main.css?v=1">
    <link rel="stylesheet" type="text/css" href="./Styles/homepagestyles.css?v=1" />
  </head>
  <body>
    <nav id="nav-bar" class="navBar">
      <a href="homepage.php" id="logo-link">
        <img
          src="./assets/Logos/FitLocker lightbg.png"
          alt="FitLocker Logo"
          id="logo"
        />
      </a>
      <ul id="nav-links">
        <li class="nav-link active"><a href="./homepage.php">Home</a></li>
        <li class="nav-link"><a href="./about.php">About</a></li>
        <li class="nav-link"><a href="./about.php#contactUs">Contact Us</a></li>
      </ul>
      <a class="btn btn-secondary btn-sm" type="button" href="<?php echo $is_logged_in ? './dashboard.php' : './login.php'; ?>">
        <?php echo $is_logged_in ? 'Dashboard' : 'Log In'; ?>
      </a>
    </nav>

    <hr />

    <main class="homepage" id="main-container">
      <div id="left-handside">
        <h1>
          All your customer details in <span id="highlight">one place</span>
        </h1>
        <p>
          Easily store, organize and access all your customer measurements in
          our secure platform to streamline your workflow and ensure flawless
          fits every time
        </p>

        <button id="main-button" class="btn btn-md btn-primary sh-md" type="button" onclick="window.location.href='<?php echo $is_logged_in ? './register-client.php' : './signup.php'; ?>'">
          Store your first measurement
          <img src="assets/icons/arrow-right.svg" alt="right-arrow">
        </button>

        <div id="activityContainer">
          <div id="profileContainer">
            <div class="circle-profile" id="circle1"></div>
            <div class="circle-profile" id="circle2"></div>
            <div class="circle-profile" id="circle3"></div>
            <div class="circle-profile" id="circle4"></div>
            <div class="circle-profile" id="circle5"></div>
          </div>
          <div id="activityInfo">
            <span>1.5k Active Users</span>
          </div>
        </div>
      </div>
      
      <div id="right-handside">
        <div id="image-container">
          <img src="./assets/img/hero-img.png" alt="hero-image" id="image" />
        </div>
        <div class="statContainer">
          <div class="stat">
            <h3>3k+</h3>
            <p>Fashion Designers</p>
          </div>
          <div class="stat">
            <h3>6.5k</h3>
            <p>Family Accounts</p>
          </div>
          <div class="stat">
            <h3>16k</h3>
            <p>Registered Customers</p>
          </div>
        </div>

      </div>
    </div>

    <footer id="footer" class="footer">
      <img src="./assets/img/Aj Stitches.png" alt="ajstitches" id="footerImage" />
      <img src="./assets/img/armadi.png" alt="armadi" id="footerImage" />
      <img
        src="./assets/img/gentlemanly..png"
        alt="gentlemanly"
        id="footerImage"
      />
      <img
        src="./assets/img/Emperor wears.png"
        alt="emperorwears"
        id="footerImage"
      />
    </footer>

    <script src="homepagescript.js"></script>
  </body>
</html>

<?php
// Close database connection (optional, depending on your setup)
if (isset($conn)) {
    $conn->close();
}
?>