<?php
echo <<<EOT
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Micromanagement Central Yeehaw</title>
    <link rel="stylesheet" href="SCM_Style.css">
    <!-- <script src="SCM_Script.js"></script> -->    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="erpYeehaw.php">ERP</a>
            <a href="SCM.html">SCM</a>
            <a href="CRM.html">CRM</a>  
        </div>
        <br></br>
            <div id ="smallContainer">
                <div id="leftContainer">
                    <h3>Data</h3>
                    <p>we can put literally any table here, we had talked about being able to choose cols etc but thats a premium feature as of rn</p>
                    <table id="csvTable"></table>
                </div>
                <div id="rightContainer">
                    <div id="topRightContainer">
                        <h3>Plots & Figures</h3>
                        <p>dont rem if we are choosing pre-ordained charts or letting user pick params to graph</p>
                        <p>1st chart can be adjusted easily to be order sizes, profit margins, etc</p>
                        <canvas id="chartCanvas"></canvas>
                        <p>2nd chart can be when each purchase happened in order of purchaseid (lowkey useless i just needed to make this to make the next one)</p>
                        <canvas id="chartCanvas2"></canvas>
                        <p>3rd chart can be adjusted easily to be actually useful! orders/ shipments per day/ week etc. </p>
                        <canvas id="chartCanvas3"></canvas>
                        <div id ="plotsContainer"></div>
                    </div>
                    <div id="bottomRightContainer">
                        <h3>Data Summary</h3>
                        <p>just a table for chart3, can be adjusted to anything this was more for testing</p>
                        <table id="testTable"></table>
                        <p>we can change these to be any data summaries/ calcs, ex: calc yealry earnings, most profitable item, etc</p>
                        <button onclick="calculateAverage()">Calculate Average</button>
                        <button onclick="calculateMin()">Calculate Minimum</button>
                        <button onclick="calculateMax()">Calculate Maximum</button>
                        <p id="average"></p>
                        <p id="min"></p>
                        <p id="max"></p>
                    </div>
                </div>
            </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/split.js"></script>
            <!-- <script src="SCM_Script.js"></script> -->    
</body>
EOT;
$servername = "mydb.itap.purdue.edu"; // Remove spaces
$username = "sdeniz"; // Replace with your actual myPHPAdmin username
$password = "Paragon#2014"; // Replace with your actual myPHPAdmin password
$database = "sdeniz"; // Replace with your actual myPHPAdmin database name

// Create connection (ONLY NEEDED ONCE per PHP page!)
$conn = new mysqli($servername, $username, $password);

// Check connection was successful, otherwise immediately exit the script
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select the specific database
if (!$conn->select_db($database)) {
    die("Database selection failed: " . $conn->error);
}

// Create a string for our SQL query
$sql = "SELECT track_name, time FROM track WHERE time < (SELECT AVG(time) FROM track) LIMIT 15";

// Submit the string to SQL through the connection indicated in $conn
$result = $conn->query($sql); // Use $conn->query to execute the query

// Check if the query was successful
if ($result === false) {
    die("Query failed: " . $conn->error);
}


// Start the HTML table
echo "<script>document.getElementById('csvTable').innerHTML = `";
echo "<table border='1'>";
echo "<tr><th>Track Name</th><th>Time</th></tr>";
// Initialize song_lengths array
$song_lengths = array();
$track_ids = array();

// Fetch and print the results
while ($row = $result->fetch_assoc()) {
    // You can access individual columns like $row['track_name'] and $row['time']
    // Use echo to print specific values or print_r to print the entire row data
    $color = $row['time'] < 3 ? 'red' : 'transparent';
    echo "<tr><td>" . $row['track_name'] . "</td><td style='background-color: $color;'>" . $row['time'] . "</td></tr>";
    $song_lengths[] = floatval($row['time']);
    $track_ids[] = $row['track_id'];
}

// End the HTML table
echo "</table>";

echo "`;</script>";

echo "<script>var songLengths = " . json_encode($song_lengths) . ";</script>";
echo "<script>var trackIds = " . json_encode($track_ids) . ";</script>";

// Include the Chart.js library
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

// Pass the PHP array to JavaScript
echo "<script>var songLengths = " . json_encode($song_lengths) . ";</script>";
// Add a JavaScript function to calculate the average
echo '
<script>
    function calculateAverage() {
        var total = 0;
        for(var i = 0; i < songLengths.length; i++) {
            total += songLengths[i];
        }
        var avg = total / songLengths.length;
        document.getElementById("average").innerText = "Average song length: " + avg;
    }
    function calculateMin() {
        var min = Math.min.apply(null, songLengths);
        document.getElementById("min").innerText = "Minimum song length: " + min;
    }

    function calculateMax() {
        var max = Math.max.apply(null, songLengths);
        document.getElementById("max").innerText = "Maximum song length: " + max;
    }
</script>
';
// Create a canvas for your chart and use the Chart.js library to create a bar chart
echo '

<script>
    var ctx = document.getElementById("chartCanvas").getContext("2d");
    var chart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: songLengths,
            datasets: [{
                label: "Song Lengths",
                data: songLengths,
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



// Fetch the data from your database
$sql = "SELECT track_id, played FROM played";
$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

// Initialize the arrays
$track_ids = array();
$played_times = array();

// Fetch and store the results
while ($row = $result->fetch_assoc()) {
    $track_ids[] = $row['track_id'];
    $played_times[] = $row['played'];
}

// Pass the PHP arrays to JavaScript
echo "<script>var trackIds = " . json_encode($track_ids) . ";</script>";
echo "<script>var playedTimes = " . json_encode($played_times) . ";</script>";


// Create a canvas for your chart and use the Chart.js library to create a line chart
echo '
<canvas id="chart"></canvas>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById("chartCanvas2").getContext("2d");
    var chart = new Chart(ctx, {
        type: "line",
        data: {
            labels: playedTimes, // Use playedTimes as labels (x-axis)
            datasets: [{
                label: "Track ID",
                data: trackIds, // Use trackIds as data (y-axis)
                fill: false,
                borderColor: "rgb(75, 192, 192)",
                tension: 0.1
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
$sql = "SELECT DATE(played) AS play_date, COUNT( track_id ) AS songs_played
FROM played
GROUP BY DATE( played )
ORDER BY play_date";

$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
echo "<script>document.getElementById('testTable').innerHTML = `";
echo '<table>';
echo '<tr><th>Play Date</th><th>Songs Played</th></tr>';
$track_ids = array();
$play_counts = array();
while ($row = $result->fetch_assoc()) {
    echo '<tr><td>' . $row['play_date'] . '</td><td>' . $row['songs_played'] . '</td></tr>';
    $track_ids[] = $row['play_date'];
    $play_counts[] = $row['songs_played'];
}

echo '</table>';
echo "`;</script>";
while ($row = $result->fetch_assoc()) {
    var_dump($row);
    $track_ids[] = $row['play_date'];
    $play_counts[] = $row['songs_played'];
}
echo "<script>var trackIds = " . json_encode($track_ids) . ";</script>";
echo "<script>var playCounts = " . json_encode($play_counts) . ";</script>";
echo '

<script>
    var ctx = document.getElementById("chartCanvas3").getContext("2d");
    var chart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: trackIds, // Use trackIds as labels (x-axis)
            datasets: [{
                label: "Number of Plays",
                data: playCounts, // Use playCounts as data (y-axis)
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


// Close the connection (REMEMBER TO DO THIS!)
$conn->close();
?>