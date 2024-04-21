<?php
// Database configuration

$db_host = 'localhost';
$db_user = 'root';
$db_password = 'root';
$db_db = 'seif';

//connect to database
$conn = new PDO("mysql:host=$db_host;dbname=$db_db",$db_user,$db_password);

// Check connection was successful, otherwise immediately exit the script
if (!$conn) 
{
	die("Connection failed: " . $conn->connect_error);
}

// Check if username and password are set
if (isset($_POST['username']) && isset($_POST['password'])) 
{
    	$username = $_POST['username'];
    	$password = $_POST['password'];

	$query = "SELECT userID AS user_id, username, password, user_type AS role FROM users WHERE username = '$username' AND password = '$password'";
	$statement = $conn->prepare($query);
	$statement->execute();
	$result = $statement->fetchAll();
	$no=$statement->rowCount();
	if($no==0)
	{
            	?><script>alert("Unauthorized access.")</script><?php
	}else{
		foreach($result as $row)
		{
			$user_id = $row['user_id'];
        		if ($row['role'] == 'customer') 
			{
				// Redirect to customers page
                header('Location: customers_page.php?userId='.$user_id); 
        		} elseif ($row['role'] == 'supplier') {
				// Redirect to suppliers  page
                header('Location: suppliers_page.php?userId='.$user_id); 
        		} elseif ($row['role'] == 'employee') {
				// Redirect to employee user page
            	header('Location: employee_page.php?userId='.$user_id); 
        		}
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Page</title>
<link rel="stylesheet" href="loginstyle.css">
<style>
    .logo-container {
        position: absolute;
        top: 0;
        right: 0;
        padding: 10px;
    }
</style>
</head>
<body><?php
$username = '';
if(isset($_POST['username']))
{
	$username = $_POST['username'];	
}
$password = '';
if(isset($_POST['password']))
{
	$password = $_POST['password'];		
}
?>

<!-- Logo Container -->
<div class="logo-container">
    <img src="logo.png" alt="Website Logo" style="width: 100px;"> <!-- Adjust the width as needed -->
</div>

<div class="container">
    <form action="login.php" method="post">
        <h2>Login</h2>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="username" id="username" name="username" class="form-control" value="<?php echo $username; ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="form-control" value="<?php echo $password; ?>" required>
        </div>
    	<div class="form-group" class="col-md-12">        
            <button type="submit" class="btn btn-success btn-block"><b>Login</b></button>
		<p>
            <div class="form-group">
            <label for="dataNumber">Add Data:</label>
            <input type="number" id="addData" name="addData" class="form-control">
            <button type="submit" class="btn btn-info btn-block"><b>Submit</b></button>
        </div>
    </form>
</div>

</body>
</html>

