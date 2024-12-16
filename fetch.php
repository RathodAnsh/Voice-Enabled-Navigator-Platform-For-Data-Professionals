<?php
include('db_connect.php');

// Fetch all feedback from the database
$sql = "SELECT purpose, job_title, experience, submitted_at, username, Rating FROM user_experience ORDER BY submitted_at DESC";
$result = $conn->query($sql);

// Initialize an array to store the feedback
$feedbackArray = [];

if ($result->num_rows > 0) {
    // Output each feedback and store it in the array
    while ($row = $result->fetch_assoc()) {
        // Get the first letter of the username to display as initials
        $initials = strtoupper(substr($row['username'], 0, 1));

        // Store feedback details in an associative array
        $feedbackArray[] = [
            'purpose' => htmlspecialchars($row['purpose']),
            'job_title' => htmlspecialchars($row['job_title']),
            'experience' => htmlspecialchars($row['experience']),
            'submitted_at' => date("F j, Y", strtotime($row['submitted_at'])),
            'username' => htmlspecialchars($row['username']),
            'initials' => $initials,
            'rating' => $row['Rating']
        ];
    }
} else {
    // If no feedback, return a message
    $feedbackArray = [];
}

// Close the database connection after fetching all needed data
$conn->close();

// Encode feedback array as JSON to pass to JavaScript
$feedbackJson = json_encode($feedbackArray);
?>

<script>
    const feedbackArray = <?php echo $feedbackJson; ?>; // Feedback data from PHP
    let currentFeedbackIndex = 0; // Start with the first feedback
    const feedbackContainer = document.getElementById('feedback-section');
    const pageInfo = document.getElementById('page-info');

    // Function to display feedback
    function displayFeedback(index) {
        if (feedbackArray.length === 0) {
            // Show a message if there is no feedback data
            feedbackContainer.innerHTML = '<p style="color: white; font-size: 1.2em;">No review available.</p>';
            pageInfo.textContent = '';
            return; // Exit function
        }

        const feedback = feedbackArray[index];
        let stars = '';

        for (let i = 1; i <= 5; i++) {
            stars += i <= feedback.rating ? '&#9733;' : '&#9734;'; // Filled or empty star
        }

        // Display the feedback content
        feedbackContainer.innerHTML = `
            <div class="feedback-card">
                <div class="feedback-details">
                    <p class="feedback-text"><strong>Purpose:</strong> ${feedback.purpose}</p>
                    <p class="job-title"><strong>Job Title:</strong> ${feedback.job_title}</p>
                    <p class="experience"><strong>Experience:</strong> ${feedback.experience}</p>
                    <div class="stars">${stars}</div>
                </div>
                <div class="profile-info">
                    <div class="profile-image">${feedback.initials}</div>
                    <div class="user-details">
                        <span class="username">${feedback.username}</span>
                        <span class="submitted-date">${feedback.submitted_at}</span>
                    </div>
                </div>
            </div>
        `;

        // Update the page info
        pageInfo.textContent = `Feedback ${index + 1} of ${feedbackArray.length}`;
    }

    // Function to show the next feedback
    function nextFeedback() {
        currentFeedbackIndex = (currentFeedbackIndex + 1) % feedbackArray.length; // Loop back to start
        displayFeedback(currentFeedbackIndex);
    }

    // Function to show the previous feedback
    function prevFeedback() {
        currentFeedbackIndex = (currentFeedbackIndex - 1 + feedbackArray.length) % feedbackArray.length; // Loop back to end
        displayFeedback(currentFeedbackIndex);
    }

    // Initial feedback display (check if feedback exists first)
    if (feedbackArray.length > 0) {
        displayFeedback(currentFeedbackIndex);
    } else {
        feedbackContainer.innerHTML = '<p>No feedback available.</p>';
    }

    // Auto-move feedback every 5 seconds
    let autoSlide = setInterval(nextFeedback, 5000); // Move every 5 seconds

    // Add event listeners for navigation buttons
    document.getElementById('next-button').addEventListener('click', function () {
        clearInterval(autoSlide); // Stop auto-slide
        nextFeedback();
        autoSlide = setInterval(nextFeedback, 5000); // Restart auto-slide after navigation
    });

    document.getElementById('prev-button').addEventListener('click', function () {
        clearInterval(autoSlide); // Stop auto-slide
        prevFeedback();
        autoSlide = setInterval(nextFeedback, 5000); // Restart auto-slide after navigation
    });
</script>
