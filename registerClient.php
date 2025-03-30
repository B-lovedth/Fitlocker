<?php
// Start the session
session_start();

// Check if the user has an active session
if (!isset($_SESSION['user_id'])) {
  // No active session, redirect to login page
  header("Location: login.php");
  exit(); // Stop further execution
}

// If we reach here, the user has an active session
require_once 'db_connect.php'; // Database connection file

// Handle form submission (only for authenticated users)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Collect form data
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $address = $_POST['address'] ?? null; // Address is optional
  $phone = !empty($_POST['phone']) ? (int)$_POST['phone'] : null;
  $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
  $gender = $_POST['gender'];
  $add_to_family = $_POST['add_to_family'];
  $family_address = $_POST['family_address'] ?? null;

  // Measurements (convert empty fields to null)
  $measurements = [
    'height' => !empty($_POST['height']) ? (float)$_POST['height'] : null,
    'length' => !empty($_POST['length']) ? (float)$_POST['length'] : null,
    'chest' => !empty($_POST['chest']) ? (float)$_POST['chest'] : null,
    'waist' => !empty($_POST['waist']) ? (float)$_POST['waist'] : null,
    'hip' => !empty($_POST['hip']) ? (float)$_POST['hip'] : null,
    'sleeve' => !empty($_POST['sleeve']) ? (float)$_POST['sleeve'] : null,
    'inseam' => !empty($_POST['inseam']) ? (float)$_POST['inseam'] : null,
    'outseam' => !empty($_POST['outseam']) ? (float)$_POST['outseam'] : null,
    'shoulder' => !empty($_POST['shoulder']) ? (float)$_POST['shoulder'] : null,
    'short_length' => !empty($_POST['short_length']) ? (float)$_POST['short_length'] : null,
  ];

  // Get the user_id from the active session
  $user_id = $_SESSION['user_id'];

  // Handle family logic (if applicable)
  $family_id = null;
  if ($add_to_family === 'yes') {
    $stmt = $conn->prepare("SELECT family_id FROM families WHERE family_name = ? AND family_address = ? AND user_id = ?");
    $stmt->bind_param("ssi", $last_name, $family_address, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      $family_id = $result->fetch_assoc()['family_id'];
    } else {
      $stmt = $conn->prepare("INSERT INTO families (family_name, family_address, user_id) VALUES (?, ?, ?)");
      $stmt->bind_param("ssi", $last_name, $family_address, $user_id);
      $stmt->execute();
      $family_id = $stmt->insert_id;
    }
    $stmt->close();
  }

  // Insert customer data into the database
  $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, address, phone, age, gender, height, length, chest, waist, hip, sleeve, inseam, outseam, shoulder, short_length, family_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param(
    "sssiisddddddddddii",
    $first_name,
    $last_name,
    $address,
    $phone,
    $age,
    $gender,
    $measurements['height'],
    $measurements['length'],
    $measurements['chest'],
    $measurements['waist'],
    $measurements['hip'],
    $measurements['sleeve'],
    $measurements['inseam'],
    $measurements['outseam'],
    $measurements['shoulder'],
    $measurements['short_length'],
    $family_id,
    $user_id
  );

  if ($stmt->execute()) {
    $_SESSION['registration_status'] = 'success';
  } else {
    $_SESSION['registration_status'] = 'failure';
  }
  $stmt->close();
  header("Location: registerClient.php"); // Redirect to show modal
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FitLocker: Register Customer</title>
  <link rel="stylesheet" href="./Styles/main.css?v=1.0" />
  <link rel="stylesheet" href="./Styles/sidebar.css?v=1.0" />
  <link rel="stylesheet" href="./Styles/menus.css?v=1.0">
  <link rel="stylesheet" href="./Styles/register.css?v=1.0" />
</head>

