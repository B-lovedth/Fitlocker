<?php
// Start the session
session_start();

// If we reach here, the user has an active session
require_once 'db_connect.php'; // Database connection file


// Initialize edit mode and customer data
$editMode = false;
$customerData = [];

// Check for edit mode
if (isset($_GET['edit_id'])) {
  $editMode = true;
  $customer_id = $_GET['edit_id'];
  $user_id = $_SESSION['user_id'];

  $stmt = $conn->prepare("SELECT customers.*, families.family_name 
                          FROM customers 
                          LEFT JOIN families ON customers.family_id = families.family_id
                          WHERE customers.customer_id = ? AND customers.user_id = ?");
  $stmt->bind_param("ii", $customer_id, $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $customerData = $result->fetch_assoc();
  } else {
    $_SESSION['error'] = 'Customer not found';
    header("Location: search.php");
    exit();
  }
  $stmt->close();
}

// Check if the user has an active session
if (!isset($_SESSION['user_id'])) {
  // No active session, redirect to login page
  header("Location: login.php");
  exit(); // Stop further execution
}



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Collect common data
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $address = $_POST['address'] ?? null;
  $phone = !empty($_POST['phone']) ? (int)$_POST['phone'] : null;
  $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
  $gender = $_POST['gender'];
  $add_to_family = $_POST['add_to_family'] ?? 'no';
  $family_name = $_POST['family_name'] ?? null;
  $user_id = $_SESSION['user_id'];

  // Collect measurements
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


  // Handle family logic
  $family_id = null;
  if ($add_to_family === 'yes' && !empty($family_name)) {
    $stmt = $conn->prepare("SELECT family_id FROM families WHERE family_name = ? AND user_id = ?");
    $stmt->bind_param("si", $family_name, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $family_id = $result->fetch_assoc()['family_id'];
    } else {
      $stmt = $conn->prepare("INSERT INTO families (family_address, family_name, user_id) VALUES (?, ?, ?)");
      $stmt->bind_param("ssi", $address, $family_name, $user_id);
      $stmt->execute();
      $family_id = $stmt->insert_id;
    }
    $stmt->close();
  }

  // Prepare SQL based on edit mode
  if ($editMode) {
    // UPDATE existing customer
    $customer_id = $_POST['customer_id'];
    $stmt = $conn->prepare("UPDATE customers SET
      first_name = ?,
      last_name = ?,
      address = ?,
      phone = ?,
      age = ?,
      gender = ?,
      height = ?,
      length = ?,
      chest = ?,
      waist = ?,
      hip = ?,
      sleeve = ?,
      inseam = ?,
      outseam = ?,
      shoulder = ?,
      short_length = ?,
      family_id = ?
      WHERE customer_id = ? AND user_id = ?");

    $stmt->bind_param(
      "sssiisddddddddddiii",
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
      $customer_id,
      $user_id
    );
  } else {
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
  }

  // Execute and handle results
  if ($stmt->execute()) {
    $_SESSION['message'] = $editMode ? 'Customer updated successfully' : 'Customer registered successfully';
    header("Location: " . ($editMode ? "search.php" : "registerClient.php"));
  } else {
    $_SESSION['error'] = 'Database error: ' . $conn->error;
    header("Location: registerClient.php");
  }

  $stmt->close();
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FitLocker: Register Customer</title>
  <link rel="stylesheet" href="./Styles/main.css?v" />
  <link rel="stylesheet" href="./Styles/sidebar.css" />
  <link rel="stylesheet" href="./Styles/register.css" />
</head>

<body>
  <div class="container">
    <?php require_once "./sidebar.php" ?>
    <?php require_once "./accountsModal.php" ?>
    <div class="register-container">
      <header class="top">
        <div>Register Client</div>-
      </header>
      <div class="subject">
        <h1>Register Client</h1>
        <a href="./registerFamily.php">Register Family</a>
      </div>
      <form class="clientForm" action="registerClient.php" method="POST">
        <?php if ($editMode): ?>
          <input type="hidden" name="customer_id" value="<?= $customerData['customer_id'] ?>">
        <?php endif; ?>

        <div class="personal panel">
          <h3>Personal Details</h3>
          <div class="fields p-d">
            <div class="field">
              <label for="first_name">First Name</label>
              <input type="text" id="first_name" name="first_name"
                value="<?= $editMode ? htmlspecialchars($customerData['first_name']) : '' ?>" required>
            </div>
            <div class="field">
              <label for="last_name">Last Name</label>
              <input type="text" id="last_name" name="last_name"
                value="<?= $editMode ? htmlspecialchars($customerData['last_name']) : '' ?>" required>
            </div>
            <div class="field">
              <label for="address">Address</label>
              <input type="text" id="address" name="address"
                value="<?= $editMode ? htmlspecialchars($customerData['address']) : '' ?>">
            </div>
            <div class="field">
              <label for="phone">Phone</label>
              <input type="tel" id="phone" name="phone"
                value="<?= $editMode ? htmlspecialchars($customerData['phone']) : '' ?>">
            </div>
            <div class="field">
              <label for="age">Age</label>
              <input type="number" min="1" id="age" name="age"
                value="<?= $editMode ? htmlspecialchars($customerData['age']) : '' ?>">
            </div>
            <div class="field">
              <label for="gender">Gender</label>
              <select name="gender" id="gender" required>
                <option disabled <?= !$editMode ? 'selected' : '' ?>>select gender</option>
                <option value="male" <?= ($editMode && $customerData['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                <option value="female" <?= ($editMode && $customerData['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                <option value="other" <?= ($editMode && $customerData['gender'] === 'other') ? 'selected' : '' ?>>Other</option>
              </select>
            </div>
            <div class="field">
              <label for="add_to_family">Add to family</label>
              <div class="row">
                <label class="flex small">
                  <input type="radio" name="add_to_family" value="yes"
                    <?= ($editMode && !empty($customerData['family_id'])) ? 'checked' : '' ?>>
                  Yes
                </label>
                <label class="flex small">
                  <input type="radio" name="add_to_family" value="no"
                    <?= (!$editMode || empty($customerData['family_id'])) ? 'checked' : '' ?>>
                  No
                </label>
              </div>
            </div>
            <div class="field" id="family_name_field" style="display:<?= ($editMode && !empty($customerData['family_id'])) ? 'block' : 'none' ?>;">
              <label for="family_name">Family Name</label>
              <input type="text" id="family_name" name="family_name"
                value="<?= $editMode ? htmlspecialchars($customerData['family_name']) : '' ?>">
            </div>
          </div>
        </div>

        <div class="measurement panel">
          <h3>Measurements (cm)</h3>
          <div class="fields m-d">
            <div class="field">
              <label for="height">Height</label>
              <input type="number" step="0.01" id="height" name="height"
                value="<?= $editMode && !empty($customerData['height']) ? htmlspecialchars($customerData['height']) : '' ?>">
            </div>
            <div class="field">
              <label for="length">Length</label>
              <input type="number" step="0.01" id="length" name="length"
                value="<?= $editMode && !empty($customerData['length']) ? htmlspecialchars($customerData['length']) : '' ?>">
            </div>
            <div class="field">
              <label for="chest">Chest</label>
              <input type="number" step="0.01" id="chest" name="chest"
                value="<?= $editMode && !empty($customerData['chest']) ? htmlspecialchars($customerData['chest']) : '' ?>">
            </div>
            <div class="field">
              <label for="waist">Waist</label>
              <input type="number" step="0.01" id="waist" name="waist"
                value="<?= $editMode && !empty($customerData['waist']) ? htmlspecialchars($customerData['waist']) : '' ?>">
            </div>
            <div class="field">
              <label for="hip">Hip</label>
              <input type="number" step="0.01" id="hip" name="hip"
                value="<?= $editMode && !empty($customerData['hip']) ? htmlspecialchars($customerData['hip']) : '' ?>">
            </div>
            <div class="field">
              <label for="sleeve">Sleeve</label>
              <input type="number" step="0.01" id="sleeve" name="sleeve"
                value="<?= $editMode && !empty($customerData['sleeve']) ? htmlspecialchars($customerData['sleeve']) : '' ?>">
            </div>
            <div class="field">
              <label for="inseam">Inseam</label>
              <input type="number" step="0.01" id="inseam" name="inseam"
                value="<?= $editMode && !empty($customerData['inseam']) ? htmlspecialchars($customerData['inseam']) : '' ?>">
            </div>
            <div class="field">
              <label for="outseam">Outseam</label>
              <input type="number" step="0.01" id="outseam" name="outseam"
                value="<?= $editMode && !empty($customerData['outseam']) ? htmlspecialchars($customerData['outseam']) : '' ?>">
            </div>
            <div class="field">
              <label for="shoulder">Shoulder</label>
              <input type="number" step="0.01" id="shoulder" name="shoulder"
                value="<?= $editMode && !empty($customerData['shoulder']) ? htmlspecialchars($customerData['shoulder']) : '' ?>">
            </div>
            <div class="field">
              <label for="short_length">Short Length</label>
              <input type="number" step="0.01" id="short_length" name="short_length"
                value="<?= $editMode && !empty($customerData['short_length']) ? htmlspecialchars($customerData['short_length']) : '' ?>">
            </div>
          </div>
        </div>

        <button type="submit">
          <?= $editMode ? 'Update Customer' : 'Register' ?>
        </button>
      </form>
    </div>
  </div>

  <?php require_once "./success-failureModal.php" ?>

  <script src="./Scripts/script.js?v1.0"></script>
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
      document.getElementById('family_name_field').style.display = 'none';
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

    // Show/hide family name field based on "Add to family" selection
    document.querySelectorAll('input[name="add_to_family"]').forEach(radio => {
      radio.addEventListener('change', function() {
        document.getElementById('family_name_field').style.display = this.value === 'yes' ? 'block' : 'none';
      });
    });
  </script>
</body>

</html>