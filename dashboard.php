<?php
session_start();
require_once 'db_connect.php'; // Assumes a file that establishes $conn (database connection)

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch username
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['username'];
$stmt->close();

// Define current and previous month date boundaries
$current_month_start = date('Y-m-01 00:00:00');
$previous_month_start = date('Y-m-01 00:00:00', strtotime('-1 month'));
$previous_month_end = date('Y-m-t 23:59:59', strtotime('-1 month'));

// Function to calculate percentage change
function calculate_change($current, $previous)
{
    if ($previous == 0) {
        return 0;
    }
    return round((($current - $previous) / $previous) * 100, 2);
}

// **Individual Accounts**
$individual_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE family_id IS NULL AND user_id = ? AND created_at >= ?");
$individual_current_stmt->bind_param("is", $user_id, $current_month_start);
$individual_current_stmt->execute();
$individual_current = 25; // $individual_current_stmt->get_result()->fetch_assoc()['count'];
$individual_current_stmt->close();

$individual_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE family_id IS NULL AND user_id = ? AND created_at >= ? AND created_at <= ?");
$individual_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$individual_previous_stmt->execute();
$individual_previous = 20; // $individual_previous_stmt->get_result()->fetch_assoc()['count'];
$individual_previous_stmt->close();

$individual_change = calculate_change($individual_current, $individual_previous);
$individual_color = $individual_change > 0 ? 'green' : ($individual_change < 0 ? 'red' : 'gray');

// **Family Accounts**
$family_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families WHERE user_id = ? AND created_at >= ?");
$family_current_stmt->bind_param("is", $user_id, $current_month_start);
$family_current_stmt->execute();
$family_current = 5; // $family_current_stmt->get_result()->fetch_assoc()['count'];
$family_current_stmt->close();

$family_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families WHERE user_id = ? AND created_at >= ? AND created_at <= ?");
$family_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$family_previous_stmt->execute();
$family_previous = 10; // $family_previous_stmt->get_result()->fetch_assoc()['count'];
$family_previous_stmt->close();

$family_change = calculate_change($family_current, $family_previous);
$family_color = $family_change > 0 ? 'green' : ($family_change < 0 ? 'red' : 'gray');

// **Males**
$male_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'male' AND user_id = ? AND created_at >= ?");
$male_current_stmt->bind_param("is", $user_id, $current_month_start);
$male_current_stmt->execute();
$male_current = 15; // $male_current_stmt->get_result()->fetch_assoc()['count'];
$male_current_stmt->close();

$male_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'male' AND user_id = ? AND created_at >= ? AND created_at <= ?");
$male_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$male_previous_stmt->execute();
$male_previous = 5; // $male_previous_stmt->get_result()->fetch_assoc()['count'];
$male_previous_stmt->close();

$male_change = calculate_change($male_current, $male_previous);
$male_color = $male_change > 0 ? 'green' : ($male_change < 0 ? 'red' : 'gray');

// **Females**
$female_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'female' AND user_id = ? AND created_at >= ?");
$female_current_stmt->bind_param("is", $user_id, $current_month_start);
$female_current_stmt->execute();
$female_current = 10; // $female_current_stmt->get_result()->fetch_assoc()['count'];
$female_current_stmt->close();

$female_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'female' AND user_id = ? AND created_at >= ? AND created_at <= ?");
$female_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$female_previous_stmt->execute();
$female_previous = 15; // $female_previous_stmt->get_result()->fetch_assoc()['count'];
$female_previous_stmt->close();

$female_change = calculate_change($female_current, $female_previous);
$female_color = $female_change > 0 ? 'green' : ($female_change < 0 ? 'red' : 'gray');

// **Orphans (customers without a family)**
$orphan_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE family_id IS NULL AND user_id = ? AND created_at >= ?");
$orphan_current_stmt->bind_param("is", $user_id, $current_month_start);
$orphan_current_stmt->execute();
$orphan_current = 13; // $orphan_current_stmt->get_result()->fetch_assoc()['count'];
$orphan_current_stmt->close();

