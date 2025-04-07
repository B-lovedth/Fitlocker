<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Add after session_start()
if (isset($_SESSION['message'])) {
    $successMessage = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
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
                     COUNT(c.customer_id) AS member_count,
                     GROUP_CONCAT(CONCAT(c.first_name, ' ', c.last_name) SEPARATOR ', ') AS member_names
              FROM families f
              LEFT JOIN customers c ON f.family_id = c.family_id
              WHERE f.user_id = ?";
} else {
    $query = "SELECT c.customer_id, c.first_name, c.last_name, c.age, c.gender, c.address,
                     c.phone, c.height, c.length, c.chest, c.waist, c.hip, c.sleeve, 
                     c.inseam, c.outseam, c.shoulder, c.short_length,
                     c.created_at, f.family_name
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
    $query .= " ORDER BY c.first_name, c.last_name";
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
    <title>Search</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./Styles/main.css">
    <link rel="stylesheet" href="./Styles/sidebar.css?v=1.0" />
    <link rel="stylesheet" href="./Styles/menus.css?v=1.0">
    <link rel="stylesheet" href="./Styles/search.css?v=1.0">
    <title>FitLocker: Search</title>
    <style>
        .clear-search {
            position: absolute;
            height: 1.5rem;
            right: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            opacity: 0.7;
            display: <?= !empty($searchTerm) ? 'block' : 'none' ?>;
        }

        .clear-search img {
            height: 100%;
            aspect-ratio: 1/1;
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
            flex-direction: column;
            gap: 3rem;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
            position: relative;
        }
        .modal-content strong {
            font-weight: 500;
        }

        .modal-content hr {
            margin: .5rem 0 .5rem;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin-bottom: 1rem;
        }

        .modal-header img {
            height: 1rem;
        }
        .modal-section {
            display: flex;
            flex-direction: column;
            margin-top: 1rem;
        }

        #closeModal {
            height: 3rem;
            width: 3rem;
        }

        .modal-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
    </style>
</head>

