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
            $(document).on('click', '.nav-btn.next-btn', this.nextStep.bind(this));
            $(document).on('click', '.nav-btn.prev-btn', this.prevStep.bind(this));
            $(document).on('click', '.submit-assessment-btn', this.submitAssessment.bind(this));
            
            // DOB dropdown changes
            $(document).on('change', '.dob-dropdown', this.handleDOBChange.bind(this));
            
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
                // Single choice - clear other selections first
                $question.find('.ennu-answer-option').removeClass('selected');
                
                // Select this option
                $option.addClass('selected');
                $option.find('input[type="radio"]').prop('checked', true);
                
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
            var $currentStep = $(".ennu-question-step.active");
            
            // Check for DOB dropdowns first
            var $dobDropdowns = $currentStep.find('.dob-dropdown');
            if ($dobDropdowns.length > 0) {
                var $monthSelect = $currentStep.find('.dob-month');
                var $daySelect = $currentStep.find('.dob-day');
                var $yearSelect = $currentStep.find('.dob-year');
                
                var month = $monthSelect.val();
                var day = $daySelect.val();
                var year = $yearSelect.val();
                
                if (month && day && year) {
                    return true; // DOB is complete
                } else {
                    this.showValidationError("Please complete your date of birth before continuing.");
                    return false;
                }
            }
            
            // Check for contact information form
            var $contactForm = $currentStep.find('.contact-info-form');
            if ($contactForm.length > 0) {
                console.log('Contact form detected, validating fields...');
                var $requiredFields = $contactForm.find('input[required]');
                var allFieldsFilled = true;
                var emptyFieldName = '';
                
                console.log('Found', $requiredFields.length, 'required fields');
                
                $requiredFields.each(function() {
                    var $field = $(this);
                    var value = $field.val().trim();
                    var fieldName = $field.attr('name') || $field.attr('id') || 'Unknown field';
                    
                    console.log('Checking field:', fieldName, 'Value:', value);
                    
                    if (!value) {
                        allFieldsFilled = false;
                        emptyFieldName = $field.prev('label').text() || fieldName;
                        console.log('Empty field found:', emptyFieldName);
                        return false; // Break out of each loop
                    }
                });
                
                if (allFieldsFilled) {
                    console.log('All contact fields filled, validation passed');
                    return true; // Contact form is complete
                } else {
                    console.log('Contact form validation failed');
                    this.showValidationError("Please fill in all required fields: " + emptyFieldName);
                    return false;
                }
            }
            
            // Check for radio button selections
            var $selectedOption = $currentStep.find("input[type=\"radio\"]:checked");
            if ($selectedOption.length === 0) {
                this.showValidationError("Please select an answer before continuing.");
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
                
                // Check if this is a contact form step with auto-populated fields
                var $contactForm = $targetStep.find('.contact-info-form');
                if ($contactForm.length > 0) {
                    console.log('Contact form step detected, checking for auto-populated fields...');
                    this.handleContactFormStep($targetStep);
                }
                
                // Update navigation for this step
                this.updateNavigation();
                
                console.log('Successfully showed step:', stepNumber);
            } else {
                console.error('Could not find step:', stepNumber);
                // Fallback to first step
                $('.ennu-question-step').first().addClass('active').show();
            }
            
            // Smooth scroll to assessment container
            var $container = $('.ennu-assessment');
            if ($container.length && $container.offset()) {
                $('html, body').animate({
                    scrollTop: $container.offset().top - 50
                }, 500);
            }
        },
        
        // Handle contact form step with auto-populated fields
        handleContactFormStep: function($step) {
            var $contactForm = $step.find('.contact-info-form');
            var $autoPopulatedFields = $contactForm.find('input[data-auto-populated="true"]');
            
            if ($autoPopulatedFields.length > 0) {
                console.log('Found', $autoPopulatedFields.length, 'auto-populated fields');
                
                // Check if all required fields are filled
                var $requiredFields = $contactForm.find('input[required]');
                var allFieldsFilled = true;
                
                $requiredFields.each(function() {
                    var value = $(this).val().trim();
                    if (!value) {
                        allFieldsFilled = false;
                        return false;
                    }
                });
                
                if (allFieldsFilled) {
                    console.log('All contact fields are pre-filled and valid');
                    
                    // Add visual indicator that fields are pre-filled
                    $autoPopulatedFields.each(function() {
                        $(this).css({
                            'border-color': '#28a745',
                            'background-color': '#f8fff9'
                        });
                    });
                    
                    // Show a helpful message
                    var $notice = $contactForm.find('.user-info-notice');
                    if ($notice.length > 0) {
                        $notice.append('<p style="margin: 10px 0 0 0; font-size: 0.9em; color: #155724;"><em>All fields are ready - you can proceed immediately or make changes if needed.</em></p>');
                    }
                    
                    // Enable the submit button immediately
                    this.updateNavigation();
                } else {
                    console.log('Some required fields are still empty');
                }
            }
            
            // Add real-time validation for contact fields
            $contactForm.find('input').on('input blur', function() {
                ENNUModernAssessment.validateContactField($(this));
            });
        },
        
        // Validate individual contact field
        validateContactField: function($field) {
            var value = $field.val().trim();
            var fieldType = $field.attr('type');
            var fieldName = $field.attr('name');
            var isRequired = $field.prop('required');
            
            // Reset field styling
            $field.css({
                'border-color': '#e2e8f0',
                'background-color': '#ffffff'
            });
            
            // Validate based on field type and requirements
            var isValid = true;
            var errorMessage = '';
            
            if (isRequired && !value) {
                isValid = false;
                errorMessage = 'This field is required';
            } else if (value) {
                switch (fieldType) {
                    case 'email':
                        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(value)) {
                            isValid = false;
                            errorMessage = 'Please enter a valid email address';
                        }
                        break;
                    case 'tel':
                        // Basic phone validation - at least 10 digits
                        var phoneRegex = /\d{10,}/;
                        if (!phoneRegex.test(value.replace(/\D/g, ''))) {
                            isValid = false;
                            errorMessage = 'Please enter a valid phone number';
                        }
                        break;
                    case 'text':
                        if (fieldName === 'first_name' || fieldName === 'last_name') {
                            if (value.length < 2) {
                                isValid = false;
                                errorMessage = 'Name must be at least 2 characters';
                            }
                        }
                        break;
                }
            }
            
            // Apply validation styling
            if (!isValid) {
                $field.css({
                    'border-color': '#e53e3e',
                    'background-color': '#fef5f5'
                });
                
                // Show error message
                var $errorMsg = $field.siblings('.field-error');
                if ($errorMsg.length === 0) {
                    $errorMsg = $('<div class="field-error" style="color: #e53e3e; font-size: 0.85em; margin-top: 5px;"></div>');
                    $field.parent().append($errorMsg);
                }
                $errorMsg.text(errorMessage);
            } else {
                $field.css({
                    'border-color': '#28a745',
                    'background-color': value ? '#f8fff9' : '#ffffff'
                });
                
                // Remove error message
                $field.siblings('.field-error').remove();
            }
            
            return isValid;
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
            console.log('ENNU v21.0: Starting assessment submission...');
            console.log('ENNU: Current URL:', window.location.href);
            
            // Show loading state
            this.showLoadingState();
            
            // Collect contact form data before submission
            this.collectContactFormData();
            
            // Get assessment type
            var assessmentType = this.getAssessmentTypeHardcoded();
            console.log('ENNU: Assessment type for submission:', assessmentType);
            
            // Check if we have any assessment data
            if (Object.keys(this.assessmentData).length === 0) {
                console.error('ENNU: No assessment data collected!');
                this.showError('No assessment data found. Please answer some questions first.');
                return;
            }
            
            // Create POST data
            var postData = {
                action: 'ennu_form_submit',
                assessment_type: assessmentType
            };
            
            // Add all assessment data to post data
            for (var key in this.assessmentData) {
                postData[key] = this.assessmentData[key];
            }
            
            console.log('ENNU: Submitting data:', postData);
            console.log('ENNU: Total fields being submitted:', Object.keys(postData).length);
            
            // Submit via AJAX with fallback to custom endpoint
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: postData,
                beforeSend: function() {
                    console.log('ENNU: AJAX request starting...');
                },
                success: this.handleSubmissionSuccess.bind(this),
                error: function(xhr, status, error) {
                    console.log('ENNU: WordPress AJAX failed, trying custom endpoint...');
                    console.log('ENNU: Error details:', {xhr: xhr, status: status, error: error});
                    
                    // Try custom endpoint as fallback
                    $.ajax({
                        url: '/ennu-submit/',
                        type: 'POST',
                        data: postData,
                        success: this.handleSubmissionSuccess.bind(this),
                        error: this.handleSubmissionError.bind(this)
                    }.bind(this));
                }.bind(this),
                complete: function() {
                    console.log('ENNU: AJAX request completed');
                }
            });
        },
        
        // Hardcoded assessment type detection
        getAssessmentTypeHardcoded: function() {
            var url = window.location.href.toLowerCase();
            console.log('ENNU: Detecting assessment type from URL:', url);
            
            // Simple hardcoded detection
            if (url.indexOf('hair') !== -1) {
                console.log('ENNU: Detected HAIR assessment');
                return 'hair_assessment';
            }
            if (url.indexOf('ed') !== -1 || url.indexOf('erectile') !== -1) {
                console.log('ENNU: Detected ED assessment');
                return 'ed_treatment_assessment';
            }
            if (url.indexOf('weight') !== -1) {
                console.log('ENNU: Detected WEIGHT LOSS assessment');
                return 'weight_loss_assessment';
            }
            if (url.indexOf('skin') !== -1) {
                console.log('ENNU: Detected SKIN assessment');
                return 'skin_assessment';
            }
            if (url.indexOf('health') !== -1) {
                console.log('ENNU: Detected HEALTH assessment');
                return 'health_assessment';
            }
            
            console.log('ENNU: Could not detect assessment type, defaulting to hair_assessment');
            return 'hair_assessment'; // Default fallback
        },
        
        // Get assessment type from URL or data attribute
        getAssessmentType: function() {
            var url = window.location.href.toLowerCase();
            console.log('ENNU: Detecting assessment type from URL:', url);
            
            // Check for specific assessment types in URL (match PHP detection logic)
            if (url.includes('hair-assessment') || url.includes('hair_assessment')) {
                console.log('ENNU: Detected HAIR assessment from URL');
                return 'hair_assessment';
            }
            if (url.includes('ed-treatment') || url.includes('ed_treatment')) {
                console.log('ENNU: Detected ED TREATMENT assessment from URL');
                return 'ed_treatment_assessment';
            }
            if (url.includes('weight-loss') || url.includes('weight_loss')) {
                console.log('ENNU: Detected WEIGHT LOSS assessment from URL');
                return 'weight_loss_assessment';
            }
            if (url.includes('skin-assessment') || url.includes('skin_assessment')) {
                console.log('ENNU: Detected SKIN assessment from URL');
                return 'skin_assessment';
            }
            if (url.includes('health-assessment') || url.includes('health_assessment')) {
                console.log('ENNU: Detected HEALTH assessment from URL');
                return 'health_assessment';
            }
            
            // Fallback: check for assessment type in page data or form
            var $assessmentContainer = $('.ennu-assessment-container');
            if ($assessmentContainer.length) {
                var dataType = $assessmentContainer.data('assessment-type');
                if (dataType) {
                    console.log('ENNU: Found assessment type in data attribute:', dataType);
                    return dataType.replace(/-/g, '_'); // Convert hyphens to underscores
                }
            }
            
            console.log('ENNU: Could not detect assessment type, defaulting to general_assessment');
            return 'general_assessment';
        },
        
        // Show loading state
        showLoadingState: function() {
            var $submitBtn = $('.ennu-nav-btn.submit');
            $submitBtn.prop('disabled', true);
            $submitBtn.html('<span class="ennu-loading"></span> Processing...');
        },
             // Handle successful submission response
        handleSubmissionSuccess: function(response) {
            console.log('ENNU: Submission response received:', response);
            console.log('ENNU: Response type:', typeof response);
            console.log('ENNU: Response success property:', response ? response.success : 'undefined');
            console.log('ENNU: Full response object:', JSON.stringify(response, null, 2));
            
            // Check for success in multiple formats
            var isSuccess = false;
            var responseData = null;
            var message = 'Assessment completed successfully!';
            var redirectUrl = null;
            
            if (response) {
                // WordPress AJAX success format
                if (response.success === true) {
                    isSuccess = true;
                    responseData = response.data || {};
                    console.log('ENNU: WordPress AJAX success format detected');
                    console.log('ENNU: Response data:', responseData);
                }
                // Direct success object format
                else if (response.message && response.redirect_url) {
                    isSuccess = true;
                    responseData = response;
                    console.log('ENNU: Direct success object format detected');
                }
                // Check if response itself is the data
                else if (response.redirect_url) {
                    isSuccess = true;
                    responseData = response;
                    console.log('ENNU: Response is data object format detected');
                }
                
                if (isSuccess && responseData) {
                    message = responseData.message || message;
                    redirectUrl = responseData.redirect_url;
                    
                    console.log('ENNU: Success data extracted:', {
                        message: message,
                        redirectUrl: redirectUrl,
                        assessmentType: responseData.assessment_type,
                        userId: responseData.user_id,
                        fieldsSaved: responseData.fields_saved
                    });
                }
            }
            
            if (isSuccess) {
                console.log('ENNU: Submission successful!');
                
                // Check if we have a redirect URL for results page
                if (redirectUrl && redirectUrl.trim() !== '') {
                    console.log('ENNU: Valid redirect URL found:', redirectUrl);
                    
                    // Show success message briefly then redirect
                    this.showSuccessMessage(message + ' Redirecting to your results...');
                    
                    // Ensure redirect happens
                    setTimeout(() => {
                        console.log('ENNU: Executing redirect to:', redirectUrl);
                        try {
                            window.location.href = redirectUrl;
                        } catch (e) {
                            console.error('ENNU: Redirect failed, trying alternative method:', e);
                            window.location.replace(redirectUrl);
                        }
                    }, 2000);
                } else {
                    console.warn('ENNU: No valid redirect URL found, generating fallback URL');
                    
                    // Generate fallback redirect URL based on assessment type
                    var assessmentType = responseData.assessment_type || this.getAssessmentTypeHardcoded();
                    var fallbackUrl = this.generateFallbackRedirectUrl(assessmentType);
                    
                    console.log('ENNU: Using fallback redirect URL:', fallbackUrl);
                    
                    this.showSuccessMessage(message + ' Redirecting to your results...');
                    
                    setTimeout(() => {
                        console.log('ENNU: Executing fallback redirect to:', fallbackUrl);
                        window.location.href = fallbackUrl;
                    }, 2000);
                }
            } else {
                console.error('ENNU: Submission failed with response:', response);
                var errorMessage = 'An error occurred during submission';
                
                // Try to extract error message
                if (response) {
                    if (response.data && response.data.message) {
                        errorMessage = response.data.message;
                    } else if (response.message) {
                        errorMessage = response.message;
                    }
                }
                
                console.error('ENNU: Error message:', errorMessage);
                this.showError(errorMessage);
            }
        },
        
        // Generate fallback redirect URL based on assessment type
        generateFallbackRedirectUrl: function(assessmentType) {
            var baseUrl = window.location.origin;
            
            switch (assessmentType) {
                case 'hair_assessment':
                    return baseUrl + '/hair-assessment-results/';
                case 'ed_treatment_assessment':
                    return baseUrl + '/ed-treatment-results/';
                case 'weight_loss_assessment':
                    return baseUrl + '/weight-loss-results/';
                case 'health_assessment':
                    return baseUrl + '/health-assessment-results/';
                case 'skin_assessment':
                    return baseUrl + '/skin-assessment-results/';
                default:
                    return baseUrl + '/assessment-results/';
            }
        },
        
        // Handle submission error
        handleSubmissionError: function(xhr, status, error) {
            console.error('ENNU: Assessment submission error:', error);
            console.error('ENNU: XHR status:', status);
            console.error('ENNU: XHR response:', xhr.responseText);
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
        
        // Show success message
        showSuccessMessage: function(message) {
            // Hide assessment questions
            $('.ennu-question-step').hide();
            $('.ennu-progress-wrapper').hide();
            
            // Show success message
            var $success = $(`
                <div class="ennu-success-message" style="text-align: center; padding: 40px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 8px; margin: 20px 0;">
                    <h2 style="margin-bottom: 20px;">✅ ${message}</h2>
                    <p>Redirecting to your results...</p>
                    <div class="ennu-loading-spinner" style="margin: 20px auto; width: 40px; height: 40px; border: 4px solid #c3e6cb; border-top: 4px solid #155724; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                </div>
            `);
            
            $('.ennu-assessment-container').append($success);
        },
        
        // Handle DOB dropdown changes
        handleDOBChange: function(e) {
            console.log('DOB dropdown changed:', e.target);
            var $dropdown = $(e.target);
            var $container = $dropdown.closest('.ennu-question-step');
            var questionNumber = $container.data('question');
            
            console.log('Container found:', $container.length);
            console.log('Question number:', questionNumber);
            
            // Update days based on selected month/year
            if ($dropdown.hasClass('dob-month') || $dropdown.hasClass('dob-year')) {
                this.updateDaysInMonth($container);
            }
            
            // Calculate and display age
            this.calculateAndDisplayAge($container);
            
            // Check if all DOB fields are filled for auto-progression
            this.checkDOBCompletion($container);
        },
        
        // Update days in month based on selected month and year
        updateDaysInMonth: function($container) {
            var $monthSelect = $container.find('.dob-month');
            var $daySelect = $container.find('.dob-day');
            var $yearSelect = $container.find('.dob-year');
            
            var month = parseInt($monthSelect.val());
            var year = parseInt($yearSelect.val());
            var currentDay = parseInt($daySelect.val());
            
            if (!month) return;
            
            // Calculate days in month
            var daysInMonth = new Date(year || 2024, month, 0).getDate();
            
            // Clear and rebuild day options
            $daySelect.find('option:not(:first)').remove();
            
            for (var day = 1; day <= daysInMonth; day++) {
                var dayValue = day.toString().padStart(2, '0');
                var selected = (day === currentDay && day <= daysInMonth) ? 'selected' : '';
                $daySelect.append(`<option value="${dayValue}" ${selected}>${day}</option>`);
            }
            
            // If current day is invalid for this month, clear selection
            if (currentDay > daysInMonth) {
                $daySelect.val('');
            }
        },
        
        // Calculate and display age
        calculateAndDisplayAge: function($container) {
            console.log('Calculating age for container:', $container);
            var $monthSelect = $container.find('.dob-month');
            var $daySelect = $container.find('.dob-day');
            var $yearSelect = $container.find('.dob-year');
            var $ageDisplay = $container.find('.calculated-age');
            var $dobCombined = $container.find('.dob-combined');
            var $ageField = $container.find('.calculated-age-field');
            
            console.log('Found elements:', {
                month: $monthSelect.length,
                day: $daySelect.length, 
                year: $yearSelect.length,
                ageDisplay: $ageDisplay.length,
                dobCombined: $dobCombined.length,
                ageField: $ageField.length
            });
            
            var month = $monthSelect.val();
            var day = $daySelect.val();
            var year = $yearSelect.val();
            
            console.log('Values:', { month, day, year });
            
            if (month && day && year) {
                // Create DOB string
                var dobString = `${year}-${month}-${day}`;
                if ($dobCombined.length > 0) {
                    $dobCombined.val(dobString);
                }
                
                // Calculate age
                var birthDate = new Date(year, month - 1, day);
                var today = new Date();
                var age = today.getFullYear() - birthDate.getFullYear();
                var monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                console.log('Calculated age:', age);
                
                // Display age with fallback
                if ($ageDisplay.length > 0) {
                    $ageDisplay.text(age);
                    // Add animation
                    $ageDisplay.parent().addClass('pulse-animation');
                    setTimeout(() => {
                        $ageDisplay.parent().removeClass('pulse-animation');
                    }, 600);
                } else {
                    console.warn('Age display element not found');
                }
                
                if ($ageField.length > 0) {
                    $ageField.val(age);
                } else {
                    console.warn('Age field element not found');
                }
                
                // Auto-progress if all DOB fields are filled
                this.checkDOBCompletion($container);
                
            } else {
                console.log('Not all fields filled, showing --');
                if ($ageDisplay.length > 0) {
                    $ageDisplay.text('--');
                }
                if ($ageField.length > 0) {
                    $ageField.val('');
                }
            }
        },
        
        // Check if DOB is complete for auto-progression
        checkDOBCompletion: function($container) {
            var $monthSelect = $container.find('.dob-month');
            var $daySelect = $container.find('.dob-day');
            var $yearSelect = $container.find('.dob-year');
            
            var month = $monthSelect.val();
            var day = $daySelect.val();
            var year = $yearSelect.val();
            
            if (month && day && year) {
                // Mark as answered for progression
                $container.addClass('answered');
                
                // Auto-progress after delay
                clearTimeout(this.autoProgressTimer);
                this.autoProgressTimer = setTimeout(() => {
                    if (this.currentStep < this.totalSteps) {
                        this.nextStep();
                    }
                }, this.autoProgressDelay);
            } else {
                $container.removeClass('answered');
                clearTimeout(this.autoProgressTimer);
            }
        },
        
        // Collect contact form data
        collectContactFormData: function() {
            var $contactForm = $('.contact-info-form');
            if ($contactForm.length > 0) {
                console.log('Collecting contact form data...');
                
                // Collect all contact form fields
                $contactForm.find('input').each((index, element) => {
                    var $field = $(element);
                    var fieldName = $field.attr('name');
                    var fieldValue = $field.val().trim();
                    
                    if (fieldName && fieldValue) {
                        this.assessmentData[fieldName] = fieldValue;
                        console.log('Collected field:', fieldName, '=', fieldValue);
                    }
                });
                
                // Also collect DOB data if present
                var $dobMonth = $('.dob-month');
                var $dobDay = $('.dob-day');
                var $dobYear = $('.dob-year');
                
                if ($dobMonth.length && $dobDay.length && $dobYear.length) {
                    var month = $dobMonth.val();
                    var day = $dobDay.val();
                    var year = $dobYear.val();
                    
                    if (month && day && year) {
                        this.assessmentData['date_of_birth_month'] = month;
                        this.assessmentData['date_of_birth_day'] = day;
                        this.assessmentData['date_of_birth_year'] = year;
                        this.assessmentData['date_of_birth'] = month + '/' + day + '/' + year;
                        console.log('Collected DOB:', month + '/' + day + '/' + year);
                    }
                }
                
                console.log('Final assessment data:', this.assessmentData);
            }
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
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        if ($('.ennu-assessment').length > 0) {
            ENNUModernAssessment.init();
        }
    });

    // Expose global objects for compatibility
    window.ENNUAssessment = ENNUModernAssessment;
    window.ENNUModernAssessment = ENNUModernAssessment;

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

