<?php
// Database configuration
$host = 'localhost';
$dbname = 'DATABASENAME';
$username = 'database_username';
$password = 'database_password';

//connect to database
$conn = new mysqli($servername, $username, $password);

// Check connection was successful, otherwise immediately exit the script
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select the specific database
if (!$conn->select_db($database)) {
    die("Database selection failed: " . $conn->error);
}

// Check if email and password are set
if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the password and check user role
    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] == 'backOffice') {
            header('Location: back_office_page.php'); // Redirect to back office page
        } elseif ($user['role'] == 'store') {
            header('Location: store_user_page.php'); // Redirect to store user page
        } else {
            // Handle unknown role
            echo "Unauthorized access.";
        }
    } else {
        echo "Invalid credentials.";
    }
} else {
    echo "Please fill in all fields.";
}
?>
