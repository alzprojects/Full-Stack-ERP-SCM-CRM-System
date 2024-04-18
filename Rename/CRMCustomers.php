<?php
// Check if this is an AJAX request for user data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch_users') {
    $servername = "mydb.itap.purdue.edu";
    $username = "azimbali";
    $password = "Max!024902!!";
    $database = "azimbali";

    // It's recommended to move database credentials to a secure location
    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed']));
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] == 'fetch_users') {
            $userDetails = getAllCustomerIDs($conn);
            header('Content-Type: application/json');
            echo json_encode($userDetails);
            $conn->close();
            exit;
        } elseif ($_POST['action'] == 'fetch_customer_details' && isset($_POST['customerID'])) {
            $customerID = $conn->real_escape_string($_POST['customerID']); // Prevent SQL Injection
            $purchaseDetails = getCustomerPurchaseDetails($conn, $customerID);
            header('Content-Type: application/json');
            echo json_encode($purchaseDetails);
            $conn->close();
            exit;
        }
    }
    
    function getAllCustomerIDs($conn) {
        $sql = "SELECT * FROM customers";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            die(json_encode(['error' => 'Query execution failed']));
        }
        $userDetails = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $userDetails[] = array(
                'customerID' => $row['customerID'],
                'gender' => $row['gender'],
                'name' => $row['name']
            );
        }
        return $userDetails;
    }
    
    function getCustomerPurchaseDetails($conn, $customerID) {
        $sql = "SELECT * FROM purchase WHERE customerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $customerID);
        $stmt->execute();
        $result = $stmt->get_result();
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
        return $details;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles2.css">
    <title>Fetch User Data</title>
</head>

<body>
    <button id="loadDataBtn">Load Data</button>
    <div>
        <select id="customerSelect">
            <option value="">Select a Customer</option>
        </select>
        <button id="loadCustomerDataBtn">Load Customer Data</button>
    </div>
    <div id="dataDisplay"></div>

<script>
    let allUserData = [];  // This will store all the user data
    document.getElementById('loadDataBtn').addEventListener('click', function() {
        if (allUserData.length === 0) {  // Fetch only if data has not been loaded
            fetchData();
        } else {
            displayData(allUserData);  // Display all data if already loaded
        }
    });

    function fetchData() {
        fetch('CRMCustomers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetch_users'
        })
        .then(response => response.json())
        .then(data => {
            console.log('Fetched data:', data)
            allUserData = data;  // Store fetched data
            displayData(allUserData);  // Display all data
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('dataDisplay').innerHTML = '<strong>Failed to load data. Please try again.</strong>';
        });
    }

    function displayData(data, filterType) {
        const display = document.getElementById('dataDisplay');
        display.innerHTML = '';  // Clear previous data

        const table = document.createElement('table');
        table.style.width = '100%';
        table.setAttribute('border', '1');

        const headerRow = table.insertRow();
        let headers = Object.keys(data[0]);

        // Decide which headers to display
        if (filterType === 'userType') {
            headers = ['user_type'];  // Only display 'user_type' when filtering by type
        }

        // Create header cells
        headers.forEach(headerText => {
            let headerCell = document.createElement('th');
            headerCell.textContent = headerText;
            headerRow.appendChild(headerCell);
        });

        // Populate table rows with data
        data.forEach(item => {
            const row = table.insertRow();
            headers.forEach(header => {
                let cell = row.insertCell();
                cell.textContent = item[header];
            });
        });

        // Append the table to the display element
        display.appendChild(table);
    }
    document.getElementById('loadDataBtn').addEventListener('click', function() {
    fetchData();
});

    document.getElementById('loadCustomerDataBtn').addEventListener('click', function() {
        var selectedCustomerId = document.getElementById('customerSelect').value;
        if (selectedCustomerId) {
            fetchCustomerData(selectedCustomerId);
        }
    });

    function populateDropdown(data) {
        const select = document.getElementById('customerSelect');
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.customerID;
            option.textContent = item.customerID;
            select.appendChild(option);
        });
    }

    function fetchCustomerData(customerId) {
        fetch('CRMCustomers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=fetch_customer_details&customerID=${customerId}`
        })
        .then(response => response.json())
        .then(data => {
            console.log('Fetched customer data:', data);
            displayData([data]);  // Assuming you want to display this data in the same way
        })
        .catch(error => {
            console.error('Error fetching customer data:', error);
            document.getElementById('dataDisplay').innerHTML = '<strong>Failed to load customer details. Please try again.</strong>';
        });
    }

</script>
</body>
</html>

