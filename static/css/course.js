// Function to fetch recommendations
async function getRecommendations() {
    const courseTitle = document.getElementById("course-input").value;

    console.log(`Course title entered: "${courseTitle}"`); // Debugging log

    // Call the backend API to get recommendations
    const response = await fetch('/recommend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 'course title': courseTitle }) // Match with Flask code
    });

    // Get the result from the backend
    const recommendations = await response.json();

    // Display the recommendations
    const resultsDiv = document.getElementById("results");
    resultsDiv.innerHTML = ''; // Clear previous results
    
    const recommendationMessage = document.getElementById("recommendation-message");
    
    if (recommendations.error) {
        resultsDiv.innerHTML = `<p>${recommendations.error}</p>`;
    } else {
        // Use a Set to track displayed course titles to avoid duplicates
        const displayedCourses = new Set();
        const titlesToRead = []; // Array to hold titles for reading
        recommendations.forEach(course => {
            // Check if the course has already been displayed
            if (!displayedCourses.has(course['course title'])) {
                // Add the course title to the Set to avoid displaying it again
                displayedCourses.add(course['course title']);
                titlesToRead.push(course['course title']); // Add to titles to read

                // Create HTML structure for each course
                const courseElement = document.createElement("div");
                courseElement.classList.add("course-card");
                courseElement.innerHTML = `
                    <h3>${course['course title']}</h3>
                    <button class="view-btn" onclick="openCourseModal(
                        '${course['course title']}', 
                        '${course['course description']}', 
                        '${course['Ratings']}', 
                        '${course['duration']}', 
                        '${course['course level']}', 
                        '${course['Course Type']}', 
                        '${course['course url']}'
                    )">View Course</button>
                `;
                resultsDiv.appendChild(courseElement);
            }
        });
        // Call the function to read the specific course titles
        readCourseTitles(titlesToRead);
    }
}

// Voice Assistant Integration for Course
const voiceAssistant = document.getElementById('voice-assistant-btn');
const courseInput = document.getElementById('course-input');

// Function to make the system ask a question aloud
function speak(text, callback) {
    const speech = new SpeechSynthesisUtterance(text);
    speech.lang = 'en-IN';
    window.speechSynthesis.speak(speech);

    // Call the callback function when speaking ends
    speech.onend = () => {
        console.log('Finished speaking.');
        if (callback) callback();  // Start voice recognition after speaking ends
    };
}

// Function to start voice recognition and capture the user's input
function startVoiceRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        alert('Your browser does not support Speech Recognition.');
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = 'en-US';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = () => {
        console.log('Listening for user response...');
    };

    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        courseInput.value = transcript;  // Set recognized text to the input field
        courseInput.disabled = false;  // Enable the input field after receiving input
        console.log(`User said: ${transcript}`);
    };

    recognition.onerror = (event) => {
        console.error('Recognition error:', event.error);
        alert('There was an error with speech recognition. Please try again.');
    };

    recognition.onend = () => {
        console.log('Voice recognition ended.');
    };

    // Start listening after the question is spoken
    recognition.start();
}

// Function to enable multiple fields
function enableFields(fields) {
    fields.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            element.disabled = false;
            console.log(`Enabled field: ${field.id}`);  // Log field enablement
        }
    });
}

// Attach the voice recognition function to the button click event
voiceAssistant.addEventListener('click', () => {
    // Speak the question, then start voice recognition
    speak('Tell me the skills or course required.', startVoiceRecognition);
});

// Function to read the course titles aloud
function readCourseTitles(titles) {
    if (titles.length === 0) return; // Exit if no titles to read

    const speech = new SpeechSynthesisUtterance();
    speech.text = `The Recommended courses are: ${titles.join(', ')}`;
    speech.lang = 'en-US';
    speech.volume = 1; // Volume (0 to 1)
    speech.rate = 1;   // Speed of speech (0.1 to 10)
    speech.pitch = 1;  // Pitch of speech (0 to 2)

    window.speechSynthesis.speak(speech);
}

// Function to open the course modal with detailed information
function openCourseModal(title, description, rating, duration, level, type, url) {
    // Populate the modal with course details
    document.getElementById('course-title').innerText = title;
    document.getElementById('course-description').innerHTML = `<strong>Course Description:</strong> ${description}`;
    document.getElementById('course-rating').innerHTML = `<strong>Rating:</strong> ${rating}`;
    document.getElementById('course-duration').innerHTML = `<strong>Duration:</strong> ${duration}`;
    document.getElementById('course-level').innerHTML = `<strong>Level:</strong> ${level}`;
    document.getElementById('course-type').innerHTML = `<strong>Type:</strong> ${type}`;
    document.getElementById('course-url').querySelector('a').href = url;

    // Display the modal
    document.getElementById('courseModal').style.display = 'block';
}

// Function to close the modal
function closeModal() {
    document.getElementById('courseModal').style.display = 'none';
}

function displayRecommendations(recommendations) {
    const resultsDiv = document.getElementById("results");
    resultsDiv.innerHTML = ''; // Clear previous results

    recommendations.forEach(course => {
        const courseElement = document.createElement("div");
        courseElement.classList.add("course-card");
        courseElement.innerHTML = `
            <h3>${course['course title']}</h3>
            <button class="view-btn" onclick="openCourseModal(
                '${course['course title']}', 
                '${course['course description']}', 
                '${course['Ratings']}', 
                '${course['duration']}', 
                '${course['course level']}', 
                '${course['Course Type']}', 
                '${course['course url']}'
            )">View Course</button>
        `;
        resultsDiv.appendChild(courseElement);
    });
}

// Function to detect page refresh and stop the voice assistant
window.onbeforeunload = () => {
    window.speechSynthesis.cancel(); // Stop the voice assistant on refresh
    isSpeaking = false; // Stop the speaking state
}

// Function to handle "Go to Course" click
function goToCourse(url) {
    // Stop the voice assistant before redirecting to the course
    window.speechSynthesis.cancel(); // Stop ongoing speech
    isSpeaking = false; // Stop the speaking state

    // Redirect to the course URL
    window.location.href = url;
}
