/**
 * ENNU Life Main JavaScript
 * Handles form submissions, UI interactions, and assessment functionality
 */

(function($) {
    'use strict';
    
    // Main ENNU object
    window.ENNU = {
        
        // Initialize the plugin
        init: function() {
            this.bindEvents();
            this.initForms();
            this.initProgressBars();
            console.log('ENNU Life plugin initialized');
        },
        
        // Bind all event handlers
        bindEvents: function() {
            // Form submissions
            $(document).on('submit', '.ennu-form, .ennu-assessment-form', this.handleFormSubmit.bind(this));
            
            // Progress tracking
            $(document).on('input change', '.ennu-form input, .ennu-form select, .ennu-form textarea', this.updateProgress);
            
            // Field validation
            $(document).on('blur', '.ennu-form input[required], .ennu-form select[required]', this.validateField);
            
            // Navigation
            $(document).on('click', '.ennu-nav-link', this.handleNavigation.bind(this));
            
            // Assessment start buttons
            $(document).on('click', '.start-assessment', this.handleAssessmentStart.bind(this));
        },
        
        // Initialize forms
        initForms: function() {
            $('.ennu-form').each(function() {
                var $form = $(this);
                ENNU.initProgressBar($form);
                ENNU.validateRequiredFields($form);
            });
        },
        
        // Initialize progress bars
        initProgressBars: function() {
            $('.ennu-progress-bar').each(function() {
                var $progressBar = $(this);
                var $form = $progressBar.closest('.ennu-form');
                if ($form.length) {
                    ENNU.updateProgress.call($form[0]);
                }
            });
        },
        
        // Validate required fields
        validateRequiredFields: function($form) {
            $form.find('input[required], select[required]').each(function() {
                var $field = $(this);
                var value = $field.val().trim();
                
                if (value === '') {
                    ENNU.showFieldError($field, 'This field is required');
                } else {
                    ENNU.clearFieldError($field);
                }
            });
        },
        
        // Initialize progress bar
        initProgressBar: function($form) {
            var $progress = $form.find('.ennu-progress-bar');
            if ($progress.length) {
                this.updateProgress.call($form[0]);
            }
        },
        
        // Update form progress
        updateProgress: function() {
            var $form = $(this).closest('.ennu-form');
            var $progress = $form.find('.ennu-progress-bar');
            
            if ($progress.length) {
                var totalFields = $form.find('input[required], select[required]').length;
                var completedFields = $form.find('input[required], select[required]').filter(function() {
                    return $(this).val().trim() !== '';
                }).length;
                
                var percentage = totalFields > 0 ? (completedFields / totalFields) * 100 : 0;
                $progress.css('width', percentage + '%');
            }
        },
        
        // Handle form submission
        handleFormSubmit: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var formData = new FormData(this);
            
            // Detect assessment type from form or URL
            var assessmentType = $form.data('assessment-type') || 
                                $form.find('input[name="assessment_type"]').val() ||
                                ENNU.detectAssessmentType($form);
            
            // Get nonce from form or global variable
            var nonce = $form.find('input[name="ennu_nonce"]').val() || 
                       $form.find('input[name="_wpnonce"]').val() || 
                       (typeof ennuAjax !== 'undefined' ? ennuAjax.nonce : '');
            
            // Add required AJAX parameters
            formData.append('action', 'ennu_form_submit');
            formData.append('nonce', nonce);
            formData.append('assessment_type', assessmentType);
            
            // Show loading state
            ENNU.showLoading($form);
            
            // Submit via AJAX
            $.ajax({
                url: typeof ennuAjax !== 'undefined' ? ennuAjax.ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    ENNU.hideLoading($form);
                    
                    if (response.success) {
                        var message = response.data.message || 'Assessment submitted successfully!';
                        ENNU.showSuccess($form, message);
                        
                        // Show results if available
                        if (response.data.results) {
                            ENNU.displayResults($form, response.data.results);
                        }
                        
                        // Reset form
                        $form[0].reset();
                        ENNU.updateProgress.call($form[0]);
                        
                        // Scroll to results
                        setTimeout(function() {
                            var $results = $form.find('.ennu-results');
                            if ($results.length) {
                                $('html, body').animate({
                                    scrollTop: $results.offset().top - 100
                                }, 500);
                            }
                        }, 500);
                        
                    } else {
                        var errorMessage = response.data ? 
                            (response.data.message || response.data) : 
                            'An error occurred while submitting your assessment.';
                        ENNU.showError($form, errorMessage);
                    }
                },
                error: function(xhr, status, error) {
                    ENNU.hideLoading($form);
                    console.error('AJAX Error:', status, error);
                    ENNU.showError($form, 'Network error. Please check your connection and try again.');
                }
            });
        },
        
        // Show loading state
        showLoading: function($form) {
            var $submitBtn = $form.find('button[type="submit"], input[type="submit"]');
            $submitBtn.prop('disabled', true);
            $submitBtn.data('original-text', $submitBtn.text());
            $submitBtn.html('<span class="ennu-spinner"></span> Processing...');
        },
        
        // Hide loading state
        hideLoading: function($form) {
            var $submitBtn = $form.find('button[type="submit"], input[type="submit"]');
            $submitBtn.prop('disabled', false);
            var originalText = $submitBtn.data('original-text') || 'Submit';
            $submitBtn.text(originalText);
        },
        
        // Show success message
        showSuccess: function($form, message) {
            this.showMessage($form, message, 'success');
        },
        
        // Show error message
        showError: function($form, message) {
            this.showMessage($form, message, 'error');
        },
        
        // Show message
        showMessage: function($form, message, type) {
            // Remove existing messages
            $form.find('.ennu-message').remove();
            
            var $message = $('<div class="ennu-message ennu-message-' + type + '">' + message + '</div>');
            $form.prepend($message);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $message.fadeOut(function() {
                    $message.remove();
                });
            }, 5000);
        },
        
        // Show field error
        showFieldError: function($field, message) {
            this.clearFieldError($field);
            
            var $error = $('<div class="ennu-field-error">' + message + '</div>');
            $field.addClass('ennu-field-invalid');
            $field.after($error);
        },
        
        // Clear field error
        clearFieldError: function($field) {
            $field.removeClass('ennu-field-invalid');
            $field.siblings('.ennu-field-error').remove();
        },
        
        // Validate field
        validateField: function() {
            var $field = $(this);
            var value = $field.val().trim();
            var isValid = true;
            var errorMessage = '';
            
            // Required field validation
            if ($field.prop('required') && value === '') {
                isValid = false;
                errorMessage = 'This field is required';
            }
            
            // Email validation
            if ($field.attr('type') === 'email' && value !== '') {
                if (!ENNU.validateEmail(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                }
            }
            
            // Phone validation
            if ($field.attr('type') === 'tel' && value !== '') {
                if (value.length < 10) {
                    isValid = false;
                    errorMessage = 'Please enter a valid phone number';
                }
            }
            
            if (isValid) {
                ENNU.clearFieldError($field);
            } else {
                ENNU.showFieldError($field, errorMessage);
            }
            
            return isValid;
        },
        
        // Handle navigation
        handleNavigation: function(e) {
            e.preventDefault();
            var $link = $(e.currentTarget);
            var target = $link.attr('href');
            
            if (target && target.startsWith('#')) {
                this.scrollToSection(target.substring(1));
            }
        },
        
        // Handle assessment start
        handleAssessmentStart: function(e) {
            e.preventDefault();
            var $btn = $(e.currentTarget);
            var assessmentType = $btn.data('assessment-type');
            
            if (assessmentType) {
                this.startAssessment(assessmentType);
            }
        },
        
        // Scroll to section
        scrollToSection: function(sectionId) {
            var $section = $("#" + sectionId);
            if ($section.length) {
                $("html, body").animate({
                    scrollTop: $section.offset().top - 100
                }, 800);
            }
        },

        // Start assessment
        startAssessment: function(assessmentType) {
            var siteUrl = typeof ennuAjax !== 'undefined' ? ennuAjax.siteUrl : window.location.origin;
            window.location.href = siteUrl + "/" + assessmentType + "/";
        },
        
        // Detect assessment type from form content or URL
        detectAssessmentType: function($form) {
            // Check URL for assessment type
            var url = window.location.href;
            if (url.includes('welcome-assessment')) return 'welcome-assessment';
            if (url.includes('ed-treatment')) return 'ed-treatment-assessment';
            if (url.includes('weight-loss')) return 'weight-loss-assessment';
            if (url.includes('skin-assessment')) return 'skin-assessment-enhanced';
            if (url.includes('hair-restoration')) return 'hair-restoration-assessment';
            if (url.includes('hair-assessment')) return 'hair-assessment';
            if (url.includes('hormone')) return 'hormone-assessment';
            if (url.includes('health')) return 'health-assessment';
            
            // Check form fields to detect type
            if ($form.find('input[name*="ed_"], select[name*="live_longer"]').length) {
                return 'ed-treatment-assessment';
            }
            if ($form.find('input[name*="welcome_"], select[name*="severity"]').length) {
                return 'ed-treatment-assessment';
            }
            if ($form.find('input[name*="weight"], input[name*="goal_weight"]').length) {
                return 'weight-loss-assessment';
            }
            if ($form.find('input[name*="skin"], select[name*="skin_type"]').length) {
                return 'skin-assessment-enhanced';
            }
            if ($form.find('input[name*="hair"], select[name*="hair_loss"]').length) {
                return 'hair-restoration-assessment';
            }
            if ($form.find('input[name*="hormone"], select[name*="energy"]').length) {
                return 'hormone-assessment';
            }
            
            // Default to health assessment
            return 'health-assessment';
        },
        
        // Display assessment results
        displayResults: function($form, results) {
            var $resultsContainer = $form.find('.ennu-results');
            if (!$resultsContainer.length) {
                $resultsContainer = $('<div class="ennu-results"></div>');
                $form.append($resultsContainer);
            }
            
            var resultsHtml = '<div class="ennu-results-content">';
            resultsHtml += '<h3>Your Assessment Results</h3>';
            
            // Display score if available
            if (results.score !== undefined) {
                var scoreClass = results.score >= 80 ? 'excellent' : 
                               results.score >= 60 ? 'good' : 
                               results.score >= 40 ? 'fair' : 'needs-attention';
                
                resultsHtml += '<div class="ennu-score ' + scoreClass + '">';
                resultsHtml += '<div class="score-circle">' + results.score + '</div>';
                resultsHtml += '<div class="score-label">Overall Score</div>';
                resultsHtml += '</div>';
            }
            
            // Display recommendations
            if (results.recommendations && results.recommendations.length > 0) {
                resultsHtml += '<div class="ennu-recommendations">';
                resultsHtml += '<h4>Recommendations</h4>';
                resultsHtml += '<ul>';
                results.recommendations.forEach(function(rec) {
                    resultsHtml += '<li>' + rec + '</li>';
                });
                resultsHtml += '</ul>';
                resultsHtml += '</div>';
            }
            
            // Display next steps
            if (results.next_steps) {
                resultsHtml += '<div class="ennu-next-steps">';
                resultsHtml += '<h4>Next Steps</h4>';
                resultsHtml += '<p>' + results.next_steps + '</p>';
                resultsHtml += '</div>';
            }
            
            // Display specific result data
            if (results.bmi) {
                resultsHtml += '<div class="ennu-metric">';
                resultsHtml += '<span class="metric-label">BMI:</span> ';
                resultsHtml += '<span class="metric-value">' + results.bmi + ' (' + (results.bmi_category || '') + ')</span>';
                resultsHtml += '</div>';
            }
            
            if (results.severity_level) {
                resultsHtml += '<div class="ennu-metric">';
                resultsHtml += '<span class="metric-label">Severity Level:</span> ';
                resultsHtml += '<span class="metric-value">' + results.severity_level + '</span>';
                resultsHtml += '</div>';
            }
            
            resultsHtml += '<div class="ennu-cta">';
            resultsHtml += '<p>Ready to take the next step? Our specialists are here to help.</p>';
            resultsHtml += '<button class="ennu-btn" onclick="ENNU.scheduleConsultation()">Schedule Consultation</button>';
            resultsHtml += '</div>';
            
            resultsHtml += '</div>';
            
            $resultsContainer.html(resultsHtml);
            $resultsContainer.slideDown(300);
        },
        
        // Schedule consultation
        scheduleConsultation: function() {
            this.showAlert('Consultation scheduling will be available soon. Please contact us directly for now.', 'info');
        },
        
        // Show alert
        showAlert: function(message, type) {
            alert(message); // Simple fallback - can be enhanced with modal
        },
        
        // Validate email
        validateEmail: function(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        ENNU.init();
    });
    
})(jQuery);

