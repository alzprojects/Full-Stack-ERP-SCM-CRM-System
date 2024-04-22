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
    <link rel="stylesheet" href="SCM_Style.css"></link>
</head>
<body>
    <div class="container">
        <h2>Micromanagement Central Yeehaw</h2>
        <div class="navbar">
            <a href="homePage.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>">Home</a>
            <a href="login.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>">Login</a>
        </div>
        <div class="buttons">
            <?php if ($_SESSION['SCMAccess'] == 1) : ?>
                <a href="SCMInventory.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>"><button>SCM</button></a>
            <?php endif; ?>
            <?php if ($_SESSION['ERPAccess'] == 1) : ?>
                <a href="ERP_Inventory.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>"><button>ERP</button></a>
            <?php endif; ?>
            <?php if ($_SESSION['CRMAccess'] == 1) : ?>
                <a href="CRMCustomers.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>"><button>CRM</button></a>
            <?php endif; ?>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>