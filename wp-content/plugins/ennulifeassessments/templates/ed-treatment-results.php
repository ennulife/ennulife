<?php
/**
 * ED Treatment Assessment Results Template
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
                <circle cx="12" cy="12" r="10" stroke="#f093fb" stroke-width="2" fill="#fef7ff"/>
                <path d="m9 12 2 2 4-4" stroke="#f093fb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        
        <div class="thank-you-section">
            <h1>Your ED Assessment is Complete!</h1>
            <p class="thank-you-message">
                Thank you for taking this important step. Our medical professionals will confidentially review your responses to recommend the most effective ED treatment options for you.
            </p>
            
            <div class="next-steps">
                <h2>What's Next?</h2>
                <p>
                    Schedule a confidential consultation with our medical specialists to discuss your personalized treatment options in a discreet and professional environment.
                </p>
                
                <div class="consultation-cta">
                    <a href="<?php echo esc_url(home_url('/book-ed-consultation/')); ?>" class="schedule-consultation-btn ed-theme">
                        <span class="btn-icon">🔒</span>
                        Schedule Your Confidential Consultation
                    </a>
                </div>
                
                <div class="additional-info">
                    <h3>What to expect in your consultation:</h3>
                    <ul>
                        <li>Confidential medical consultation</li>
                        <li>FDA-approved treatment options</li>
                        <li>Discreet and professional care</li>
                        <li>Personalized treatment recommendations</li>
                    </ul>
                </div>
                
                <div class="privacy-notice">
                    <p><strong>🔒 Your Privacy is Protected:</strong> All consultations are completely confidential and HIPAA compliant. Your information is secure and private.</p>
                </div>
                
                <div class="contact-info">
                    <p><strong>Confidential questions?</strong> Call us at <a href="tel:+1-800-ENNU-MENS">(800) ENNU-MENS</a> or email <a href="mailto:confidential@ennulife.com">confidential@ennulife.com</a></p>
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
    box-shadow: 0 8px 25px rgba(240, 147, 251, 0.15);
    text-align: center;
    border-top: 5px solid #f093fb;
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
    color: #f093fb;
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
    color: #f093fb;
    font-size: 1.8em;
    margin-bottom: 20px;
}

.consultation-cta {
    margin: 30px 0;
}

.schedule-consultation-btn {
    display: inline-block;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 18px 35px;
    text-decoration: none;
    border-radius: 50px;
    font-size: 1.1em;
    font-weight: bold;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
}

.schedule-consultation-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(240, 147, 251, 0.4);
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
    background: linear-gradient(135deg, #fef7ff 0%, #fdf2ff 100%);
    padding: 25px;
    border-radius: 10px;
    border-left: 4px solid #f093fb;
}

.additional-info h3 {
    color: #f093fb;
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

.privacy-notice {
    margin-top: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
    border-radius: 8px;
    border-left: 4px solid #28a745;
    font-size: 0.95em;
}

.contact-info {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 0.95em;
}

.contact-info a {
    color: #f093fb;
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

