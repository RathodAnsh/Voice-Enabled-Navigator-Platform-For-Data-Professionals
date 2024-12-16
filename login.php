<?php
session_start();
include 'db_connect.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Prepare SQL to fetch the hashed password
        $sql = "SELECT password FROM signup WHERE username = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $response['status'] = 'error';
            $response['message'] = 'Error preparing the SQL statement: ' . $conn->error;
            echo json_encode($response);
            exit();
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['password'];

            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // Password is correct, start session
                $_SESSION['username'] = $username;

                // Check for redirect parameter
                if (isset($_GET['redirect'])) {
                    $redirect_url = urldecode($_GET['redirect']);
                    $response['redirect'] = $redirect_url;
                } else {
                    $response['redirect'] = 'index.php';
                }

                $response['status'] = 'success';
                $response['message'] = 'You have been logged in successfully.';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Incorrect password.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Username not found.';
        }

        $stmt->close();
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Please enter both username and password.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>
