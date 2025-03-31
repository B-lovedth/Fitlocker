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
  <link rel="stylesheet" href="./Styles/main.css?v=1.0" />
  <link rel="stylesheet" href="./Styles/about.css?v=1.0" />
  <link rel="stylesheet" href="./Styles/menus.css?v=1.0">
</head>

<body>
<header>
          <!-- Responsive Navbar -->
          <div class="menu-head">
            <div id="logo"><a href="homepage.php"><img src="assets/Logos/FitLocker lightbg.png" alt="Fitlocker Logo"></a></div>
            <nav class="navbar">
                <ul class="nav-items">
                    <li class="btn btn-sm btn-ghost"><a href="./homepage.php" class=" sm">Home</a></li>
                    <li class="btn btn-sm btn-ghost"><a href="./about.php" class="bold sm">About</a></li>
                    <li class="btn btn-sm btn-ghost"><a href="./about.php#contactUs" class="sm">Contact</a></li>
                </ul>            
            </nav>
            <img src="./assets/icons/menu-hamburger.svg" id="hamburger" class="hide" alt="hamburger-menu">
            <a class="sign-in" href="<?php echo $is_logged_in ? './dashboard.php' : './login.php';?>">
              <button class="btn btn-secondary btn-sm"><?php echo $is_logged_in ? 'Dashboard' : 'Log In'; ?></button>
            </a>
          </div>
          <hr>
          <div class="hamburger-menu hide">
            <ul class="menu-items">
              <li class="btn btn-sm btn-ghost"><a href="./homepage.php" class="bold sm">Home</a></li>
              <li class="btn btn-sm btn-ghost"><a href="./about.php" class="sm">About</a></li>
              <li class="btn btn-sm btn-ghost"><a href="./about.php#contactUs" class="sm">Contact</a></li>
            </ul>
            <a class="" href="<?php echo $is_logged_in ? './dashboard.php' : './login.php';?>">
              <button class="btn btn-secondary btn-sm"><?php echo $is_logged_in ? 'Dashboard' : 'Log In'; ?></button>
            </a>
          </div>
            
    </header> 
    <div id="overlay" class="hide"></div>

  <main>
    <section class="page" id="aboutUs">
      <div class="page-info">
        <h1>Who we are?</h1>
        <p>We're just a bunch of CSC students that had to do this for good grades. That's all. <br> What? Were you expecting more? Sorry nothing for you. <br> Scroll, click the button or do something man, irdc.</p>
        <button id="main-button" class="btn btn-md btn-primary sh-md" type="button" onclick="window.location.href='<?php echo $is_logged_in ? './registerClient.php' : './signup.php'; ?>'">Get Started <img src="assets/icons/arrow-right.svg" alt="right-arrow"></button>
      </div>
      <div class="page-image">
        <img src="./assets/img/about-us.svg" alt="hero-image" class="section-image">
      </div>
    </section>
    <hr>
    <section class="page" id="contactUs">
      <div class="page-image">
        <img src="./assets/img/contact-us.svg" alt="hero-image" class="section-image">
      </div>
      <div class="page-info">
        <h1>Get in Touch</h1>
        <p>For any complaints, queries, or suggestions, please leave a mail, call or visit our social media handles below:</p>
        <div id="largeContactButtons">
          <a href="mailto:tegaomoh7@gmail.com"><button class="btn btn-md btn-primary sh-md" id="mail"><img src="./assets/icons/mail.svg" alt="">Send an Email</button></a>
          <a href="tel:+2347038188246"><button class="btn btn-md btn-secondary sh-md" id="call"><img src="./assets/icons/call.svg" alt="">Call Us</button></a>
        </div>
        <div id="smallContactButtons">
          <button class="btn btn-sm btn-outline sh-sm"><img src="./assets/icons/instagram.svg" alt="instagram" class="social-icon"></button>
          <button class="btn btn-sm btn-outline sh-sm"><img src="./assets/icons/facebook.svg" alt="facebook" class="social-icon"></button>
          <button class="btn btn-sm btn-outline sh-sm"><img src="./assets/icons/twitter-alt.svg" alt="twitter" class="social-icon"></button>
        </div>
      </div>
      </section>
  
    <hr>
    
  </main>


  <footer>
    <p class="sm">COPYRIGHT © FitLocker, 2025</p>
  </footer>

  <script src="./Scripts/navbar.js"></script>
</body>

</html>


<?php
// Close database connection (optional, depending on your setup)
if (isset($conn)) {
  $conn->close();
}
?>