$orphan_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE family_id IS NULL AND user_id = ? AND created_at >= ? AND created_at <= ?");
$orphan_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$orphan_previous_stmt->execute();
$orphan_previous = 5; // $orphan_previous_stmt->get_result()->fetch_assoc()['count'];
$orphan_previous_stmt->close();

$orphan_change = calculate_change($orphan_current, $orphan_previous);
$orphan_color = $orphan_change > 0 ? 'green' : ($orphan_change < 0 ? 'red' : 'gray');

// **Empty Families (families with no customers)**
$empty_family_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families f LEFT JOIN customers c ON f.family_id = c.family_id WHERE f.user_id = ? AND c.customer_id IS NULL AND f.created_at >= ?");
$empty_family_current_stmt->bind_param("is", $user_id, $current_month_start);
$empty_family_current_stmt->execute();
$empty_family_current = 4; // $empty_family_current_stmt->get_result()->fetch_assoc()['count'];
$empty_family_current_stmt->close();

$empty_family_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families f LEFT JOIN customers c ON f.family_id = c.family_id WHERE f.user_id = ? AND c.customer_id IS NULL AND f.created_at >= ? AND f.created_at <= ?");
$empty_family_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$empty_family_previous_stmt->execute();
$empty_family_previous = 9; // $empty_family_previous_stmt->get_result()->fetch_assoc()['count'];
$empty_family_previous_stmt->close();

$empty_family_change = calculate_change($empty_family_current, $empty_family_previous);
$empty_family_color = $empty_family_change > 0 ? 'green' : ($empty_family_change < 0 ? 'red' : 'gray');

// **Total Counts Section**

// Total Individual Accounts (All Customers)
$individual_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE user_id = ?");
$individual_total_stmt->bind_param("i", $user_id);
$individual_total_stmt->execute();
$individual_total = 150; //$individual_total_stmt->get_result()->fetch_assoc()['count'];
$individual_total_stmt->close();

// Total Family Accounts
$family_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families WHERE user_id = ?");
$family_total_stmt->bind_param("i", $user_id);
$family_total_stmt->execute();
$family_total = 50; // $family_total_stmt->get_result()->fetch_assoc()['count'];
$family_total_stmt->close();

// Total Males
$male_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'male' AND user_id = ?");
$male_total_stmt->bind_param("i", $user_id);
$male_total_stmt->execute();
$male_total = 36; // $male_total_stmt->get_result()->fetch_assoc()['count'];
$male_total_stmt->close();

// Total Females
$female_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'female' AND user_id = ?");
$female_total_stmt->bind_param("i", $user_id);
$female_total_stmt->execute();
$female_total = 60; // $female_total_stmt->get_result()->fetch_assoc()['count'];
$female_total_stmt->close();

// Total Orphans (same as Individual Accounts, reflecting current total)
$orphan_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE family_id IS NULL AND user_id = ?");
$orphan_total_stmt->bind_param("i", $user_id);
$orphan_total_stmt->execute();
$orphan_total = 5; // $orphan_total_stmt->get_result()->fetch_assoc()['count'];
$orphan_total_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLocker: Dashboard</title>
    <link rel="stylesheet" href="./Styles/main.css?v=1">
    <link rel="stylesheet" href="./Styles/sidebar.css?v=1">
    <link rel="stylesheet" href="./Styles/dashboardstyles.css?v=1">
</head>

