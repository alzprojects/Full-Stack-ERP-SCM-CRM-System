<?php
$servername = "mydb.itap.purdue.edu"; 
$username = "azimbali"; 
$password = "Max!024902";
$database = $username; 
$conn = mysqli_connect($server, $username, $password, $db);
//Checks if connection was successful else exit
if(!$conn) {
	die("Connection failed: ". mysqli_connect_error());
}
echo "Success!";
//
//Rest of Code
//

//Close the connection
mysqli_close($conn);
?>