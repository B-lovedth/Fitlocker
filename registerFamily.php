<?php
session_start();

// Check if the user has an active session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'db_connect.php';

// Fetch existing customers for the current user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT customer_id, first_name, last_name FROM customers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$customers_result = $stmt->get_result();
$customers = $customers_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle AJAX request for family search
if (isset($_GET['check_family'])) {
    $family_name = $_GET['family_name'] ?? '';
    $family_address = $_GET['family_address'] ?? '';

    if (!empty($family_name) && !empty($family_address)) {
        $stmt = $conn->prepare("SELECT family_id FROM families WHERE family_name = ? AND family_address = ? AND user_id = ?");
        $stmt->bind_param("ssi", $family_name, $family_address, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $family_id = $result->fetch_assoc()['family_id'];

            // Get existing family members
            $member_stmt = $conn->prepare("SELECT customer_id, first_name, last_name FROM customers WHERE family_id = ? AND user_id = ?");
            $member_stmt->bind_param("ii", $family_id, $user_id);
            $member_stmt->execute();
            $members_result = $member_stmt->get_result();
            $family_members = $members_result->fetch_all(MYSQLI_ASSOC);
            $member_stmt->close();

            echo json_encode(['exists' => true, 'members' => $family_members]);
        } else {
            echo json_encode(['exists' => false]);
        }

        $stmt->close();
        exit();
    }

    echo json_encode(['exists' => false]);
    exit();
}

