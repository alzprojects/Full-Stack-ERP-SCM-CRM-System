<?php
session_start(); 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to the database
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get username and password from the form
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Create the query
    $sql = "SELECT userID FROM users WHERE username='$username' AND password='$password'";

    // Execute the query
    $result = $conn->query($sql);

    // Check if the username and password are correct
    if ($result->num_rows > 0) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
    	$_SESSION['userID'] = $result->fetch_assoc()['userID'];  
        header("location: homepage.php");
    } else {
        $_SESSION['error'] = 'Invalid username or password.';
        header("location: login.php"); // Ensure the redirect is to this file itself or wherever the form is handled
        exit;
    }

    // Close the connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Page</title>
<link rel="stylesheet" type="text/css" href="SCM_Style.css">
</head>
<body>

<div class="login-container">
    <form action="login.php" method="post">
        <h2>Login</h2>
        <div class="input-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="input-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="button-group">
            <button type="submit">Login</button>
    </form>
    <form action="dataGenerationProjectTest.php" method="post">
        <div class="button-group">
            <button>Login As Guest</button>
        </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
<script>
    alert('<?php echo $_SESSION['error']; ?>'); // This will display the error message as an alert
    <?php unset($_SESSION['error']); ?>
</script>
<?php endif ?>

</body>
</html>
