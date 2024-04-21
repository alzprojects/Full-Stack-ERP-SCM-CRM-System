<?php
// Database configuration

$db_host = 'localhost';
$db_user = 'root';
$db_password = 'root';
$db_db = 'seif';

//connect to database
$conn = new PDO("mysql:host=$db_host;dbname=$db_db",$db_user,$db_password);

if(isset($_GET['userId']))
{
	$userId = $_GET['userId'];
	$query = "SELECT c.customerID, c.fname, c.lname FROM `customers` c INNER JOIN `enumcustomer` e ON c.customerID = e.customerID WHERE e.userID = $userId";
	$statement = $conn->prepare($query);
	$statement->execute();
	$result = $statement->fetchAll();
	foreach($result as $row)
	{
		$fname = $row['fname'];
		$lname = $row['lname'];
        $customerID = $row['customerID'];
	}
}else{
	?><script>history.back();</script><?php
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Page</title>
<link rel="stylesheet" href="customerstyle.css">
<style>
    .welcome {
        font-size: 18px;
        margin-top: 20px;
    }
    .button-group {
        margin-top: 50px;
    }
    .btn-custom {
        width: 100px;
        margin-right: 15px;
    }
    .logo-container {
        position: absolute;
        top: 0;
        right: 0;
        padding: 10px;
    }
</style>
</head>
<body>
    <!-- Logo Container -->
<div class="logo-container">
    <img src="logo.png" alt="Website Logo" style="width: 100px;">
</div>
<script>
function btn_logout_onclick() 
{
    window.location.href = "login.php";
}
</script>
<div class="container">
    <div class="welcome">
        <p>Welcome, <strong><?php echo 'Customer ID: '.$customerID .' ('. strtoupper($fname) .' '. strtoupper($lname) .')'; ?></strong></p>
    </div>
    <div class="button-group"><?php

        ?><button class="btn btn-danger btn-custom" onclick="return btn_logout_onclick();">Logout</button><?php
    ?></div>
</div>

</body>
</html>
