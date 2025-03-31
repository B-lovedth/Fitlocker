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
$data = [];
$filters = [
    'lower_age'    => $_GET['lower_age'] ?? null,
    'upper_age'    => $_GET['upper_age'] ?? null,
    'gender'       => $_GET['sex'] ?? null,
    'family_name'  => $_GET['family_name'] ?? null,
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
                     c.height, c.chest, c.waist, c.hip, c.sleeve, 
                     c.inseam, c.outseam, c.shoulder, c.short_length,
                     f.family_name
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
$types = 'i';

// Age filter
if ($viewMode === 'individual' && ($filters['lower_age'] || $filters['upper_age'])) {
    $filterClauses[] = "c.age BETWEEN ? AND ?";
    $types .= 'ii';
    $params[] = $filters['lower_age'] ?: 0;
    $params[] = $filters['upper_age'] ?: 100;
}

// Gender filter
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
        $types .= 's';
        $paramValues[] = $searchPattern;
    } else {
        $types .= 'sss';
        array_push($paramValues, $searchPattern, $searchPattern, $searchPattern);
    }
}

// Merge filter parameters
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

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            cursor: pointer;
        }

        .modal-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
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

                    <select name="sex" id="sex" class="input-small" placeholder="male">
                        <option disabled selected>Choose Sex</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                        <option value="O">Other</option>
                    </select>
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

    <div class="modal-overlay" id="customerModal">
        <div class="modal-content">
            <button class="modal-close" id="closeModal">
                <img src="assets/icons/close-x.svg" alt="Close">
            </button>
            <div id="modalContent"></div>
            <div class="modal-actions">
                <button id="editCustomer" class="btn btn-primary">Edit</button>
                <button id="deleteCustomer" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>

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
        // Pass customer data to JS
        let currentCustomerId = null;
        const customers = <?= json_encode(array_column($data, null, 'customer_id')) ?>;
        const families = <?= json_encode(array_column($data, null, 'family_id')) ?>
        


        // Modal handling
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', () => {

                currentCustomerId = button.dataset.customerId; //Store customer ID
                const customer = customers[currentCustomerId];
                if (customer) {
                    const content = `
                        <h2>${customer.first_name} ${customer.last_name}</h2>
                        <p><strong>Age:</strong> ${customer.age}</p>
                        <p><strong>Gender:</strong> ${customer.gender}</p>
                        ${customer.family_name ? `<p><strong>Family:</strong> ${customer.family_name}</p>` : ''}
                        
                        <div class="measurements">
                            ${customer.height ? `<p>Height: ${customer.height}cm</p>` : ''}
                            ${customer.chest ? `<p>Chest: ${customer.chest}cm</p>` : ''}
                            ${customer.waist ? `<p>Waist: ${customer.waist}cm</p>` : ''}
                            ${customer.hip ? `<p>Hip: ${customer.hip}cm</p>` : ''}
                            ${customer.sleeve ? `<p>Sleeve: ${customer.sleeve}cm</p>` : ''}
                            ${customer.inseam ? `<p>Inseam: ${customer.inseam}cm</p>` : ''}
                            ${customer.outseam ? `<p>Outseam: ${customer.outseam}cm</p>` : ''}
                            ${customer.shoulder ? `<p>Shoulder: ${customer.shoulder}cm</p>` : ''}
                            ${customer.short_length ? `<p>Short Length: ${customer.short_length}cm</p>` : ''}
                        </div>
                    `;
                    document.getElementById('modalContent').innerHTML = content;
                    document.getElementById('customerModal').style.display = 'flex';
                }
            });
        });

        document.querySelectorAll('.view-family').forEach(button => {
            button.addEventListener('click', () => {
                const family = families[button.dataset.familyId];
                if (family) {
                    const content = `
                        <h2><strong>Family Name:</strong> ${family.family_name}</h2>
                        <h3><strong>Family Address:</strong> ${family.family_address}</h3>

                       
                       
                        
                    `;
                    document.getElementById('modalContent').innerHTML = content;
                    document.getElementById('customerModal').style.display = 'flex';
                }
            });
        });

        document.getElementById('editCustomer').addEventListener('click', () => {
            if (currentCustomerId) {
                window.location.href = `registerClient.php?edit_id=${currentCustomerId}`;
            }
        });

        document.getElementById('deleteCustomer').addEventListener('click', () => {
            if (currentCustomerId && confirm('Are you sure you want to delete this customer?')) {
                window.location.href = `deleteCustomer.php?customer_id=${currentCustomerId}`;
            }
        });

        // Close modal handlers
        document.getElementById('closeModal').addEventListener('click', () => {
            document.getElementById('customerModal').style.display = 'none';
        });

        document.getElementById('customerModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('customerModal')) {
                document.getElementById('customerModal').style.display = 'none';
            }
        });


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