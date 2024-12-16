<?php
include 'session.php';
include 'db_connect.php';

$name = "";
$loggedIn = isset($_SESSION['username']);
$isUserLoggedIn = $loggedIn ? 'true' : 'false';

if ($loggedIn) {
    $username = $_SESSION['username'];

    $query = "SELECT username FROM signup WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['username'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voice-Enabled Navigator Platform</title>
    <link rel="stylesheet" href="static/css/styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- SweetAlert library -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const isUserLoggedIn = <?php echo json_encode($isUserLoggedIn); ?>;
            const userExperienceSection = document.getElementById("userExperienceSection");
            const formFields = userExperienceSection.querySelectorAll("input, select, textarea, button[type='submit']");
            const feedbackMessage = document.getElementById("feedbackMessage");

            if (isUserLoggedIn === 'true') {
                formFields.forEach(field => {
                    field.disabled = false;
                });
            } else {
                formFields.forEach(field => {
                    field.disabled = true;
                });
                feedbackMessage.innerText = "Please login/signup to give your user experience.";
                feedbackMessage.style.color = "red";
            }
        });

        function submitFeedback(event) {
            const isUserLoggedIn = <?php echo json_encode($isUserLoggedIn); ?>;
            const feedbackMessage = document.getElementById("feedbackMessage");

            if (isUserLoggedIn !== 'true') {
                event.preventDefault();
                feedbackMessage.innerText = "Please login/signup to give your user experience.";
                feedbackMessage.style.color = "red";
            } else {
            }
        }
    </script>
</head>
<body>
    <div class="wrapper">
        <header>
            <div class="header-left">
                <img src="static/css/images/data-removebg-preview.png" alt="Logo" class="logo">
                <h1>Voice-Enabled Navigator Platform For Data Professionals</h1>
            </div>
            <div class="header-right">
                <a href="#home">Home</a>
                <a href="#about-us" id="about-us-button">About Us</a>
                <a href="#services">Services</a>
                <a href="#userExperienceSection">User Experience</a>

                <?php if ($loggedIn): ?>
                <div class="profile-button" id="profileDropdownContainer">
                    <img src="static/css/images/profiles-removebg-preview.png" alt="Profile Icon" class="pro" />
                    <button id="profileBtn"><?php echo htmlspecialchars($name); ?></button>

                    <!-- Dropdown container -->
                    <div id="logoutDropdown" class="dropdown-content">
                        <a href="profile.php">Profile</a> <!-- Link to Profile page -->
                        <a href="logout.php">Logout</a>   <!-- Link to Logout action -->
                    </div>
                </div>
                <?php else: ?>
                <a href="templates/login.html" class="btn">Login/Signup</a>
                <?php endif; ?>
            </div>
        </header>
    </div>

    <div class="banner" id="home">
    </div>
    <main>
        <section id="about-us" class="about-us">
            <div class="about-us-content">
                <h2>About Us</h2>
                <p>Welcome to our Voice-Enabled Navigator Platform for Data Professionals! 
                    We're here to help you advance your career with personalized guidance. 
                    Using advanced voice technology, we provide tailored salary predictions, job recommendations, and course suggestions. 
                    Whether you're looking to switch roles, boost your skills, or explore new career paths, our platform supports you every step of the way. 
                    Join us and let our voice assistant guide you to a brighter professional future.
                </p>
            </div>
        </section>
        <section class="services" id="services">
            <h2>Our Services</h2>
            <div class="services-container">
                <a href="<?php echo $loggedIn ? 'http://127.0.0.1:5000' : 'templates/login.html'; ?>" class="service-card">
                    <img src="static/css/images/salary.jpg" alt="Service 1">
                    <div class="overlay">
                        <h3>Salary Prediction</h3>
                        <p>Find out what you could be earning in your next data professional role.</p>
                    </div>
                </a>
                <a href="<?php echo $loggedIn ? 'http://127.0.0.1:5002' : 'templates/login.html'; ?>" class="service-card">
                    <img src="static/css/images/jobs.jpg" alt="Service 2">
                    <div class="overlay">
                        <h3>Job Recommendation</h3>
                        <p>Let us help you find the perfect job for your career growth.</p>
                    </div>
                </a>
                <a href="<?php echo $loggedIn ? 'http://127.0.0.1:5001' : 'templates/login.html'; ?>" class="service-card">
                    <img src="static/css/images/course.jpg" alt="Service 3">
                    <div class="overlay">
                        <h3>Course Recommendation</h3>
                        <p>Advance your career with the right learning opportunities.</p>
                    </div>
                </a>
            </div>
        </section>
        <section id="userExperienceSection" class="feedback-section">
    <div class="feedback-content">
        <h2>User Experience</h2>
        <form class="feedback-form" id="experienceForm" method="POST" action="experience.php" onsubmit="submitFeedback(event)">
            <label for="purpose">Purpose:</label>
            <select id="purpose" name="purpose" required>
                <option value="" disabled selected>Select a purpose</option>
                <option value="Salary Prediction">Salary Prediction</option>
                <option value="Job Recommendation">Job Recommendation</option>
                <option value="Course Recommendation">Course Recommendation</option>
                <option value="All">All</option>
            </select>

            <label>Job Title:</label>
            <div class="checkbox-group">
                <label><input type="checkbox" name="job_title[]" value="Data Analyst"> Data Analyst</label>
                <label><input type="checkbox" name="job_title[]" value="Data Engineering"> Data Engineering</label>
                <label><input type="checkbox" name="job_title[]" value="Data Scientist"> Data Scientist</label>
                <label><input type="checkbox" name="job_title[]" value="Business Analyst"> Business Analyst</label>
            </div>

            <label for="rating">Rating:</label>
            <div class="rating-container">
                <div class="stars">
                    <input type="radio" name="rating" id="star1" value="5" />
                    <label for="star1" title="5 stars">★</label>
                    <input type="radio" name="rating" id="star2" value="4" />
                    <label for="star2" title="4 stars">★</label>
                    <input type="radio" name="rating" id="star3" value="3" />
                    <label for="star3" title="3 stars">★</label>
                    <input type="radio" name="rating" id="star4" value="2" />
                    <label for="star4" title="2 stars">★</label>
                    <input type="radio" name="rating" id="star5" value="1" />
                    <label for="star5" title="1 star">★</label>
                </div>
            </div>

            <label for="experience">Experience:</label>
            <textarea id="experience" name="experience" placeholder="Enter your experience here..." required></textarea>
            <button type="submit">Submit Feedback</button>
        </form>
        <div id="feedbackMessage" class="feedback-message"></div>
    </div>
