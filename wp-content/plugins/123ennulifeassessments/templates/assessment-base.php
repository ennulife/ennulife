<?php
/**
 * ENNU Life Base Assessment Template
 * 
 * @package ENNU_Life
 * @version 14.1.12
 */

if (!defined('ABSPATH')) {
    exit;
}

$assessment_type = $atts['type'] ?? 'health-assessment';
$assessment_title = $atts['title'] ?? 'Health Assessment';
?>

<div class="ennu-assessment-container" data-assessment-type="<?php echo esc_attr($assessment_type); ?>">
    <div class="ennu-assessment-header">
        <h2><?php echo esc_html($assessment_title); ?></h2>
        <div class="ennu-progress-container">
            <div class="ennu-progress-bar" style="width: 0%;"></div>
        </div>
    </div>
    
    <form class="ennu-assessment-form" method="post">
        <?php wp_nonce_field('ennu_assessment_' . $assessment_type, 'assessment_nonce'); ?>
        <input type="hidden" name="assessment_type" value="<?php echo esc_attr($assessment_type); ?>">
        
        <div class="ennu-form-sections">
            <!-- Assessment questions will be loaded here -->
            <div class="ennu-form-section active">
                <h3>Personal Information</h3>
                <div class="ennu-form-row">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="ennu-form-row">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="ennu-form-row">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="ennu-form-row">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>
            </div>
        </div>
        
        <div class="ennu-form-navigation">
            <button type="button" class="ennu-btn ennu-btn-secondary ennu-prev-btn" style="display: none;">Previous</button>
            <button type="button" class="ennu-btn ennu-btn-primary ennu-next-btn">Next</button>
            <button type="submit" class="ennu-btn ennu-btn-primary ennu-submit-btn" style="display: none;">Submit Assessment</button>
        </div>
    </form>
    
    <div class="ennu-results" style="display: none;">
        <!-- Assessment results will be displayed here -->
    </div>
</div>

