<?php
/**
 * Assessment Results Template - Dynamic Thank You Page for All Assessments
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get assessment type from URL parameter or session
$assessment_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'general';

// Define assessment-specific content
$assessment_content = array(
    'hair_assessment' => array(
        'title' => 'Hair Assessment Complete!',
        'message' => 'Thank you for completing your hair health assessment. Our specialists will review your responses to create a personalized hair restoration plan.',
        'benefits' => array(
            'Personalized hair restoration plan',
            'Advanced treatment options discussion',
            'Hair growth timeline and expectations',
            'Customized treatment pricing'
        )
    ),
    'ed_treatment_assessment' => array(
        'title' => 'ED Treatment Assessment Complete!',
        'message' => 'Thank you for completing your ED treatment assessment. Our medical professionals will review your responses confidentially.',
        'benefits' => array(
            'Confidential medical consultation',
            'Personalized treatment recommendations',
            'Discussion of treatment options',
            'Discreet and professional care'
        )
    ),
    'weight_loss_assessment' => array(
        'title' => 'Weight Loss Assessment Complete!',
        'message' => 'Thank you for completing your weight loss assessment. Our team will create a personalized weight management plan for you.',
        'benefits' => array(
            'Customized weight loss strategy',
            'Nutritional guidance and support',
            'Medical weight loss options',
            'Long-term success planning'
        )
    ),
    'health_assessment' => array(
        'title' => 'Health Assessment Complete!',
        'message' => 'Thank you for completing your comprehensive health assessment. Our healthcare team will review your responses.',
        'benefits' => array(
            'Comprehensive health evaluation',
            'Personalized wellness recommendations',
            'Preventive care planning',
            'Ongoing health monitoring'
        )
    ),
    'skin_assessment' => array(
        'title' => 'Skin Assessment Complete!',
        'message' => 'Thank you for completing your skin health assessment. Our dermatology specialists will review your responses.',
        'benefits' => array(
            'Personalized skincare regimen',
            'Advanced treatment options',
            'Skin health improvement plan',
            'Professional product recommendations'
        )
    ),
    'general' => array(
        'title' => 'Assessment Complete!',
        'message' => 'Thank you for completing your health assessment. Our team will review your responses and contact you soon.',
        'benefits' => array(
            'Personalized treatment recommendations',
            'Discussion of your health goals',
            'Custom treatment plan',
            'Pricing and next steps'
        )
    )
);

$content = isset($assessment_content[$assessment_type]) ? $assessment_content[$assessment_type] : $assessment_content['general'];

get_header();
?>

<div class="ennu-results-container">
    <div class="ennu-results-content">
        <div class="success-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="#28a745" stroke-width="2" fill="#f8fff9"/>
                <path d="m9 12 2 2 4-4" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        
        <div class="thank-you-section">
            <h1><?php echo esc_html($content['title']); ?></h1>
            <p class="thank-you-message">
                <?php echo esc_html($content['message']); ?>
            </p>
            
            <div class="next-steps">
                <h2>What's Next?</h2>
                <p>
                    Schedule a consultation with our specialists to discuss your personalized treatment options and get started on your health journey.
                </p>
                
                <div class="consultation-cta">
                    <a href="<?php echo esc_url(home_url('/book-consultation/')); ?>" class="schedule-consultation-btn">
                        <span class="btn-icon">📅</span>
                        Schedule Your Free Consultation
                    </a>
                </div>
                
                <div class="additional-info">
                    <h3>What to expect in your consultation:</h3>
                    <ul>
                        <?php foreach ($content['benefits'] as $benefit): ?>
                            <li><?php echo esc_html($benefit); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="contact-info">
                    <p><strong>Questions?</strong> Call us at <a href="tel:+1-800-ENNU-LIFE">(800) ENNU-LIFE</a> or email <a href="mailto:info@ennulife.com">info@ennulife.com</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ennu-results-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: Arial, sans-serif;
}

.ennu-results-content {
    background: #fff;
    border-radius: 10px;
    padding: 40px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.thank-you-section h1 {
    color: #2c5aa0;
    font-size: 2.5em;
    margin-bottom: 20px;
}

.thank-you-message {
    font-size: 1.2em;
    color: #666;
    margin-bottom: 40px;
    line-height: 1.6;
}

.next-steps {
    margin-top: 40px;
}

.next-steps h2 {
    color: #2c5aa0;
    font-size: 1.8em;
    margin-bottom: 20px;
}

.consultation-cta {
    margin: 30px 0;
}

.schedule-consultation-btn {
    display: inline-block;
    background: #007cba;
    color: white;
    padding: 15px 30px;
    text-decoration: none;
    border-radius: 5px;
    font-size: 1.1em;
    font-weight: bold;
    transition: background 0.3s ease;
}

.schedule-consultation-btn:hover {
    background: #005a87;
    color: white;
    text-decoration: none;
}

.additional-info {
    margin-top: 40px;
    text-align: left;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
}

.additional-info ul {
    margin: 10px 0;
    padding-left: 20px;
}

.additional-info li {
    margin: 8px 0;
    color: #555;
}

@media (max-width: 768px) {
    .ennu-results-content {
        padding: 20px;
    }
    
    .thank-you-section h1 {
        font-size: 2em;
    }
    
    .schedule-consultation-btn {
        padding: 12px 24px;
        font-size: 1em;
    }
}
</style>

<?php
get_footer();
?>