<body>
  <?php require_once "./sidebar.php"?>
  <?php require_once "./accountsModal.php"?>
  <div class="container">
    <?php require_once "./navbar.php" ?>
    <div id="overlay" class="hide"></div>
    <main class="main-section-container">
      <div id="main-section-header">
        <h2>Register Client</h2>
        <a href="./registerFamily.php"><button class="btn btn-sm btn-secondary sh-sm">Register Family Instead</button></a>
      </div>
      <form class="clientForm" action="registerClient.php" method="POST">
        <div class="personal panel sh-md">
          <h3>Personal Details</h3>
          <hr>
          <div class="fields slim">
            <div class="field">
              <label for="first_name">First Name</label>
              <input type="text" id="first_name" name="first_name" required />
            </div>
            <div class="field">
              <label for="last_name">Last Name</label>
              <input type="text" id="last_name" name="last_name" required />
            </div>
            <div class="field">
              <label for="address">Address</label>
              <input type="text" id="address" name="address" />
            </div>
            <div class="field">
              <label for="phone">Phone</label>
              <input type="tel" id="phone" name="phone" />
            </div>
            <div class="field">
              <label for="age">Age</label>
              <input type="number" min="1" id="age" name="age" />
            </div>
            <div class="field">
              <label for="gender">Gender</label>
              <select name="gender" id="gender" required>
                <option disabled selected>select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="field">
              <label for="add_to_family">Add to family</label>
              <div class="row">
                <label class="flex small">
                  <input type="radio" name="add_to_family" value="yes">Yes
                </label>
                <label class="flex small">
                  <input type="radio" name="add_to_family" value="no" checked>No
                </label>
              </div>
            </div>
            <div class="field" id="family_address_field" style="display:none;">
              <label for="family_address">Family Address</label>
              <input type="text" id="family_address" name="family_address" />
            </div>
          </div>
        </div>
        <div class="measurement panel sh-md">
          <h3>Measurements (cm)</h3>
          <hr>
          <div class="fields wide">
            <div class="field">
              <label for="height">Height (cm)</label>
              <input type="number" step="1" id="height" name="height" />
            </div>
            <div class="field">
              <label for="length">Length (cm)</label>
              <input type="number" step="1" id="length" name="length" />
            </div>
            <div class="field">
              <label for="chest">Chest (cm)</label>
              <input type="number" step="1" id="chest" name="chest" />
            </div>
            <div class="field">
              <label for="waist">Waist (cm)</label>
              <input type="number" step="1" id="waist" name="waist" />
            </div>
            <div class="field">
              <label for="hip">Hip (cm)</label>
              <input type="number" step="1" id="hip" name="hip" />
            </div>
            <div class="field">
              <label for="sleeve">Sleeve (cm)</label>
              <input type="number" step="1" id="sleeve" name="sleeve" />
            </div>
            <div class="field">
              <label for="inseam">Inseam (cm)</label>
              <input type="number" step="1" id="inseam" name="inseam" />
            </div>
            <div class="field">
              <label for="outseam">Outseam (cm)</label>
              <input type="number" step="1" id="outseam" name="outseam" />
            </div>
            <div class="field">
              <label for="shoulder">Shoulder (cm)</label>
              <input type="number" step="1" id="shoulder" name="shoulder" />
            </div>
            <div class="field">
              <label for="short_length">Short Length (cm)</label>
              <input type="number" step="1" id="short_length" name="short_length" />
            </div>
          </div>
        </div>
        <button class="btn btn-sm btn-primary sh-sm" type="submit">Register</button>
      </form>
    </main>  

  </div>

  <?php require_once "./success-failureModal.php" ?>

  <script src="./Scripts/script.js?v1.0"></script>
  <script src="./Scripts/navbar.js?v=1.0"></script>
  <script src="./Scripts/dashboardscript.js?v=1.0"></script>
  <script>
    function showModal(modalId) {
      document.getElementById(modalId).style.display = 'block';
    }

    function hideModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function registerAgain() {
      hideModal('successModal');
      document.querySelector('.clientForm').reset();
      // Reset visibility of toggled fields
      document.getElementById('family_address_field').style.display = 'none';
    }

    function tryAgain() {
      hideModal('errorModal');
      document.getElementById('first_name').focus();
    }

    function goToDashboard() {
      window.location.href = 'dashboard.php';
    }

    // Show modal based on registration status
    <?php if (isset($_SESSION['registration_status'])): ?>
      <?php if ($_SESSION['registration_status'] === 'success'): ?>
        showModal('successModal');
      <?php else: ?>
        showModal('errorModal');
      <?php endif; ?>
      <?php unset($_SESSION['registration_status']); ?>
    <?php endif; ?>

    // Show/hide family address field based on "Add to family" selection
    document.querySelectorAll('input[name="add_to_family"]').forEach(radio => {
      radio.addEventListener('change', function() {
        document.getElementById('family_address_field').style.display = this.value === 'yes' ? 'block' : 'none';
      });
    });
  </script>
</body>

</html>