</section>

<section id="user" class="user">
    <div class="user-content">
        <h2>Reviews</h2>
        <div id="feedback-section">
        <span id="page-info"></span>
            <?php include('fetch.php'); ?> <!-- Include the PHP file to fetch feedbacks -->
        </div>

        <div class="navigation-buttons">
            <button id="prev-button" class="nav-button" onclick="prevFeedback()">&lt;</button>
            <button id="next-button" class="nav-button" onclick="nextFeedback()">&gt;</button>
        </div>
    </div>
</section>

        <!-- script for SweetAlert integration -->
        <script>
            document.getElementById('experienceForm').addEventListener('submit', function(event) {
    event.preventDefault();

    // Get form data
    var formData = new FormData(this);
    var purpose = formData.get('purpose');
    var jobTitles = formData.getAll('job_title[]');
    var experience = formData.get('experience');
    var stars = formData.get('rating');

    // Validate fields manually
    if (!purpose || !experience) {
        swal({
            title: "Incomplete Form",
            text: "",
            icon: "warning",
            button: "OK"
        });
        return; // Stop form submission
    }

    // Submit the form via fetch if all fields are filled
    fetch('experience.php', {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.ok) {
            return response.json();
        } else {
            throw new Error('Network response was not ok.');
        }
    }).then(data => {
        if (data.status === 'success') {
            swal({
                title: "Experience Submitted Successfully!",
                icon: "success",
                text: "Your Experience is Submitted.",
                buttons: false,
                timer: 3000
            }).then(() => {
                document.getElementById('experienceForm').reset(); // Reset the form after successful submission
            });
        } else {
            swal({
                title: "Experience Rejected!",
                icon: "error",
                button: "OK"
            });
        }
    }).catch(error => {
        console.error('Error:', error);
        swal({
            title: "An error occurred!",
            text: error.message,
            icon: "error",
            button: "OK"
        });
    });
});
        </script>

<script>
    let currentIndex = 0; // Track the current feedback index
    const feedbacks = <?php echo json_encode($feedbacks); ?>; // Convert PHP feedbacks to JavaScript array

    function displayFeedback(index) {
        const feedbackSection = document.getElementById('feedback-section');
        feedbackSection.innerHTML = ''; // Clear previous feedback
        if (index >= 0 && index < feedbacks.length) {
            // Create feedback card HTML
            const feedbackCard = `
                <div class="feedback-card">
                    <div class="stars">
                        ${'★'.repeat(feedbacks[index].Rating) + '☆'.repeat(5 - feedbacks[index].Rating)}
                    </div>
                    <p class="feedback-text">${feedbacks[index].purpose}</p>
                    <div class="profile-info">
                        <div class="profile-image">${feedbacks[index].username.charAt(0).toUpperCase()}</div>
                        <div class="user-details">
                            <span class="username">${feedbacks[index].username}</span>
                            <span class="submitted-date">${new Date(feedbacks[index].submitted_at).toLocaleDateString()}</span>
                        </div>
                    </div>
                </div>
            `;
            feedbackSection.innerHTML = feedbackCard; // Display new feedback
        }
    }

    function nextFeedback() {
        currentIndex++;
        if (currentIndex >= feedbacks.length) {
            currentIndex = 0; // Loop back to the first feedback
        }
        displayFeedback(currentIndex);
    }

    function prevFeedback() {
        currentIndex--;
        if (currentIndex < 0) {
            currentIndex = feedbacks.length - 1; // Loop back to the last feedback
        }
        displayFeedback(currentIndex);
    }

    // Initial display of the first feedback
    document.addEventListener('DOMContentLoaded', function() {
        displayFeedback(currentIndex);
    });
</script>

    </main>
    <footer>
    <div class="footer-bottom">
        <p style="color: red;">Copyright © 2024 Data Professionals. Designed by
            <span>
                <a href="team.php" class="dynamic-link">AAA Team</a>
            </span>
        </p>
    </div>
</footer>

    <script src="static/css/script.js"></script>
</body>
</html>
