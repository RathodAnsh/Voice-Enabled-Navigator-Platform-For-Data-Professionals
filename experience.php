<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in to submit feedback.']);
    exit;
}

// Retrieve and sanitize form inputs
$purpose = trim($_POST['purpose'] ?? '');
$jobTitles = $_POST['job_title'] ?? [];
$experience = trim($_POST['experience'] ?? '');
$rating = intval($_POST['rating'] ?? 0); // Corrected variable name here

// Validate form inputs
if (empty($purpose) || empty($experience) || empty($rating)) { // Corrected validation condition
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

// Serialize job titles array into a string
$jobTitlesStr = implode(', ', $jobTitles);

// Get the username from the session
$username = $_SESSION['username'];

// Prepare the SQL query to insert feedback
$query = "INSERT INTO user_experience (username, purpose, job_title, experience, Rating) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('sssss', $username, $purpose, $jobTitlesStr, $experience, $rating); // Changed $Rating to $rating

// Execute the query and provide feedback
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'You have successfully submitted your experience']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error submitting feedback.']);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
