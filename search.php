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
$viewMode = $_GET['view'] ?? 'individual';
$data = []; // Changed from $customers to $data for both views
$filters = [
    'lower_age' => $_GET['lower_age'] ?? null,
    'upper_age' => $_GET['upper_age'] ?? null,
    'gender' => $_GET['sex'] ?? null,
    'family_name' => $_GET['family_name'] ?? null,
    'lower_height' => $_GET['lower_height'] ?? null,
    'upper_height' => $_GET['upper_height'] ?? null
];

// Get current user ID
$user_id = $_SESSION['user_id'];

// Build base query based on view mode
if ($viewMode === 'family') {
    $query = "SELECT f.family_id, f.family_name, f.family_address, 
                     COUNT(c.customer_id) AS member_count
              FROM families f
              LEFT JOIN customers c ON f.family_id = c.family_id
              WHERE f.user_id = ?";
} else {
    $query = "SELECT c.customer_id, c.first_name, c.last_name, c.age, c.gender,
                     c.height, f.family_name
              FROM customers c
              LEFT JOIN families f ON c.family_id = f.family_id
              WHERE c.user_id = ?";
}

// Add search term if exists
if (!empty($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    if ($viewMode === 'family') {
        $query .= " AND f.family_name LIKE ?";
    } else {
        $query .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR f.family_name LIKE ?)";
    }
}

// Add filters
$filterClauses = [];
$params = [];
$types = 'i'; // Start with user_id param type

// Age filter (only for individual view)
if ($viewMode === 'individual' && ($filters['lower_age'] || $filters['upper_age'])) {
    $filterClauses[] = "c.age BETWEEN ? AND ?";
    $types .= 'ii';
    $params[] = $filters['lower_age'] ?: 0;
    $params[] = $filters['upper_age'] ?: 100;
}

// Gender filter (only for individual view)
if ($viewMode === 'individual' && $filters['gender'] && in_array(strtoupper($filters['gender']), ['M', 'F', 'O'])) {
    $filterClauses[] = "c.gender = ?";
    $types .= 's';
    $params[] = match (strtoupper($filters['gender'])) {
        'M' => 'male',
        'F' => 'female',
        'O' => 'other'
    };
}

// Family name filter
if ($filters['family_name']) {
    $filterClauses[] = "f.family_name LIKE ?";
    $types .= 's';
    $params[] = "%{$filters['family_name']}%";
}

// Add filters to query
if (!empty($filterClauses)) {
    $query .= " AND " . implode(" AND ", $filterClauses);
}

// Finalize query
if ($viewMode === 'family') {
    $query .= " GROUP BY f.family_id ORDER BY f.family_name";
} else {
    $query .= " ORDER BY c.last_name, c.first_name";
}

// Prepare and execute
$stmt = $conn->prepare($query);
$paramValues = [$user_id];

if (!empty($searchTerm)) {
    $searchPattern = "%$searchTerm%";
    if ($viewMode === 'family') {
        $paramValues[] = $searchPattern;
    } else {
        array_push($paramValues, $searchPattern, $searchPattern, $searchPattern);
    }
}

$paramValues = array_merge($paramValues, $params);
$stmt->bind_param($types, ...$paramValues);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./Styles/style.css">
    <link rel="stylesheet" href="./Styles/search.css">
    <title>FitLocker: Search</title>
    <style>
        .searchbar {
            position: relative;
        }

        .clear-search {
            position: absolute;
            right: 200px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            display: <?= !empty($searchTerm) ? 'block' : 'none' ?>;
        }

        #account-switch {
            width: 60px;
            height: 30px;
            background: #ddd;
            border-radius: 15px;
            position: relative;
            cursor: pointer;
            margin: 0 10px;
        }

        #switch-button {
            position: absolute;
            width: 26px;
            height: 26px;
            background: #fff;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: transform 0.3s ease;
        }
    </style>
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
                <input type="text" name="search" placeholder="Search your registered customers"
                    value="<?= htmlspecialchars($searchTerm) ?>" id="searchInput">
                <!-- Add clear button -->
                <button type="button" class="clear-search" id="clearSearch"
                    onclick="resetSearch()"
                    title="Clear search">
                    <img src="assets/icons/close-x.svg" alt="Clear">
                </button>
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
                <div id="accounts-table">
                    <?php if ($viewMode === 'family'): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Family Name</th>
                                    <th>Address</th>
                                    <th>Members</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $family): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($family['family_name']) ?></td>
                                        <td><?= htmlspecialchars($family['family_address']) ?></td>
                                        <td><?= $family['member_count'] ?></td>
                                        <td>
                                            <button class="view-family btn btn-sm btn-outline"
                                                data-family-id="<?= $family['family_id'] ?>">
                                                View Family
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
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
                                <?php foreach ($data as $customer): ?>
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
                    <?php endif; ?>
                </div>

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
    <script>
        // Function to reset search
        function resetSearch() {
            document.getElementById('searchInput').value = '';
            window.location.href = 'search.php'; // Reload without search parameters
        }

        // Show/hide clear button based on input
        document.getElementById('searchInput').addEventListener('input', function(e) {
            document.getElementById('clearSearch').style.display =
                this.value.trim() ? 'block' : 'none';
        });

        document.addEventListener('DOMContentLoaded', function() {
            const accountSwitch = document.getElementById('account-switch');
            const switchButton = document.getElementById('switch-button');
            const urlParams = new URLSearchParams(window.location.search);
            const currentView = urlParams.get('view') || 'individual';

            // Initialize switch position
            if (currentView === 'family') {
                switchButton.style.transform = 'translateX(100%)';
            }

            // Handle switch click
            accountSwitch.addEventListener('click', function() {
                const newView = currentView === 'individual' ? 'family' : 'individual';
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('view', newView);
                window.location.href = newUrl.toString();
            });
        });
    </script>
</body>

</html>