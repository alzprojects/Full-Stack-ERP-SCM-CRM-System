<?php
/* 
Notes from Sarah!
This is how all functions will be commented.
This section here is all of the basic HTML structure that allows the contents
to be split into different containers and for everything to flow nicely.
This is also where all of the different functions are called and manipulated.
*/
echo <<<EOT
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Micromanagement Central Yeehaw</title>
    <link rel="stylesheet" href="SCM_Style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
    <div class="container">
        <h2>Micromanagement Central Yeehaw</h2>
        <div class="navbar">
            <a href="homePage.html">Home</a>
            <a href="login.html">Login</a>
            <a href="ERP_Inventory.php">Inventory</a>
            <a href="ERP_Finances.php">Finances</a>
            <a href="ERP_HR.php">HR</a>   
        </div>
        <h3>ERP Human Resources</h3>
            <div id ="smallContainer">
                <div id="leftContainer">
                    <h3>Data</h3>
                    <p>Enter a user ID, and see a table with their locationID, start date,
                    and if they're an employee, supplier, or customer (but just employees rn
                    so other userIDs wont pull anything up)</p>
                    <form method="post" >
                        <label for="product_id">Enter User ID (0 will Select All):</label>
                        <input type="number" id="product_id" name="product_id" required>
                        <button type="submit">Submit</button>
                    </form>
                    <p id="product_id_paragraph"></p>
                    <p id="user_type_paragraph"></p>
                    <table id="artistTable"></table>
                </div>
                <div id="rightContainer">
                    <div id="topRightContainer">
                        <h3>Plots & Figures</h3>
                        <p>Distribution of User Types</p>
                        <canvas id="chartCanvas"></canvas>
                        <p>Distribution of Employees per Location </p>
                        <canvas id="chartCanvas2"></canvas>
                    </div>
                    <div id="bottomRightContainer">
                        <h3>Data Summary</h3>
                        <p><strong>Calculation 1:</strong> Most Recent Hire Date</p>
                        <p id="recentStartDate"></p>
                        <p><strong>Calculation 2:</strong> Location with Most Employees</p>
                        <p id="mostUsers"></p>
                        <p><strong>Calculation 3:</strong> Total Number of Employees</p>
                        <p id="totalU"></p>
                    </div>
                </div>
            </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/split.js"></script>
            <!-- <script src="SCM_Script.js"></script> -->    
</body>
EOT;



/*
These next 2 echo statements are to call the JS script library, and then to
create the function that allows for the containers to be resizable. 
The separation is between left and right, and then top and bottom on the
right side. 
*/
echo "<script src='https://cdn.jsdelivr.net/npm/split.js'></script>";

echo '
<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM is loaded");

    Split(["#leftContainer", "#rightContainer"], {
        sizes: [50, 50],
        minSize: 100,
        gutterSize: 3,
        cursor: "col-resize",
        gutter: function(index, direction) {
            const gutter = document.createElement("div");
            gutter.style.backgroundColor = "#181651";
            return gutter;
        }
    });

    Split(["#topRightContainer", "#bottomRightContainer"], {
        direction: "vertical",
        sizes: [50, 50],
        minSize: 100,
        gutterSize: 3,
        cursor: "row-resize",
        gutter: function(index, direction) {
            const gutter = document.createElement("div");
            gutter.style.backgroundColor = "#181651";
            return gutter;
        }
    });
});
</script>
';
/* 
This is where the database setup happens, it is currently connected to my 
Purdue database where the music database from lab10 is still loaded. 
*/

$servername = "mydb.itap.purdue.edu";
$username = "g1135081";
$password = "4i1]4S*Mns83";
$database = "g1135081";

// Create connection (ONLY NEEDED ONCE per PHP page!)
$conn = new mysqli($servername, $username, $password);

// Check connection was successful, otherwise immediately exit the script
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    $conn->close();
}

// Select the specific database
if (!$conn->select_db($database)) {
    die("Database selection failed: " . $conn->error);
}

/*
This is where the first query happens. This is directly code from lab.
This query isn't actually being used right now. Its purpose is to highlight
the cells whose time is less than avg in red. We can repurpose. I kept it
because it is connected to other functions but will likely be eventually
deleted. 
*/


/*
This function will calculate what the average purchase price 
is based on quantity of products multiplied by price of products. */

$mostRecentStartDate = getMostRecentStartDate($conn);
echo "<script>document.getElementById('recentStartDate').innerText = 'The most recent start date is: " . $mostRecentStartDate . "';</script>";

function getMostRecentStartDate($conn) {
    // Construct the SQL query
    $sql = "SELECT MAX(start_date) as most_recent_start_date
            FROM users";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the most recent start date
        return $row['most_recent_start_date'];
    } else {
        // No result, return null
        return null;
    }
}

/*
func to calc 
*/

