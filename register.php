<?php
include 'db_connect.php';

//header('Content-Type: application/json');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $first_name = htmlspecialchars($_POST['first_name']); // Corrected to $first_name
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM signup WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        // User already exists
        $response['status'] = 'error';
        $response['message'] = 'Username or email already exists.';
    } else {
        // Prepare the SQL statement for insertion
        $stmt = $conn->prepare("INSERT INTO signup (first_name, email, username, password) VALUES (?, ?, ?, ?)");

        if ($stmt === false) {
            $response['status'] = 'error';
            $response['message'] = 'Database prepare failed: ' . $conn->error;
            echo json_encode($response);
            exit();
        }

        // Bind parameters
        $stmt->bind_param("ssss", $first_name, $email, $username, $hashed_password); // Changed $name to $first_name

        // Execute the statement
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Your account has been created successfully.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'There was an error creating your account. Please try again.';
            if ($stmt->errno == 1062) { // Duplicate entry error
                $response['message'] = 'Username or email already exists.';
            }
        }

        // Close the statement
        $stmt->close();
    }

    // Close the connection
    $conn->close();
    echo json_encode($response);
}
?>
