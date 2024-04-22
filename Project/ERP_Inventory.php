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
        <h3>ERP Inventory</h3>
            <div id ="smallContainer">
                <div id="leftContainer">
                    <h3>Data</h3>
                    <p>Enter a product ID, and see a table with the quantity, location,
                    inventory detail ID, order ID, and delivery date </p>
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
                        <p id="chartDescriptionInventoryInventory" style="display: none;">Quantity of Orders Delivered over Time (per Product)</p>
                        <canvas id="chartCanvas4"></canvas>
                        <p>Quantity of Orders Delivered over Time (all Products): </p>
                        <canvas id="chartCanvas3"></canvas>
                        <p>Distribution of Delivery Locations:</p>
                        <canvas id="chartCanvas2"></canvas>
                        
                    </div>
                    <div id="bottomRightContainer">
                        <h3>Data Summary</h3>
                        <p><strong>Calculation 1:</strong> Average, Min, Max Order Cost</p>
                        <p id="orderStats"></p>
                        <p><strong>Calculation 2:</strong> Total Number of Orders Processed</p>
                        <p id="totalP"></p>
                        <p><strong>Calculation 3:</strong> Total Number of Products Sold</p>
                        <p id="totalQ"></p>
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
This function will calculate what the average purchase price 
is based on quantity of products multiplied by price of products. */

$orderStats = getOrderStats($conn);
echo "<script>document.getElementById('orderStats').innerText = 'The average order cost is: " . $orderStats['average_cost'] . ", the minimum order cost is: " . $orderStats['min_cost'] . ", and the maximum order cost is: " . $orderStats['max_cost'] . "';</script>";

