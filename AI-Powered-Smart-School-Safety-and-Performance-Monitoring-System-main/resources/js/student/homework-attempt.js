/**
 * Student Homework Attempt JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeMCQOptions();
    initializeTextareas();
    initializeNavigation();
    initializeSubmission();
    updateProgress();
});

// Handle MCQ option selection
function initializeMCQOptions() {
    document.querySelectorAll('.mcq-option').forEach(option => {
        option.addEventListener('click', function() {
            const questionIdx = this.dataset.question;
            const parent = this.closest('.mcq-options');
            
            // Deselect all options in this question
            parent.querySelectorAll('.mcq-option').forEach(opt => opt.classList.remove('selected'));
            
            // Select this option
            this.classList.add('selected');
            this.querySelector('input').checked = true;
            
            // Mark question card as answered
            document.getElementById(`question-${questionIdx}`).classList.add('answered');
            
            updateProgress();
        });
    });
}

// Handle textarea inputs
function initializeTextareas() {
    document.querySelectorAll('.answer-input').forEach(textarea => {
        textarea.addEventListener('input', function() {
            const questionIdx = this.dataset.question;
            const card = document.getElementById(`question-${questionIdx}`);
            
            if (this.value.trim()) {
                card.classList.add('answered');
            } else {
                card.classList.remove('answered');
            }
            
            updateProgress();
        });
    });
}

// Initialize question navigation buttons
function initializeNavigation() {
    document.querySelectorAll('.question-nav-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            document.getElementById(targetId).scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });
}

// Initialize submission functionality
function initializeSubmission() {
    const submitBtn = document.getElementById('submitHomeworkBtn');
    const saveBtn = document.getElementById('saveProgressBtn');
    const confirmBtn = document.getElementById('confirmSubmitBtn');
    const modal = new bootstrap.Modal(document.getElementById('confirmSubmitModal'));

    submitBtn.addEventListener('click', function() {
        const answered = getAnsweredCount();
        document.getElementById('modalAnsweredCount').textContent = answered;
        modal.show();
    });

    confirmBtn.addEventListener('click', async function() {
        modal.hide();
        await submitHomework();
    });

    saveBtn.addEventListener('click', async function() {
        await saveProgress();
    });
}

// Get count of answered questions
function getAnsweredCount() {
    return document.querySelectorAll('.question-card.answered').length;
}

// Update progress bar and navigation buttons
function updateProgress() {
    const answered = getAnsweredCount();
    const percentage = (answered / totalQuestions) * 100;
    
    document.getElementById('progressBar').style.width = `${percentage}%`;
    document.getElementById('answeredCount').textContent = answered;
    
    // Update navigation buttons
    document.querySelectorAll('.question-nav-btn').forEach((btn, idx) => {
        const card = document.getElementById(`question-${idx}`);
        if (card && card.classList.contains('answered')) {
            btn.classList.add('answered');
        } else {
            btn.classList.remove('answered');
        }
    });
}

// Collect all answers
function collectAnswers() {
    const answers = [];
    
    for (let i = 0; i < totalQuestions; i++) {
        const card = document.getElementById(`question-${i}`);
        let answer = '';
        
        // Check for MCQ
        const selectedOption = card.querySelector('.mcq-option.selected input');
        if (selectedOption) {
            answer = selectedOption.value;
        }
        
        // Check for textarea
        const textarea = card.querySelector('.answer-input');
        if (textarea) {
            answer = textarea.value.trim();
        }
        
        answers.push({
            question_idx: i,
            answer: answer
        });
    }
    
    return answers;
}

// Submit homework
async function submitHomework() {
    const answers = collectAnswers();
    
    try {
        showLoading('Submitting homework...');
        
        const response = await fetch(submitUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ answers })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Homework submitted successfully!');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showError(data.error || 'Failed to submit homework');
        }
    } catch (error) {
        console.error('Submit error:', error);
        showError('An error occurred while submitting');
    }
}

// Save progress
async function saveProgress() {
    const answers = collectAnswers();
    
    try {
        const response = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ answers })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Progress saved!');
        } else {
            showError(data.error || 'Failed to save progress');
        }
    } catch (error) {
        console.error('Save error:', error);
        showError('An error occurred while saving');
    }
}

// Helper functions for notifications
function showLoading(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    }
}

function showSuccess(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    } else {
        alert(message);
    }
}

function showError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    } else {
        alert(message);
    }
}