$locationWithMostUsers = getLocationWithMostUsers($conn);
echo "<script>document.getElementById('mostUsers').innerText = 'The location with the most employees is: " . $locationWithMostUsers . "';</script>";

function getLocationWithMostUsers($conn) {
    // Construct the SQL query
    $sql = "SELECT locationID, COUNT(userID) as user_count
            FROM employees
            WHERE locationID IS NOT NULL
            GROUP BY locationID
            ORDER BY user_count DESC
            LIMIT 1";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the locationID
        return $row['locationID'];
    } else {
        // No result, return null
        return null;
    }
}

/*
func to calc total number of users in users table
*/

$totalUsers = getTotalUsers($conn);
echo "<script>document.getElementById('totalU').innerText = 'The total number of employees: " . $totalUsers . "';</script>";

function getTotalUsers($conn) {
    // Construct the SQL query
    $sql = "SELECT COUNT(userID) as total_users 
            FROM users
            WHERE user_type = 'employee'";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the total number of users
        return $row['total_users'];
    } else {
        // No result, return 0
        return 0;
    }
}
/*
When the user chooses a specific productID, it shows the history of
purchases over time for that specific product
 */

function sanitize_input($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}


/*
This is where we ge to the fun stuff! This is the function that lets the user
properly query and control the data being displayed. The catch is that it 
only affects the table in the "data" section right now. Not anything else. 
There is an option to select all with 0. Otherwise you choose an ArtistID, 
which will eventually be an EmployeeID or ProductID to view details about
the thing you want to see.  
*/

