<?php
/**
 * Weight Loss Assessment Results Template
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
                <circle cx="12" cy="12" r="10" stroke="#4facfe" stroke-width="2" fill="#f0faff"/>
                <path d="m9 12 2 2 4-4" stroke="#4facfe" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        
        <div class="thank-you-section">
            <h1>Your Weight Loss Assessment is Complete!</h1>
            <p class="thank-you-message">
                Thank you for completing your weight management assessment. Our team will create a comprehensive weight loss plan designed specifically for your goals and lifestyle.
            </p>
            
            <div class="next-steps">
                <h2>What's Next?</h2>
                <p>
                    Schedule a consultation with our weight loss specialists to discuss your personalized treatment options and start your transformation journey today.
                </p>
                
                <div class="consultation-cta">
                    <a href="<?php echo esc_url(home_url('/book-weight-loss-consultation/')); ?>" class="schedule-consultation-btn weight-theme">
                        <span class="btn-icon">⚖️</span>
                        Schedule Your Weight Loss Consultation
                    </a>
                </div>
                
                <div class="additional-info">
                    <h3>What to expect in your consultation:</h3>
                    <ul>
                        <li>Customized weight loss strategy</li>
                        <li>Medical weight loss options (Semaglutide, etc.)</li>
                        <li>Nutritional guidance and meal planning</li>
                        <li>Long-term success and maintenance plan</li>
                    </ul>
                </div>
                
                <div class="success-stats">
                    <h3>🎯 Our Success Stories</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">85%</div>
                            <div class="stat-label">Achieve Goal Weight</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">15-30lbs</div>
                            <div class="stat-label">Average Weight Loss</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">90%</div>
                            <div class="stat-label">Maintain Results</div>
                        </div>
                    </div>
                </div>
                
                <div class="contact-info">
                    <p><strong>Questions about weight loss?</strong> Call us at <a href="tel:+1-800-ENNU-SLIM">(800) ENNU-SLIM</a> or email <a href="mailto:weightloss@ennulife.com">weightloss@ennulife.com</a></p>
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
    box-shadow: 0 8px 25px rgba(79, 172, 254, 0.15);
    text-align: center;
    border-top: 5px solid #4facfe;
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
    color: #4facfe;
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
    color: #4facfe;
    font-size: 1.8em;
    margin-bottom: 20px;
}

.consultation-cta {
    margin: 30px 0;
}

.schedule-consultation-btn {
    display: inline-block;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 18px 35px;
    text-decoration: none;
    border-radius: 50px;
    font-size: 1.1em;
    font-weight: bold;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
}

.schedule-consultation-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79, 172, 254, 0.4);
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
    background: linear-gradient(135deg, #f0faff 0%, #e6f7ff 100%);
    padding: 25px;
    border-radius: 10px;
    border-left: 4px solid #4facfe;
}

.additional-info h3 {
    color: #4facfe;
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

.success-stats {
    margin-top: 40px;
    padding: 25px;
    background: linear-gradient(135deg, #f0fff4 0%, #e6ffed 100%);
    border-radius: 10px;
    border-left: 4px solid #28a745;
}

.success-stats h3 {
    color: #28a745;
    margin-bottom: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 2em;
    font-weight: bold;
    color: #4facfe;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9em;
    color: #666;
}

.contact-info {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 0.95em;
}

.contact-info a {
    color: #4facfe;
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
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
get_footer();
?>

