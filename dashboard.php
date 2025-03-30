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
$individual_current = $individual_current_stmt->get_result()->fetch_assoc()['count'];
$individual_current_stmt->close();

$individual_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE family_id IS NULL AND user_id = ? AND created_at >= ? AND created_at <= ?");
$individual_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$individual_previous_stmt->execute();
$individual_previous = $individual_previous_stmt->get_result()->fetch_assoc()['count'];
$individual_previous_stmt->close();

$individual_change = calculate_change($individual_current, $individual_previous);
$individual_color = $individual_change > 0 ? 'green' : ($individual_change < 0 ? 'red' : 'gray');

// **Family Accounts**
$family_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families WHERE user_id = ? AND created_at >= ?");
$family_current_stmt->bind_param("is", $user_id, $current_month_start);
$family_current_stmt->execute();
$family_current = $family_current_stmt->get_result()->fetch_assoc()['count'];
$family_current_stmt->close();

$family_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families WHERE user_id = ? AND created_at >= ? AND created_at <= ?");
$family_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$family_previous_stmt->execute();
$family_previous = $family_previous_stmt->get_result()->fetch_assoc()['count'];
$family_previous_stmt->close();

$family_change = calculate_change($family_current, $family_previous);
$family_color = $family_change > 0 ? 'green' : ($family_change < 0 ? 'red' : 'gray');

// **Males**
$male_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'male' AND user_id = ? AND created_at >= ?");
$male_current_stmt->bind_param("is", $user_id, $current_month_start);
$male_current_stmt->execute();
$male_current = $male_current_stmt->get_result()->fetch_assoc()['count'];
$male_current_stmt->close();

$male_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'male' AND user_id = ? AND created_at >= ? AND created_at <= ?");
$male_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$male_previous_stmt->execute();
$male_previous = 0; // $male_previous_stmt->get_result()->fetch_assoc()['count'];
$male_previous_stmt->close();

$male_change = calculate_change($male_current, $male_previous);
$male_color = $male_change > 0 ? 'green' : ($male_change < 0 ? 'red' : 'gray');

// **Females**
$female_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'female' AND user_id = ? AND created_at >= ?");
$female_current_stmt->bind_param("is", $user_id, $current_month_start);
$female_current_stmt->execute();
$female_current = $female_current_stmt->get_result()->fetch_assoc()['count'];
$female_current_stmt->close();

$female_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'female' AND user_id = ? AND created_at >= ? AND created_at <= ?");
$female_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$female_previous_stmt->execute();
$female_previous = 0; // $female_previous_stmt->get_result()->fetch_assoc()['count'];
$female_previous_stmt->close();

$female_change = calculate_change($female_current, $female_previous);
$female_color = $female_change > 0 ? 'green' : ($female_change < 0 ? 'red' : 'gray');

// **Orphans (customers without a family)**
$orphan_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE family_id IS NULL AND user_id = ? AND created_at >= ?");
$orphan_current_stmt->bind_param("is", $user_id, $current_month_start);
$orphan_current_stmt->execute();
$orphan_current = $orphan_current_stmt->get_result()->fetch_assoc()['count'];
$orphan_current_stmt->close();

$orphan_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE family_id IS NULL AND user_id = ? AND created_at >= ? AND created_at <= ?");
$orphan_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$orphan_previous_stmt->execute();
$orphan_previous = 0; // $orphan_previous_stmt->get_result()->fetch_assoc()['count'];
$orphan_previous_stmt->close();

$orphan_change = calculate_change($orphan_current, $orphan_previous);
$orphan_color = $orphan_change > 0 ? 'green' : ($orphan_change < 0 ? 'red' : 'gray');

// **Empty Families (families with no customers)**
$empty_family_current_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families f LEFT JOIN customers c ON f.family_id = c.family_id WHERE f.user_id = ? AND c.customer_id IS NULL AND f.created_at >= ?");
$empty_family_current_stmt->bind_param("is", $user_id, $current_month_start);
$empty_family_current_stmt->execute();
$empty_family_current = $empty_family_current_stmt->get_result()->fetch_assoc()['count'];
$empty_family_current_stmt->close();