<body>
    <!-- sidebar import -->
    <?php require_once "./sidebar.php" ?>
    <!-- modal impport -->
    <?php require_once "./accountsModal.php" ?>

    <div class="container">
        <?php require_once "./navbar.php" ?>
        <div id="overlay" class="hide"></div>
        <main class="main-section-container">
            <form class="clientForm" method="GET">
                <div class="search-container">
                    <div class="searchbar">
                        <input type="text" name="search" placeholder="Search your registered customers"
                            value="<?= htmlspecialchars($searchTerm) ?>" id="searchInput">
                        <!-- Add clear button -->
                        <button type="button" class="clear-search" id="clearSearch"
                            onclick="resetSearch()"
                            title="Clear search">
                            <img src="assets/icons/clear-x-outline.svg" alt="Clear">
                        </button>
                    </div>
                    <button type="submit" class="btn btn-sm btn-secondary" id="entersearch">Search</button>
                    <button class="btn btn-sm btn-ghost" id="filter">Filters</button>
                </div>
                <div id="filter-section" class="fields slim">
                    <div class="field">
                        <label for="age1 age2">Age</label>
                        <div class="input-range">
                            <input type="number" name="lower_age" id="age1" class="input-small" placeholder="18" min="0">
                            -
                            <input type="number" name="upper_age" id="age2" class="input-small" placeholder="35" min="0">
                        </div>
                    </div>
                    <div class="field">
                        <label for="sex">Sex(M/F)</label>
                        <select name="sex" id="sex" placeholder="male">
                            <option class="sm" disabled selected>Choose Sex</option>
                            <option class="sm" value="M">Male</option>
                            <option class="sm" value="F">Female</option>
                            <option class="sm" value="O">Other</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="family">Family</label>
                        <input type="text" name="family_name" id="family" placeholder="Otedola" class="input-small">
                    </div>
                </div>
            </form>

            <!-- /*     if ($customers == [] || $customers == null) {
                    echo
                    ('
                        <div id="empty-search">
                            <img src="./assets/icons/no-search.svg" alt="">
                            <h3>You havent searched anything</h3>
                            <ul>t
                                <li>Use the search bar to find your customers by name</li>
                                <li>Add filters to narrow down your search</li>
                                <li>Switch to family if youre looking for a family</li>
                                <li>When you see results click on any customer to view details in full.</li>
                            </ul>
                        </div>
                    ');
                } else {
                   echo  
                   ('
                   ');
                   
                   } */
             -->

            <section id="accounts" class="main-section-container">
                <div id="main-section-header">
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
                    <?php if ($viewMode === 'family'): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th class="col1">Family Name</th>
                                    <th class="col2">Address</th>
                                    <th class="col3">Members</th>
                                    <th class="col6"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $family): ?>
                                    <tr>
                                        <td class="col1"><?= htmlspecialchars($family['family_name'])?></td>
                                        <td class="col2"><?= htmlspecialchars($family['family_address'])? : '-' ?></td>
                                        <td class="col3"><?= $family['member_count']?></td>
                                        <td class="col6">
                                            <button class="view-family btn btn-sm btn-outline col6"
                                                data-family-id="<?= $family['family_id'] ?>">
                                                View Family
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <table class="">
                            <thead>
                                <tr>
                                    <th class="col1">Name</th>
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
                                        <td class="col3"><?= htmlspecialchars($customer['age'])? : '-' ?></td>
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

            </section>
        </main>
    </div>
    
    <div class="modal-overlay" id="customerModal">
        <div class="modal-content sh-lg">
            <div id="modalContent"></div>
            <div class="modal-actions">
                <button id="editCustomer" class="btn btn-sm btn-primary sh-sm">Edit</button>
                <button id="deleteCustomer" class="btn btn-sm btn-danger sh-sm">Delete</button>
            </div>
        </div>
    </div>

    <script src="./Scripts/script.js?v=1.0"></script>
    <script src="./Scripts/navbar.js"></script>
    <script src="./Scripts/dashboardscript.js?v=1.0"></script>
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
                        <div class="modal-header">
                        <h3>${customer.first_name} ${customer.last_name}</h3>
                        <button class="btn btn-sm btn-secondary sh-sm close-search" id="close-search">
                            <img src="./assets/icons/close-x.svg" alt="close-search">
                        </button>
                        </div>
                        <hr>
                        <p><strong>Age:</strong> ${customer.age || 'N/A'}</p>
                        <p><strong>Gender:</strong> ${customer.gender || 'N/A'}</p>
                        <p><strong>Address:</strong> ${customer.address || 'N/A'}</p>
                        <p><strong>Phone:</strong> ${customer.phone || 'N/A'}</p>
                        ${customer.family_name ? `<p><strong>Family:</strong> ${customer.family_name}</p>` : ''}
                        <p><strong>Date Created:</strong> ${new Date(customer.created_at).toLocaleDateString() || 'N/A'}</p>

                        <div class="measurements">
                            <div class="modal-section">
                            <h5>Body Measurements</h5>
                            <hr>
                            ${customer.height ? `<strong><p>Height:</strong> ${customer.height}cm</p>` : ''}
                            ${customer.length ? `<strong><p>Length:</strong> ${customer.length}cm</p>` : ''}
                            ${customer.chest ? `<strong><p>Chest:</strong> ${customer.chest}cm</p>` : ''}
                            ${customer.waist ? `<strong><p>Waist:</strong> ${customer.waist}cm</p>` : ''}
                            ${customer.hip ? `<strong><p>Hip:</strong> ${customer.hip}cm</p>` : ''}
                            </div>
                            
                            <div class="modal-section">
                            <h5>Garment Measurements</h5>
                            <hr>
                            ${customer.sleeve ? `<strong><p>Sleeve:</strong> ${customer.sleeve}cm</p>` : ''}
                            ${customer.inseam ? `<strong><p>Inseam:</strong> ${customer.inseam}cm</p>` : ''}
                            ${customer.outseam ? `<strong><p>Outseam:</strong> ${customer.outseam}cm</p>` : ''}
                            ${customer.shoulder ? `<strong><p>Shoulder:</strong> ${customer.shoulder}cm</p>` : ''}
                            ${customer.short_length ? `<strong><p>Short Length:</strong> ${customer.short_length}cm</p>` : ''}
                            </div>
                        </div>
                    `;
                    document.getElementById('modalContent').innerHTML = content;
                    document.getElementById('customerModal').style.display = 'flex';
                    document.querySelector('.modal-actions').style.display = 'flex';

                    //Close Search with the button
                    document.getElementById("close-search").addEventListener('click', () => {
                        document.getElementById('customerModal').style.display = 'none';
                    });
                    }
            });
        });

        document.querySelectorAll('.view-family').forEach(button => {
            button.addEventListener('click', () => {
                const family = families[button.dataset.familyId];
                if (family) {
                    const content = `
                        <div class="modal-header">
                        <h2>${family.family_name} Family</h2>
                        <button class="btn btn-sm btn-secondary sh-sm close-search" id="close-search">
                            <img src="./assets/icons/close-x.svg" alt="close-search">
                        </button>
                        </div>
                        <hr>
                        <p><strong>Family Address:</strong> ${family.family_address || 'N/A'}</p>
                        <p><strong>Members Count:</strong> ${family.member_count || 'N/A'}</p>
                        <h3><strong>Family Members:</strong></h3>
                        <p>${family.member_names || 'This Family is empty'}</p> 
                    `;
                    document.getElementById('modalContent').innerHTML = content;
                    document.getElementById('customerModal').style.display = 'flex';
                    document.querySelector('.modal-actions').style.display = 'none';
                    
                    //Close Search with the button
                    document.getElementById("close-search").addEventListener('click', () => {
                        document.getElementById('customerModal').style.display = 'none';
                    });
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
        document.getElementById('customerModal').addEventListener('click', (e) => { //Close by touching anywhere outside the modal
            if (e.target === document.getElementById('customerModal')) {
                document.getElementById('customerModal').style.display = 'none';
            }
        });
        console.log(document.getElementById("close-search"));
            

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
                accountSwitch.classList.toggle("flex-end");
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