$user_id = 0; // Default value

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $user_id = intval(sanitize_input($_POST["product_id"]));
    echo "
    <script>
    document.getElementById('product_id_paragraph').innerText = 'The selected user ID is: ' + $user_id;
    </script>
    ";
    // Check if the user ID exists in the database
    $sql = "SELECT COUNT(*) FROM users WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0 && $user_id != 0) {
        // If the user ID does not exist in the database, throw an error
        echo "
        <script>
        alert('Error: The entered user ID does not exist in the database.');
        </script>
        ";
        exit;
        }
    if ($user_id == "0") {
    // Construct the SQL query without a WHERE clause
    $sql = "SELECT users.userID, users.start_date, users.end_date, users.user_type 
            FROM users 
            ";
    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    // Execute the statement
    $stmt->execute();
    // Bind the results
    $stmt->bind_result($user_id, $start_date, $end_date, $user_type);
    // Fetch and display the data in a table row
    $table = "<table id='artistTable'>";
    $table .= "<tr><th>User ID</th><th>Start Date</th><th>End Date</th><th>User Type</th></tr>";
    while ($stmt->fetch()) {
        $table .= "<tr>";
        $table .= "<td>" . $user_id. "</td>";
        $table .= "<td>" . $start_date . "</td>";
        $table .= "<td>" . $end_date . "</td>";
        $table .= "<td>" . $user_type . "</td>";
        $table .= "</tr>";
    }
    $table .= "</table>";
    echo "<script>document.getElementById('artistTable').innerHTML = `" . $table . "`;</script>";
        
    } else {
    
    // Call the getUserData function
    $stmt = getUserData($conn, $user_id);
    
    }
}
function getUserData($conn, $user_id) {
    // Get the user type
    $sql = "SELECT user_type FROM users WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_type);
    $stmt->fetch();
    $stmt->close();
    $user_id = intval($user_id);
    
    // Based on the user type, prepare a different SQL query
    if ($user_type == 'supplier') {
        $sql = "SELECT users.userID, users.start_date, users.end_date, users.username, users.password, supplier.supplierID, supplier.name, supplier.address
                FROM users
                JOIN enumSupplier ON users.userID = enumSupplier.userID
                JOIN supplier ON enumSupplier.supplierID = supplier.supplierID
                WHERE users.userID = ?";
    } elseif ($user_type == 'customer') {
        $sql = "SELECT users.userID, users.start_date, users.end_date, users.username, users.password, customers.customerID, customers.fname,customers.lname, customers.gender
                FROM users
                JOIN enumCustomer ON users.userID = enumCustomer.userID
                JOIN customers ON enumCustomer.customerID = customers.customerID
                WHERE users.userID = ?";
    } elseif ($user_type == 'employee') {
        $sql = "SELECT users.userID, users.start_date, users.end_date, users.username, users.password, employees.locationID
                FROM users  
                JOIN employees ON users.userID = employees.userID 
                WHERE users.userID = ?";
    } else {
        // If the user type is not recognized, return null
        return null;
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    // Bind the user ID parameter
    $stmt->bind_param("i", $user_id);
    // Execute the statement
    $stmt->execute();

    // Bind the results
    if ($user_type == 'supplier') {
        echo "
        <script>
        document.getElementById('user_type_paragraph').innerText = 'The selected user type is: ' + '$user_type';
        </script>
        ";
        $stmt->bind_result($user_id, $start_date, $end_date, $username, $password, $supplierID, $name, $address);        $table = "<table id='artistTable'>";
        $table = "<table id='artistTable'>";
        $table .= "<tr><th>Supplier ID</th><th>Supplier Name</th><th>Address</th><th>Username</th><th>Password</th><th>Start Date</th><th>End Date</th></tr>";
        while ($stmt->fetch()) {
            $table .= "<tr>";
            $table .= "<td>" . $supplierID . "</td>";
            $table .= "<td>" . $name . "</td>";
            $table .= "<td>" . $address . "</td>";
            $table .= "<td>" . $username . "</td>";
            $table .= "<td>" . $password . "</td>";
            $table .= "<td>" . $start_date . "</td>";
            $table .= "<td>" . $end_date . "</td>";
            $table .= "</tr>";
        }
        $table .= "</table>";
        echo "<script>document.getElementById('artistTable').innerHTML = `" . $table . "`;</script>";
    } elseif ($user_type == 'customer') {
        echo "
        <script>
        document.getElementById('user_type_paragraph').innerText = 'The selected user type is: ' + '$user_type';
        </script>
        ";
        $stmt->bind_result($user_id, $start_date, $end_date, $username, $password, $customerID, $fname, $lname, $gender);        $table = "<table id='artistTable'>";
        $table = "<table id='artistTable'>";
        $table .= "<tr><th>Customer ID</th><th>Customer Name</th><th>Gender</th><th>Username</th><th>Password</th><th>Start Date</th><th>End Date</th></tr>";
        while ($stmt->fetch()) {
            $table .= "<tr>";
            $table .= "<td>" . $customerID . "</td>";
            $table .= "<td>" . $name . "</td>";
            $table .= "<td>" . $gender . "</td>";
            $table .= "<td>" . $username . "</td>";
            $table .= "<td>" . $password . "</td>";
            $table .= "<td>" . $start_date . "</td>";
            $table .= "<td>" . $end_date . "</td>";
            $table .= "</tr>";
        }
        $table .= "</table>";
        echo "<script>document.getElementById('artistTable').innerHTML = `" . $table . "`;</script>";
    } elseif ($user_type == 'employee') {
        echo "
        <script>
        document.getElementById('user_type_paragraph').innerText = 'The selected user type is: ' + '$user_type';
        </script>
        ";
        $stmt->bind_result($user_id, $start_date, $end_date, $username, $password, $locationID);
        $table = "<table id='artistTable'>";
        $table .= "<tr><th>Location ID</th><th>Username</th><th>Password</th><th>Start Date</th><th>End Date</th></tr>";
        while ($stmt->fetch()) {
            $table .= "<tr>";
            $table .= "<td>" . $locationID . "</td>";
            $table .= "<td>" . $username . "</td>";
            $table .= "<td>" . $password . "</td>";
            $table .= "<td>" . $start_date . "</td>";
            $table .= "<td>" . $end_date . "</td>";
            $table .= "</tr>";
        }
        $table .= "</table>";
        echo "<script>document.getElementById('artistTable').innerHTML = `" . $table . "`;</script>";

    }

}

$sql = "SELECT user_type, COUNT(userID) as user_count 
        FROM users 
        GROUP BY user_type";

$stmt = $conn->prepare($sql);

// Execute the statement
$stmt->execute();

// Bind the results
$stmt->bind_result($user_type, $user_count);

// Initialize arrays to store the data
$userTypeArray = [];
$userCountArray = [];

// Fetch and store the data in arrays
while ($stmt->fetch()) {
    $userTypeArray[] = $user_type;
    $userCountArray[] = $user_count;
}

// Convert the arrays to JSON
$userTypeJson = json_encode($userTypeArray);
$userCountJson = json_encode($userCountArray);

// Output the script to create the pie chart
echo "
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<script>
var ctx = document.getElementById('chartCanvas').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: $userTypeJson,
        datasets: [{
            data: $userCountJson,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    }
});
</script>
";

$sql = "SELECT locationID, COUNT(userID) as user_count 
        FROM employees 
        GROUP BY locationID";

$stmt = $conn->prepare($sql);

// Execute the statement
$stmt->execute();

// Bind the results
$stmt->bind_result($locationID, $user_count);

// Initialize arrays to store the data
$locationArray = [];
$userCountArray = [];

// Fetch and store the data in arrays
while ($stmt->fetch()) {
    $locationArray[] = $locationID;
    $userCountArray[] = $user_count;
}

// Convert the arrays to JSON
$locationJson = json_encode($locationArray);
$userCountJson = json_encode($userCountArray);

// Output the script to create the pie chart
echo "
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<script>
var ctx = document.getElementById('chartCanvas2').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: $locationJson,
        datasets: [{
            data: $userCountJson,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    }
});
</script>
";

// Close the connection (REMEMBER TO DO THIS!)
$conn->close();
?>

