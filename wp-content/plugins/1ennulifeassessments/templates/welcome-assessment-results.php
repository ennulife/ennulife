<?php
/**
 * Hair Assessment Results Template
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="ennu-results-container">
    <div class="ennu-results-content">
        <div class="success-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="#667eea" stroke-width="2" fill="#f8f9ff"/>
                <path d="m9 12 2 2 4-4" stroke="#667eea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        
        <div class="thank-you-section">
            <h1>Your Hair Assessment is Complete!</h1>
            <p class="thank-you-message">
                Thank you for completing your hair health assessment. Our hair restoration specialists will review your responses to create a personalized hair growth plan tailored to your specific needs.
            </p>
            
            <div class="next-steps">
                <h2>What's Next?</h2>
                <p>
                    Schedule a consultation with our hair restoration specialists to discuss your personalized treatment options and get started on your hair growth journey.
                </p>
                
                <div class="consultation-cta">
                    <a href="<?php echo esc_url(home_url('/book-hair-consultation/')); ?>" class="schedule-consultation-btn hair-theme">
                        <span class="btn-icon">🦱</span>
                        Schedule Your Hair Consultation
                    </a>
                </div>
                
                <div class="additional-info">
                    <h3>What to expect in your consultation:</h3>
                    <ul>
                        <li>Personalized hair restoration strategy</li>
                        <li>Advanced treatment options (PRP, transplants, medications)</li>
                        <li>Hair growth timeline and realistic expectations</li>
                        <li>Customized pricing for your treatment plan</li>
                    </ul>
                </div>
                
                <div class="contact-info">
                    <p><strong>Questions about hair restoration?</strong> Call us at <a href="tel:+1-800-ENNU-HAIR">(800) ENNU-HAIR</a> or email <a href="mailto:hair@ennulife.com">hair@ennulife.com</a></p>
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
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
    text-align: center;
    border-top: 5px solid #667eea;
}

.success-icon {
    margin-bottom: 30px;
    animation: fadeInScale 0.6s ease-out;
}

@keyframes fadeInScale {
    0% { opacity: 0; transform: scale(0.5); }
    100% { opacity: 1; transform: scale(1); }
}

.thank-you-section h1 {
    color: #667eea;
    font-size: 2.5em;
    margin-bottom: 20px;
    font-weight: bold;
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
    color: #667eea;
    font-size: 1.8em;
    margin-bottom: 20px;
}

.consultation-cta {
    margin: 30px 0;
}

.schedule-consultation-btn {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 18px 35px;
    text-decoration: none;
    border-radius: 50px;
    font-size: 1.1em;
    font-weight: bold;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.schedule-consultation-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

.btn-icon {
    margin-right: 10px;
    font-size: 1.2em;
}

.additional-info {
    margin-top: 40px;
    text-align: left;
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    padding: 25px;
    border-radius: 10px;
    border-left: 4px solid #667eea;
}

.additional-info h3 {
    color: #667eea;
    margin-bottom: 15px;
}

.additional-info ul {
    margin: 10px 0;
    padding-left: 20px;
}

.additional-info li {
    margin: 12px 0;
    color: #555;
    line-height: 1.5;
}

.contact-info {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 0.95em;
}

.contact-info a {
    color: #667eea;
    text-decoration: none;
    font-weight: bold;
}

.contact-info a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .ennu-results-content {
        padding: 25px;
    }
    
    .thank-you-section h1 {
        font-size: 2em;
    }
    
    .schedule-consultation-btn {
        padding: 15px 28px;
        font-size: 1em;
    }
}
</style>

<?php
get_footer();
?>

