<?php
// Database configuration
$host = 'localhost';
$dbname = 'DATABASENAME';
$username = 'your_database_username';
$password = 'your_database_password';

//connect to database
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

// Check if email and password are set
if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    header(Location: AnalysisV1.html);
    $stmt = $conn->prepare("SELECT email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the password and check user role
    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] == 'back_office') {
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