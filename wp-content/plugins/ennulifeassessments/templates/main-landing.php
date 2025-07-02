<?php
/**
 * ENNU Life Main Landing Template
 * 
 * @package ENNU_Life
 * @version 14.1.12
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ennu-main-landing">
    <div class="ennu-hero-section">
        <h1>Welcome to ENNU Life</h1>
        <p>Your comprehensive health assessment and wellness platform</p>
        <div class="ennu-cta-buttons">
            <a href="#assessments" class="ennu-btn ennu-btn-primary">Start Assessment</a>
            <a href="#services" class="ennu-btn ennu-btn-secondary">View Services</a>
        </div>
    </div>
    
    <div class="ennu-features-section" id="assessments">
        <h2>Health Assessments</h2>
        <div class="ennu-assessment-grid">
            <div class="ennu-assessment-card">
                <h3>General Health</h3>
                <p>Comprehensive health evaluation</p>
                <a href="/health-assessment/" class="ennu-btn">Start Assessment</a>
            </div>
            <div class="ennu-assessment-card">
                <h3>Weight Management</h3>
                <p>Personalized weight loss guidance</p>
                <a href="/weight-loss-assessment/" class="ennu-btn">Start Assessment</a>
            </div>
            <div class="ennu-assessment-card">
                <h3>Skin Analysis</h3>
                <p>Advanced skin health evaluation</p>
                <a href="/skin-assessment/" class="ennu-btn">Start Assessment</a>
            </div>
        </div>
    </div>
</div>

