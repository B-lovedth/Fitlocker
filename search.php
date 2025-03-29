<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$searchTerm = '';
$customers = [];

// Get current user ID
$user_id = $_SESSION['user_id'];

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);

    // Prepare search query
    $stmt = $conn->prepare("SELECT 
        c.customer_id,
        c.first_name,
        c.last_name,
        c.age,
        c.gender,
        f.family_name
    FROM customers c
    LEFT JOIN families f ON c.family_id = f.family_id
    WHERE c.user_id = ? 
    AND (c.first_name LIKE ? 
        OR c.last_name LIKE ? 
        OR f.family_name LIKE ?)
    ORDER BY c.last_name, c.first_name");

    $searchPattern = "%$searchTerm%";
    $stmt->bind_param("isss", $user_id, $searchPattern, $searchPattern, $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Get all customers by default
    $stmt = $conn->prepare("SELECT 
        c.customer_id,
        c.first_name,
        c.last_name,
        c.age,
        c.gender,
        f.family_name
    FROM customers c
    LEFT JOIN families f ON c.family_id = f.family_id
    WHERE c.user_id = ?
    ORDER BY c.last_name, c.first_name");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./Styles/style.css">
        <link rel="stylesheet" href="./Styles/search.css">
        <title>FitLocker: Search</title>
    </head>
</head>

<body id="dashboard">
    <aside class="left-sidebar">
        <div class="hide">
            <button class="btn btn-sm btn-ghost"><img src="assets/img/dummy-image.svg" alt="logo">
                <p class="sidebar-text hide sm">FitLocker</p>
            </button>
            <button class="btn btn-sm btn-ghost"><img src="assets/icons/dashboard-icon.svg" alt="dashboard-icon">
                <p class="sidebar-text hide">Dashboard</p>
            </button>
            <button class="btn btn-sm btn-ghost"><img src="assets/icons/search.svg" alt="search-icon">
                <p class="sidebar-text hide">Search</p>
            </button>
            <button class="btn btn-sm btn-ghost"><img src="assets/icons/stats.svg" alt="stats-icon">
                <p class="sidebar-text hide">Stats</p>
            </button>
        </div>
        <div>
            <button class="btn btn-sm btn-ghost"><img src="assets/icons/question-circle.svg" alt="question-circle-icon">
                <p class="sidebar-text hide">Help</p>
            </button>
            <button class="btn btn-sm btn-ghost"><img src="assets/icons/setting.svg" alt="settings">
                <p class="sidebar-text hide">Settings</p>
            </button>
            <button class="btn btn-sm btn-ghost" id="expand"><img src="assets/icons/expand.svg" alt="expand" id="expand-icon">
                <p class="sidebar-text hide">Expand</p>
            </button>
        </div>
    </aside>

    <main>
        <header>
            <!-- Responsive Navbar -->
            <div class="page-title">
                <a href="#"><img src="assets/icons/expand.svg" alt="expand-icon"></a>
                <p class="sm">Search</p>
            </div>
            <div class="header-images">
                <img src="assets/icons/heart-alt.svg" alt="favorites-icon">
                <img src="assets/icons/menu-hamburger.svg" alt="" id="hamburger" class="hide">
            </div>

        </header>

        <form class="search-container" method="GET">
            <div class="searchbar">
                <input type="search" name="search" placeholder="Search your registered customers"
                    value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="btn btn-sm btn-secondary" id="entersearch">Search</button>
                <button class="btn btn-sm btn-ghost" id="filter">Filters</button>
            </div>
            <div id="filter-section" class="hide">
                <div class="input-container">
                    <label for="age1 age2">Age</label>
                    <div class="input-range">
                        <input type="number" name="lower_age" id="age1" class="input-small" placeholder="18" min="0">
                        -
                        <input type="number" name="upper_age" id="age2" class="input-small" placeholder="35" min="0">
                    </div>
                </div>
                <div class="input-container">
                    <label for="sex">Sex(M/F)</label>
                    <input type="text" name="sex" id="sex" placeholder="M" class="input-small">
                </div>
                <div class="input-container">
                    <label for="family">Family</label>
                    <input type="text" name="family_name" id="family" placeholder="Otedola" class="input-small">
                </div>
                <div class="input-container">
                    <label for="height">Height</label>
                    <div class="input-range">
                        <input type="text" name="lower_height" id="" placeholder="5'4" class="input-small">
                        -
                        <input type="text" name="higher_height" id="age" placeholder="6'2" min="0">
                    </div>
                </div>
            </div>
        </form>

        <section id="accounts">
            <div id="accounts-header">
                <h3>Accounts</h3>
                <div class="mode">
                    <p class="sm">Individual</p>
                    <div id="account-switch">
                        <div id="switch-button"></div>
                    </div>
                    <p class="sm">Family</p>
                </div>
            </div>
            <div id="accounts-table">
                <table>
                    <thead>
                        <tr>
                            <th class="col1">Name <span class="sort"><img src="assets/icons/chevron-up.svg" alt=""><img src="assets/icons/chevron-down.svg" alt=""></span></th>
                            <th class="col2">Surname</th>
                            <th class="col3">Age</th>
                            <th class="col4">Sex</th>
                            <th class="col5">Family Member</th>
                            <th class="col6"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td class="col1"><?= htmlspecialchars($customer['first_name']) ?></td>
                                <td class="col2"><?= htmlspecialchars($customer['last_name']) ?></td>
                                <td class="col3"><?= htmlspecialchars($customer['age']) ?></td>
                                <td class="col4"><?= strtoupper($customer['gender'][0] ?? '') ?></td>
                                <td class="col5"><?= $customer['family_name'] ? 'Yes' : 'No' ?></td>
                                <td class="col6">
                                    <button class="view-details btn btn-sm btn-outline border-thick"
                                        data-customer-id="<?= $customer['customer_id'] ?>">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- Mobile Menu for dashboard-->
    <aside class="hamburger-menu hide">
        <div class="menu-head">
            <div class="logo-lg"><a href="#"><img src="assets/img/dummy-image.svg" alt=""></a></div>
            <img src="assets/icons/close-x.svg" alt="" id="close-menu">
        </div>
        <ul class="menu-items">
            <li class="btn btn-sm btn-ghost"><a href="dashboard.css"><img src="assets/icons/dashboard-icon.svg" alt="dashboard-icon">Dashboard</a></li>
            <li class="btn btn-sm btn-ghost"><a href="search.html"><img src="assets/icons/search.svg" alt="search-icon">Search</a></li>
            <li class="btn btn-sm btn-ghost"><a href="stats.html"><img src="assets/icons/stats.svg" alt="stats-icon">Stats</a></li>
            <li class="btn btn-sm btn-ghost"><a href="help.html"><img src="assets/icons/question-circle.svg" alt="question-circle">Help</a></li>
            <li class="btn btn-sm btn-ghost"><a href="stats.html"><img src="assets/icons/setting.svg" alt="settings-icon">Settings</a></li>
            <li class="btn btn-sm btn-ghost"><a href="favorites.html"><img src="assets/icons/heart-alt.svg" alt="heart-alt">Favorites</a></li>
        </ul>
        <button class="btn btn-sm btn-secondary">Sign In</button>
    </aside>




    <script src="scripts/script.js"></script>
</body>

</html>