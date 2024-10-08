<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['userID']) && $_POST['action'] == 'fetch_user_access') {
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $userID = intval($_POST['userID']);
    $stmt = $conn->prepare("SELECT * FROM employees WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No user found']);
    }
    $stmt->close();
    $conn->close();
    exit;
}
// Check if this is an AJAX request for user data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch_users') {
    // Database connection settings
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Function to get all user IDs
    function getAllUserIDs($conn) {
        $sql = "SELECT userID, start_date, user_type FROM users";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            die("Error executing query: " . mysqli_error($conn));
        }
        $userDetails = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $userDetails[] = array(
                'userID' => $row['userID'],
                'start_date' => $row['start_date'],
                'user_type' => $row['user_type']
            );
        }
        return $userDetails;
    }

    // Call the function and return data
    $userDetails = getAllUserIDs($conn);
    header('Content-Type: application/json');
    echo json_encode($userDetails);
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Micromanagement Central Yeehaw</title>
    <link rel="stylesheet" href="SCM_Style.css">
</head>

<body>
    <div class="container">
        <h2>Micromanagement Central Yeehaw</h2>
            <div class="navbar">
                <a href="homePage.html">Home</a>
                <a href="login.html">Login</a>
                <a href="CRMUsers.php">Users</a>
                <a href="CRMCustomers.php">Customers</a>
                <a href="CRMSuppliers.php">Suppliers</a>
            </div>
        <div id ="smallContainer">
        <div id ="leftContainer">
        Choose the load data button to pull in all relevant user data from database!
        <br></br>
        <button id="loadDataBtn">Load Data</button>
        <br></br>
        <div>
            Filter users by type (employee, customer, supplier)
            <br></br>
            <label for="userTypeSelect">Filter by User Type:</label>
            <select id="userTypeSelect" onchange="filterData()">
                <option value="">All Types</option>
                <option value="employee">Employee</option>
                <option value="customer">Customer</option>
                <option value="supplier">Supplier</option>
            </select>
        </div>
        <br></br>

        Choose a date range to filter users by start date (optional)
        <br></br>
        <div>
            <label for="startDateFrom">Start Date From:</label>
            <input type="date" id="startDateFrom" onchange="filterData()">
            <label for="startDateTo">to</label>
            <input type="date" id="startDateTo" onchange="filterData()">
        </div>
        <br></br>
        <div id="dataDisplay"></div>
        </div>
    </div>
    </div>
<script>
    let allUserData = [];  // This will store all the user data
    document.getElementById('loadDataBtn').addEventListener('click', function() {
        if (allUserData.length === 0) {  // Fetch only if data has not been loaded
            fetchData();
        } else {
            displayData(allUserData);  // Display all data if already loaded
        }
    });

    function fetchUserAccess() {
        let userID = <?php echo isset($_SESSION['userID']) ? $_SESSION['userID'] : 0; ?>;
        console.log('Fetching user access for user ID:', userID);
        fetch('CRMUsers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=fetch_user_access&userID=${userID}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data || data.error || data.userID != userID || data.CRMAccess != 1) {
                alert('You do not have access to view this page.');
                window.location.href = 'homepage.html';
            }
        })
        .catch(error => {
            console.error('Error fetching user data:', error);
            alert('Error fetching user data. You do not have access to view this page.');
            window.location.href = 'homepage.html';
        });
    }

    function fetchData() {
        fetch('CRMUsers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetch_users'
        })
        .then(response => response.json())
        .then(data => {
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


    function filterData() {
        const userType = document.getElementById('userTypeSelect').value;
        const startDateFrom = document.getElementById('startDateFrom').value;
        const startDateTo = document.getElementById('startDateTo').value;

        let filteredData = allUserData.filter(user => {
            // Convert user's start_date from string to Date object
            const userDate = new Date(user.start_date);

            // Create Date objects from the input fields, default to extreme values if empty
            const from = startDateFrom ? new Date(startDateFrom) : new Date(-8640000000000000); // Very early date if no start date is provided
            const to = startDateTo ? new Date(startDateTo) : new Date(8640000000000000); // Very distant future date if no end date is provided

            // Check the user type and date range
            return (!userType || user.user_type === userType) && // Filter by user type if selected
                (!startDateFrom || userDate >= from) && // Filter by start date if provided
                (!startDateTo || userDate <= to); // Filter by end date if provided
        });

        displayData(filteredData);
    }
    fetchUserAccess();
</script>
</body>
</html>