function getOrderStats($conn) {
    // Construct the SQL query
    $sql = "SELECT AVG(orderCost) as average_cost, MIN(orderCost) as min_cost, MAX(orderCost) as max_cost
            FROM `order`";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the average, min, and max cost
        return $row;
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

$totalQuantity = getTotalQuantitySold($conn);
echo "<script>document.getElementById('totalQ').innerText = 'The total number of products sold is: " . $totalQuantity . "';</script>";

function getTotalQuantitySold($conn) {
    // Construct the SQL query
    $sql = "SELECT SUM(quantity) as total_quantity
            FROM inventoryDetail";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the total quantity
        return $row['total_quantity'];
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
echo "<script>document.getElementById('totalP').innerText = 'The total number of order processed: " . $totalPlayed . "';</script>";

function getTotalSongsPlayed($conn) {
    // Construct the SQL query
    $sql = "SELECT COUNT(orderID) as total_purchases 
        FROM `order`";

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

/*
This is where we get to the fun stuff! This is the function that lets the user
properly query and control the data being displayed. The catch is that it 
only affects the table in the "data" section right now. Not anything else. 
There is an option to select all with 0. Otherwise you choose an ArtistID, 
which will eventually be an EmployeeID or ProductID to view details about
the thing you want to see.  
*/

$product_id = 0; // Default value
echo "
<script>
document.getElementById('chartCanvas4').style.display = 'none';
document.getElementById('chartDescriptionInventory').style.display = 'none';

</script>
";
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $product_id = intval(sanitize_input($_POST["product_id"]));
    echo"
    <script>
    document.getElementById('chartDescriptionInventory').innerText = 'Purchase over Time (for productID: ' + $product_id + ')';
    document.getElementById('product_id_paragraph').innerText = 'The product ID is: ' + $product_id;
    if ($product_id != 0) {
        document.getElementById('chartCanvas4').style.display = 'block';
        document.getElementById('chartDescriptionInventory').style.display = 'block';

    }
    </script>
    ";
}

// Typing 0 works as a select all
if ($product_id == "0") {
    // Construct the SQL query without a WHERE clause
    $sql = "SELECT inventoryDetail.quantity, inventoryDetail.productID, inventoryDetail.locationID, inventoryDetail.inventoryDetailID, 
            `order`.orderID, `order`.deliveryDate
            FROM inventoryDetail 
            INNER JOIN orderDetail ON inventoryDetail.inventoryDetailID = orderDetail.inventoryDetailID 
            INNER JOIN `order` ON orderDetail.orderID = `order`.orderID 
            ORDER BY `order`.deliveryDate ASC
            LIMIT 20
            ";
    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
} else {
    $product_id = intval($product_id);
    // Construct the SQL query with a placeholder for the artist ID
    $sql = "SELECT inventoryDetail.quantity, inventoryDetail.productID, inventoryDetail.locationID, inventoryDetail.inventoryDetailID, 
            `order`.orderID, `order`.deliveryDate
            FROM inventoryDetail 
            INNER JOIN orderDetail ON inventoryDetail.inventoryDetailID = orderDetail.inventoryDetailID 
            INNER JOIN `order` ON orderDetail.orderID = `order`.orderID 
            WHERE inventoryDetail.productID = ?
            GROUP BY `order`.deliveryDate
            ORDER BY `order`.deliveryDate ASC
            LIMIT 20
            ";
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
$stmt->bind_result($quantity, $productID, $locationID, $inventoryDetailID, $orderID, $deliveryDate);

$quantityArray = [];
$deliveryDateArray = [];
if ($product_id == 0) {
    // Fetch and display the data in a table row
$table = "<table id='artistTable'>";
$table .= "<tr><th>Product ID</th><th>Quantity</th><th>Location ID</th><th>Inventory Detail ID</th><th>Order ID</th><th>Delivery Date</th></tr>";
    
while ($stmt->fetch()) {
    $table .= "<tr>";
    $table .= "<td>" . $productID. "</td>";
    $table .= "<td>" . $quantity . "</td>";
    $table .= "<td>" . $locationID . "</td>";
    $table .= "<td>" . $inventoryDetailID . "</td>";
    $table .= "<td>" . $orderID . "</td>";
    $table .= "<td>" . $deliveryDate . "</td>";
    $table .= "</tr>";
    $quantityArray[] = $quantity;
    $deliveryDateArray[] = $deliveryDate;
}
$table .= "</table>";
echo "<script>document.getElementById('artistTable').innerHTML = `" . $table . "`;</script>";
}
else{
    // Fetch and display the data in a table row
$table = "<table id='artistTable'>";
$table .= "<tr><th>Quantity</th><th>Location ID</th><th>Inventory Detail ID</th><th>Order ID</th><th>Delivery Date</th></tr>";
    
while ($stmt->fetch()) {
    $table .= "<tr>";
    $table .= "<td>" . $quantity . "</td>";
    $table .= "<td>" . $locationID . "</td>";
    $table .= "<td>" . $inventoryDetailID . "</td>";
    $table .= "<td>" . $orderID . "</td>";
    $table .= "<td>" . $deliveryDate . "</td>";
    $table .= "</tr>";
    $quantityArray[] = $quantity;
    $deliveryDateArray[] = $deliveryDate;
}
$table .= "</table>";
echo "<script>document.getElementById('artistTable').innerHTML = `" . $table . "`;</script>";
}

    
// Convert the arrays to JSON
$quantityJson = json_encode($quantityArray);
$deliveryDateJson = json_encode($deliveryDateArray);

// Output the script to create the chart
echo "
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<script>
if ($product_id != 0) {
var ctx = document.getElementById('chartCanvas4').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: $deliveryDateJson,
        datasets: [{
            label: 'Quantity',
            data: $quantityJson,
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
});}
</script>
";
/*
This query and chart is for the "purchases per date" graph, and is formulated
to display a line graph of how purchases fluctuate with time. X-axis is 
date and y-axis is number of plays, which is going to be number of purchases.
*/
$sql = "SELECT sum(inventoryDetail.quantity) as q, inventoryDetail.inventoryDetailID, 
        `order`.orderID, `order`.deliveryDate as d
        FROM inventoryDetail 
        INNER JOIN orderDetail ON inventoryDetail.inventoryDetailID = orderDetail.inventoryDetailID 
        INNER JOIN `order` ON orderDetail.orderID = `order`.orderID 
        GROUP BY `order`.deliveryDate
        LIMIT 20
        ";

$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

$purchase_ids = array();
$purchase_counts = array();

while ($row = $result->fetch_assoc()) {
    $purchase_ids[] = $row['d'];
    $purchase_counts[] = $row['q'];
}

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
                label: "Number of Orders Delivered",
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

$sql = "SELECT locationID, SUM(quantity) as total_quantity 
        FROM inventoryDetail 
        GROUP BY locationID";

$stmt = $conn->prepare($sql);

// Execute the statement
$stmt->execute();

// Bind the results
$stmt->bind_result($locationID, $total_quantity);

// Initialize arrays to store the data
$locationArray = [];
$quantityArray = [];

// Fetch and store the data in arrays
while ($stmt->fetch()) {
    $locationArray[] = $locationID;
    $quantityArray[] = $total_quantity;
}

// Convert the arrays to JSON
$locationJson = json_encode($locationArray);
$quantityJson = json_encode($quantityArray);

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
            label: 'Quantity at Location',
            data: $quantityJson,
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

