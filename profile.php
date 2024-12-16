<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in and assign the username
$loggedIn = isset($_SESSION['username']);
$username = $loggedIn ? $_SESSION['username'] : '';

// Initialize variables for user details
$firstName = '';
$email = '';
$lastName = ''; // Last Name remains empty by default
$mobile = '';   // Mobile number remains empty by default

// Fetch user details (first name, email, last name, and mobile) from the database if the user is logged in
if ($loggedIn) {
    $stmt = $conn->prepare("SELECT first_name, email, last_name, mobile_no FROM signup WHERE username = ?");
    if ($stmt === false) {
        error_log("Error preparing statement: " . $conn->error);
        exit();
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($firstName, $email, $lastName, $mobile);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission to insert or update last name and mobile number into the database
if ($_SERVER["REQUEST_METHOD"] == "POST" && $loggedIn && isset($_POST['first_name'], $_POST['email'], $_POST['last_name'], $_POST['mobile'])) {
    // Get updated form values
    $firstName = htmlspecialchars($_POST['first_name']);
    $email = htmlspecialchars($_POST['email']);
    $lastName = htmlspecialchars($_POST['last_name']);
    $mobile = htmlspecialchars($_POST['mobile']);

    // Update the database with last name and mobile number
    $stmt = $conn->prepare("UPDATE signup SET first_name = ?, email = ?, last_name = ?, mobile_no = ? WHERE username = ?");
    if ($stmt === false) {
        error_log("Error preparing statement: " . $conn->error);
        $response = ['status' => 'error', 'message' => 'An internal error occurred.'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("sssss", $firstName, $email, $lastName, $mobile, $username);
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Your profile has been updated.'];
    } else {
        error_log("Error executing statement: " . $stmt->error);
        $response = ['status' => 'error', 'message' => 'There was an error updating your profile.'];
    }
    $stmt->close();
    
    // Output the JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Handle Change Password form submission
if ($loggedIn && isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_new_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmNewPassword = $_POST['confirm_new_password'];

    // Ensure the new passwords match
    if ($newPassword !== $confirmNewPassword) {
        $response = ['status' => 'error', 'message' => 'New passwords do not match.'];
        echo json_encode($response);
        exit();
    }

    // Fetch stored password for verification
    $stmt = $conn->prepare("SELECT password FROM signup WHERE username = ?");
    if ($stmt === false) {
        error_log("Error preparing statement: " . $conn->error);
        $response = ['status' => 'error', 'message' => 'An internal error occurred.'];
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($storedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verify the current password
    if (password_verify($currentPassword, $storedPassword)) {
        // Hash the new password and update the database
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("UPDATE signup SET password = ? WHERE username = ?");
        if ($stmt === false) {
            error_log("Error preparing statement: " . $conn->error);
            $response = ['status' => 'error', 'message' => 'An internal error occurred.'];
            echo json_encode($response);
            exit();
        }

        $stmt->bind_param("ss", $hashedPassword, $username);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Password updated successfully.'];
        } else {
            error_log("Error executing statement: " . $stmt->error);
            $response = ['status' => 'error', 'message' => 'Error updating password.'];
        }
        $stmt->close();
    } else {
        // If current password is incorrect
        $response = ['status' => 'error', 'message' => 'Current password is incorrect.'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voice-Enabled Navigator Platform</title>
    <link rel="stylesheet" href="static/css/profile.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- SweetAlert library -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>
<div class="wrapper">
    <header>
        <div class="header-left">
            <img src="static/css/images/data-removebg-preview.png" alt="Logo" class="logo">
            <h1>Voice-Enabled Navigator Platform For Data Professionals</h1>
        </div>
        <div class="header-right">
            <?php if ($loggedIn): ?>
            <div class="profile-button" id="profileDropdownContainer">
                <img src="static/css/images/profiles-removebg-preview.png" alt="Profile Icon" class="pro" />
                <button id="profileBtn"><?php echo htmlspecialchars($username); ?></button>

                <!-- Dropdown container -->
                <div id="logoutDropdown" class="dropdown-content">
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
            <?php else: ?>
            <a href="templates/login.html" class="btn">Login/Signup</a>
            <?php endif; ?>
        </div>
    </header>
</div>
<main>
    <section id="about-us" class="about-us">
        <div class="about-us-content">
            <h2>Profile Section</h2>

            <!-- Buttons for Edit Profile and Change Password -->
            <div class="profile-actions">
                <button class="edit-profile-btn">Edit Profile</button>
                <button class="change-password-btn">Change Password</button>
            </div>

            <!-- Input fields for user data -->
            <form id="profileForm" method="POST" action="profile.php" style="display: none;">
                <div class="profile-actions">
                    <!-- First Name (fetched from database) -->
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required>

                    <!-- Last Name (user input) -->
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" placeholder="Enter last name">

                    <!-- Email (fetched from database) -->
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

                    <!-- Mobile Number (user input) -->
                    <label for="mobile">Mobile No:</label>
                    <input type="text" id="mobile" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" placeholder="Enter mobile number">

                    <!-- Submit button -->
                    <div class="save-btn-container">
                        <button type="submit" class="save-btn">Save Changes</button>
                    </div>
                </div>
            </form>

            <!-- Change Password Form -->
            <form id="changePasswordForm" method="POST" action="profile.php" style="display:none;">
                <div class="profile-actions">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password" required>

                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>

                    <label for="confirm_new_password">Confirm New Password:</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm new password" required>

                    <div class="save-btn-container">
                        <button type="submit" class="save-btn">Update Password</button>
                    </div>
                </div>

                <div id="password-message" class="password-message">
                    <p>Password Requirement</p>
                <label>
                    <span>At least 8 characters long</span>
                </label>
                <label>
                    <span>At least one capital letter</span>
                </label>
                <label>
                    <span>At least one special character</span>
                </label>
                <label>
                    <span>At least one number</span>
                </label>
            </div>
            </form>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var profileBtn = document.getElementById('profileBtn');
    var dropdown = document.getElementById('logoutDropdown');
    var editProfileBtn = document.querySelector('.edit-profile-btn');
    var changePasswordBtn = document.querySelector('.change-password-btn');
    var profileForm = document.getElementById('profileForm');
    var passwordForm = document.getElementById('changePasswordForm'); 

    // Toggle dropdown visibility for the profile menu
    profileBtn.addEventListener('click', function() {
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        } else {
            dropdown.style.display = 'block';
        }
    });

    // Close the dropdown if the user clicks outside of it
    window.addEventListener('click', function(event) {
        if (!profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Show profile form and hide the password form when "Edit Profile" is clicked
    editProfileBtn.addEventListener('click', function() {
        profileForm.style.display = 'block';
        passwordForm.style.display = 'none';
    });

    // Show password form and hide the profile form when "Change Password" is clicked
    changePasswordBtn.addEventListener('click', function() {
        passwordForm.style.display = 'block';
        profileForm.style.display = 'none';
    });
});
</script>

<script>
    // Profile form submission
    document.querySelector('#profileForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Get form data
        var formData = new FormData(this);
        var firstName = formData.get('first_name');
        var lastName = formData.get('last_name');
        var email = formData.get('email');
        var mobile = formData.get('mobile');

        // Validate fields manually
        if (!lastName || !mobile) {
            swal({
                title: "Incomplete Profile Section",
                text: "Please fill in all fields.",
                icon: "warning",
                button: "OK"
            });
            return; // Stop form submission
        }

        // Submit the form via fetch
        fetch('profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            try {
                var data = JSON.parse(text);

                if (data.status === 'success') {
                    swal({
                        title: "Profile Updated Successfully!",
                        text: data.message,
                        icon: "success",
                        buttons: false,
                        timer: 3000
                    }).then(() => {
                        document.querySelector('#profileForm').reset();
                    });
                } else {
                    swal({
                        title: "Profile Update Failed!",
                        text: data.message,
                        icon: "error",
                        button: "OK"
                    });
                }
            } catch (error) {
                swal({
                    title: "An error occurred!",
                    text: "Unexpected response from the server.",
                    icon: "error",
                    button: "OK"
                });
                console.error('Parsing error:', error);
            }
        })
        .catch(error => {
            swal({
                title: "An error occurred!",
                text: error.message,
                icon: "error",
                button: "OK"
            });
            console.error('Fetch error:', error);
        });
    });

    document.querySelector('#changePasswordForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Get form data
        var formData = new FormData(this);
        var currentPassword = formData.get('current_password');
        var newPassword = formData.get('new_password');
        var confirmNewPassword = formData.get('confirm_new_password');

        // Validate password fields
        if (!currentPassword || !newPassword || !confirmNewPassword) {
            swal({
                title: "Incomplete Password Section",
                text: "Please fill in all fields.",
                icon: "warning",
                button: "OK"
            });
            return;
        }

        if (newPassword !== confirmNewPassword) {
            swal({
                title: "Password Mismatch",
                text: "New passwords do not match.",
                icon: "error",
                button: "OK"
            });
            return;
        }

        fetch('profile.php', { 
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            try {
                var data = JSON.parse(text);

                if (data.status === 'success') {
                    swal({
                        title: "Password Updated Successfully!",
                        text: data.message,
                        icon: "success",
                        buttons: false,
                        timer: 3000
                    }).then(() => {
                        document.querySelector('#passwordForm').reset();
                    });
                } else {
                    swal({
                        title: "Password Update Failed!",
                        text: data.message,
                        icon: "error",
                        button: "OK"
                    });
                }
            } catch (error) {
                swal({
                    title: "An error occurred!",
                    text: "Unexpected response from the server.",
                    icon: "error",
                    button: "OK"
                });
                console.error('Parsing error:', error);
            }
        })
        .catch(error => {
            swal({
                title: "An error occurred!",
                text: error.message,
                icon: "error",
                button: "OK"
            });
            console.error('Fetch error:', error);
        });
    });
</script>


</body>
</html>