// Handle AJAX request for customer search
if (isset($_GET['search_customers'])) {
    $search_term = '%' . $_GET['search_term'] . '%';

    $stmt = $conn->prepare("SELECT customer_id, first_name, last_name FROM customers 
                           WHERE user_id = ? AND (first_name LIKE ? OR last_name LIKE ?)");
    $stmt->bind_param("iss", $user_id, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $filtered_customers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode($filtered_customers);
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Collect form data
        $family_name = trim($_POST['family_name'] ?? '');
        $family_address = trim($_POST['family_address'] ?? '');
        $selected_customers = $_POST['customers'] ?? [];

        if (empty($family_name)) {
            throw new Exception("Family name is required.");
        }

        // Check if family already exists
        $stmt = $conn->prepare("SELECT family_id FROM families WHERE family_name = ? AND family_address = ? AND user_id = ?");
        $stmt->bind_param("ssi", $family_name, $family_address, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $family_id = $result->fetch_assoc()['family_id'];

            // Get existing family members
            $member_stmt = $conn->prepare("SELECT customer_id FROM customers WHERE family_id = ? AND user_id = ?");
            $member_stmt->bind_param("ii", $family_id, $user_id);
            $member_stmt->execute();
            $members_result = $member_stmt->get_result();
            $existing_members = array_column($members_result->fetch_all(MYSQLI_ASSOC), 'customer_id');
            $member_stmt->close();
        } else {
            // Create new family
            $stmt = $conn->prepare("INSERT INTO families (family_name, family_address, user_id, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("ssi", $family_name, $family_address, $user_id);
            $stmt->execute();
            $family_id = $stmt->insert_id;
            $existing_members = [];
        }
        $stmt->close();

        // Convert selected_customers to integers for comparison
        $selected_customers = array_map('intval', $selected_customers);

        // Determine customers to remove (in existing_members but not in selected_customers)
        $customers_to_remove = array_diff($existing_members, $selected_customers);

        // Remove unselected customers from the family
        if (!empty($customers_to_remove)) {
            $placeholders = implode(',', array_fill(0, count($customers_to_remove), '?'));
            $types = str_repeat('i', count($customers_to_remove));
            $stmt = $conn->prepare("UPDATE customers SET family_id = NULL WHERE customer_id IN ($placeholders) AND user_id = ?");
            $params = array_merge($customers_to_remove, [$user_id]);
            $stmt->bind_param($types . "i", ...$params);
            $stmt->execute();
            $stmt->close();
        }

        // Assign selected customers to the family
        if (!empty($selected_customers)) {
            $placeholders = implode(',', array_fill(0, count($selected_customers), '?'));
            $types = str_repeat('i', count($selected_customers));

            // Remove these customers from any other families first
            $stmt = $conn->prepare("UPDATE customers SET family_id = NULL WHERE customer_id IN ($placeholders) AND user_id = ?");
            $params = array_merge($selected_customers, [$user_id]);
            $stmt->bind_param($types . "i", ...$params);
            $stmt->execute();
            $stmt->close();

            // Now add them to the new family
            $stmt = $conn->prepare("UPDATE customers SET family_id = ? WHERE customer_id IN ($placeholders) AND user_id = ?");
            $params = array_merge([$family_id], $selected_customers, [$user_id]);
            $stmt->bind_param("i" . $types . "i", ...$params);
            $stmt->execute();
            $stmt->close();
        }

        // Set success message
        $_SESSION['registration_status'] = 'success';
        $_SESSION['is_empty_family'] = empty($selected_customers);
        session_write_close();
        header("Location: registerFamily.php");
        exit();
    } catch (Exception $e) {
        error_log("Family Registration Error: " . $e->getMessage());
        $_SESSION['registration_status'] = 'failure';
        $_SESSION['error_message'] = $e->getMessage();
        session_write_close();
        header("Location: registerFamily.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register Family</title>
    <link rel="stylesheet" href="./Styles/main.css" />
    <link rel="stylesheet" href="./Styles/sidebar.css?v=1.0" />
    <link rel="stylesheet" href="./Styles/menus.css?v=1.0">
    <link rel="stylesheet" href="./Styles/register.css?v=1.0" />
    <link rel="stylesheet" href="./Styles/modals.css">
    <style>
        .customer-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 0.5rem;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            margin: 1rem 0 1rem;
        }

        .customer-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .customer-item:hover {
            transform: scale(1.01);
        }

        .customer-item:last-child {
            border-bottom: none;
        }

        .customer-item label {
            cursor: pointer;
        }

        .selected-customers {
            padding: 1rem;
            background-color: #f9f9f9;
            border: 0.5px solid #1b1b1b;
            border-radius: 0.5rem;
        }

        .selected-customers h4 {
            margin-top: 0;
            margin-bottom: 1rem;
        }

        #selectedCustomersList {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            gap: 0.5rem;
        }

        .selected-customer-tag:hover {
            background-color: transparent;
            color: #1b1b1b;
        }

        .selected-customer-tag {
            background-color: transparent;
        }

        .existing-members {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f0f8ff;
            border: 1px solid #b0c4de;
            border-radius: 0.25rem;
            display: none;
        }

        .existing-members h4 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #4682b4;
        }

        #existingMembersList {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            gap: .5rem;
        }
    </style>
</head>

<body>
    <?php require_once "./sidebar.php" ?>
    <?php require_once "./accountsModal.php" ?>
    <div class="container">
        <?php require_once "./navbar.php" ?>
        <div id="overlay" class="hide"></div>
        <!-- Main body -->
        <main class="main-section-container">
            <div id="main-section-header">
                <h2>Register Family</h2>
                <a href="./registerClient.php"><button class="btn btn-sm btn-secondary sh-sm">Register Client Instead</button></a>
            </div>
            <form class="clientForm" action="registerFamily.php" method="POST">
                <div class="personal panel sh-md">
                    <h3>Family Details</h3>
                    <hr>
                    <div class="fields slim">
                        <div class="field">
                            <label for="family_name">Family Name</label>
                            <input type="text" id="family_name" name="family_name" required />
                        </div>
                        <div class="field">
                            <label for="family_address">Family Address</label>
                            <input type="text" id="family_address" name="family_address" />
                        </div>
                        <div class="existing-members" id="existingMembers">
                            <h4>Existing Family Members</h4>
                            <div id="existingMembersList"></div>
                        </div>
                    </div>
                </div>
                <div class="measurement panel sh-md">
                    <h3>Select Customers to Add to Family</h3>
                    <hr>
                    <div class="fields wide">
                        <div class="field">
                            <input type="search" id="customerSearch" class="search-box" placeholder="Search customers..." />
                            <div class="customer-list" id="customerList">
                                <?php foreach ($customers as $customer): ?>
                                    <div class="customer-item">
                                        <input type="checkbox" id="customer_<?php echo $customer['customer_id']; ?>"
                                            name="customers[]" value="<?php echo $customer['customer_id']; ?>"
                                            class="customer-checkbox" />
                                        <label for="customer_<?php echo $customer['customer_id']; ?>">
                                            <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="selected-customers">
                                <h4>Selected Customers</h4>
                                <div id="selectedCustomersList"></div>
                            </div>

                        </div>
                    </div>
                </div>
                <button class="btn btn-sm btn-primary sh-sm" type="submit">Register</button>
            </form>
        </main>
    </div>

    <?php require_once "./success-failureModal.php" ?>

    <script src="./Scripts/script.js?v=1.0"></script>
    <script src="./Scripts/navbar.js"></script>
    <script src="./Scripts/dashboardscript.js?v=1.0"></script>

    <script>
        const customerSearch = document.getElementById('customerSearch');
        const customerList = document.getElementById('customerList');
        const customers = <?php echo json_encode($customers); ?>;
        let selectedCustomerIds = [];

        // Modal helper functions
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function registerAgain() {
            hideModal('successModal');
            document.querySelector('.clientForm').reset();
            document.getElementById('selectedCustomersList').innerHTML = '';
            document.getElementById('existingMembers').style.display = 'none';
            document.querySelectorAll('.customer-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            selectedCustomerIds = [];
            updateSelectedCustomersDisplay();
        }

        function tryAgain() {
            hideModal('errorModal');
            document.getElementById('family_name').focus();
        }

        function goToDashboard() {
            window.location.href = 'dashboard.php';
        }

        // Show modal based on registration status
        <?php if (isset($_SESSION['registration_status'])): ?>
            <?php if ($_SESSION['registration_status'] === 'success'): ?>
                document.getElementById('successModalMessage').textContent =
                    <?php echo json_encode($_SESSION['is_empty_family'] ? 'Empty family created successfully!' : 'Family created successfully!'); ?>;
                showModal('successModal');
            <?php elseif ($_SESSION['registration_status'] === 'failure'): ?>
                document.getElementById('errorModalMessage').textContent =
                    <?php echo json_encode($_SESSION['error_message'] ?? 'An error occurred during family registration.'); ?>;
                showModal('errorModal');
            <?php endif; ?>
            <?php unset($_SESSION['registration_status'], $_SESSION['error_message'], $_SESSION['is_empty_family']); ?>
        <?php endif; ?>

        // Render customer list with persistent selections
        function renderCustomerList(customerArray) {
            let html = '';
            if (customerArray.length === 0) {
                html = '<p class="sm">No customers found matching your search.</p>';
            } else {
                customerArray.forEach(customer => {
                    const isChecked = selectedCustomerIds.includes(String(customer.customer_id));
                    html += `
                    <div class="customer-item">
                        <input type="checkbox" id="customer_${customer.customer_id}" 
                               name="customers[]" value="${customer.customer_id}" 
                               class="customer-checkbox" ${isChecked ? 'checked' : ''} />
                        <label for="customer_${customer.customer_id}">
                            ${customer.first_name} ${customer.last_name}
                        </label>
                    </div>
                `;
                });
            }
            customerList.innerHTML = html;
            attachCheckboxListeners();
        }

        // Attach event listeners to checkboxes
        function attachCheckboxListeners() {
            document.querySelectorAll('.customer-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        if (!selectedCustomerIds.includes(this.value)) {
                            selectedCustomerIds.push(this.value);
                        }
                    } else {
                        selectedCustomerIds = selectedCustomerIds.filter(id => id !== this.value);
                    }
                    updateSelectedCustomersDisplay();
                });
            });
        }

        // Update selected customers display
        function updateSelectedCustomersDisplay() {
            const selectedList = document.getElementById('selectedCustomersList');
            let html = '';
            if (selectedCustomerIds.length === 0) {
                html = '<p class="sm">No customers selected</p>';
            } else {
                selectedCustomerIds.forEach(id => {
                    const customer = customers.find(c => String(c.customer_id) === id);
                    if (customer) {
                        const name = `${customer.first_name} ${customer.last_name}`;
                        html += `<span class="selected-customer-tag btn btn-tn btn-outline">${name}</span>`;
                    }
                });
            }
            selectedList.innerHTML = html;
        }

        // Search functionality
        customerSearch.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase().trim();
            if (searchTerm.length < 2) {
                renderCustomerList(customers);
                return;
            }
            fetch(`registerFamily.php?search_customers=1&search_term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(filteredCustomers => {
                    renderCustomerList(filteredCustomers);
                })
                .catch(error => console.error('Error searching customers:', error));
        });

        // Check for existing family
        const familyNameInput = document.getElementById('family_name');
        const familyAddressInput = document.getElementById('family_address');

        function checkExistingFamily() {
            const familyName = familyNameInput.value.trim();
            const familyAddress = familyAddressInput.value.trim();
            if (familyName && familyAddress) {
                fetch(`registerFamily.php?check_family=1&family_name=${encodeURIComponent(familyName)}&family_address=${encodeURIComponent(familyAddress)}`)
                    .then(response => response.json())
                    .then(data => {
                        const existingMembersDiv = document.getElementById('existingMembers');
                        const existingMembersList = document.getElementById('existingMembersList');
                        if (data.exists && data.members.length > 0) {
                            let html = '';
                            data.members.forEach(member => {
                                html += `<span class="selected-customer-tag btn btn-tn btn-outline">${member.first_name} ${member.last_name}</span>`;
                                // Add to selectedCustomerIds if not already present
                                if (!selectedCustomerIds.includes(String(member.customer_id))) {
                                    selectedCustomerIds.push(String(member.customer_id));
                                    // Check the checkbox if it exists and trigger change event
                                    const checkbox = document.getElementById(`customer_${member.customer_id}`);
                                    if (checkbox) {
                                        checkbox.checked = true;
                                        checkbox.dispatchEvent(new Event('change'));
                                    }
                                }
                            });
                            existingMembersList.innerHTML = html;
                            existingMembersDiv.style.display = 'block';
                            updateSelectedCustomersDisplay();
                        } else {
                            existingMembersDiv.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Error checking existing family:', error));
            }
        }

        familyNameInput.addEventListener('blur', checkExistingFamily);
        familyAddressInput.addEventListener('blur', checkExistingFamily);

        // Initial setup
        renderCustomerList(customers);
        updateSelectedCustomersDisplay();

        // Form submission confirmation for empty family
        document.querySelector('.clientForm').addEventListener('submit', function(event) {
            if (selectedCustomerIds.length === 0) {
                const confirmEmpty = confirm("You are creating an empty family. Do you want to proceed?");
                if (!confirmEmpty) {
                    event.preventDefault();
                }
            }
        });
    </script>
</body>

</html>