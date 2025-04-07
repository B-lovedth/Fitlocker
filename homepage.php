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
    <link rel="stylesheet" href="./Styles/main.css?v=1.0">
    <link rel="stylesheet" href="./Styles/homepagestyles.css?v=1.0" />
    <link rel="stylesheet" href="./Styles/menus.css?v=1.0">
  </head>
  <body>
    <header>
          <!-- Responsive Navbar -->
          <div class="menu-head">
            <div id="logo"><a href="homepage.php"><img src="assets/Logos/fitlocker-logo.svg" alt="Fitlocker Logo"></a></div>
            <nav class="navbar">
                <ul class="nav-items">
                  <a href="./homepage.php" class="sm"><li class="btn btn-sm btn-ghost bold">Home</li></a>
                  <a href="./about.php" class="sm"><li class="btn btn-sm btn-ghost">About</li></a>
                  <a href="./about.php#contactUs" class="sm"><li class="btn btn-sm btn-ghost">Contact</li></a>
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
              <a href="./homepage.php" class=" sm"><li class="btn btn-sm btn-ghost bold">Home</li></a>
              <a href="./about.php" class="bold sm"><li class="btn btn-sm btn-ghost">About</li></a>
              <a href="./about.php#contactUs" class="sm"><li class="btn btn-sm btn-ghost">Contact</li></a>
            </ul>
            <a class="" href="<?php echo $is_logged_in ? './dashboard.php' : './login.php';?>">
              <button class="btn btn-secondary btn-sm"><?php echo $is_logged_in ? 'Dashboard' : 'Log In'; ?></button>
            </a>
          </div>
            
    </header> 
    <div id="overlay" class="hide"></div>

    <main class="homepage" id="main-container">
      <div id="left-handside">
        <h1>
          All Your Customer Details In <span id="highlight">One Place</span>
        </h1>
        <p>
          Easily store, organize and access all your customer measurements in
          our secure platform to streamline your workflow and ensure flawless
          fits every time
        </p>

        <button id="main-button" class="btn btn-md btn-primary sh-md" type="button" onclick="window.location.href='<?php echo $is_logged_in ? './registerClient.php' : './signup.php'; ?>'">Store your first measurement <img src="assets/icons/arrow-right.svg" alt="right-arrow"></button>

        <div id="activityContainer">
          <img src="./assets/img/profile-images.png" alt="user-profiles-images">
          <h5>1.5k+ Active Users</h5>
        </div>
      </div>
      
      <div id="right-handside">
        <div id="image-container">
          <img src="./assets/img/hero-img.png" alt="hero-image" id="image" />
        </div>
        <div class="statContainer">
          <div class="stat">
            <h4>3k+</h4>
            <p class="sm">Fashion Designers</p>
          </div>
          <div class="stat">
            <h4>6.5k</h4>
            <p class="sm">Family Accounts</p>
          </div>
          <div class="stat">
            <h4>16k</h4>
            <p class="sm">Registered Customers</p>
          </div>
        </div>

      </div>

    </main>
      
    <footer id="footer" class="footer">
      <img src="./assets/img/Aj Stitches.png" alt="ajstitches-logo" />
      <img src="./assets/img/armadi.png" alt="armadi-logo" id="armadi-img" />
      <img
        src="./assets/img/gentlemanly..png"
        alt="gentlemanly-logo"
      />
      <img
        src="./assets/img/Emperor wears.png"
        alt="emperorwears-logo"
      />
    </footer>

    <script src="homepagescript.js"></script>
    <script src="./Scripts/navbar.js"></script>
  </body>
</html>

<?php
// Close database connection (optional, depending on your setup)
if (isset($conn)) {
    $conn->close();
}
?>