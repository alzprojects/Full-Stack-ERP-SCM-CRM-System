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
	
	$query = "SELECT * FROM employees WHERE user_id = '$userId'";
	$statement = $conn->prepare($query);
	$statement->execute();
	$result = $statement->fetchAll();
	foreach($result as $row)
	{
		$firstName = $row['first_name'];
		$lastName = $row['last_name'];
		$SCMAccess = $row['AccessSCM'];
		$ERPAccess = $row['AccessERP'];
		$CRMAccess = $row['AccessCRM'];
		$location = $row['location_id'];
	}
}else{
	?><script>history.back();</script><?php
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Page</title>
<link rel="stylesheet" href="employeestyle.css">
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

<!-- Logo Container-->
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
	<p>Welcome, <strong><?php echo 'ID: ' . $userId . ' (' . strtoupper($firstName) . ' ' . strtoupper($lastName) . ') Location: ' . strtoupper($location); ?></strong></p>
    </div>
    <div class="button-group"><?php
        if ($SCMAccess == 1){
        	?><button class="btn btn-primary btn-custom">SCM</button><?php
        }
        if ($ERPAccess == 1){
        	?><button class="btn btn-primary btn-custom">ERP</button><?php
        }
        if ($CRMAccess == 1){
        	?><button class="btn btn-primary btn-custom">CRM</button><?php
        }
        ?><button class="btn btn-danger btn-custom" onclick="return btn_logout_onclick();">Logout</button><?php
    ?></div>
</div>

</body>
</html>
