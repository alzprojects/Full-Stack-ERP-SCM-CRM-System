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
        <h3>ERP Finances</h3>
            <div id ="smallContainer">
                <div id="leftContainer">
                    <h3>Data</h3>
                    <p>Enter a Product ID, and see a table with relevant data</p>
                    <form method="post" >
                        <label for="product_id">Enter Product ID (0 will Select All):</label>
                        <input type="number" id="product_id" name="product_id" required>
                        <button type="submit">Submit</button>
                    </form>
                    <p id="product_id_paragraph"></p>
                    <table id="artistTable"></table>
                </div>
                <div id="rightContainer">
                    <div id="topRightContainer">
                        <h3>Plots & Figures</h3>
                        <p id="chartDescription" style="display: none;">Purchase over Time (for specified productID)</p>
                        <canvas id="chartCanvas"></canvas>
                        <p>Purchases over Time (all Products) </p>
                        <canvas id="chartCanvas3"></canvas>
                        <p>Purchases by Day of Week:</p>
                        <canvas id="chartCanvas2"></canvas>
                        <p>Customer Satisfaction Distribution:</p>
                        <canvas id="chartCanvas4"></canvas>

                    </div>
                    <div id="bottomRightContainer">
                        <h3>Data Summary</h3>
                        <p><strong>Calculation 1:</strong> Average Revenue per Purchase</p>
                        <p id="avgP"></p>
                        <p><strong>Calculation 2:</strong> Most Popular Item </p>
                        <p id="mostP"></p>
                        <p><strong>Calculation 3:</strong> Total Number of Purchases Processed</p>
                        <p id="totalP"></p>
                        <p><strong>Calculation 4:</strong> Highest Performing Location</p>
                        <p id="highest_performing_location"></p>
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
$username = "azimbali";
$password = "Max!024902!!";
$database = "azimbali";

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
This function will calculate what the average purchase price 
is based on quantity of products multiplied by price of products. */

$averagePrice = getAveragePurchasePrice($conn);
echo "<script>document.getElementById('avgP').innerText = 'The average purchase price is: " . $averagePrice . "';</script>";

function getAveragePurchasePrice($conn) {
    // Construct the SQL query
    $sql = "SELECT AVG(p.price * pd.quantity) as average_price
            FROM product p
            JOIN purchaseDetail pd ON p.productID = pd.productID";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the average price
        return $row['average_price'];
    } else {
        // No result, return null
        return null;
    }
}
/*
This function calculates the date that has the most songs played. It actually
picks the specific time too, not just the day. Once the proper DB is linked, 
it will be the date with the most purchases. 
*/

$date = getDateWithMostPurchases($conn);
echo "<script>document.getElementById('mostP').innerText = 'The product with the most items purchased is: " . $date . "';</script>";

function getDateWithMostPurchases($conn) {
    // Construct the SQL query

    $sql = "SELECT productID, SUM(quantity) as total_quantity
        FROM purchaseDetail
        GROUP BY productID
        ORDER BY total_quantity DESC
        LIMIT 1";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the date
        return $row['productID'];
    } else {
        // No result, return null
        return null;
    }
}
$highest_performing_location = getHighestPerformingLocation($conn);
echo "<script>document.getElementById('highest_performing_location').innerText = 'The highest performing location was: " . $highest_performing_location['locationID'] . " with a revenue of: " . $highest_performing_location['total_revenue'] . "';</script>";
function getHighestPerformingLocation($conn) {
    // Construct the SQL query
    $sql = "SELECT SUM(product.price * purchaseDetail.quantity) as total_revenue, purchase.locationID
            FROM product
            JOIN purchaseDetail ON product.productID = purchaseDetail.productID
            JOIN purchase ON purchase.purchaseID = purchaseDetail.purchaseID
            GROUP BY purchase.locationID
            ORDER BY total_revenue DESC
            LIMIT 1";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the total revenue and location ID
        return $row;
    } else {
        // No result, return null
        return null;
    }
}


/*
This function is similar to the previous one, but calculates what the total
number of purchases placed all year was. Will be amended to be number of purchases
placed all year. 
*/

$totalPlayed = getTotalSongsPlayed($conn);
echo "<script>document.getElementById('totalP').innerText = 'The total number of purchases processed: " . $totalPlayed . "';</script>";

