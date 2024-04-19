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
