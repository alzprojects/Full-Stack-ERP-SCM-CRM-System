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

// Check if email and password are set
if (isset($_POST['email']) && isset($_POST['password'])) 
{
    	$email = $_POST['email'];
    	$password = $_POST['password'];

	$query = "SELECT user_id, email, password, role FROM users WHERE email = '$email' AND password = '$password'";
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
				// Redirect to back office page
            			//header('Location: back_office_page.php'); 
            			?><script>alert("alex")</script><?php
        		} elseif ($row['role'] == 'supplier') {
				// Redirect to store user page
            			//header('Location: store_user_page.php'); 
            			?><script>alert("seif")</script><?php
        		} elseif ($row['role'] == 'employee') {
				// Redirect to store user page
            			header('Location: employee_page.php?userId='.$user_id); 
        		}
		}
	}
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Page</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body><?php
$email = '';
if(isset($_POST['email']))
{
	$email = $_POST['email'];	
}
$password = '';
if(isset($_POST['password']))
{
	$password = $_POST['password'];		
}
?><div class="container">
    <form action="login.php" method="post">
        <h2>Login</h2>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="form-control" value="<?php echo $password; ?>" required>
        </div>
	<div class="form-group" class="col-md-12">        
            <button type="submit" class="btn btn-success btn-block"><b>Login</b></button>
            <!-- Guest Login Button -->
            <button type="button" class="btn btn-primary btn-block" onclick="location.href='index.html'"><b>Login as Guest</b></button> 
	</div>
    </form>
</div>

</body>
</html>
