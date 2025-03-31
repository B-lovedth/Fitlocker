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

        // Basic validation
        if (empty($family_name)) {
            throw new Exception("Family name is required.");
        }
        if (empty($family_address)) {
            throw new Exception("Family address is required.");
        }

        // Check if family already exists
        $stmt = $conn->prepare("SELECT family_id FROM families WHERE family_name = ? AND family_address = ? AND user_id = ?");
        $stmt->bind_param("ssi", $family_name, $family_address, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $family_id = $result->fetch_assoc()['family_id'];
        } else {
            // Create new family
            $stmt = $conn->prepare("INSERT INTO families (family_name, family_address, user_id, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("ssi", $family_name, $family_address, $user_id);
            $stmt->execute();
            $family_id = $stmt->insert_id;
        }
        $stmt->close();

        // First, clear any existing family associations for the selected customers
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
    } catch (Exception $e) {
        error_log("Family Registration Error: " . $e->getMessage());
        $_SESSION['registration_status'] = 'failure';
        $_SESSION['error_message'] = $e->getMessage();
    }
    header("Location: registerFamily.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register Family</title>
    <link rel="stylesheet" href="./Styles/main.css?v=1.0" />
    <link rel="stylesheet" href="./Styles/sidebar.css?v=1.0" />
    <link rel="stylesheet" href="./Styles/menus.css?v=1.0">
    <link rel="stylesheet" href="./Styles/register.css?v=1.0" />
    <style>
        .customer-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .customer-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        
        .customer-item:last-child {
            border-bottom: none;
        }
        
        .customer-item label {
            margin-left: 10px;
            cursor: pointer;
        }
        
        .search-box {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        .selected-customers {
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .selected-customers h4 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .selected-customer-tag {
            display: inline-block;
            background-color: #e0e0e0;
            padding: 5px 10px;
            margin: 3px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .existing-members {
            margin-top: 10px;
            padding: 10px;
            background-color: #f0f8ff;
            border: 1px solid #b0c4de;
            border-radius: 4px;
            display: none;
        }
        
        .existing-members h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #4682b4;
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
                <div class="personal panel">
                    <h3>Family Details</h3>
                    <hr>
                    <div class="fields slim">
                        <div class="field">
                            <label for="family_name">Family Name</label>
                            <input type="text" id="family_name" name="family_name" required />
                        </div>
                        <div class="field">
                            <label for="family_address">Family Address</label>
                            <input type="text" id="family_address" name="family_address" required />
                        </div>
                        <div class="existing-members" id="existingMembers">
                            <h4>Existing Family Members</h4>
                            <div id="existingMembersList"></div>
                        </div>
                    </div>
                </div>
                <div class="measurement panel">
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
        // Helper functions for modals
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function registerAgain() {
            hideModal('successModal');
            document.querySelector('.clientForm').reset();
            document.getElementById('selectedCustomersList').innerHTML = '';
            document.getElementById('existingMembers').style.display = 'none';
            
            // Uncheck all checkboxes
            document.querySelectorAll('.customer-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            updateSelectedCustomers();
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
                document.getElementById('errorModalMessage').textContent = <?php echo json_encode($_SESSION['error_message'] ?? 'An error occurred during family registration.'); ?>;
                showModal('errorModal');
            <?php endif; ?>
            <?php unset($_SESSION['registration_status'], $_SESSION['error_message'], $_SESSION['is_empty_family']); ?>
        <?php endif; ?>

        // Customer search functionality
        const customerSearch = document.getElementById('customerSearch');
        const customerList = document.getElementById('customerList');
        const originalCustomerList = customerList.innerHTML;
        
        // Store customer data for client-side filtering
        const customers = [
            <?php foreach ($customers as $customer): ?>
            ,{
                id: <?php echo $customer['customer_id']; ?>,
                name: "<?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>"
            },
            <?php endforeach; ?>
        ];
        
        // Handle search box input - client-side implementation
        customerSearch.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm.length < 2) {
                // If search term is too short, restore original list
                customerList.innerHTML = originalCustomerList;
                return;
            }
            
            // Send request for server-side search
            fetch(`registerFamily.php?search_customers=1&search_term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(filteredCustomers => {
                    let html = '';
                    
                    filteredCustomers.forEach(customer => {
                        // Preserve checked state from the current checkboxes
                        const isChecked = document.getElementById(`customer_${customer.customer_id}`)?.checked || false;
                        
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
                    
                    if (filteredCustomers.length === 0) {
                        html = '<p>No customers found matching your search.</p>';
                    }
                    
                    customerList.innerHTML = html;
                    
                    // Re-attach event listeners to checkboxes
                    document.querySelectorAll('.customer-checkbox').forEach(checkbox => {
                        checkbox.addEventListener('change', updateSelectedCustomers);
                    });
                })
                .catch(error => {
                    console.error('Error searching customers:', error);
                });
        });
        
        // Function to update the selected customers display
        function updateSelectedCustomers() {
            const selectedList = document.getElementById('selectedCustomersList');
            const selectedCheckboxes = document.querySelectorAll('.customer-checkbox:checked');
            
            let html = '';
            
            if (selectedCheckboxes.length === 0) {
                html = '<p>No customers selected</p>';
            } else {
                selectedCheckboxes.forEach(checkbox => {
                    const label = checkbox.nextElementSibling.textContent.trim();
                    html += `<span class="selected-customer-tag">${label}</span>`;
                });
            }
            
            selectedList.innerHTML = html;
        }
        
        // Add change event listeners to all checkboxes
        document.querySelectorAll('.customer-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCustomers);
        });
        
        // Initialize selected customers display
        updateSelectedCustomers();
        
        // Add empty family confirmation
        document.querySelector('.clientForm').addEventListener('submit', function(event) {
            const selectedCheckboxes = document.querySelectorAll('.customer-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                const confirmEmpty = confirm("You are creating an empty family. Do you want to proceed?");
                if (!confirmEmpty) {
                    event.preventDefault(); // Stop form submission if user cancels
                }
            }
        });
        
        // Check for existing family when name and address are both filled
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
                                html += `<span class="selected-customer-tag">${member.first_name} ${member.last_name}</span>`;
                                
                                // Auto-check the corresponding checkbox
                                const checkbox = document.getElementById(`customer_${member.customer_id}`);
                                if (checkbox) {
                                    checkbox.checked = true;
                                }
                            });
                            
                            existingMembersList.innerHTML = html;
                            existingMembersDiv.style.display = 'block';
                            
                            // Update the selected customers display
                            updateSelectedCustomers();
                        } else {
                            existingMembersDiv.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error checking existing family:', error);
                    });
            }
        }
        
        familyNameInput.addEventListener('blur', checkExistingFamily);
        familyAddressInput.addEventListener('blur', checkExistingFamily);
    </script>
</body>
</html>