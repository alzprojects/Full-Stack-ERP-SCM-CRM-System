<?php
session_start();
$servername = "mydb.itap.purdue.edu";
$username = "g1135081";
$password = "4i1]4S*Mns83";
$database = "g1135081";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT * FROM employees WHERE userID = " . $_SESSION['userID'];
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fname = $row['fname'];
    $lname = $row['lname'];
    $CRMAccess = $row['CRMAccess'];
    $ERPAccess = $row['ERPAccess'];
    $SCMAccess = $row['SCMAccess'];
    $locationID = $row['locationID'];
}
$conn->close();
$_SESSION['fname'] = $fname;
$_SESSION['lname'] = $lname;
$_SESSION['CRMAccess'] = $CRMAccess;
$_SESSION['ERPAccess'] = $ERPAccess;
$_SESSION['SCMAccess'] = $SCMAccess;
$_SESSION['locationID'] = $locationID;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }
    
    .container {
        width: 700px;
        height: 800px;
        margin: 0 auto; 
        padding: 20px; 
        background-color: #e0f7fa; 
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
    }
    
    h2 {
        color: #333;
    }
    
    input[type="file"],
    button {
        margin-bottom: 10px;
    }
    
    canvas {
        margin-bottom: 20px;
    }
    
    #statistics, #statisticsTwo {
        width: 200;
        text-align: center;
    }
    
    #contLeft{
        width: 50%;
        min-height: 400px;
        float: left;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
        background-color: #ffffff; 
    
    }
    
    #contRight{
        width: 50%;
        min-height: 400px;
        float: right;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
        background-color: #ffffff; 
    
    }
    
    h3 {
        font-weight: bold;
    }
    
    .bold {
        font-weight: bold;
      }
      
    /* Style the navigation bar */
    .navbar {
        overflow: hidden;
        background-color: #181651;
    }
    
    /* Style the links inside the navigation bar */
    .navbar a, .navbar .dropdown .dropbtn {
        float: left;
        display: block;
        color: white;
        text-align: center;
        padding: 14px 20px;
        text-decoration: none;
    }
    
    /* Change the color of links on hover */
    .navbar a:hover, .navbar .dropdown .dropbtn:hover {
        background-color: #ddd;
        color: black;
    }
    
    .header {
        display: flex;
        justify-content: start;
        align-items: center;
    }
    
    
    .buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
    }
    
    .buttons button {
        padding: 20px 40px;
        font-size: 20px;
        cursor: pointer;
        background-color: #181651;
        color: white;
    }
    
    #tableTest {
        width: 100%;
        max-height: 100px;
        overflow-y: scroll;
        overflow-x: auto;
        padding: 8px; /* Add some padding to the table cells */
    }
    #tableTest td, #tableTest th {
        border: 1px solid #181651;
    }
    </style>
    <title>Analysis v1</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Micromanagement Central Yeehaw</h2>
        <div class="navbar">
            <a href="homePage.html?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>">Home</a>
            <a href="login.html?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>">Login</a>
        </div>
        <div class="buttons">
            <?php if ($_SESSION['SCMAccess'] == 1) : ?>
                <a href="../SCM/SCMInventory.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>"><button>SCM</button></a>
            <?php endif; ?>
            <?php if ($_SESSION['ERPAccess'] == 1) : ?>
                <a href="../Project/ERP_Inventory.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>"><button>ERP</button></a>
            <?php endif; ?>
            <?php if ($_SESSION['CRMAccess'] == 1) : ?>
                <a href="../Rename/CRMCustomers.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>"><button>CRM</button></a>
            <?php endif; ?>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>