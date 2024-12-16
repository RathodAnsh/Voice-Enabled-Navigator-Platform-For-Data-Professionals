document.addEventListener('DOMContentLoaded', function() {
    // Form toggling
    const showRegister = document.getElementById('show-register');
    const showLogin = document.getElementById('show-login');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    if (showRegister && showLogin) {
        showRegister.addEventListener('click', function(event) {
            event.preventDefault();
            if (loginForm) loginForm.classList.remove('active-form');
            if (registerForm) registerForm.classList.add('active-form');
        });
    
        showLogin.addEventListener('click', function(event) {
            event.preventDefault();
            if (registerForm) registerForm.classList.remove('active-form');
            if (loginForm) loginForm.classList.add('active-form');
        });
    }
    
    const passwordInput = document.getElementById('password-input');
    const charLengthCheckbox = document.getElementById('char-length-checkbox');
    const capitalLetterCheckbox = document.getElementById('capital-letter-checkbox');
    const specialCharCheckbox = document.getElementById('special-char-checkbox');
    const numberCheckbox = document.getElementById('number-checkbox');
    const errorMessage = document.getElementById('error-message');

    const validatePassword = () => {
        const value = passwordInput.value;
        const lengthValid = value.length >= 8 && value.length <= 15;
        const capitalValid = /[A-Z]/.test(value);
        const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(value);
        const numberValid = /[0-9]/.test(value);

        charLengthCheckbox.checked = lengthValid;
        capitalLetterCheckbox.checked = capitalValid;
        specialCharCheckbox.checked = specialValid;
        numberCheckbox.checked = numberValid;

        // Hide the error message when the criteria are valid
        if (lengthValid && capitalValid && specialValid && numberValid) {
            errorMessage.style.display = 'none';
        } else {
            errorMessage.style.display = 'block';
        }
    };

    if (passwordInput) {
        passwordInput.addEventListener('input', validatePassword);
    }

    // Add event listener to the register form submission
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const value = passwordInput.value;
            const lengthValid = value.length >= 8 && value.length <= 15;
            const capitalValid = /[A-Z]/.test(value);
            const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(value);
            const numberValid = /[0-9]/.test(value);

            if (!(lengthValid && capitalValid && specialValid && numberValid)) {
                event.preventDefault(); // Prevent form submission
                errorMessage.style.display = 'block'; // Show error message
            }
        });
    }

    // Voice Assistant Integration for salary
    const voiceAssistantBtn = document.getElementById('voice-assistant-btn');
    const fields = [
        { id: 'education_level', question: 'What is your education qualification?' },
        { id: 'experience', question: 'Are you experienced or a fresher?' },
        { id: 'company_name', question: 'What is the name of your company?' },
        { id: 'job_title', question: 'What was your job title?' },
    ];

    // Voice Assistant Integration for job
    const voiceAssistantBtnJob = document.getElementById('voice-btn');
    const jobFields = [
        { id: 'qualification', question: 'What is your education qualification?', type: 'text' },
        { id: 'years-experience', question: 'What is your years of experience?', type: 'text' },
        { id: 'job-title', question: 'What is your Job Title?', type: 'text' },
        { id: 'expected-salary', question: 'What should be your expected salary?', type: 'text' },
        { id: 'location', question: 'What is your preferred job location?', type: 'text' },
        { id: 'skills', question: 'What are your skills?', type: 'text' }
    ];

    let currentFieldIndex = 0;
    let currentFieldIndexJob = 0;

    if (voiceAssistantBtn) {
        voiceAssistantBtn.addEventListener('click', function() {
            console.log('Voice Assistant button clicked for salary');
            askNextQuestion();
        });
    } else {
        console.error('Voice Assistant button not found in the DOM for salary.');
    }

    if (voiceAssistantBtnJob) {
        voiceAssistantBtnJob.addEventListener('click', function() {
            console.log('Voice Assistant button clicked for job');
            askNextQuestionJob();
        });
    } else {
        console.error('Voice Assistant button not found in the DOM for job.');
    }

    function askNextQuestion() {
        if (currentFieldIndex < fields.length) {
            const field = fields[currentFieldIndex];
            console.log(`Asking question: ${field.question}`);
            speak(field.question, () => {
                if (field.id === 'experience') {
                    handleExperienceInput('salary');
                } else {
                    recognizeSpeech(field.id, () => {
                        currentFieldIndex++;
                        askNextQuestion();
                    });
                }
            });
        } else {
            console.log('All questions asked for salary. Enabling fields.');
            enableFields(fields);
        }
    }

    function askNextQuestionJob() {
        if (currentFieldIndexJob < jobFields.length) {
            const field = jobFields[currentFieldIndexJob];
            console.log(`Asking question: ${field.question}`);
            speak(field.question, () => {
                if (field.id === 'experience') {
                    handleExperienceInput('job');
                } else if (field.id === 'job_title' && field.type === 'text') {
                    recognizeSpeech(field.id, () => {
                        currentFieldIndexJob++;
                        askNextQuestionJob();
                    });
                } else {
                    recognizeSpeech(field.id, () => {
                        currentFieldIndexJob++;
                        askNextQuestionJob();
                    });
                }
            });
        } else {
            console.log('All questions asked for job. Enabling fields.');
            enableFields(jobFields);
        }
    }

    function handleExperienceInput(context) {
        const experienceTextInput = document.getElementById('experience-select');
        const yearsExperienceInput = document.getElementById('min_experience');
    
        if (!('SpeechRecognition' in window || 'webkitSpeechRecognition' in window)) {
            console.error('Speech recognition not supported in this browser.');
            alert('Speech recognition not supported in this browser.');
            return;
        }
    
        // Ensure the years experience input is visible initially
        if (yearsExperienceInput) {
            yearsExperienceInput.style.display = 'inline'; // Make sure it's visible
        }
    
        // If there's already a value in the input, move to the next question
        if (yearsExperienceInput && yearsExperienceInput.value) {
            if (context === 'salary') {
                currentFieldIndex++;
                askNextQuestion();
            } else {
                currentFieldIndexJob++;
                askNextQuestionJob();
            }
            return;
        }
    
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.lang = 'en-US'; // Set language to Indian English
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;
    
        recognition.onresult = (event) => {
            const speechResult = event.results[0][0].transcript.toLowerCase();
            const numericValue = convertWordsToNumber(speechResult); // Convert spoken input to a number
            console.log(`Recognized speech result: ${speechResult}`);
    
            if (speechResult.includes('fresher')) {
                if (experienceTextInput) {
                    experienceTextInput.value = 'Fresher'; // Set experience text to "Fresher"
                }
                if (yearsExperienceInput) {
                    yearsExperienceInput.disabled = false; // Enable input for fresher
                    yearsExperienceInput.style.display = 'inline'; // Ensure the field is visible
                    yearsExperienceInput.value = '0'; // Clear any previous value
                    yearsExperienceInput.focus(); // Focus on the input field
    
                    // Add input event listener to restrict input to 0 only
                    yearsExperienceInput.addEventListener('input', function enforceZeroInput(e) {
                        if (e.target.value !== '0') {
                            e.target.value = '0'; // Enforce 0
                        }
                    });
    
                    // Add blur event listener to move to the next question after 0 is entered
                    yearsExperienceInput.addEventListener('blur', function onExperienceBlur() {
                        if (yearsExperienceInput.value === '0') {
                            if (context === 'salary') {
                                currentFieldIndex++;
                                askNextQuestion();
                            } else {
                                currentFieldIndexJob++;
                                askNextQuestionJob();
                            }
    
                            // Remove event listeners to avoid duplicate events
                            yearsExperienceInput.removeEventListener('input', enforceZeroInput);
                            yearsExperienceInput.removeEventListener('blur', onExperienceBlur);
                        }
                    });
                }
            } else if (speechResult.includes('experience')) {
                if (experienceTextInput) {
                    experienceTextInput.value = 'Experience'; // Set experience text
                }
                if (yearsExperienceInput) {
                    yearsExperienceInput.disabled = false; // Enable manual input
                    yearsExperienceInput.style.display = 'inline'; // Show the input field
                    yearsExperienceInput.value = ''; // Clear any previous value
                    yearsExperienceInput.focus(); // Focus on the input field
    
                    // Add an event listener for manual input
                    yearsExperienceInput.addEventListener('input', function onExperienceInput() {
                        if (yearsExperienceInput.value.trim() !== '') {
                            // After user enters a value, move to the next question
                            if (context === 'salary') {
                                currentFieldIndex++;
                                askNextQuestion();
                            } else {
                                currentFieldIndexJob++;
                                askNextQuestionJob();
                            }
    
                            // Remove event listener after input is detected
                            yearsExperienceInput.removeEventListener('input', onExperienceInput);
                        }
                    });
                }
            } else {
                console.log('Speech result did not match "fresher" or "experience"');
            }
        };
    
        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            alert('Speech recognition error: ' + event.error);
        };
    
        recognition.onspeechend = () => {
            recognition.stop();
            console.log('Speech recognition stopped.');
        };
    
        recognition.start();
    }

    function convertWordsToNumber(words) {
        const wordToNumberMap = {
            'zero': 0, 'one': 1, 'two': 2, 'three': 3, 'four': 4, 'five': 5,
            'six': 6, 'seven': 7, 'eight': 8, 'nine': 9, 'ten': 10,
            'eleven': 11, 'twelve': 12, 'thirteen': 13, 'fourteen': 14,
            'fifteen': 15, 'sixteen': 16, 'seventeen': 17, 'eighteen': 18,
            'nineteen': 19, 'twenty': 20, 'thirty': 30, 'forty': 40,
            'fifty': 50, 'sixty': 60, 'seventy': 70, 'eighty': 80,
            'ninety': 90, 'hundred': 100
        };

        const wordsArray = words.split(' ');
        let number = 0;

        wordsArray.forEach(word => {
            if (wordToNumberMap[word] !== undefined) {
                number += wordToNumberMap[word];
            }
        });

        return isNaN(number) ? null : number;
    }
    
    
    function enableFields(fields) {
        fields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.disabled = false;
            }
        });
    }
    
    function speak(text, callback) {
        if (!('speechSynthesis' in window)) {
            console.error('Text-to-speech not supported in this browser.');
            alert('Text-to-speech not supported in this browser.');
            return;
        }

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        utterance.onend = function() {
            if (callback) {
                callback();
            }
        };

        window.speechSynthesis.speak(utterance);
    }

    function recognizeSpeech(fieldId, callback) {
        if (!('SpeechRecognition' in window || 'webkitSpeechRecognition' in window)) {
            console.error('Speech recognition not supported in this browser.');
            alert('Speech recognition not supported in this browser.');
            return;
        }

        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.lang = 'en-US';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        recognition.onresult = (event) => {
            const speechResult = event.results[0][0].transcript;
            const inputElement = document.getElementById(fieldId);
            if (inputElement) {
                inputElement.value = speechResult;
            }
            if (callback) {
                callback();
            }
        };

        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            alert('Speech recognition error: ' + event.error);
        };

        recognition.onspeechend = () => {
            recognition.stop();
            console.log('Speech recognition stopped.');
        };

        recognition.start();
    }

    // Smooth scrolling effect for links