<body>
    <div class="container">
        <aside class="dashboard-navbar" id="sideNav">
            <div id="topSide">
                <a href="homepage.php" class="home-link">
                    <div></div>
                </a>
                <button
                    type="button"
                    class="nav-icon btn active"
                    onclick="switchActive(this), window.location.href='dashboard.php'">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 -960 960 960"
                        fill="#000000">
                        <path
                            d="M520-600v-240h320v240H520ZM120-440v-400h320v400H120Zm400 320v-400h320v400H520Zm-400 0v-240h320v240H120Zm80-400h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="nav-icon btn"
                    id="navbarSearchIcon"
                    onclick="switchActive(this), window.location.href='search.php'">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 -960 960 960"
                        fill="#000000">
                        <path
                            d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="nav-icon btn"
                    id="statIcon"
                    onclick="switchActive(this)">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 -960 960 960"
                        fill="#000000">
                        <path
                            d="M160-160v-320h160v320H160Zm240 0v-640h160v640H400Zm240 0v-440h160v440H640Z" />
                    </svg>
                </button>
            </div>
            <div id="bottomSide">
                <button type="button" class="nav-icon" id="helpIcon" onclick="window.location.href='./about.php#contactUs'">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 -960 960 960"
                        fill="#000000">
                        <path
                            d="M478-240q21 0 35.5-14.5T528-290q0-21-14.5-35.5T478-340q-21 0-35.5 14.5T428-290q0 21 14.5 35.5T478-240Zm-36-154h74q0-33 7.5-52t42.5-52q26-26 41-49.5t15-56.5q0-56-41-86t-97-30q-57 0-92.5 30T342-618l66 26q5-18 22.5-39t53.5-21q32 0 48 17.5t16 38.5q0 20-12 37.5T506-526q-44 39-54 59t-10 73Zm38 314q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z" />
                    </svg>
                </button>
                <button type="button" class="nav-icon" id="accountsIcon">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 -960 960 960"
                        fill="#000000">
                        <path
                            d="M234-276q51-39 114-61.5T480-360q69 0 132 22.5T726-276q35-41 54.5-93T800-480q0-133-93.5-226.5T480-800q-133 0-226.5 93.5T160-480q0 59 19.5 111t54.5 93Zm246-164q-59 0-99.5-40.5T340-580q0-59 40.5-99.5T480-720q59 0 99.5 40.5T620-580q0 59-40.5 99.5T480-440Zm0 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q53 0 100-15.5t86-44.5q-39-29-86-44.5T480-280q-53 0-100 15.5T294-220q39 29 86 44.5T480-160Zm0-360q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17Zm0-60Zm0 360Z" />
                    </svg>
                </button>
                <button type="button" class="nav-icon" id="logoutIcon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="#000000">
                        <path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h280v80H200Zm440-160-55-58 102-102H360v-80h327L585-622l55-58 200 200-200 200Z" />
                    </svg>
                </button>

            </div>
        </aside>

        <div class="main-dashboard">
            <div class="title-container">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    height="24px"
                    viewBox="0 -960 960 960"
                    width="24px"
                    fill="#000000">
                    <path
                        d="M480-120v-80h280v-560H480v-80h280q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H480Zm-80-160-55-58 102-102H120v-80h327L345-622l55-58 200 200-200 200Z" />
                </svg>
                <span id="title">Dashboard</span>
            </div>

            <div id="info">
                <h2>Welcome <?php echo htmlspecialchars($username); ?></h2>

                <button type="button" onclick="window.location.href='./search.php'" class="search-btn-main">
                    <img src="./assets/icons/search-white.svg" alt="search-button">
                    <p class="sm">Search</p>
                </button>

            </div>



            <div class="grid-container">
                <a href="registerClient.php" class="box large" id="box1">
                    <div id="leftsideInfoLarge">
                        <h2 id="largeBoxName">Register a Person</h2>
                        <button type="button" id="largeBoxIcon">
                            <img src="./assets/icons/plus-icon.svg" alt="add-client">
                        </button>
                    </div>
                    <div id="boxImage">
                        <img src="" alt="" />
                    </div>
                </a>

                <a href="registerFamily.php" class="box large" id="box2">
                    <div id="leftsideInfoLarge">
                        <h2 id="largeBoxName">Register a Family</h2>
                        <button type="button" id="largeBoxIcon">
                            <img src="./assets/icons/plus-icon.svg" alt="">
                        </button>
                    </div>
                    <div id="boxImage">
                        <img src="" alt="" />
                    </div>
                </a>

                <div class="box" id="box3">
                    <div id="boxTopside">
                        <h2 id="boxName">Individual Accounts</h2>
                        <img src="./assets/icons/arrow-right.svg" alt="arrowIcon">
                    </div>
                    <div id="boxBottomside">
                        <div id="boxCount"><?php echo $individual_total; ?></div>
                        <div id="countChangeBox">
                            <div id="countChange" class="<?php echo $individual_color; ?>"><?php echo $individual_change; ?>%</div>
                            <span id="countChangeInfo">vs last month</span>
                        </div>
                    </div>
                </div>
                <div class="box" id="box4">
                    <div id="boxTopside">
                        <h2 id="boxName">Family Accounts</h2>
                        <img src="./assets/icons/arrow-right.svg" alt="arrowIcon">
                    </div>
                    <div id="boxBottomside">
                        <div id="boxCount"><?php echo $family_total; ?></div>
                        <div id="countChangeBox">
                            <div id="countChange" class="<?php echo $family_color; ?>"><?php echo $family_change; ?>%</div>
                            <span id="countChangeInfo">vs last month</span>
                        </div>
                    </div>
                </div>
                <div class="box" id="box5">
                    <div id="boxTopside">
                        <h2 id="boxName">Males</h2>
                        <img src="./assets/icons/arrow-right.svg" alt="arrowIcon">
                    </div>
                    <div id="boxBottomside">
                        <div id="boxCount"><?php echo $male_total; ?></div>
                        <div id="countChangeBox">
                            <div id="countChange" class="<?php echo $male_color; ?>"><?php echo $male_change; ?>%</div>
                            <span id="countChangeInfo">vs last month</span>
                        </div>
                    </div>
                </div>
                <div class="box" id="box6">
                    <div id="boxTopside">
                        <h2 id="boxName">Females</h2>
                        <img src="./assets/icons/arrow-right.svg" alt="arrowIcon">
                    </div>
                    <div id="boxBottomside">
                        <div id="boxCount"><?php echo $female_total; ?></div>
                        <div id="countChangeBox">
                            <div id="countChange" class="<?php echo $female_color; ?>"><?php echo $female_change; ?>%</div>
                            <span id="countChangeInfo">vs last month</span>
                        </div>
                    </div>
                </div>
                <div class="box" id="box7">
                    <div id="boxTopside">
                        <h2 id="boxName">Orphans</h2>
                        <img src="./assets/icons/arrow-right.svg" alt="arrowIcon">
                    </div>
                    <div id="boxBottomside">
                        <div id="boxCount"><?php echo $orphan_total; ?></div>
                        <div id="countChangeBox">
                            <div id="countChange" class="<?php echo $orphan_color; ?>"><?php echo $orphan_change; ?>%</div>
                            <span id="countChangeInfo">vs last month</span>
                        </div>
                    </div>
                </div>
                <div class="box" id="box8">
                    <div id="boxTopside">
                        <h2 id="boxName">Empty Families</h2>
                        <img src="./assets/icons/arrow-right.svg" alt="arrowIcon">
                    </div>
                    <div id="boxBottomside">
                        <div id="boxCount"><?php echo $empty_family_current; ?></div>
                        <div id="countChangeBox">
                            <div id="countChange" class="<?php echo $empty_family_color; ?>"><?php echo $empty_family_change; ?>%</div>
                            <span id="countChangeInfo">vs last month</span>
                        </div>
                    </div>
                </div>

                <a href="about.php" class="box large" id="box9">
                    <div id="leftsideInfoLarge">
                        <h2 id="largeBoxName">Get Help</h2>
                        <button type="button" id="largeBoxIcon">
                            <img src="./assets/icons/contact_support_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.svg" alt="help">
                        </button>
                    </div>
                    <div id="boxImage">
                        <img src="" alt="" />
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="./Scripts/password.js"></script>
    <script src="./Scripts/dashboardscript.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page is fully loaded');
            const logoutButton = document.getElementById('logoutIcon');

            if (logoutButton) {
                console.log('Logout button found');
                logoutButton.addEventListener('click', function() {
                    console.log('Logout button clicked');
                    if (confirm('Are you sure you want to Log Out?')) {
                        fetch('logout.php')
                            .then(response => response.text())
                            .then(data => {
                                if (data === 'success') {
                                    window.location.href = 'homepage.php';
                                }
                            })
                            .catch(error => {
                                console.error('Error during logout:', error);
                            });
                    }
                });
            } else {
                console.error('Logout button not found');
            }
        });
    </script>
</body>

</html>