function getTotalSongsPlayed($conn) {
    // Construct the SQL query
    $sql = "SELECT COUNT(purchaseID) as total_purchases 
        FROM purchase";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the total number of songs played
        return $row['total_purchases'];
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

$product_id = 0; // Default value
echo "
<script>
document.getElementById('chartCanvas').style.display = 'none';
document.getElementById('chartDescription').style.display = 'none';
</script>
";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $product_id = intval(sanitize_input($_POST["product_id"]));
    echo "
    <script>
    document.getElementById('chartDescription').innerText = 'Quantity of Orders Delivered over Time (for productID: ' + $product_id + ')';
    document.getElementById('product_id_paragraph').innerText = 'The product ID is: ' + $product_id;
    if ($product_id != 0) {
        document.getElementById('chartCanvas').style.display = 'block';
        document.getElementById('chartDescription').style.display = 'block';

    }
    </script>
    ";
    // Check if the product ID exists in the database
    $sql = "SELECT COUNT(*) FROM product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0 && $product_id != 0) {
        // If the product ID does not exist in the database, display an error message
        echo "
        <script>
        alert('Error: The entered product ID does not exist in the database.');
        </script>
        ";
        exit; // Stop the execution of the script
    }
}
// Typing 0 works as a select all
if ($product_id == "0") {
    $sql = "SELECT `date`, COUNT(purchaseID) as purchase_count
            FROM purchase
            GROUP BY `date`
            ORDER BY `date`";   

    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT p.`date`, COUNT(p.purchaseID) as purchase_count
            FROM purchase p
            JOIN purchaseDetail pd ON p.purchaseID = pd.purchaseID
            WHERE pd.productID = ?
            GROUP BY p.`date`
            ORDER BY p.`date`";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
}
// Execute the statement
$stmt->execute();

// Bind the results
$stmt->bind_result($date, $purchase_count);


/* This table outputs time played vs number of songs
played during that time, which can be modified to be
date vs number of purchases made etc. Not currently
embedded in HTML. Also for graph 1 (that adjusts 
based on product_id) */
$dateArray = array();
$purchaseCount = array();

while ($stmt->fetch()) {
    $dateArray[] = $date;
    $purchaseCount[] = $purchase_count;
}

