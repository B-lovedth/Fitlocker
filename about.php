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
  <title>FitLocker: About Us</title>
  <link rel="stylesheet" href="./Styles/main.css?v=1" />
  <link rel="stylesheet" href="./Styles/about.css?v=1" />
</head>

<body>
  <nav id="nav-bar" class="navBar">
    <a href="homepage.php" id="logo-link">
      <img
        src="./assets/Logos/FitLocker lightbg.png"
        alt="FitLocker Logo"
        id="logo" />
    </a>
    <ul id="nav-links">
      <li class="nav-link"><a href="./homepage.php">Home</a></li>
      <li class="nav-link active"><a href="./about.php">About</a></li>
      <li class="nav-link"><a href="./about.php#contactUs">Contact Us</a></li>
    </ul>
    <a class="btn btn-secondary btn-sm" type="button" href="<?php echo $is_logged_in ? './dashboard.php' : './login.php'; ?>">
      <?php echo $is_logged_in ? 'Dashboard' : 'Log In'; ?>
    </a>
  </nav>

  <hr>

  <section class="page" id="about">
    <div class="page-container">
      <div id="aboutInfo">
        <h1>Who we are?</h1>
        <p>We're just a bunch of CSC students that had to do this for good grades. That's all. <br> What? Were you expecting more? Sorry nothing for you. <br> Scroll, click the button or do something man, irdc.</p>
        <button id="aboutButton" type="button" onclick="window.location.href='<?php echo $is_logged_in ? './registerClient.php' : './signup.php'; ?>'">
          Get Started
          <svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            id="arrowIcon">
            <path
              d="M5.5 12L18 12"
              stroke="#1B1B1B"
              stroke-width="2"
              stroke-linecap="round" />
            <path
              d="M12.5 5.5L18.7172 11.7172C18.8734 11.8734 18.8734 12.1266 18.7172 12.2828L12.5 18.5"
              stroke="#1B1B1B"
              stroke-width="2"
              stroke-linecap="round" />
          </svg>
        </button>
      </div>
      <div id="aboutImage">
        <img src="./assets/img/hero-img.png" alt="hero-image" class="section-image">
      </div>
    </div>
  </section>

  <hr>


  <section class="page" id="contactUs">
    <div class="page-container">
      <div id="contactUsImage">
        <img src="./assets/img/hero-img.png" alt="hero-image" class="section-image">
      </div>
      <div id="contactUsInfo">
        <h1>Get in Touch</h1>
        <p>For any complaints, queries, or suggestions, please call or visit our social media handles below:</p>
        <div id="largeContactButtons">
          <button class="large-btn" id="mail"><img src="./assets/icons/mail_24dp_000000_FILL0_wght400_GRAD0_opsz24.svg" alt="">Send an Email</button>
          <button class="large-btn" id="call"><img src="./assets/icons/call_24dp_000000_FILL0_wght400_GRAD0_opsz24.svg" alt="">Call Us</button>
        </div>
        <div id="smallContactButtons">
          <button class="small-btn"><img src="./assets/icons/instagram.svg" alt="instagram" class="social-icon"></button>
          <button class="small-btn"><img src="./assets/icons/facebook.svg" alt="facebook" class="social-icon"></button>
          <button class="small-btn"><img src="./assets/icons/twitter-alt.svg" alt="twitter" class="social-icon"></button>
        </div>
      </div>
    </div>
  </section>

  <hr>

  <footer>
    <p class="sm">COPYRIGHT FitLocker, 2025</p>
  </footer>






</body>

</html>


<?php
// Close database connection (optional, depending on your setup)
if (isset($conn)) {
  $conn->close();
}
?>