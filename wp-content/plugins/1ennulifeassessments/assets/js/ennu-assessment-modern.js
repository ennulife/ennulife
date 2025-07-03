/* jshint esversion: 6 */
/**
 * ENNU Life Modern Assessment JavaScript
 * Handles click-to-select interface, auto-progression, and smooth animations
 * 
 * Browser Compatibility: ES6+ (Chrome 51+, Firefox 54+, Safari 10+, Edge 15+)
 * For IE11 support, transpile this file to ES5 using Babel
 */

(function($) {
    'use strict';
    
    // Modern Assessment Controller
    window.ENNUModernAssessment = {
        
        currentStep: 1,
        totalSteps: 0,
        assessmentData: {},
        autoProgressDelay: 1500, // 1.5 seconds
        autoProgressTimer: null,
        
        // Initialize the modern assessment
        init: function() {
            this.bindEvents();
            this.initializeAssessment();
            this.updateProgress();
            console.log('ENNU Modern Assessment initialized');
        },
        
        // Bind all event handlers
        bindEvents: function() {
            // Answer option selection
            $(document).on('click', '.ennu-answer-option', this.handleAnswerSelection.bind(this));
            
            // Navigation buttons
            $(document).on('click', '.ennu-nav-btn.next', this.nextStep.bind(this));
            $(document).on('click', '.ennu-nav-btn.prev', this.prevStep.bind(this));
            $(document).on('click', '.ennu-nav-btn.submit', this.submitAssessment.bind(this));
            
            // Keyboard navigation
            $(document).on('keydown', this.handleKeyboardNavigation.bind(this));
        },
        
        // Initialize assessment settings
        initializeAssessment: function() {
            console.log('Initializing assessment...');
            
            // Count total steps
            this.totalSteps = $('.ennu-question-step').length;
            console.log('Total steps found:', this.totalSteps);
            
            // Reset to first step
            this.currentStep = 1;
            
            // Ensure all steps are hidden except first
            $('.ennu-question-step').removeClass('active').hide();
            
            // Show first step
            this.showStep(1);
            
            console.log('Assessment initialized with', this.totalSteps, 'steps');
            
            // Update step counter
            $('#currentStep').text(this.currentStep);
            $('#totalSteps').text(this.totalSteps);
        },
        
        // Handle answer selection
        handleAnswerSelection: function(e) {
            e.preventDefault();
            
            var $option = $(e.currentTarget);
            var $question = $option.closest('.ennu-question-step');
            var questionType = $question.data('question-type') || 'single';
            var questionKey = $question.data('question-key');
            var answerValue = $option.data('value');
            
            // Handle different question types
            if (questionType === 'single') {
                // Single choice - clear other selections
                $question.find('.ennu-answer-option').removeClass('selected');
                $option.addClass('selected');
                
                // Store answer
                this.assessmentData[questionKey] = answerValue;
                
                // Auto-progress after delay
                this.startAutoProgress();
                
            } else if (questionType === 'multiple') {
                // Multiple choice - toggle selection
                $option.toggleClass('selected');
                
                // Collect all selected values
                var selectedValues = [];
                $question.find('.ennu-answer-option.selected').each(function() {
                    selectedValues.push($(this).data('value'));
                });
                
                this.assessmentData[questionKey] = selectedValues;
                
                // Show next button for multiple choice
                this.showNavigationButtons();
            }
            
            // Add selection animation
            this.animateSelection($option);
        },
        
        // Start auto-progression timer
        startAutoProgress: function() {
            // Clear existing timer
            if (this.autoProgressTimer) {
                clearTimeout(this.autoProgressTimer);
            }
            
            // Show auto-progress indicator
            this.showAutoProgressIndicator();
            
            // Set timer for auto-progression
            this.autoProgressTimer = setTimeout(() => {
                this.nextStep();
            }, this.autoProgressDelay);
        },
        
        // Show auto-progress indicator
        showAutoProgressIndicator: function() {
            var $indicator = $('.ennu-auto-progress');
            if ($indicator.length === 0) {
                $indicator = $(`
                    <div class="ennu-auto-progress">
                        <div class="ennu-auto-progress-text">Moving to next question...</div>
                        <div class="ennu-auto-progress-bar">
                            <div class="ennu-auto-progress-fill"></div>
                        </div>
                    </div>
                `);
                $('.ennu-question-step.active').append($indicator);
            }
            
            $indicator.show();
        },
        
        // Hide auto-progress indicator
        hideAutoProgressIndicator: function() {
            $('.ennu-auto-progress').hide();
        },
        
        // Animate selection
        animateSelection: function($option) {
            // Add pulse animation
            $option.addClass('pulse-animation');
            
            setTimeout(() => {
                $option.removeClass('pulse-animation');
            }, 600);
        },
        
        // Show navigation buttons
        showNavigationButtons: function() {
            var $navigation = $('.ennu-navigation');
            if ($navigation.length === 0) {
                $navigation = $(`
                    <div class="ennu-navigation">
                        <button type="button" class="ennu-nav-btn secondary prev" style="display: none;">Previous</button>
                        <button type="button" class="ennu-nav-btn primary next">Next</button>
                    </div>
                `);
                $('.ennu-question-step.active').append($navigation);
            }
            
            // Show/hide appropriate buttons
            if (this.currentStep > 1) {
                $navigation.find('.prev').show();
            }
            
            if (this.currentStep === this.totalSteps) {
                $navigation.find('.next').text('Complete Assessment').addClass('submit');
            }
        },
        
        // Move to next step
        nextStep: function() {
            console.log('nextStep called. Current step:', this.currentStep, 'Total steps:', this.totalSteps);
            
            // Validate current step before progression
            if (!this.validateCurrentStep()) {
                console.log('Validation failed, cannot proceed to next step');
                return;
            }
            
            if (this.currentStep < this.totalSteps) {
                this.hideAutoProgressIndicator();
                this.currentStep++;
                console.log('Moving to step:', this.currentStep);
                this.showStep(this.currentStep);
                this.updateProgress();
            } else {
                console.log('Assessment complete, submitting...');
                this.submitAssessment();
            }
        },
        
        // Validate current step has required selections
        validateCurrentStep: function() {
            var $currentStep = $('.ennu-question-step.active');
            var $selectedOption = $currentStep.find('.ennu-answer-option.selected');
            
            if ($selectedOption.length === 0) {
                this.showValidationError('Please select an answer before continuing.');
                return false;
            }
            
            return true;
        },
        
        // Show validation error message
        showValidationError: function(message) {
            // Remove any existing error messages
            $('.ennu-validation-error').remove();
            
            // Add error message
            var $error = $('<div class="ennu-validation-error" style="color: #e74c3c; margin: 10px 0; padding: 10px; background: #fdf2f2; border: 1px solid #e74c3c; border-radius: 4px;">' + message + '</div>');
            $('.ennu-question-step.active').prepend($error);
            
            // Auto-hide after 3 seconds
            setTimeout(function() {
                $error.fadeOut(300, function() {
                    $error.remove();
                });
            }, 3000);
        },
        
        // Move to previous step
        prevStep: function() {
            if (this.currentStep > 1) {
                this.hideAutoProgressIndicator();
                this.currentStep--;
                this.showStep(this.currentStep);
                this.updateProgress();
            }
        },
                // Show specific step
        showStep: function(stepNumber) {
            console.log('showStep called with stepNumber:', stepNumber);
            
            // Hide all steps first
            $('.ennu-question-step').removeClass('active').hide();
            
            // Find target step - try multiple selectors for compatibility
            var $targetStep = $(`.ennu-question-step[data-step="${stepNumber}"]`);
            
            // Fallback: if data-step selector fails, use nth-child
            if ($targetStep.length === 0) {
                $targetStep = $('.ennu-question-step').eq(stepNumber - 1);
                console.log('Using fallback selector for step:', stepNumber);
            }
            
            console.log('Target step found:', $targetStep.length, 'elements');
            
            if ($targetStep.length > 0) {
                // Show target step with animation
                $targetStep.addClass('active').fadeIn(300);
                
                // Update navigation for this step
                this.updateNavigation();
                
                console.log('Successfully showed step:', stepNumber);
            } else {
                console.error('Could not find step:', stepNumber);
                // Fallback to first step
                $('.ennu-question-step').first().addClass('active').show();
            }
            
            // Smooth scroll to assessment container
            $('html, body').animate({
                scrollTop: $('.ennu-assessment-container').offset().top - 50
            }, 500);
        },
        
        // Update progress bar
        updateProgress: function() {
            var progress = (this.currentStep / this.totalSteps) * 100;
            $('.ennu-progress-fill').css('width', progress + '%');
            $('#currentStep').text(this.currentStep);
        },
        
        // Update navigation buttons
        updateNavigation: function() {
            var $navigation = $('.ennu-navigation');
            var $prevBtn = $navigation.find('.prev');
            var $nextBtn = $navigation.find('.next');
            
            // Show/hide previous button
            if (this.currentStep > 1) {
                $prevBtn.show();
            } else {
                $prevBtn.hide();
            }
            
            // Update next button text
            if (this.currentStep === this.totalSteps) {
                $nextBtn.text('Complete Assessment').removeClass('next').addClass('submit');
            } else {
                $nextBtn.text('Next').removeClass('submit').addClass('next');
            }
        },
        
        // Handle keyboard navigation
        handleKeyboardNavigation: function(e) {
            switch(e.keyCode) {
                case 37: // Left arrow - previous
                    if (this.currentStep > 1) {
                        this.prevStep();
                    }
                    break;
                case 39: // Right arrow - next
                    if (this.currentStep < this.totalSteps) {
                        this.nextStep();
                    }
                    break;
                case 13: // Enter - select first option or next
                    var $firstOption = $('.ennu-question-step.active .ennu-answer-option').first();
                    if ($firstOption.length && !$firstOption.hasClass('selected')) {
                        $firstOption.click();
                    } else {
                        this.nextStep();
                    }
                    break;
            }
        },
        
        // Submit assessment
        submitAssessment: function() {
            // Show loading state
            this.showLoadingState();
            
            // Prepare form data
            var formData = new FormData();
            var self = this; // Store reference to avoid scope issues
            
            // Helper function to append array values (extracted to avoid function in loop)
            function appendArrayValue(key, value, index) {
                formData.append(key + '[' + index + ']', value);
            }
            
            // Add assessment data
            for (var key in this.assessmentData) {
                if (Array.isArray(this.assessmentData[key])) {
                    this.assessmentData[key].forEach(appendArrayValue.bind(null, key));
                } else {
                    formData.append(key, this.assessmentData[key]);
                }
            }
            
            // Add required AJAX parameters
            formData.append('action', 'ennu_form_submit');
            formData.append('nonce', ennuAjax.nonce);
            formData.append('assessment_type', this.getAssessmentType());
            
            // Submit via AJAX
            $.ajax({
                url: ennuAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: this.handleSubmissionSuccess.bind(this),
                error: this.handleSubmissionError.bind(this)
            });
        },
        
        // Get assessment type from URL or data attribute
        getAssessmentType: function() {
            var url = window.location.href;
            if (url.includes('ed-treatment')) return 'ed-treatment-assessment';
            if (url.includes('weight-loss')) return 'weight-loss-assessment';
            if (url.includes('skin-assessment')) return 'skin-assessment-enhanced';
            if (url.includes('hair-restoration')) return 'hair-restoration-assessment';
            if (url.includes('hair-assessment')) return 'hair-assessment';
            if (url.includes('hormone')) return 'hormone-assessment';
            if (url.includes('health')) return 'health-assessment';
            return 'general-assessment';
        },
        
        // Show loading state
        showLoadingState: function() {
            var $submitBtn = $('.ennu-nav-btn.submit');
            $submitBtn.prop('disabled', true);
            $submitBtn.html('<span class="ennu-loading"></span> Processing...');
        },
        
        // Handle successful submission
        handleSubmissionSuccess: function(response) {
            if (response.success) {
                this.showResults(response.data);
            } else {
                this.showError(response.data.message || 'An error occurred');
            }
        },
        
        // Handle submission error
        handleSubmissionError: function(xhr, status, error) {
            console.error('Assessment submission error:', error);
            this.showError('Network error. Please check your connection and try again.');
        },
        
        // Show assessment results
        showResults: function(data) {
            // Hide assessment questions
            $('.ennu-question-step').hide();
            $('.ennu-progress-wrapper').hide();
            
            // Show results section
            var $results = $('.ennu-results-section');
            if ($results.length === 0) {
                $results = this.createResultsSection(data);
                $('.ennu-assessment-container').append($results);
            }
            
            $results.show();
            
            // Scroll to results
            $('html, body').animate({
                scrollTop: $results.offset().top - 50
            }, 500);
        },
        
        // Create results section
        createResultsSection: function(data) {
            var resultsHtml = `
                <div class="ennu-results-section">
                    <h2 class="ennu-results-title">Your Assessment Results</h2>
                    ${data.score ? `<div class="ennu-score-display">${data.score}</div>` : ''}
                    <div class="ennu-results-content">
                        ${data.message ? `<p class="ennu-message success">${data.message}</p>` : ''}
                        ${data.recommendations ? this.formatRecommendations(data.recommendations) : ''}
                        ${data.next_steps ? `<div class="ennu-next-steps"><h3>Next Steps</h3><p>${data.next_steps}</p></div>` : ''}
                    </div>
                </div>
            `;
            
            return $(resultsHtml);
        },
        
        // Format recommendations
        formatRecommendations: function(recommendations) {
            if (!Array.isArray(recommendations)) return '';
            
            var html = '<div class="ennu-recommendations"><h3>Recommendations</h3><ul>';
            recommendations.forEach(function(rec) {
                html += `<li>${rec}</li>`;
            });
            html += '</ul></div>';
            
            return html;
        },
        
        // Show error message
        showError: function(message) {
            var $error = $(`<div class="ennu-message error">${message}</div>`);
            $('.ennu-question-step.active').prepend($error);
            
            // Remove error after 5 seconds
            setTimeout(() => {
                $error.fadeOut(() => $error.remove());
            }, 5000);
            
            // Reset submit button
            var $submitBtn = $('.ennu-nav-btn.submit');
            $submitBtn.prop('disabled', false);
            $submitBtn.text('Complete Assessment');
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.ennu-modern-assessment').length > 0) {
            ENNUModernAssessment.init();
        }
    });
    
})(jQuery);

// Add CSS for pulse animation
const pulseCSS = `
    .pulse-animation {
        animation: pulse 0.6s ease-in-out;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
`;

// Inject CSS
const style = document.createElement('style');
style.textContent = pulseCSS;
document.head.appendChild(style);