const aboutUsLink = document.querySelector('a[href="#about-us"]');
const servicesLink = document.querySelector('a[href="#services"]');
const homeLink = document.querySelector('a[href="#home"]');
const feedbackLink = document.querySelector('a[href="#userExperienceSection"]');

const headerHeight = document.querySelector('header')?.offsetHeight || 0;

function smoothScrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        window.scrollTo({
            top: section.offsetTop - headerHeight,
            behavior: 'smooth'
        });
    }
}

if (aboutUsLink) {
    aboutUsLink.addEventListener('click', function(event) {
        event.preventDefault();
        smoothScrollToSection('about-us');
    });
}

if (servicesLink) {
    servicesLink.addEventListener('click', function(event) {
        event.preventDefault();
        smoothScrollToSection('services');
    });
}

if (homeLink) {
    homeLink.addEventListener('click', function(event) {
        event.preventDefault();
        smoothScrollToSection('home');
    });
}

if (feedbackLink) {
    feedbackLink.addEventListener('click', function(event) {
        event.preventDefault();
        smoothScrollToSection('userExperienceSection');
    });
}


     // Redirect to login/signup page on click
        loginButton.addEventListener('click', function() {
            window.location.href = 'login.html';
        });
    })
        // User Experience Section
        document.addEventListener('DOMContentLoaded', () => {
        console.log('Document loaded');
    
        const userExperienceSection = document.getElementById('feedback');
        const experienceInput = document.getElementById('experience'); // Feedback textarea or input
        const overlay = document.querySelector('.overlay'); // The overlay element

        if (userExperienceSection.classList.contains('disabled-section')) {
            // Ensure the overlay is visible and blocks interaction
            overlay.style.visibility = 'visible';
            experienceInput.setAttribute('disabled', 'disabled'); // Disable the feedback input
        } else {
            // Enable the feedback section
            overlay.style.visibility = 'hidden'; // Hide the overlay
            experienceInput.removeAttribute('disabled'); // Enable the feedback input
            experienceInput.style.pointerEvents = 'auto'; // Allow pointer events
            experienceInput.style.opacity = '1'; // Ensure opacity is set to full
        }
    });

    document.getElementById('purpose').addEventListener('change', function() {
        var jobTitleCheckboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');
        
        if (this.value === 'Course Recommendation') {
            // Disable all job title checkboxes when 'Course Recommendation' is selected
            jobTitleCheckboxes.forEach(function(checkbox) {
                checkbox.disabled = true;
                checkbox.checked = false; // Uncheck checkboxes when disabled
            });
        } else {
            // Enable all job title checkboxes for other purposes
            jobTitleCheckboxes.forEach(function(checkbox) {
                checkbox.disabled = false;
            });
        }
    });
    
    

    // Show sections on scroll with smooth transition
    // const aboutUsSection = document.getElementById('about-us');
    // const servicesSection = document.getElementById('services');
    // const feedbackSection = document.getElementById('feedback');

    // window.addEventListener('scroll', function() {
    //     const screenPosition = window.innerHeight / 1.3;

    //     if (aboutUsSection && aboutUsSection.getBoundingClientRect().top < screenPosition) {
    //         aboutUsSection.classList.add('visible');
    //     }

    //     if (servicesSection && servicesSection.getBoundingClientRect().top < screenPosition) {
    //         servicesSection.classList.add('visible');
    //     }

    //     if (feedbackSection && feedbackSection.getBoundingClientRect().top < screenPosition) {
    //         feedbackSection.classList.add('visible');
    //     }
    // });
    
    document.addEventListener("DOMContentLoaded", function () {

        const animatedText = document.getElementById("animatedText");
        let hasAnimated = false;
    
        const animateText = () => {
            if (!hasAnimated) {
                animatedText.classList.add("animate");
                hasAnimated = true;
                setTimeout(() => {
                    animatedText.classList.remove("animate");
                    hasAnimated = false;
                }, 3000);
            }
        };
    
        // Trigger the animation when the page is loaded
        animateText();
    
        // Trigger the animation when scrolling back to the top
        window.addEventListener('scroll', function () {
            if (window.scrollY === 0) {
                animateText();
            }
        });
    
        // Trigger the animation when switching back to the tab
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                animateText();
            }
        });
    });
    
    document.getElementById('profileBtn').addEventListener('click', function(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('logoutDropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });
    
    // Close the dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('logoutDropdown');
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        }
    });
    
    // Prevent the dropdown from closing when clicking inside the dropdown
    document.getElementById('logoutDropdown').addEventListener('click', function(event) {
        event.stopPropagation();
    });

    // Feedback form submission
    function submitFeedback(event) {
        event.preventDefault();
        const purposeSelect = document.getElementById('purpose');
        const feedbackInput = document.getElementById('feedback');
        const feedbackMessage = document.getElementById('feedbackMessage');

        const purpose = purposeSelect?.value.trim();
        const feedbackText = feedbackInput?.value.trim();

        if (purpose && feedbackText) {
            feedbackMessage.textContent = 'Feedback successfully submitted!';
            if (purposeSelect) purposeSelect.value = '';
            if (feedbackInput) feedbackInput.value = '';
        } else {
        }
    }

    const feedbackForm = document.querySelector('.feedback-form');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', submitFeedback);
    }

    

    // Show the pop-up when the section is hovered
//     document.querySelector('.disabled-section').addEventListener('mouseenter', function() {
//     document.getElementById('popup-message').style.display = 'block';
// });

// // Hide the pop-up when the section is not hovered
// document.querySelector('.disabled-section').addEventListener('mouseleave', function() {
//     document.getElementById('popup-message').style.display = 'none';
// });