echo "
<script>
if ($product_id != 0) {
    var ctx = document.getElementById('chartCanvas').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: " . json_encode($dateArray) . ",
            datasets: [{
                label: 'Number of Purchases',
                data: " . json_encode($purchaseCount) . ",
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
</script>
";

/*
This is where we ge to the fun stuff! This is the function that lets the user
properly query and control the data being displayed. The catch is that it 
only affects the table in the "data" section right now. Not anything else. 
There is an option to select all with 0. Otherwise you choose an ArtistID, 
which will eventually be an EmployeeID or ProductID to view details about
the thing you want to see.  
*/

$product_id = 0; // Default value

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $product_id = intval(sanitize_input($_POST["product_id"]));
}
    
// Typing 0 works as a select all
if ($product_id == "0") {
    // Construct the SQL query without a WHERE clause
    $sql = "SELECT pd.purchaseID, pd.quantity, p.`date`, p.locationID, pd.productID
            FROM purchaseDetail pd
            JOIN purchase p ON pd.purchaseID = p.purchaseID";
    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
} else {
    // Convert the artist ID to an integer
    $product_id = intval($product_id);
    // Construct the SQL query with a placeholder for the artist ID
    $sql = "SELECT pd.purchaseID, pd.quantity, p.`date`, p.locationID, pd.productID
            FROM purchaseDetail pd
            JOIN purchase p ON pd.purchaseID = p.purchaseID
            WHERE pd.productID = ?";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    // Bind the artist ID parameter
    if ($stmt) {
        $stmt->bind_param("i", $product_id);
    }
}
    // Execute the statement
    $stmt->execute();

    // Bind the results
    $stmt->bind_result($purchaseID, $quantity, $date, $locationID, $productID);
if ($product_id == 0) {
// Fetch and display the data in a table row
$table = "<table id='artistTable'>";
$table .= "<tr><th>ProductID</th><th>PurchaseID</th><th>Item Quantity</th><th>Date</th><th>Location</th></tr>";
while ($stmt->fetch()) {
    $table .= "<tr>";
    $table .= "<td>" . $productID . "</td>";
    $table .= "<td>" . $purchaseID . "</td>";
    $table .= "<td>" . $quantity . "</td>";
    $table .= "<td>" . $date . "</td>";
    $table .= "<td>" . $locationID . "</td>";
    $table .= "</tr>";
}
$table .= "</table>";
echo "<script>document.getElementById('artistTable').innerHTML = `" . $table . "`;</script>";
}
else{
    // Fetch and display the data in a table row
    $table = "<table id='artistTable'>";
    $table .= "<tr><th>PurchaseID</th><th>Item Quantity</th><th>Date</th><th>Location</th></tr>";
    while ($stmt->fetch()) {
        $table .= "<tr>";
        $table .= "<td>" . $purchaseID . "</td>";
        $table .= "<td>" . $quantity . "</td>";
        $table .= "<td>" . $date . "</td>";
        $table .= "<td>" . $locationID . "</td>";
        $table .= "</tr>";
    }
    $table .= "</table>";
    echo "<script>document.getElementById('artistTable').innerHTML = `" . $table . "`;</script>";
}
    
    

/*
This query and chart is for the "purchases per date" graph, and is formulated
to display a line graph of how purchases fluctuate with time. X-axis is 
date and y-axis is number of plays, which is going to be number of purchases.
*/

$sql = " SELECT DATE(`date`) AS purchase_date, COUNT( purchaseID ) AS purchase_count
FROM purchase
GROUP BY DATE(`date`)
ORDER BY purchase_date";

$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
echo "<script>document.getElementById('testTable').innerHTML = `";
echo '<table>';
echo '<tr><th>Purchase Date</th><th>Purchase IDs</th></tr>';
$purchase_ids = array();
$purchase_counts = array();
while ($row = $result->fetch_assoc()) {
    echo '<tr><td>' . $row['purchase_date'] . '</td><td>' . $row['purchase_count'] . '</td></tr>';
    $purchase_ids[] = $row['purchase_date'];
    $purchase_counts[] = $row['purchase_count'];
}

echo '</table>';
echo "`;</script>";

echo "<script>var purchaseIds = " . json_encode($purchase_ids) . ";</script>";
echo "<script>var purchaseCounts = " . json_encode($purchase_counts) . ";</script>";


echo '

<script>
    var ctx = document.getElementById("chartCanvas3").getContext("2d");
    var chart = new Chart(ctx, {
        type: "line",
        data: {
            labels: purchaseIds, // Use trackIds as labels (x-axis)
            datasets: [{
                label: "Number of Purchases",
                data: purchaseCounts, // Use playCounts as data (y-axis)
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                borderColor: "rgba(75, 192, 192, 1)",
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
';

/*
This function does something very similar to the previous one, but splits
up the data by day of the week instead of just the date. Somewhere in the 
first 10 lines sorcery was performed to make the code work. Don't know how
or why it worked. Hasn't been tested much. Also makes a bar chart instead
of a line chart. 
*/

// First loop: Convert dates to days of the week
$result->data_seek(0); // Reset result pointer to the beginning
$purchase_ids = array();
$purchase_counts = array('Sunday' => 0, 'Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0, 'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0);

while ($row = $result->fetch_assoc()) {
    $date = $row['purchase_date'];
    $dayOfWeek = date('l', strtotime($date));
    $purchase_counts[$dayOfWeek] += $row['purchase_count']; // Increment the count for the corresponding day of the week
}

// Pass the PHP arrays to JavaScript
echo "<script>var purchaseIds = " . json_encode($purchase_ids) . ";</script>";
echo "<script>var purchaseCounts = " . json_encode($purchase_counts) . ";</script>";

// Generate the chart
echo '
<script>
    var ctx = document.getElementById("chartCanvas2").getContext("2d");
    var chart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: Object.keys(purchaseCounts), // Use the days of the week as labels (x-axis)
            datasets: [{
                label: "Number of Purchases",
                data: Object.values(purchaseCounts), // Use the purchase counts as data (y-axis)
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                borderColor: "rgba(75, 192, 192, 1)",
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
';

function getSatisfactionRatings($conn) {
    // Construct the SQL query
    $sql = "SELECT satisfactionRating, COUNT(*) as count FROM purchase GROUP BY satisfactionRating";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch all rows from the result
        $ratings = [];
        while ($row = $result->fetch_assoc()) {
            $ratings[$row['satisfactionRating']] = $row['count'];
        }
        // Return the ratings
        return $ratings;
    } else {
        // No result, return null
        return null;
    }
}

$ratings = getSatisfactionRatings($conn);

echo '
<script>
    var ctx = document.getElementById("chartCanvas4").getContext("2d");
    var chart = new Chart(ctx, {
        type: "pie",
        data: {
            labels: Object.keys(' . json_encode($ratings) . '), // Use the satisfaction ratings as labels
            datasets: [{
                data: Object.values(' . json_encode($ratings) . '), // Use the counts as data
                backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#FF9F40"]
            }]
        }
    });
</script>
';

// Close the connection (REMEMBER TO DO THIS!)
$conn->close();
?>

