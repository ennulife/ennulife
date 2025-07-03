<?php
/**
 * ENNU Life Form Handler
 * Handles all form submissions with enterprise-grade security
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Life_Form_Handler {
    
    public function __construct() {
        // Register AJAX handlers with proper security
        add_action('wp_ajax_ennu_form_submit', array($this, 'handle_ajax_submission'));
        add_action('wp_ajax_nopriv_ennu_form_submit', array($this, 'handle_ajax_submission'));
        add_action('wp_ajax_ennu_submit_form', array($this, 'handle_ajax_submission'));
        add_action('wp_ajax_nopriv_ennu_submit_form', array($this, 'handle_ajax_submission'));
    }
    
    public function handle_ajax_submission() {
        // Comprehensive security checks
        if (!$this->verify_security()) {
            wp_send_json_error(array('message' => 'Security verification failed'));
        }
        
        // Extract form data from $_POST
        $form_data = array();
        $form_type = '';
        
        // Check if data is in form_data array or directly in $_POST
        if (isset($_POST['form_data']) && is_array($_POST['form_data'])) {
            $form_data = $_POST['form_data'];
            $form_type = sanitize_text_field($_POST['form_type'] ?? $_POST['assessment_type'] ?? '');
        } else {
            // Extract from direct POST data
            foreach ($_POST as $key => $value) {
                if ($key === 'action' || $key === 'nonce') continue;
                
                if ($key === 'assessment_type' || $key === 'form_type') {
                    $form_type = sanitize_text_field($value);
                } else {
                    $form_data[$key] = $value;
                }
            }
        }
        
        // If no form type specified, try to detect from form data
        if (empty($form_type)) {
            $form_type = $this->detect_form_type($form_data);
        }
        
        // Sanitize form data
        $form_data = $this->sanitize_form_data($form_data);
        
        // Validate required fields
        $validation_result = $this->validate_form_data($form_type, $form_data);
        if (!$validation_result['valid']) {
            wp_send_json_error(array('message' => $validation_result['message']));
        }
        
        // Process the form using dual storage system
        $result = $this->handle_form_submission($form_type, $form_data);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => $result['message'],
                'submission_id' => $result['submission_id'],
                'results' => $result['results'] ?? array(),
                'next_step' => $this->get_next_step($form_type, $result)
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    private function verify_security() {
        // Standardized nonce verification - prioritize 'ennu_nonce' for consistency
        $nonce = $_POST['ennu_nonce'] ?? $_POST['nonce'] ?? $_POST['_wpnonce'] ?? '';
        
        if (empty($nonce)) {
            error_log('ENNU Security Check Failed: No nonce provided');
            return false;
        }
        
        // Verify nonce with standardized action name
        if (!wp_verify_nonce($nonce, 'ennu_ajax_nonce')) {
            error_log('ENNU Security Check Failed: Nonce verification failed. Nonce: ' . $nonce);
            return false;
        }
        
        // Rate limiting check
        if (!$this->check_rate_limit()) {
            return false;
        }
        
        return true;
    }
    
    private function check_rate_limit() {
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $transient_key = 'ennu_rate_limit_' . md5($user_ip);
        
        $submissions = get_transient($transient_key);
        if ($submissions === false) {
            $submissions = 0;
        }
        
        // Allow max 10 submissions per hour
        if ($submissions >= 10) {
            return false;
        }
        
        set_transient($transient_key, $submissions + 1, HOUR_IN_SECONDS);
        return true;
    }
    
    private function sanitize_form_type($form_type) {
        $allowed_types = array(
            'ed-treatment-assessment',
            'weight-loss-assessment', 
            'health-assessment',
            'skin-assessment-enhanced',
            'hair-assessment',
            'membership-calculator',
            'health-investment-calculator',
            'smart-booking',
            'wellness-booking',
            'personalized-health-survey'
        );
        
        $sanitized = sanitize_text_field($form_type);
        return in_array($sanitized, $allowed_types) ? $sanitized : 'general';
    }
    
    private function sanitize_form_data($form_data) {
        if (!is_array($form_data)) {
            return array();
        }
        
        $sanitized = array();
        foreach ($form_data as $key => $value) {
            $clean_key = sanitize_key($key);
            
            if (is_array($value)) {
                $sanitized[$clean_key] = array_map('sanitize_text_field', $value);
            } elseif (in_array($key, array('message', 'notes', 'description'))) {
                $sanitized[$clean_key] = sanitize_textarea_field($value);
            } elseif ($key === 'email') {
                $sanitized[$clean_key] = sanitize_email($value);
            } elseif ($key === 'url') {
                $sanitized[$clean_key] = esc_url_raw($value);
            } else {
                $sanitized[$clean_key] = sanitize_text_field($value);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Detect form type from form data if not explicitly provided
     */
    private function detect_form_type($form_data) {
        // Look for specific field patterns to identify form type
        if (isset($form_data['ed_symptoms']) || isset($form_data['severity'])) {
            return 'ed-treatment-assessment';
        }
        
        if (isset($form_data['current_weight']) && isset($form_data['goal_weight'])) {
            return 'weight-loss-assessment';
        }
        
        if (isset($form_data['skin_concerns']) || isset($form_data['skin_type'])) {
            return 'skin-assessment-enhanced';
        }
        
        if (isset($form_data['hair_loss_severity']) || isset($form_data['hair_loss_pattern'])) {
            return 'hair-restoration-assessment';
        }
        
        if (isset($form_data['hormone_symptoms']) || isset($form_data['energy_level'])) {
            return 'hormone-assessment';
        }
        
        // Default to health assessment
        return 'health-assessment';
    }
    
    private function validate_form_data($form_type, $form_data) {
        $required_fields = $this->get_required_fields($form_type);
        
        foreach ($required_fields as $field) {
            if (empty($form_data[$field])) {
                return array(
                    'valid' => false,
                    'message' => sprintf('The field "%s" is required.', ucwords(str_replace('_', ' ', $field)))
                );
            }
        }
        
        // Email validation
        if (isset($form_data['email']) && !is_email($form_data['email'])) {
            return array(
                'valid' => false,
                'message' => 'Please enter a valid email address.'
            );
        }
        
        return array('valid' => true);
    }
    
    private function get_required_fields($form_type) {
        $required_fields = array(
            'ed-treatment-assessment' => array('name', 'email', 'age'),
            'weight-loss-assessment' => array('name', 'email', 'current_weight', 'goal_weight'),
            'health-assessment' => array('name', 'email', 'phone'),
            'hair-assessment' => array('name', 'email', 'mobile'),
            'hair-restoration-assessment' => array('name', 'email', 'mobile'),
            'hair_assessment' => array('name', 'email', 'mobile'),
            'hair_restoration_assessment' => array('name', 'email', 'mobile'),
            'smart-booking' => array('name', 'email', 'service_type', 'preferred_date'),
            'default' => array('name', 'email')
        );
        
        return isset($required_fields[$form_type]) ? $required_fields[$form_type] : $required_fields['default'];
    }
    
    public function handle_form_submission($form_type, $form_data) {
        // Use the new dual storage system
        $database = ENNU_Life_Database::get_instance();
        $result = $database->save_assessment($form_type, $form_data);
        
        if ($result['success']) {
            // Send notification email if configured
            $this->send_notification_email($form_type, $form_data, $result);
            
            return array(
                'success' => true,
                'message' => 'Assessment submitted successfully!',
                'submission_id' => $result['post_id'],
                'results' => $result['results']
            );
        } else {
            return array(
                'success' => false,
                'message' => $result['message']
            );
        }
    }
    
    private function process_form_by_type($form_type, $form_data) {
        switch ($form_type) {
            case 'ed-treatment-assessment':
                return $this->process_ed_assessment($form_data);
            case 'weight-loss-assessment':
                return $this->process_weight_loss_assessment($form_data);
            case 'health-assessment':
                return $this->process_health_assessment($form_data);
            default:
                return array('results' => array('status' => 'completed', 'type' => $form_type));
        }
    }
    
    private function process_ed_assessment($form_data) {
        $score = 0;
        $recommendations = array();
        
        if (isset($form_data['severity']) && $form_data['severity'] === 'severe') {
            $score += 3;
            $recommendations[] = 'Immediate consultation recommended';
        }
        
        return array(
            'results' => array(
                'score' => $score,
                'severity_level' => $this->get_severity_level($score),
                'recommendations' => $recommendations,
                'next_steps' => 'Schedule consultation with our specialists'
            )
        );
    }
    
    private function process_weight_loss_assessment($form_data) {
        $current_weight = floatval($form_data['current_weight'] ?? 0);
        $goal_weight = floatval($form_data['goal_weight'] ?? 0);
        $height = floatval($form_data['height'] ?? 0);
        
        $weight_to_lose = $current_weight - $goal_weight;
        $bmi = $height > 0 ? ($current_weight / (($height / 100) * ($height / 100))) : 0;
        
        return array(
            'results' => array(
                'weight_to_lose' => $weight_to_lose,
                'bmi' => round($bmi, 1),
                'bmi_category' => $this->get_bmi_category($bmi),
                'estimated_timeline' => $this->calculate_weight_loss_timeline($weight_to_lose)
            )
        );
    }
    
    private function process_health_assessment($form_data) {
        return array(
            'results' => array(
                'assessment_complete' => true,
                'consultation_recommended' => true,
                'priority_level' => 'standard'
            )
        );
    }
    
    private function get_severity_level($score) {
        if ($score >= 5) return 'High';
        if ($score >= 3) return 'Moderate';
        return 'Low';
    }
    
    private function get_bmi_category($bmi) {
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal weight';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }
    
    private function calculate_weight_loss_timeline($weight_to_lose) {
        $weeks = ceil($weight_to_lose / 1.5);
        return sprintf('%d-%d weeks', $weeks - 4, $weeks + 4);
    }
    
    private function get_next_step($form_type, $result) {
        $next_steps = array(
            'ed-treatment-assessment' => 'consultation_booking',
            'weight-loss-assessment' => 'program_selection',
            'health-assessment' => 'appointment_scheduling',
            'default' => 'thank_you'
        );
        
        return isset($next_steps[$form_type]) ? $next_steps[$form_type] : $next_steps['default'];
    }
    
    /**
     * Send notification email for new assessment submission
     */
    private function send_notification_email($form_type, $form_data, $result) {
        $admin_email = get_option('admin_email');
        $user_email = $form_data['email'] ?? '';
        
        // Admin notification
        if ($admin_email) {
            $subject = sprintf('New %s Submission - ENNU Life', ucwords(str_replace('-', ' ', $form_type)));
            $message = $this->generate_admin_notification_email($form_type, $form_data, $result);
            wp_mail($admin_email, $subject, $message);
        }
        
        // User confirmation email
        if ($user_email) {
            $subject = 'Assessment Received - ENNU Life';
            $message = $this->generate_user_confirmation_email($form_type, $form_data, $result);
            wp_mail($user_email, $subject, $message);
        }
    }
    
    /**
     * Generate admin notification email content
     */
    private function generate_admin_notification_email($form_type, $form_data, $result) {
        $message = "New assessment submission received:\n\n";
        $message .= "Assessment Type: " . ucwords(str_replace('-', ' ', $form_type)) . "\n";
        $message .= "Submission ID: " . $result['post_id'] . "\n";
        $message .= "Date: " . current_time('F j, Y g:i A') . "\n\n";
        
        $message .= "User Information:\n";
        $message .= "Name: " . ($form_data['full_name'] ?? $form_data['name'] ?? 'Not provided') . "\n";
        $message .= "Email: " . ($form_data['email'] ?? 'Not provided') . "\n";
        $message .= "Phone: " . ($form_data['phone'] ?? 'Not provided') . "\n\n";
        
        if (isset($result['results']['score'])) {
            $message .= "Assessment Score: " . $result['results']['score'] . "\n";
        }
        
        if (isset($result['results']['recommendations'])) {
            $message .= "Recommendations: " . implode(', ', $result['results']['recommendations']) . "\n";
        }
        
        $message .= "\nView full details in WordPress admin: " . admin_url('edit.php?post_type=ennu_' . str_replace('-', '_', explode('-', $form_type)[0]) . '_assessment');
        
        return $message;
    }
    
    /**
     * Generate user confirmation email content
     */
    private function generate_user_confirmation_email($form_type, $form_data, $result) {
        $name = $form_data['full_name'] ?? $form_data['name'] ?? 'there';
        
        $message = "Dear {$name},\n\n";
        $message .= "Thank you for completing your " . ucwords(str_replace('-', ' ', $form_type)) . " with ENNU Life.\n\n";
        $message .= "We have received your submission and our team will review it shortly.\n\n";
        
        if (isset($result['results']['next_steps'])) {
            $message .= "Next Steps: " . $result['results']['next_steps'] . "\n\n";
        }
        
        $message .= "If you have any questions, please don't hesitate to contact us.\n\n";
        $message .= "Best regards,\n";
        $message .= "The ENNU Life Team\n";
        
        return $message;
    }
}