$empty_family_previous_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families f LEFT JOIN customers c ON f.family_id = c.family_id WHERE f.user_id = ? AND c.customer_id IS NULL AND f.created_at >= ? AND f.created_at <= ?");
$empty_family_previous_stmt->bind_param("iss", $user_id, $previous_month_start, $previous_month_end);
$empty_family_previous_stmt->execute();
$empty_family_previous = 0; // $empty_family_previous_stmt->get_result()->fetch_assoc()['count'];
$empty_family_previous_stmt->close();

$empty_family_change = calculate_change($empty_family_current, $empty_family_previous);
$empty_family_color = $empty_family_change > 0 ? 'green' : ($empty_family_change < 0 ? 'red' : 'gray');

// **Total Counts Section**

// Total Individual Accounts (All Customers)
$individual_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE user_id = ?");
$individual_total_stmt->bind_param("i", $user_id);
$individual_total_stmt->execute();
$individual_total = $individual_total_stmt->get_result()->fetch_assoc()['count'];
$individual_total_stmt->close();

// Total Family Accounts
$family_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families WHERE user_id = ?");
$family_total_stmt->bind_param("i", $user_id);
$family_total_stmt->execute();
$family_total = $family_total_stmt->get_result()->fetch_assoc()['count'];
$family_total_stmt->close();

// Total Males
$male_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'male' AND user_id = ?");
$male_total_stmt->bind_param("i", $user_id);
$male_total_stmt->execute();
$male_total = $male_total_stmt->get_result()->fetch_assoc()['count'];
$male_total_stmt->close();

// Total Females
$female_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE gender = 'female' AND user_id = ?");
$female_total_stmt->bind_param("i", $user_id);
$female_total_stmt->execute();
$female_total = $female_total_stmt->get_result()->fetch_assoc()['count'];
$female_total_stmt->close();

// Total Orphans (same as Individual Accounts, reflecting current total)
$orphan_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE family_id IS NULL AND user_id = ?");
$orphan_total_stmt->bind_param("i", $user_id);
$orphan_total_stmt->execute();
$orphan_total = $orphan_total_stmt->get_result()->fetch_assoc()['count'];
$orphan_total_stmt->close();


$empty_family_total_stmt = $conn->prepare("SELECT COUNT(*) as count FROM families f LEFT JOIN customers c ON f.family_id = c.family_id WHERE f.user_id = ? AND c.customer_id IS NULL");
$empty_family_total_stmt->bind_param("i", $user_id);
$empty_family_total_stmt->execute();
$empty_family_total = $empty_family_total_stmt->get_result()->fetch_assoc()['count'];
$empty_family_total_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLocker: Dashboard</title>
    <link rel="stylesheet" href="./Styles/main.css?v=1.0">
    <link rel="stylesheet" href="./Styles/sidebar.css?v=1.0">
    <link rel="stylesheet" href="./Styles/menus.css?v=1.0">
    <link rel="stylesheet" href="./Styles/dashboardstyles.css?v=1.0">
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
        
                    <div id="main-section-header">
                        <h2>Welcome <?php echo htmlspecialchars($username); ?></h2>
                        <button class="btn btn-sm btn-outline sh-sm" type="button" onclick="window.location.href='./search.php'" class="search-btn-main">
                            <img src="./assets/icons/search-white.svg" alt="search-button">
                            Search
                        </button>
                    </div>
        
                    <div class="grid-container">
                        <a href="registerClient.php" class="box large sh-md" id="box1">
                            <div id="leftsideInfoLarge">
                                <h3 id="largeBoxName">Register a Person</h3>
                                <button type="button" id="largeBoxIcon">
                                    <img src="./assets/icons/plus-icon.svg" alt="add-client">
                                </button>
                            </div>
                            <div id="boxImage">
                                <img src="" alt="" />
                            </div>
                        </a>
        
                        <a href="registerFamily.php" class="box large sh-sm" id="box2">
                            <div id="leftsideInfoLarge">
                                <h3 id="largeBoxName">Register a Family</h3>
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
                                <h3 id="boxName">Individual Accounts</h3>
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
                                <div id="boxCount"><?php echo $empty_family_total; ?></div>
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
                </main>
            </div>
        </div>

    <script src="./Scripts/script.js"></script>
    <script src="./Scripts/navbar.js"></script>
    <script src="./Scripts/dashboardscript.js?v=1.0"></script>

</body>

</html>