<?php
/**
 * ENNU Life Assessment Shortcodes Class - Perfected Version
 * 
 * Handles all assessment shortcodes with proper security, performance,
 * and WordPress standards compliance.
 * 
 * @package ENNU_Life
 * @version 14.1.11
 * @author ENNU Life Development Team
 * @since 14.1.11
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 * ENNU Assessment Shortcodes Class
 * 
 * Provides secure, performant, and accessible assessment shortcodes
 * with Pixfort icon integration and proper WordPress standards.
 */
final class ENNU_Assessment_Shortcodes {
    
    /**
     * Assessment configurations
     * 
     * @var array
     */
    private $assessments = array();
    
    /**
     * Template cache
     * 
     * @var array
     */
    private $template_cache = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_assessments();
        $this->register_shortcodes();
        $this->setup_hooks();
    }
    
    /**
     * Initialize assessment configurations
     */
    private function init_assessments() {
        $this->assessments = array(
            'hair_assessment' => array(
                'title' => __( 'Hair Assessment', 'ennu-life' ),
                'description' => __( 'Comprehensive hair health evaluation', 'ennu-life' ),
                'questions' => 10,
                'theme_color' => '#667eea',
                'icon_set' => 'hair'
            ),
            'hair_restoration_assessment' => array(
                'title' => __( 'Hair Restoration Assessment', 'ennu-life' ),
                'description' => __( 'Advanced hair restoration evaluation', 'ennu-life' ),
                'questions' => 11,
                'theme_color' => '#764ba2',
                'icon_set' => 'restoration'
            ),
            'ed_treatment_assessment' => array(
                'title' => __( 'ED Treatment Assessment', 'ennu-life' ),
                'description' => __( 'Confidential ED treatment evaluation', 'ennu-life' ),
                'questions' => 11,
                'theme_color' => '#f093fb',
                'icon_set' => 'medical'
            ),
            'weight_loss_assessment' => array(
                'title' => __( 'Weight Loss Assessment', 'ennu-life' ),
                'description' => __( 'Personalized weight management evaluation', 'ennu-life' ),
                'questions' => 12,
                'theme_color' => '#4facfe',
                'icon_set' => 'fitness'
            ),
            'weight_loss_quiz' => array(
                'title' => __( 'Weight Loss Quiz', 'ennu-life' ),
                'description' => __( 'Quick weight loss readiness quiz', 'ennu-life' ),
                'questions' => 8,
                'theme_color' => '#43e97b',
                'icon_set' => 'quiz'
            ),
            'health_assessment' => array(
                'title' => __( 'Health Assessment', 'ennu-life' ),
                'description' => __( 'Comprehensive health evaluation', 'ennu-life' ),
                'questions' => 10,
                'theme_color' => '#fa709a',
                'icon_set' => 'health'
            ),
            'advanced_skin_assessment' => array(
                'title' => __( 'Advanced Skin Assessment', 'ennu-life' ),
                'description' => __( 'Detailed skin health analysis', 'ennu-life' ),
                'questions' => 9,
                'theme_color' => '#a8edea',
                'icon_set' => 'skin'
            ),
            'skin_assessment_enhanced' => array(
                'title' => __( 'Skin Assessment Enhanced', 'ennu-life' ),
                'description' => __( 'Enhanced skin evaluation', 'ennu-life' ),
                'questions' => 8,
                'theme_color' => '#d299c2',
                'icon_set' => 'skincare'
            ),
            'hormone_assessment' => array(
                'title' => __( 'Hormone Assessment', 'ennu-life' ),
                'description' => __( 'Comprehensive hormone evaluation', 'ennu-life' ),
                'questions' => 12,
                'theme_color' => '#ffecd2',
                'icon_set' => 'hormone'
            )
        );
    }
    
    /**
     * Register all assessment shortcodes (Method 1 - Specific Shortcodes Only)
     */
    private function register_shortcodes() {
        // Register only the 5 core PRD-compliant assessment shortcodes
        $core_assessments = array(
            'hair_assessment' => 'ennu-hair-assessment',
            'ed_treatment_assessment' => 'ennu-ed-treatment-assessment',
            'weight_loss_assessment' => 'ennu-weight-loss-assessment',
            'health_assessment' => 'ennu-health-assessment',
            'skin_assessment' => 'ennu-skin-assessment'
        );
        
        foreach ( $core_assessments as $assessment_key => $shortcode_tag ) {
            if ( isset( $this->assessments[ $assessment_key ] ) ) {
                add_shortcode( $shortcode_tag, array( $this, 'render_assessment_shortcode' ) );
                error_log( "ENNU: Registered shortcode [{$shortcode_tag}] for {$assessment_key}" );
            } else {
                error_log( "ENNU: Warning - Assessment {$assessment_key} not found in configurations" );
            }
        }
        
        error_log( "ENNU: Registered " . count( $core_assessments ) . " core assessment shortcodes" );
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assessment_assets' ) );
        add_action( 'wp_ajax_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
        add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
    }
    
    /**
     * Render assessment shortcode
     * 
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     * @param string $tag Shortcode tag
     * @return string
     */
    public function render_assessment_shortcode( $atts, $content = '', $tag = '' ) {
        // Extract assessment type from shortcode tag
        $assessment_type = str_replace( array( 'ennu-', '-' ), array( '', '_' ), $tag );
        
        // Validate assessment type
        if ( ! isset( $this->assessments[ $assessment_type ] ) ) {
            return $this->render_error_message( __( 'Invalid assessment type.', 'ennu-life' ) );
        }
        
        // Parse attributes
        $atts = shortcode_atts( array(
            'theme' => 'default',
            'show_progress' => 'true',
            'auto_advance' => 'true',
            'cache' => 'true'
        ), $atts, $tag );
        
        // Check cache
        $cache_key = md5( $assessment_type . serialize( $atts ) );
        if ( $atts['cache'] === 'true' && isset( $this->template_cache[ $cache_key ] ) ) {
            return $this->template_cache[ $cache_key ];
        }
        
        try {
            // Render assessment
            $output = $this->render_assessment( $assessment_type, $atts );
            
            // Cache output
            if ( $atts['cache'] === 'true' ) {
                $this->template_cache[ $cache_key ] = $output;
            }
            
            return $output;
            
        } catch ( Exception $e ) {
            error_log( 'ENNU Assessment Error: ' . $e->getMessage() );
            return $this->render_error_message( __( 'Assessment temporarily unavailable.', 'ennu-life' ) );
        }
    }
    
    /**
     * Render assessment HTML
     * 
     * @param string $assessment_type Assessment type
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function render_assessment( $assessment_type, $atts ) {
        $config = $this->assessments[ $assessment_type ];
        $current_user = wp_get_current_user();
        
        // Start output buffering
        ob_start();
        
        // Include assessment template
        $template_file = $this->get_assessment_template( $assessment_type );
        if ( file_exists( $template_file ) ) {
            include $template_file;
        } else {
            echo $this->render_default_assessment( $assessment_type, $config, $atts );
        }
        
        return ob_get_clean();
    }
    
    /**
     * Get assessment template file path
     * 
     * @param string $assessment_type Assessment type
     * @return string
     */
    private function get_assessment_template( $assessment_type ) {
        $template_name = 'assessment-' . str_replace( '_', '-', $assessment_type ) . '.php';
        
        // Check theme directory first
        $theme_template = get_stylesheet_directory() . '/ennu-life/' . $template_name;
        if ( file_exists( $theme_template ) ) {
            return $theme_template;
        }
        
        // Check plugin templates directory
        $plugin_template = ENNU_LIFE_PLUGIN_PATH . 'templates/' . $template_name;
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
        
        return '';
    }
    
    /**
     * Render default assessment template
     * 
     * @param string $assessment_type Assessment type
     * @param array $config Assessment configuration
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function render_default_assessment( $assessment_type, $config, $atts ) {
        $current_user = wp_get_current_user();
        $nonce = wp_create_nonce( 'ennu_assessment_' . $assessment_type );
        
        ob_start();
        ?>
        <div class="ennu-assessment ennu-<?php echo esc_attr( $assessment_type ); ?>" 
             data-assessment="<?php echo esc_attr( $assessment_type ); ?>"
             data-theme="<?php echo esc_attr( $atts['theme'] ); ?>">
             
            <!-- Assessment Header -->
            <div class="assessment-header">
                <h1 class="assessment-title"><?php echo esc_html( $config['title'] ); ?></h1>
                <p class="assessment-description"><?php echo esc_html( $config['description'] ); ?></p>
                
                <?php if ( $atts['show_progress'] === 'true' ) : ?>
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" data-progress="0"></div>
                    </div>
                    <div class="progress-text">
                        <span><?php esc_html_e( 'Question', 'ennu-life' ); ?> 
                              <span id="currentStep" class="current-question">1</span> 
                              <?php esc_html_e( 'of', 'ennu-life' ); ?> 
                              <span id="totalSteps" class="total-questions"><?php echo esc_html( $config['questions'] ); ?></span>
                        </span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Assessment Form -->
            <form class="assessment-form" data-assessment="<?php echo esc_attr( $assessment_type ); ?>">
                <?php wp_nonce_field( 'ennu_assessment_' . $assessment_type, 'assessment_nonce' ); ?>
                <input type="hidden" name="action" value="ennu_submit_assessment">
                <input type="hidden" name="assessment_type" value="<?php echo esc_attr( $assessment_type ); ?>">
                
                <!-- Questions Container -->
                <div class="questions-container">
                    <?php echo $this->render_assessment_questions( $assessment_type, $config ); ?>
                </div>
                
                <!-- Contact Information (Final Step) -->
                <div class="question contact-question" data-question="<?php echo esc_attr( $config['questions'] + 1 ); ?>">
                    <h2><?php esc_html_e( 'Get Your Personalized Results', 'ennu-life' ); ?></h2>
                    <p><?php esc_html_e( 'Enter your contact information to receive your detailed assessment results and personalized recommendations.', 'ennu-life' ); ?></p>
                    
                    <div class="contact-fields">
                        <div class="field-group">
                            <label for="contact_name"><?php esc_html_e( 'Full Name', 'ennu-life' ); ?> <span class="required">*</span></label>
                            <input type="text" 
                                   id="contact_name" 
                                   name="contact_name" 
                                   value="<?php echo esc_attr( $current_user->display_name ); ?>" 
                                   required 
                                   autocomplete="name">
                        </div>
                        
                        <div class="field-group">
                            <label for="contact_email"><?php esc_html_e( 'Email Address', 'ennu-life' ); ?> <span class="required">*</span></label>
                            <input type="email" 
                                   id="contact_email" 
                                   name="contact_email" 
                                   value="<?php echo esc_attr( $current_user->user_email ); ?>" 
                                   required 
                                   autocomplete="email">
                        </div>
                        
                        <div class="field-group">
                            <label for="contact_phone"><?php esc_html_e( 'Phone Number', 'ennu-life' ); ?></label>
                            <input type="tel" 
                                   id="contact_phone" 
                                   name="contact_phone" 
                                   autocomplete="tel">
                        </div>
                    </div>
                    
                    <div class="privacy-notice">
                        <p><small><?php esc_html_e( 'Your information is secure and will only be used to provide your assessment results and relevant health information. We respect your privacy.', 'ennu-life' ); ?></small></p>
                    </div>
                    
                    <button type="submit" class="submit-assessment-btn">
                        <span class="btn-text"><?php esc_html_e( 'Get My Results', 'ennu-life' ); ?></span>
                        <span class="btn-loading" style="display: none;"><?php esc_html_e( 'Processing...', 'ennu-life' ); ?></span>
                    </button>
                </div>
                
                <!-- Success Message -->
                <div class="assessment-success" style="display: none;">
                    <div class="success-icon">✓</div>
                    <h2><?php esc_html_e( 'Assessment Complete!', 'ennu-life' ); ?></h2>
                    <p><?php esc_html_e( 'Thank you for completing your assessment. Your personalized results and recommendations will be sent to your email shortly.', 'ennu-life' ); ?></p>
                    <div class="next-steps">
                        <h3><?php esc_html_e( 'What happens next?', 'ennu-life' ); ?></h3>
                        <ul>
                            <li><?php esc_html_e( 'Our medical team will review your responses', 'ennu-life' ); ?></li>
                            <li><?php esc_html_e( 'You\'ll receive personalized recommendations via email', 'ennu-life' ); ?></li>
                            <li><?php esc_html_e( 'A specialist may contact you to discuss treatment options', 'ennu-life' ); ?></li>
                        </ul>
                    </div>
                </div>
            </form>
            
            <!-- Navigation -->
            <div class="assessment-navigation">
                <button type="button" class="nav-btn prev-btn" disabled>
                    <span><?php esc_html_e( 'Previous', 'ennu-life' ); ?></span>
                </button>
                <button type="button" class="nav-btn next-btn" disabled>
                    <span><?php esc_html_e( 'Next', 'ennu-life' ); ?></span>
                </button>
            </div>
        </div>
        
        <!-- Assessment Styles -->
        <style>
        .ennu-assessment {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .assessment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .assessment-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: <?php echo esc_attr( $config['theme_color'] ); ?>;
            margin-bottom: 10px;
        }
        
        .assessment-description {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 20px;
        }
        
        .progress-container {
            margin: 20px 0;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, <?php echo esc_attr( $config['theme_color'] ); ?>, <?php echo esc_attr( $this->adjust_color_brightness( $config['theme_color'], 20 ) ); ?>);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 0.9rem;
            color: #666;
        }
        
        .questions-container {
            position: relative;
            min-height: 400px;
        }
        
        .question {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        .question.active {
            display: block;
        }
        
        .question h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .question p {
            font-size: 1rem;
            color: #666;
            margin-bottom: 30px;
        }
        
        .options-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .options-grid[data-columns="2"] { grid-template-columns: repeat(2, 1fr); }
        .options-grid[data-columns="3"] { grid-template-columns: repeat(3, 1fr); }
        .options-grid[data-columns="4"] { grid-template-columns: repeat(2, 1fr); }
        .options-grid[data-columns="5"] { grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); }
        
        @media (max-width: 768px) {
            .options-grid {
                grid-template-columns: 1fr !important;
            }
        }
        
        .option-card {
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }
        
        .option-card:hover {
            border-color: <?php echo esc_attr( $config['theme_color'] ); ?>;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .option-card.selected {
            border-color: <?php echo esc_attr( $config['theme_color'] ); ?>;
            background: linear-gradient(135deg, <?php echo esc_attr( $config['theme_color'] ); ?>15, <?php echo esc_attr( $config['theme_color'] ); ?>25);
            color: #333;
        }
        
        .option-card .icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .option-card span {
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .contact-fields {
            display: grid;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .field-group {
            display: flex;
            flex-direction: column;
        }
        
        .field-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .field-group input {
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .field-group input:focus {
            outline: none;
            border-color: <?php echo esc_attr( $config['theme_color'] ); ?>;
        }
        
        .required {
            color: #e74c3c;
        }
        
        .privacy-notice {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid <?php echo esc_attr( $config['theme_color'] ); ?>;
        }
        
        .submit-assessment-btn {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, <?php echo esc_attr( $config['theme_color'] ); ?>, <?php echo esc_attr( $this->adjust_color_brightness( $config['theme_color'], -20 ) ); ?>);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-assessment-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .submit-assessment-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .assessment-success {
            text-align: center;
            padding: 40px 20px;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #27ae60;
            margin-bottom: 20px;
        }
        
        .assessment-success h2 {
            color: #27ae60;
            margin-bottom: 15px;
        }
        
        .next-steps {
            margin-top: 30px;
            text-align: left;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        
        .next-steps ul {
            list-style: none;
            padding: 0;
        }
        
        .next-steps li {
            padding: 8px 0;
            position: relative;
            padding-left: 25px;
        }
        
        .next-steps li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #27ae60;
            font-weight: bold;
        }
        
        .assessment-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .nav-btn {
            padding: 12px 24px;
            border: 2px solid <?php echo esc_attr( $config['theme_color'] ); ?>;
            background: transparent;
            color: <?php echo esc_attr( $config['theme_color'] ); ?>;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .nav-btn:hover:not(:disabled) {
            background: <?php echo esc_attr( $config['theme_color'] ); ?>;
            color: white;
        }
        
        .nav-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .ennu-assessment {
                padding: 15px;
                margin: 10px;
            }
            
            .assessment-title {
                font-size: 2rem;
            }
            
            .question h2 {
                font-size: 1.5rem;
            }
            
            .option-card {
                padding: 15px;
            }
            
            .assessment-navigation {
                flex-direction: column;
                gap: 10px;
            }
        }
        </style>
        
        <!-- Assessment JavaScript -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const assessment = new ENNUAssessment('<?php echo esc_js( $assessment_type ); ?>', {
                totalQuestions: <?php echo esc_js( $config['questions'] ); ?>,
                autoAdvance: <?php echo esc_js( $atts['auto_advance'] === 'true' ? 'true' : 'false' ); ?>,
                showProgress: <?php echo esc_js( $atts['show_progress'] === 'true' ? 'true' : 'false' ); ?>
            });
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render assessment questions
     * 
     * @param string $assessment_type Assessment type
     * @param array $config Assessment configuration
     * @return string
     */
    private function render_assessment_questions( $assessment_type, $config ) {
        $questions = $this->get_assessment_questions( $assessment_type );
        $output = '';
        
        foreach ( $questions as $index => $question ) {
            $question_number = $index + 1;
            $output .= $this->render_question( $question_number, $question, $config );
        }
        
        return $output;
    }
    
    /**
     * Render individual question
     * 
     * @param int $question_number Question number
     * @param array $question Question data
     * @param array $config Assessment configuration
     * @return string
     */
    private function render_question( $question_number, $question, $config ) {
        $active_class = $question_number === 1 ? 'active' : '';
        $columns = count( $question['options'] );
        
        ob_start();
        ?>
        <div class="ennu-question-step question <?php echo esc_attr( $active_class ); ?>" 
             data-step="<?php echo esc_attr( $question_number ); ?>" 
             data-question="<?php echo esc_attr( $question_number ); ?>">
            <h2><?php echo esc_html( $question['title'] ); ?></h2>
            <?php if ( ! empty( $question['description'] ) ) : ?>
                <p><?php echo esc_html( $question['description'] ); ?></p>
            <?php endif; ?>
            
            <div class="options-grid" data-columns="<?php echo esc_attr( $columns ); ?>">
                <?php foreach ( $question['options'] as $option ) : ?>
                    <div class="ennu-answer-option option-card" data-value="<?php echo esc_attr( $option['value'] ); ?>">
                        <div class="icon"><?php echo $this->get_option_icon( $option['icon'], $config['icon_set'] ); ?></div>
                        <span><?php echo esc_html( $option['label'] ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get option icon (Pixfort or fallback)
     * 
     * @param string $icon_key Icon key
     * @param string $icon_set Icon set
     * @return string
     */
    private function get_option_icon( $icon_key, $icon_set ) {
        // Pixfort icon mapping
        $pixfort_icons = array(
            'age_18_25' => '<i class="pix-icon icon-person-graduated"></i>',
            'age_26_35' => '<i class="pix-icon icon-person-man"></i>',
            'age_36_45' => '<i class="pix-icon icon-person-doctor"></i>',
            'age_46_55' => '<i class="pix-icon icon-person-senior"></i>',
            'age_56_plus' => '<i class="pix-icon icon-person-king"></i>',
            'male' => '<i class="pix-icon icon-person-man"></i>',
            'female' => '<i class="pix-icon icon-person-woman"></i>',
            'other' => '<i class="pix-icon icon-user"></i>',
            'speed_slow' => '<i class="pix-icon icon-speedometer-1"></i>',
            'speed_moderate' => '<i class="pix-icon icon-speedometer-2"></i>',
            'speed_fast' => '<i class="pix-icon icon-speedometer-3"></i>',
            'speed_very_fast' => '<i class="pix-icon icon-speedometer-4"></i>'
        );
        
        // Check if Pixfort icon exists
        if ( isset( $pixfort_icons[ $icon_key ] ) ) {
            return $pixfort_icons[ $icon_key ];
        }
        
        // Fallback to text-based icons (NO UNICODE)
        $fallback_icons = array(
            'age_18_25' => '<span class="text-icon">GRAD</span>',
            'age_26_35' => '<span class="text-icon">M</span>',
            'age_36_45' => '<span class="text-icon">DR</span>',
            'age_46_55' => '<span class="text-icon">SR</span>',
            'age_56_plus' => '<span class="text-icon">F</span>',
            'male' => '<span class="text-icon">M</span>',
            'female' => '<span class="text-icon">F</span>',
            'other' => '<span class="text-icon">O</span>',
            'speed_slow' => '<span class="text-icon">SLOW</span>',
            'speed_moderate' => '<span class="text-icon">MED</span>',
            'speed_fast' => '<span class="text-icon">FAST</span>',
            'speed_very_fast' => '<span class="text-icon">MAX</span>',
            'concern_thinning' => '<span class="text-icon">!</span>',
            'concern_loss' => '<span class="text-icon">-</span>',
            'concern_growth' => '<span class="text-icon">+</span>',
            'concern_damage' => '<span class="text-icon">ZAP</span>'
        );
        
        return isset( $fallback_icons[ $icon_key ] ) ? $fallback_icons[ $icon_key ] : '<span class="text-icon">?</span>';
    }
    
    /**
     * Get assessment questions configuration
     * 
     * @param string $assessment_type Assessment type
     * @return array
     */
    private function get_assessment_questions( $assessment_type ) {
        // This would typically be loaded from a configuration file or database
        // For now, returning a sample structure
        
        switch ( $assessment_type ) {
            case 'hair_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your age range?', 'ennu-life' ),
                        'description' => __( 'This helps us recommend age-appropriate hair treatments.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '18-25', 'label' => '18-25', 'icon' => 'GRAD', 'fallback' => '18-25' ),
                            array( 'value' => '26-35', 'label' => '26-35', 'icon' => 'M', 'fallback' => '26-35' ),
                            array( 'value' => '36-45', 'label' => '36-45', 'icon' => 'DR', 'fallback' => '36-45' ),
                            array( 'value' => '46-55', 'label' => '46-55', 'icon' => 'SR', 'fallback' => '46-55' ),
                            array( 'value' => '56+', 'label' => '56+', 'icon' => 'F', 'fallback' => '56+' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Hair loss patterns can vary by gender.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male', 'icon' => 'M', 'fallback' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female', 'icon' => 'F', 'fallback' => 'Female' ),
                            array( 'value' => 'other', 'label' => 'Other', 'icon' => 'O', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'What are your main hair concerns?', 'ennu-life' ),
                        'description' => __( 'Select your primary hair issue.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'thinning', 'label' => 'Thinning Hair', 'icon' => '!', 'fallback' => 'Thin' ),
                            array( 'value' => 'receding', 'label' => 'Receding Hairline', 'icon' => '-', 'fallback' => 'Recede' ),
                            array( 'value' => 'bald_spots', 'label' => 'Bald Spots', 'icon' => '+', 'fallback' => 'Spots' ),
                            array( 'value' => 'overall_loss', 'label' => 'Overall Hair Loss', 'icon' => 'ZAP', 'fallback' => 'Loss' )
                        )
                    ),
                    array(
                        'title' => __( 'How long have you noticed hair changes?', 'ennu-life' ),
                        'description' => __( 'Duration helps determine treatment approach.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'recent', 'label' => 'Less than 6 months', 'icon' => '1', 'fallback' => '< 6mo' ),
                            array( 'value' => 'moderate', 'label' => '6 months - 2 years', 'icon' => '2', 'fallback' => '6mo-2yr' ),
                            array( 'value' => 'long', 'label' => '2-5 years', 'icon' => '3', 'fallback' => '2-5yr' ),
                            array( 'value' => 'very_long', 'label' => 'More than 5 years', 'icon' => '4', 'fallback' => '5yr+' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you rate the speed of hair loss?', 'ennu-life' ),
                        'description' => __( 'This helps determine urgency of treatment.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'slow', 'label' => 'Very Slow', 'icon' => 'SLOW', 'fallback' => 'Slow' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate', 'icon' => 'MED', 'fallback' => 'Med' ),
                            array( 'value' => 'fast', 'label' => 'Fast', 'icon' => 'FAST', 'fallback' => 'Fast' ),
                            array( 'value' => 'very_fast', 'label' => 'Very Fast', 'icon' => 'MAX', 'fallback' => 'Max' )
                        )
                    ),
                    array(
                        'title' => __( 'Do you have a family history of hair loss?', 'ennu-life' ),
                        'description' => __( 'Genetics play a major role in hair loss.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No Family History', 'icon' => 'OK', 'fallback' => 'None' ),
                            array( 'value' => 'mother', 'label' => 'Mother\'s Side', 'icon' => 'F', 'fallback' => 'Mom' ),
                            array( 'value' => 'father', 'label' => 'Father\'s Side', 'icon' => 'M', 'fallback' => 'Dad' ),
                            array( 'value' => 'both', 'label' => 'Both Sides', 'icon' => 'B', 'fallback' => 'Both' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your current stress level?', 'ennu-life' ),
                        'description' => __( 'Stress can significantly impact hair health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'low', 'label' => 'Low Stress', 'icon' => 'OK', 'fallback' => 'Low' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate Stress', 'icon' => 'A+', 'fallback' => 'Med' ),
                            array( 'value' => 'high', 'label' => 'High Stress', 'icon' => 'X', 'fallback' => 'High' ),
                            array( 'value' => 'very_high', 'label' => 'Very High Stress', 'icon' => 'HIGH', 'fallback' => 'Max' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe your diet quality?', 'ennu-life' ),
                        'description' => __( 'Nutrition affects hair growth and strength.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'excellent', 'label' => 'Excellent', 'icon' => 'A+', 'fallback' => 'Great' ),
                            array( 'value' => 'good', 'label' => 'Good', 'icon' => 'OK', 'fallback' => 'Good' ),
                            array( 'value' => 'fair', 'label' => 'Fair', 'icon' => 'X', 'fallback' => 'Fair' ),
                            array( 'value' => 'poor', 'label' => 'Poor', 'icon' => 'HIGH', 'fallback' => 'Poor' )
                        )
                    ),
                    array(
                        'title' => __( 'Have you tried any hair loss treatments?', 'ennu-life' ),
                        'description' => __( 'Previous treatments help guide recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No Treatments', 'icon' => 'OK', 'fallback' => 'None' ),
                            array( 'value' => 'otc', 'label' => 'Over-the-Counter', 'icon' => 'RX', 'fallback' => 'OTC' ),
                            array( 'value' => 'prescription', 'label' => 'Prescription Meds', 'icon' => 'HIGH', 'fallback' => 'RX' ),
                            array( 'value' => 'procedures', 'label' => 'Medical Procedures', 'icon' => 'MAX', 'fallback' => 'Proc' )
                        )
                    ),
                    array(
                        'title' => __( 'What are your hair restoration goals?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create the right plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'stop_loss', 'label' => 'Stop Hair Loss', 'icon' => 'X', 'fallback' => 'Stop' ),
                            array( 'value' => 'regrow', 'label' => 'Regrow Hair', 'icon' => '+', 'fallback' => 'Grow' ),
                            array( 'value' => 'thicken', 'label' => 'Thicken Hair', 'icon' => 'HIGH', 'fallback' => 'Thick' ),
                            array( 'value' => 'improve', 'label' => 'Overall Improvement', 'icon' => 'A+', 'fallback' => 'Better' )
                        )
                    )
                );
                
            case 'hair_restoration_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your age range?', 'ennu-life' ),
                        'description' => __( 'Age affects hair restoration options and success rates.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '18-25', 'label' => '18-25', 'icon' => 'GRAD', 'fallback' => '18-25' ),
                            array( 'value' => '26-35', 'label' => '26-35', 'icon' => 'M', 'fallback' => '26-35' ),
                            array( 'value' => '36-45', 'label' => '36-45', 'icon' => 'DR', 'fallback' => '36-45' ),
                            array( 'value' => '46-55', 'label' => '46-55', 'icon' => 'SR', 'fallback' => '46-55' ),
                            array( 'value' => '56+', 'label' => '56+', 'icon' => 'F', 'fallback' => '56+' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Treatment approaches vary by gender.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male', 'icon' => 'M', 'fallback' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female', 'icon' => 'F', 'fallback' => 'Female' ),
                            array( 'value' => 'other', 'label' => 'Other', 'icon' => 'O', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'What type of hair loss are you experiencing?', 'ennu-life' ),
                        'description' => __( 'Different patterns require different restoration approaches.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male_pattern', 'label' => 'Male Pattern Baldness', 'icon' => 'M', 'fallback' => 'MPB' ),
                            array( 'value' => 'female_pattern', 'label' => 'Female Pattern Hair Loss', 'icon' => 'F', 'fallback' => 'FPHL' ),
                            array( 'value' => 'alopecia', 'label' => 'Alopecia Areata', 'icon' => 'ZAP', 'fallback' => 'AA' ),
                            array( 'value' => 'other', 'label' => 'Other/Unsure', 'icon' => '?', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'How extensive is your hair loss?', 'ennu-life' ),
                        'description' => __( 'This helps determine the best restoration method.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'minimal', 'label' => 'Minimal (Early stages)', 'icon' => '1', 'fallback' => 'Min' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate', 'icon' => '2', 'fallback' => 'Med' ),
                            array( 'value' => 'advanced', 'label' => 'Advanced', 'icon' => '3', 'fallback' => 'Adv' ),
                            array( 'value' => 'severe', 'label' => 'Severe', 'icon' => '4', 'fallback' => 'Sev' )
                        )
                    ),
                    array(
                        'title' => __( 'Have you had any previous hair restoration procedures?', 'ennu-life' ),
                        'description' => __( 'Previous procedures affect future treatment options.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No Previous Procedures', 'icon' => 'OK', 'fallback' => 'None' ),
                            array( 'value' => 'fue', 'label' => 'FUE Hair Transplant', 'icon' => 'HIGH', 'fallback' => 'FUE' ),
                            array( 'value' => 'fut', 'label' => 'FUT Hair Transplant', 'icon' => 'MAX', 'fallback' => 'FUT' ),
                            array( 'value' => 'other', 'label' => 'Other Procedures', 'icon' => 'RX', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your budget range for hair restoration?', 'ennu-life' ),
                        'description' => __( 'This helps us recommend appropriate treatment options.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'under_5k', 'label' => 'Under $5,000', 'icon' => '$', 'fallback' => '<$5K' ),
                            array( 'value' => '5k_10k', 'label' => '$5,000 - $10,000', 'icon' => '$$', 'fallback' => '$5-10K' ),
                            array( 'value' => '10k_20k', 'label' => '$10,000 - $20,000', 'icon' => '$$$', 'fallback' => '$10-20K' ),
                            array( 'value' => 'over_20k', 'label' => 'Over $20,000', 'icon' => '$$$$', 'fallback' => '$20K+' )
                        )
                    ),
                    array(
                        'title' => __( 'How soon would you like to start treatment?', 'ennu-life' ),
                        'description' => __( 'Timeline affects treatment planning and scheduling.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'immediately', 'label' => 'Immediately', 'icon' => 'FAST', 'fallback' => 'Now' ),
                            array( 'value' => 'month', 'label' => 'Within a month', 'icon' => '1', 'fallback' => '1mo' ),
                            array( 'value' => 'quarter', 'label' => 'Within 3 months', 'icon' => '3', 'fallback' => '3mo' ),
                            array( 'value' => 'exploring', 'label' => 'Just exploring options', 'icon' => '?', 'fallback' => 'Later' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your primary concern about hair restoration?', 'ennu-life' ),
                        'description' => __( 'Understanding concerns helps address them properly.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'pain', 'label' => 'Pain/Discomfort', 'icon' => '!', 'fallback' => 'Pain' ),
                            array( 'value' => 'results', 'label' => 'Natural-looking Results', 'icon' => 'A+', 'fallback' => 'Results' ),
                            array( 'value' => 'downtime', 'label' => 'Recovery Time', 'icon' => 'SLOW', 'fallback' => 'Time' ),
                            array( 'value' => 'cost', 'label' => 'Cost/Value', 'icon' => '$', 'fallback' => 'Cost' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe your overall health?', 'ennu-life' ),
                        'description' => __( 'Health status affects treatment eligibility and outcomes.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'excellent', 'label' => 'Excellent', 'icon' => 'A+', 'fallback' => 'Great' ),
                            array( 'value' => 'good', 'label' => 'Good', 'icon' => 'OK', 'fallback' => 'Good' ),
                            array( 'value' => 'fair', 'label' => 'Fair', 'icon' => 'X', 'fallback' => 'Fair' ),
                            array( 'value' => 'poor', 'label' => 'Poor', 'icon' => 'HIGH', 'fallback' => 'Poor' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your main goal for hair restoration?', 'ennu-life' ),
                        'description' => __( 'Clear goals help create the best treatment plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'hairline', 'label' => 'Restore Hairline', 'icon' => '+', 'fallback' => 'Line' ),
                            array( 'value' => 'crown', 'label' => 'Fill Crown Area', 'icon' => 'O', 'fallback' => 'Crown' ),
                            array( 'value' => 'density', 'label' => 'Increase Overall Density', 'icon' => 'HIGH', 'fallback' => 'Dense' ),
                            array( 'value' => 'confidence', 'label' => 'Boost Confidence', 'icon' => 'A+', 'fallback' => 'Conf' )
                        )
                    ),
                    array(
                        'title' => __( 'Are you currently taking any medications?', 'ennu-life' ),
                        'description' => __( 'Some medications can affect hair restoration procedures.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No Medications', 'icon' => 'OK', 'fallback' => 'None' ),
                            array( 'value' => 'hair_meds', 'label' => 'Hair Loss Medications', 'icon' => 'RX', 'fallback' => 'Hair' ),
                            array( 'value' => 'blood_thinners', 'label' => 'Blood Thinners', 'icon' => '!', 'fallback' => 'Blood' ),
                            array( 'value' => 'other', 'label' => 'Other Medications', 'icon' => 'HIGH', 'fallback' => 'Other' )
                        )
                    )
                );
                
            case 'ed_treatment_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your age range?', 'ennu-life' ),
                        'description' => __( 'Age helps determine the most appropriate treatment approach.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '18-30', 'label' => '18-30', 'icon' => 'GRAD', 'fallback' => '18-30' ),
                            array( 'value' => '31-45', 'label' => '31-45', 'icon' => 'M', 'fallback' => '31-45' ),
                            array( 'value' => '46-60', 'label' => '46-60', 'icon' => 'DR', 'fallback' => '46-60' ),
                            array( 'value' => '60+', 'label' => '60+', 'icon' => 'SR', 'fallback' => '60+' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your relationship status?', 'ennu-life' ),
                        'description' => __( 'This helps us understand your treatment priorities.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'single', 'label' => 'Single', 'icon' => '1', 'fallback' => 'Single' ),
                            array( 'value' => 'dating', 'label' => 'Dating', 'icon' => '2', 'fallback' => 'Dating' ),
                            array( 'value' => 'married', 'label' => 'Married/Partnered', 'icon' => 'M', 'fallback' => 'Married' ),
                            array( 'value' => 'divorced', 'label' => 'Divorced/Separated', 'icon' => 'X', 'fallback' => 'Divorced' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe the severity of your ED?', 'ennu-life' ),
                        'description' => __( 'This helps determine the most effective treatment options.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'mild', 'label' => 'Mild', 'icon' => '1', 'fallback' => 'Mild' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate', 'icon' => '2', 'fallback' => 'Med' ),
                            array( 'value' => 'severe', 'label' => 'Severe', 'icon' => '3', 'fallback' => 'Severe' ),
                            array( 'value' => 'complete', 'label' => 'Complete', 'icon' => '4', 'fallback' => 'Complete' )
                        )
                    ),
                    array(
                        'title' => __( 'How long have you been experiencing symptoms?', 'ennu-life' ),
                        'description' => __( 'Duration affects treatment approach and expectations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'recent', 'label' => 'Less than 6 months', 'icon' => '1', 'fallback' => '<6mo' ),
                            array( 'value' => 'moderate', 'label' => '6 months - 2 years', 'icon' => '2', 'fallback' => '6mo-2yr' ),
                            array( 'value' => 'long', 'label' => '2-5 years', 'icon' => '3', 'fallback' => '2-5yr' ),
                            array( 'value' => 'very_long', 'label' => 'More than 5 years', 'icon' => '4', 'fallback' => '5yr+' )
                        )
                    ),
                    array(
                        'title' => __( 'Do you have any of these health conditions?', 'ennu-life' ),
                        'description' => __( 'Certain conditions affect treatment options and safety.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'None of these', 'icon' => 'OK', 'fallback' => 'None' ),
                            array( 'value' => 'diabetes', 'label' => 'Diabetes', 'icon' => 'D', 'fallback' => 'Diabetes' ),
                            array( 'value' => 'heart', 'label' => 'Heart Disease', 'icon' => 'H', 'fallback' => 'Heart' ),
                            array( 'value' => 'hypertension', 'label' => 'High Blood Pressure', 'icon' => 'BP', 'fallback' => 'BP' )
                        )
                    ),
                    array(
                        'title' => __( 'Have you tried any ED treatments before?', 'ennu-life' ),
                        'description' => __( 'Previous treatments help guide our recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No previous treatments', 'icon' => 'OK', 'fallback' => 'None' ),
                            array( 'value' => 'oral', 'label' => 'Oral medications', 'icon' => 'RX', 'fallback' => 'Pills' ),
                            array( 'value' => 'injections', 'label' => 'Injections', 'icon' => 'INJ', 'fallback' => 'Inject' ),
                            array( 'value' => 'devices', 'label' => 'Vacuum devices', 'icon' => 'DEV', 'fallback' => 'Device' )
                        )
                    ),
                    array(
                        'title' => __( 'Do you smoke or use tobacco?', 'ennu-life' ),
                        'description' => __( 'Smoking significantly affects blood flow and treatment effectiveness.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'never', 'label' => 'Never smoked', 'icon' => 'OK', 'fallback' => 'Never' ),
                            array( 'value' => 'former', 'label' => 'Former smoker', 'icon' => 'X', 'fallback' => 'Former' ),
                            array( 'value' => 'occasional', 'label' => 'Occasional smoker', 'icon' => '!', 'fallback' => 'Occ' ),
                            array( 'value' => 'regular', 'label' => 'Regular smoker', 'icon' => 'HIGH', 'fallback' => 'Regular' )
                        )
                    ),
                    array(
                        'title' => __( 'How often do you exercise?', 'ennu-life' ),
                        'description' => __( 'Physical fitness affects blood flow and overall sexual health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'never', 'label' => 'Never', 'icon' => 'X', 'fallback' => 'Never' ),
                            array( 'value' => 'rarely', 'label' => 'Rarely', 'icon' => '1', 'fallback' => 'Rare' ),
                            array( 'value' => 'regularly', 'label' => 'Regularly', 'icon' => 'OK', 'fallback' => 'Regular' ),
                            array( 'value' => 'daily', 'label' => 'Daily', 'icon' => 'A+', 'fallback' => 'Daily' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your current stress level?', 'ennu-life' ),
                        'description' => __( 'Stress is a major factor in erectile dysfunction.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'low', 'label' => 'Low', 'icon' => 'OK', 'fallback' => 'Low' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate', 'icon' => 'MED', 'fallback' => 'Med' ),
                            array( 'value' => 'high', 'label' => 'High', 'icon' => 'HIGH', 'fallback' => 'High' ),
                            array( 'value' => 'very_high', 'label' => 'Very High', 'icon' => 'MAX', 'fallback' => 'Max' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your primary treatment goal?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create the right treatment plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'restore', 'label' => 'Restore function', 'icon' => '+', 'fallback' => 'Restore' ),
                            array( 'value' => 'confidence', 'label' => 'Boost confidence', 'icon' => 'A+', 'fallback' => 'Conf' ),
                            array( 'value' => 'performance', 'label' => 'Improve performance', 'icon' => 'HIGH', 'fallback' => 'Perf' ),
                            array( 'value' => 'relationship', 'label' => 'Improve relationship', 'icon' => 'M', 'fallback' => 'Rel' )
                        )
                    ),
                    array(
                        'title' => __( 'Are you currently taking any medications?', 'ennu-life' ),
                        'description' => __( 'Some medications can affect ED treatment options.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No medications', 'icon' => 'OK', 'fallback' => 'None' ),
                            array( 'value' => 'blood_pressure', 'label' => 'Blood pressure meds', 'icon' => 'BP', 'fallback' => 'BP' ),
                            array( 'value' => 'antidepressants', 'label' => 'Antidepressants', 'icon' => 'AD', 'fallback' => 'AD' ),
                            array( 'value' => 'other', 'label' => 'Other medications', 'icon' => 'RX', 'fallback' => 'Other' )
                        )
                    )
                );
                
            case 'weight_loss_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your age range?', 'ennu-life' ),
                        'description' => __( 'Age affects metabolism and weight loss approach.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '18-25', 'label' => '18-25', 'icon' => 'GRAD', 'fallback' => '18-25' ),
                            array( 'value' => '26-35', 'label' => '26-35', 'icon' => 'M', 'fallback' => '26-35' ),
                            array( 'value' => '36-45', 'label' => '36-45', 'icon' => 'DR', 'fallback' => '36-45' ),
                            array( 'value' => '46-55', 'label' => '46-55', 'icon' => 'SR', 'fallback' => '46-55' ),
                            array( 'value' => '56+', 'label' => '56+', 'icon' => 'F', 'fallback' => '56+' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Weight loss strategies can vary by gender.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male', 'icon' => 'M', 'fallback' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female', 'icon' => 'F', 'fallback' => 'Female' ),
                            array( 'value' => 'other', 'label' => 'Other', 'icon' => 'O', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your primary weight loss goal?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create the right plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'lose_10', 'label' => 'Lose 10-20 lbs', 'icon' => '1', 'fallback' => '10-20' ),
                            array( 'value' => 'lose_30', 'label' => 'Lose 20-50 lbs', 'icon' => '2', 'fallback' => '20-50' ),
                            array( 'value' => 'lose_50', 'label' => 'Lose 50+ lbs', 'icon' => '3', 'fallback' => '50+' ),
                            array( 'value' => 'maintain', 'label' => 'Maintain current weight', 'icon' => 'OK', 'fallback' => 'Maintain' )
                        )
                    ),
                    array(
                        'title' => __( 'What motivates you most to lose weight?', 'ennu-life' ),
                        'description' => __( 'Understanding motivation helps maintain long-term success.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'health', 'label' => 'Health improvement', 'icon' => 'H', 'fallback' => 'Health' ),
                            array( 'value' => 'appearance', 'label' => 'Look better', 'icon' => 'A+', 'fallback' => 'Look' ),
                            array( 'value' => 'confidence', 'label' => 'Boost confidence', 'icon' => 'HIGH', 'fallback' => 'Conf' ),
                            array( 'value' => 'energy', 'label' => 'More energy', 'icon' => 'FAST', 'fallback' => 'Energy' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your target timeline?', 'ennu-life' ),
                        'description' => __( 'Realistic timelines lead to sustainable results.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '3_months', 'label' => '3 months', 'icon' => '3', 'fallback' => '3mo' ),
                            array( 'value' => '6_months', 'label' => '6 months', 'icon' => '6', 'fallback' => '6mo' ),
                            array( 'value' => '1_year', 'label' => '1 year', 'icon' => '1Y', 'fallback' => '1yr' ),
                            array( 'value' => 'no_rush', 'label' => 'No specific timeline', 'icon' => 'OK', 'fallback' => 'Flex' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe your current eating habits?', 'ennu-life' ),
                        'description' => __( 'Current habits help determine the best approach.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'healthy', 'label' => 'Generally healthy', 'icon' => 'A+', 'fallback' => 'Good' ),
                            array( 'value' => 'average', 'label' => 'Average/Mixed', 'icon' => 'OK', 'fallback' => 'Avg' ),
                            array( 'value' => 'poor', 'label' => 'Poor/Unhealthy', 'icon' => 'X', 'fallback' => 'Poor' ),
                            array( 'value' => 'emotional', 'label' => 'Emotional eating', 'icon' => '!', 'fallback' => 'Emot' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your current activity level?', 'ennu-life' ),
                        'description' => __( 'Activity level affects weight loss strategy and timeline.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'sedentary', 'label' => 'Sedentary (desk job)', 'icon' => 'SLOW', 'fallback' => 'Sed' ),
                            array( 'value' => 'light', 'label' => 'Lightly active', 'icon' => 'MED', 'fallback' => 'Light' ),
                            array( 'value' => 'moderate', 'label' => 'Moderately active', 'icon' => 'OK', 'fallback' => 'Mod' ),
                            array( 'value' => 'very_active', 'label' => 'Very active', 'icon' => 'FAST', 'fallback' => 'Active' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your biggest weight loss challenge?', 'ennu-life' ),
                        'description' => __( 'Identifying challenges helps create targeted solutions.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'cravings', 'label' => 'Food cravings', 'icon' => '!', 'fallback' => 'Crave' ),
                            array( 'value' => 'time', 'label' => 'Lack of time', 'icon' => 'FAST', 'fallback' => 'Time' ),
                            array( 'value' => 'motivation', 'label' => 'Staying motivated', 'icon' => 'HIGH', 'fallback' => 'Motiv' ),
                            array( 'value' => 'knowledge', 'label' => 'Don\'t know what to do', 'icon' => '?', 'fallback' => 'Know' )
                        )
                    ),
                    array(
                        'title' => __( 'Have you tried weight loss programs before?', 'ennu-life' ),
                        'description' => __( 'Previous experiences help avoid past mistakes.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No previous programs', 'icon' => 'OK', 'fallback' => 'None' ),
                            array( 'value' => 'diets', 'label' => 'Fad diets', 'icon' => 'X', 'fallback' => 'Diets' ),
                            array( 'value' => 'programs', 'label' => 'Commercial programs', 'icon' => 'RX', 'fallback' => 'Prog' ),
                            array( 'value' => 'medical', 'label' => 'Medical supervision', 'icon' => 'DR', 'fallback' => 'Med' )
                        )
                    ),
                    array(
                        'title' => __( 'Do you have any health conditions?', 'ennu-life' ),
                        'description' => __( 'Health conditions affect weight loss approach and safety.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No health conditions', 'icon' => 'OK', 'fallback' => 'None' ),
                            array( 'value' => 'diabetes', 'label' => 'Diabetes', 'icon' => 'D', 'fallback' => 'Diab' ),
                            array( 'value' => 'thyroid', 'label' => 'Thyroid issues', 'icon' => 'T', 'fallback' => 'Thy' ),
                            array( 'value' => 'other', 'label' => 'Other conditions', 'icon' => 'HIGH', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your budget for weight loss support?', 'ennu-life' ),
                        'description' => __( 'Budget helps determine the best program options.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'minimal', 'label' => 'Minimal ($0-50/month)', 'icon' => '$', 'fallback' => '$0-50' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate ($50-150/month)', 'icon' => '$$', 'fallback' => '$50-150' ),
                            array( 'value' => 'substantial', 'label' => 'Substantial ($150-300/month)', 'icon' => '$$$', 'fallback' => '$150-300' ),
                            array( 'value' => 'premium', 'label' => 'Premium ($300+/month)', 'icon' => '$$$$', 'fallback' => '$300+' )
                        )
                    ),
                    array(
                        'title' => __( 'What type of support do you prefer?', 'ennu-life' ),
                        'description' => __( 'Support style affects program success and satisfaction.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'self_guided', 'label' => 'Self-guided program', 'icon' => '1', 'fallback' => 'Self' ),
                            array( 'value' => 'group', 'label' => 'Group support', 'icon' => 'G', 'fallback' => 'Group' ),
                            array( 'value' => 'coach', 'label' => 'Personal coach', 'icon' => 'C', 'fallback' => 'Coach' ),
                            array( 'value' => 'medical', 'label' => 'Medical supervision', 'icon' => 'DR', 'fallback' => 'Med' )
                        )
                    )
                );
                
            case 'weight_loss_quiz':
                return array(
                    array(
                        'title' => __( 'What\'s your age range?', 'ennu-life' ),
                        'description' => __( 'Age affects your weight loss approach.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '18-30', 'label' => '18-30', 'icon' => 'GRAD', 'fallback' => '18-30' ),
                            array( 'value' => '31-45', 'label' => '31-45', 'icon' => 'M', 'fallback' => '31-45' ),
                            array( 'value' => '46-60', 'label' => '46-60', 'icon' => 'DR', 'fallback' => '46-60' ),
                            array( 'value' => '60+', 'label' => '60+', 'icon' => 'SR', 'fallback' => '60+' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'This helps personalize your recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male', 'icon' => 'M', 'fallback' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female', 'icon' => 'F', 'fallback' => 'Female' ),
                            array( 'value' => 'other', 'label' => 'Other', 'icon' => 'O', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'How much weight do you want to lose?', 'ennu-life' ),
                        'description' => __( 'Your goal determines the best approach.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '5_15', 'label' => '5-15 lbs', 'icon' => '1', 'fallback' => '5-15' ),
                            array( 'value' => '15_30', 'label' => '15-30 lbs', 'icon' => '2', 'fallback' => '15-30' ),
                            array( 'value' => '30_50', 'label' => '30-50 lbs', 'icon' => '3', 'fallback' => '30-50' ),
                            array( 'value' => '50_plus', 'label' => '50+ lbs', 'icon' => '4', 'fallback' => '50+' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your target timeline?', 'ennu-life' ),
                        'description' => __( 'Realistic timelines lead to better results.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '1_month', 'label' => '1 month', 'icon' => 'FAST', 'fallback' => '1mo' ),
                            array( 'value' => '3_months', 'label' => '3 months', 'icon' => 'MED', 'fallback' => '3mo' ),
                            array( 'value' => '6_months', 'label' => '6 months', 'icon' => 'OK', 'fallback' => '6mo' ),
                            array( 'value' => '1_year', 'label' => '1 year or more', 'icon' => 'SLOW', 'fallback' => '1yr+' )
                        )
                    ),
                    array(
                        'title' => __( 'Do you have any health conditions?', 'ennu-life' ),
                        'description' => __( 'This affects which programs are safe for you.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No conditions', 'icon' => 'OK', 'fallback' => 'None' ),
                            array( 'value' => 'diabetes', 'label' => 'Diabetes', 'icon' => 'D', 'fallback' => 'Diab' ),
                            array( 'value' => 'heart', 'label' => 'Heart disease', 'icon' => 'H', 'fallback' => 'Heart' ),
                            array( 'value' => 'other', 'label' => 'Other conditions', 'icon' => '!', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'How active are you currently?', 'ennu-life' ),
                        'description' => __( 'Current activity level affects your starting point.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'sedentary', 'label' => 'Sedentary', 'icon' => 'SLOW', 'fallback' => 'Sed' ),
                            array( 'value' => 'light', 'label' => 'Lightly active', 'icon' => 'MED', 'fallback' => 'Light' ),
                            array( 'value' => 'moderate', 'label' => 'Moderately active', 'icon' => 'OK', 'fallback' => 'Mod' ),
                            array( 'value' => 'very_active', 'label' => 'Very active', 'icon' => 'FAST', 'fallback' => 'Active' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your diet preference?', 'ennu-life' ),
                        'description' => __( 'We\'ll match you with compatible eating plans.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'balanced', 'label' => 'Balanced diet', 'icon' => 'OK', 'fallback' => 'Bal' ),
                            array( 'value' => 'low_carb', 'label' => 'Low carb', 'icon' => 'LC', 'fallback' => 'LowC' ),
                            array( 'value' => 'vegetarian', 'label' => 'Vegetarian', 'icon' => 'V', 'fallback' => 'Veg' ),
                            array( 'value' => 'flexible', 'label' => 'Flexible/Open', 'icon' => 'FLEX', 'fallback' => 'Flex' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your primary goal?', 'ennu-life' ),
                        'description' => __( 'Understanding your main goal helps create the right plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'lose_weight', 'label' => 'Lose weight', 'icon' => 'DOWN', 'fallback' => 'Lose' ),
                            array( 'value' => 'get_fit', 'label' => 'Get fit', 'icon' => 'FIT', 'fallback' => 'Fit' ),
                            array( 'value' => 'feel_better', 'label' => 'Feel better', 'icon' => 'A+', 'fallback' => 'Feel' ),
                            array( 'value' => 'look_better', 'label' => 'Look better', 'icon' => 'LOOK', 'fallback' => 'Look' )
                        )
                    )
                );
                
            case 'health_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your age range?', 'ennu-life' ),
                        'description' => __( 'Age helps us provide age-appropriate health recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '18-25', 'label' => '18-25', 'icon' => 'GRAD', 'fallback' => '18-25' ),
                            array( 'value' => '26-35', 'label' => '26-35', 'icon' => 'M', 'fallback' => '26-35' ),
                            array( 'value' => '36-45', 'label' => '36-45', 'icon' => 'DR', 'fallback' => '36-45' ),
                            array( 'value' => '46-55', 'label' => '46-55', 'icon' => 'SR', 'fallback' => '46-55' ),
                            array( 'value' => '56+', 'label' => '56+', 'icon' => 'F', 'fallback' => '56+' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Gender affects health risks and screening recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male', 'icon' => 'M', 'fallback' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female', 'icon' => 'F', 'fallback' => 'Female' ),
                            array( 'value' => 'other', 'label' => 'Other', 'icon' => 'O', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you rate your overall health?', 'ennu-life' ),
                        'description' => __( 'Your self-assessment helps guide our recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'excellent', 'label' => 'Excellent', 'icon' => 'A+', 'fallback' => 'Great' ),
                            array( 'value' => 'good', 'label' => 'Good', 'icon' => 'OK', 'fallback' => 'Good' ),
                            array( 'value' => 'fair', 'label' => 'Fair', 'icon' => 'X', 'fallback' => 'Fair' ),
                            array( 'value' => 'poor', 'label' => 'Poor', 'icon' => 'HIGH', 'fallback' => 'Poor' )
                        )
                    ),
                    array(
                        'title' => __( 'How are your energy levels?', 'ennu-life' ),
                        'description' => __( 'Energy levels can indicate various health issues.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'high', 'label' => 'High energy', 'icon' => 'FAST', 'fallback' => 'High' ),
                            array( 'value' => 'normal', 'label' => 'Normal energy', 'icon' => 'OK', 'fallback' => 'Normal' ),
                            array( 'value' => 'low', 'label' => 'Low energy', 'icon' => 'SLOW', 'fallback' => 'Low' ),
                            array( 'value' => 'very_low', 'label' => 'Very low energy', 'icon' => 'X', 'fallback' => 'VLow' )
                        )
                    ),
                    array(
                        'title' => __( 'How often do you exercise?', 'ennu-life' ),
                        'description' => __( 'Physical activity is crucial for overall health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'daily', 'label' => 'Daily', 'icon' => 'A+', 'fallback' => 'Daily' ),
                            array( 'value' => 'few_times_week', 'label' => 'Few times per week', 'icon' => 'OK', 'fallback' => '3-4x' ),
                            array( 'value' => 'weekly', 'label' => 'Once a week', 'icon' => 'MED', 'fallback' => '1x' ),
                            array( 'value' => 'rarely', 'label' => 'Rarely/Never', 'icon' => 'X', 'fallback' => 'Rare' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe your diet?', 'ennu-life' ),
                        'description' => __( 'Diet quality significantly impacts health outcomes.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'very_healthy', 'label' => 'Very healthy', 'icon' => 'A+', 'fallback' => 'VGood' ),
                            array( 'value' => 'mostly_healthy', 'label' => 'Mostly healthy', 'icon' => 'OK', 'fallback' => 'Good' ),
                            array( 'value' => 'average', 'label' => 'Average', 'icon' => 'MED', 'fallback' => 'Avg' ),
                            array( 'value' => 'poor', 'label' => 'Poor', 'icon' => 'X', 'fallback' => 'Poor' )
                        )
                    ),
                    array(
                        'title' => __( 'How well do you sleep?', 'ennu-life' ),
                        'description' => __( 'Sleep quality affects every aspect of health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'excellent', 'label' => 'Sleep very well', 'icon' => 'A+', 'fallback' => 'Great' ),
                            array( 'value' => 'good', 'label' => 'Sleep well', 'icon' => 'OK', 'fallback' => 'Good' ),
                            array( 'value' => 'fair', 'label' => 'Sleep okay', 'icon' => 'MED', 'fallback' => 'Fair' ),
                            array( 'value' => 'poor', 'label' => 'Sleep poorly', 'icon' => 'X', 'fallback' => 'Poor' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your stress level?', 'ennu-life' ),
                        'description' => __( 'Chronic stress impacts both physical and mental health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'low', 'label' => 'Low stress', 'icon' => 'OK', 'fallback' => 'Low' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate stress', 'icon' => 'MED', 'fallback' => 'Med' ),
                            array( 'value' => 'high', 'label' => 'High stress', 'icon' => 'HIGH', 'fallback' => 'High' ),
                            array( 'value' => 'very_high', 'label' => 'Very high stress', 'icon' => 'MAX', 'fallback' => 'Max' )
                        )
                    ),
                    array(
                        'title' => __( 'What are your main health goals?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create a personalized plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'prevent_disease', 'label' => 'Prevent disease', 'icon' => 'SHIELD', 'fallback' => 'Prevent' ),
                            array( 'value' => 'lose_weight', 'label' => 'Lose weight', 'icon' => 'DOWN', 'fallback' => 'Weight' ),
                            array( 'value' => 'more_energy', 'label' => 'More energy', 'icon' => 'FAST', 'fallback' => 'Energy' ),
                            array( 'value' => 'better_mood', 'label' => 'Better mood', 'icon' => 'A+', 'fallback' => 'Mood' )
                        )
                    ),
                    array(
                        'title' => __( 'What type of health assessment interests you most?', 'ennu-life' ),
                        'description' => __( 'This helps us prioritize your health screening recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'comprehensive', 'label' => 'Comprehensive checkup', 'icon' => 'ALL', 'fallback' => 'Full' ),
                            array( 'value' => 'preventive', 'label' => 'Preventive screening', 'icon' => 'SHIELD', 'fallback' => 'Prev' ),
                            array( 'value' => 'specific', 'label' => 'Specific concern', 'icon' => 'TARGET', 'fallback' => 'Spec' ),
                            array( 'value' => 'wellness', 'label' => 'Wellness optimization', 'icon' => 'A+', 'fallback' => 'Well' )
                        )
                    )
                );
                
            case 'advanced_skin_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your age range?', 'ennu-life' ),
                        'description' => __( 'Age affects skin concerns and treatment options.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '18-25', 'label' => '18-25', 'icon' => 'GRAD', 'fallback' => '18-25' ),
                            array( 'value' => '26-35', 'label' => '26-35', 'icon' => 'M', 'fallback' => '26-35' ),
                            array( 'value' => '36-45', 'label' => '36-45', 'icon' => 'DR', 'fallback' => '36-45' ),
                            array( 'value' => '46-55', 'label' => '46-55', 'icon' => 'SR', 'fallback' => '46-55' ),
                            array( 'value' => '56+', 'label' => '56+', 'icon' => 'F', 'fallback' => '56+' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Skin care needs can vary by gender.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male', 'icon' => 'M', 'fallback' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female', 'icon' => 'F', 'fallback' => 'Female' ),
                            array( 'value' => 'other', 'label' => 'Other', 'icon' => 'O', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your skin type?', 'ennu-life' ),
                        'description' => __( 'Skin type determines the best treatment approach.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'oily', 'label' => 'Oily', 'icon' => 'OIL', 'fallback' => 'Oily' ),
                            array( 'value' => 'dry', 'label' => 'Dry', 'icon' => 'DRY', 'fallback' => 'Dry' ),
                            array( 'value' => 'combination', 'label' => 'Combination', 'icon' => 'COMBO', 'fallback' => 'Combo' ),
                            array( 'value' => 'sensitive', 'label' => 'Sensitive', 'icon' => 'SENS', 'fallback' => 'Sens' )
                        )
                    ),
                    array(
                        'title' => __( 'What are your main skin concerns?', 'ennu-life' ),
                        'description' => __( 'Identifying concerns helps prioritize treatments.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'acne', 'label' => 'Acne/Breakouts', 'icon' => 'ACNE', 'fallback' => 'Acne' ),
                            array( 'value' => 'aging', 'label' => 'Aging/Wrinkles', 'icon' => 'AGE', 'fallback' => 'Age' ),
                            array( 'value' => 'pigmentation', 'label' => 'Dark spots/Pigmentation', 'icon' => 'SPOT', 'fallback' => 'Spots' ),
                            array( 'value' => 'texture', 'label' => 'Texture/Scarring', 'icon' => 'TEX', 'fallback' => 'Texture' )
                        )
                    ),
                    array(
                        'title' => __( 'How much sun exposure do you get?', 'ennu-life' ),
                        'description' => __( 'Sun exposure significantly affects skin health and aging.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'minimal', 'label' => 'Minimal (mostly indoors)', 'icon' => 'MIN', 'fallback' => 'Min' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate', 'icon' => 'MED', 'fallback' => 'Med' ),
                            array( 'value' => 'high', 'label' => 'High (outdoor job/activities)', 'icon' => 'HIGH', 'fallback' => 'High' ),
                            array( 'value' => 'very_high', 'label' => 'Very high', 'icon' => 'MAX', 'fallback' => 'Max' )
                        )
                    ),
                    array(
                        'title' => __( 'Do you use sunscreen regularly?', 'ennu-life' ),
                        'description' => __( 'Sunscreen use is crucial for preventing skin damage.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'daily', 'label' => 'Daily', 'icon' => 'A+', 'fallback' => 'Daily' ),
                            array( 'value' => 'sometimes', 'label' => 'Sometimes', 'icon' => 'OK', 'fallback' => 'Some' ),
                            array( 'value' => 'rarely', 'label' => 'Rarely', 'icon' => 'X', 'fallback' => 'Rare' ),
                            array( 'value' => 'never', 'label' => 'Never', 'icon' => 'NO', 'fallback' => 'Never' )
                        )
                    ),
                    array(
                        'title' => __( 'How extensive is your current skincare routine?', 'ennu-life' ),
                        'description' => __( 'Current routine affects treatment recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'comprehensive', 'label' => 'Comprehensive (5+ products)', 'icon' => 'COMP', 'fallback' => 'Full' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate (3-4 products)', 'icon' => 'MED', 'fallback' => 'Med' ),
                            array( 'value' => 'basic', 'label' => 'Basic (1-2 products)', 'icon' => 'BASIC', 'fallback' => 'Basic' ),
                            array( 'value' => 'minimal', 'label' => 'Minimal/None', 'icon' => 'MIN', 'fallback' => 'Min' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your budget for skincare treatments?', 'ennu-life' ),
                        'description' => __( 'Budget helps determine appropriate treatment options.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'under_100', 'label' => 'Under $100/month', 'icon' => '$', 'fallback' => '<$100' ),
                            array( 'value' => '100_300', 'label' => '$100-300/month', 'icon' => '$$', 'fallback' => '$100-300' ),
                            array( 'value' => '300_500', 'label' => '$300-500/month', 'icon' => '$$$', 'fallback' => '$300-500' ),
                            array( 'value' => 'over_500', 'label' => 'Over $500/month', 'icon' => '$$$$', 'fallback' => '$500+' )
                        )
                    ),
                    array(
                        'title' => __( 'What are your skincare goals?', 'ennu-life' ),
                        'description' => __( 'Clear goals help create the most effective treatment plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'anti_aging', 'label' => 'Anti-aging', 'icon' => 'ANTI', 'fallback' => 'Anti' ),
                            array( 'value' => 'acne_control', 'label' => 'Acne control', 'icon' => 'CTRL', 'fallback' => 'Acne' ),
                            array( 'value' => 'brightening', 'label' => 'Skin brightening', 'icon' => 'BRIGHT', 'fallback' => 'Bright' ),
                            array( 'value' => 'maintenance', 'label' => 'Maintenance/Prevention', 'icon' => 'MAINT', 'fallback' => 'Maint' )
                        )
                    )
                );
                
            case 'skin_assessment_enhanced':
                return array(
                    array(
                        'title' => __( 'What\'s your age range?', 'ennu-life' ),
                        'description' => __( 'Age helps determine appropriate skin treatments.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '18-25', 'label' => '18-25', 'icon' => 'GRAD', 'fallback' => '18-25' ),
                            array( 'value' => '26-35', 'label' => '26-35', 'icon' => 'M', 'fallback' => '26-35' ),
                            array( 'value' => '36-45', 'label' => '36-45', 'icon' => 'DR', 'fallback' => '36-45' ),
                            array( 'value' => '46-55', 'label' => '46-55', 'icon' => 'SR', 'fallback' => '46-55' ),
                            array( 'value' => '56+', 'label' => '56+', 'icon' => 'F', 'fallback' => '56+' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Gender affects skin characteristics and concerns.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male', 'icon' => 'M', 'fallback' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female', 'icon' => 'F', 'fallback' => 'Female' ),
                            array( 'value' => 'other', 'label' => 'Other', 'icon' => 'O', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your skin type?', 'ennu-life' ),
                        'description' => __( 'Knowing your skin type helps recommend the right products.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'normal', 'label' => 'Normal', 'icon' => 'NORM', 'fallback' => 'Normal' ),
                            array( 'value' => 'oily', 'label' => 'Oily', 'icon' => 'OIL', 'fallback' => 'Oily' ),
                            array( 'value' => 'dry', 'label' => 'Dry', 'icon' => 'DRY', 'fallback' => 'Dry' ),
                            array( 'value' => 'combination', 'label' => 'Combination', 'icon' => 'COMBO', 'fallback' => 'Combo' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your primary skin concern?', 'ennu-life' ),
                        'description' => __( 'This helps us prioritize your treatment recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'acne', 'label' => 'Acne/Breakouts', 'icon' => 'ACNE', 'fallback' => 'Acne' ),
                            array( 'value' => 'wrinkles', 'label' => 'Wrinkles/Fine lines', 'icon' => 'WRINK', 'fallback' => 'Wrink' ),
                            array( 'value' => 'dark_spots', 'label' => 'Dark spots', 'icon' => 'SPOT', 'fallback' => 'Spots' ),
                            array( 'value' => 'dullness', 'label' => 'Dullness/Uneven tone', 'icon' => 'DULL', 'fallback' => 'Dull' )
                        )
                    ),
                    array(
                        'title' => __( 'How much sun exposure do you typically get?', 'ennu-life' ),
                        'description' => __( 'Sun exposure affects skin damage and treatment needs.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'minimal', 'label' => 'Minimal', 'icon' => 'MIN', 'fallback' => 'Min' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate', 'icon' => 'MED', 'fallback' => 'Med' ),
                            array( 'value' => 'high', 'label' => 'High', 'icon' => 'HIGH', 'fallback' => 'High' ),
                            array( 'value' => 'very_high', 'label' => 'Very high', 'icon' => 'MAX', 'fallback' => 'Max' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe your current skincare routine?', 'ennu-life' ),
                        'description' => __( 'Current routine affects our recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'extensive', 'label' => 'Extensive (5+ steps)', 'icon' => 'EXT', 'fallback' => 'Ext' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate (3-4 steps)', 'icon' => 'MED', 'fallback' => 'Med' ),
                            array( 'value' => 'basic', 'label' => 'Basic (1-2 steps)', 'icon' => 'BASIC', 'fallback' => 'Basic' ),
                            array( 'value' => 'none', 'label' => 'No routine', 'icon' => 'NONE', 'fallback' => 'None' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your budget for skincare?', 'ennu-life' ),
                        'description' => __( 'Budget helps us recommend appropriate products and treatments.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'budget', 'label' => 'Budget-friendly ($0-50/month)', 'icon' => '$', 'fallback' => '$0-50' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate ($50-150/month)', 'icon' => '$$', 'fallback' => '$50-150' ),
                            array( 'value' => 'premium', 'label' => 'Premium ($150-300/month)', 'icon' => '$$$', 'fallback' => '$150-300' ),
                            array( 'value' => 'luxury', 'label' => 'Luxury ($300+/month)', 'icon' => '$$$$', 'fallback' => '$300+' )
                        )
                    ),
                    array(
                        'title' => __( 'What are your main skincare goals?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create the perfect plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'clear_skin', 'label' => 'Clear, healthy skin', 'icon' => 'CLEAR', 'fallback' => 'Clear' ),
                            array( 'value' => 'anti_aging', 'label' => 'Anti-aging', 'icon' => 'ANTI', 'fallback' => 'Anti' ),
                            array( 'value' => 'glow', 'label' => 'Radiant glow', 'icon' => 'GLOW', 'fallback' => 'Glow' ),
                            array( 'value' => 'maintenance', 'label' => 'Maintain current skin', 'icon' => 'MAINT', 'fallback' => 'Maint' )
                        )
                    )
                );
                
            case 'hormone_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your age range?', 'ennu-life' ),
                        'description' => __( 'Age significantly affects hormone levels and balance.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '18-25', 'label' => '18-25', 'icon' => 'GRAD', 'fallback' => '18-25' ),
                            array( 'value' => '26-35', 'label' => '26-35', 'icon' => 'M', 'fallback' => '26-35' ),
                            array( 'value' => '36-45', 'label' => '36-45', 'icon' => 'DR', 'fallback' => '36-45' ),
                            array( 'value' => '46-55', 'label' => '46-55', 'icon' => 'SR', 'fallback' => '46-55' ),
                            array( 'value' => '56+', 'label' => '56+', 'icon' => 'F', 'fallback' => '56+' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Hormone patterns and concerns vary significantly by gender.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male', 'icon' => 'M', 'fallback' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female', 'icon' => 'F', 'fallback' => 'Female' ),
                            array( 'value' => 'other', 'label' => 'Other', 'icon' => 'O', 'fallback' => 'Other' )
                        )
                    ),
                    array(
                        'title' => __( 'How are your energy levels throughout the day?', 'ennu-life' ),
                        'description' => __( 'Energy patterns can indicate hormone imbalances.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'consistent_high', 'label' => 'Consistently high', 'icon' => 'HIGH', 'fallback' => 'High' ),
                            array( 'value' => 'normal', 'label' => 'Normal ups and downs', 'icon' => 'OK', 'fallback' => 'Normal' ),
                            array( 'value' => 'afternoon_crash', 'label' => 'Afternoon energy crash', 'icon' => 'CRASH', 'fallback' => 'Crash' ),
                            array( 'value' => 'consistently_low', 'label' => 'Consistently low', 'icon' => 'LOW', 'fallback' => 'Low' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe your mood lately?', 'ennu-life' ),
                        'description' => __( 'Mood changes can be related to hormone fluctuations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'stable_positive', 'label' => 'Stable and positive', 'icon' => 'A+', 'fallback' => 'Great' ),
                            array( 'value' => 'mostly_good', 'label' => 'Mostly good', 'icon' => 'OK', 'fallback' => 'Good' ),
                            array( 'value' => 'ups_downs', 'label' => 'Frequent ups and downs', 'icon' => 'UPS', 'fallback' => 'Ups' ),
                            array( 'value' => 'often_low', 'label' => 'Often low or irritable', 'icon' => 'LOW', 'fallback' => 'Low' )
                        )
                    ),
                    array(
                        'title' => __( 'How well are you sleeping?', 'ennu-life' ),
                        'description' => __( 'Sleep quality is closely tied to hormone production.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'excellent', 'label' => 'Sleep excellently', 'icon' => 'A+', 'fallback' => 'Great' ),
                            array( 'value' => 'good', 'label' => 'Sleep well', 'icon' => 'OK', 'fallback' => 'Good' ),
                            array( 'value' => 'fair', 'label' => 'Sleep okay', 'icon' => 'FAIR', 'fallback' => 'Fair' ),
                            array( 'value' => 'poor', 'label' => 'Sleep poorly', 'icon' => 'POOR', 'fallback' => 'Poor' )
                        )
                    ),
                    array(
                        'title' => __( 'Have you noticed any weight changes recently?', 'ennu-life' ),
                        'description' => __( 'Unexplained weight changes can indicate hormone issues.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'stable', 'label' => 'Weight is stable', 'icon' => 'STABLE', 'fallback' => 'Stable' ),
                            array( 'value' => 'gradual_gain', 'label' => 'Gradual weight gain', 'icon' => 'UP', 'fallback' => 'Gain' ),
                            array( 'value' => 'sudden_gain', 'label' => 'Sudden weight gain', 'icon' => 'FAST_UP', 'fallback' => 'Fast+' ),
                            array( 'value' => 'loss', 'label' => 'Unexplained weight loss', 'icon' => 'DOWN', 'fallback' => 'Loss' )
                        )
                    ),
                    array(
                        'title' => __( 'How regular is your menstrual cycle? (if applicable)', 'ennu-life' ),
                        'description' => __( 'Cycle regularity is a key indicator of hormone health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'very_regular', 'label' => 'Very regular', 'icon' => 'REG', 'fallback' => 'Reg' ),
                            array( 'value' => 'mostly_regular', 'label' => 'Mostly regular', 'icon' => 'OK', 'fallback' => 'OK' ),
                            array( 'value' => 'irregular', 'label' => 'Irregular', 'icon' => 'IRR', 'fallback' => 'Irreg' ),
                            array( 'value' => 'not_applicable', 'label' => 'Not applicable', 'icon' => 'NA', 'fallback' => 'N/A' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your current stress level?', 'ennu-life' ),
                        'description' => __( 'Chronic stress significantly impacts hormone balance.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'low', 'label' => 'Low stress', 'icon' => 'LOW', 'fallback' => 'Low' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate stress', 'icon' => 'MED', 'fallback' => 'Med' ),
                            array( 'value' => 'high', 'label' => 'High stress', 'icon' => 'HIGH', 'fallback' => 'High' ),
                            array( 'value' => 'very_high', 'label' => 'Very high stress', 'icon' => 'MAX', 'fallback' => 'Max' )
                        )
                    ),
                    array(
                        'title' => __( 'How often do you exercise?', 'ennu-life' ),
                        'description' => __( 'Exercise affects hormone production and balance.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'daily', 'label' => 'Daily', 'icon' => 'DAILY', 'fallback' => 'Daily' ),
                            array( 'value' => 'few_times_week', 'label' => 'Few times per week', 'icon' => 'FEW', 'fallback' => 'Few' ),
                            array( 'value' => 'weekly', 'label' => 'Once a week', 'icon' => 'WEEK', 'fallback' => 'Week' ),
                            array( 'value' => 'rarely', 'label' => 'Rarely/Never', 'icon' => 'RARE', 'fallback' => 'Rare' )
                        )
                    ),
                    array(
                        'title' => __( 'What are your main hormone-related goals?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create the right treatment plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'balance', 'label' => 'Balance hormones', 'icon' => 'BAL', 'fallback' => 'Balance' ),
                            array( 'value' => 'energy', 'label' => 'Increase energy', 'icon' => 'ENERGY', 'fallback' => 'Energy' ),
                            array( 'value' => 'mood', 'label' => 'Improve mood', 'icon' => 'MOOD', 'fallback' => 'Mood' ),
                            array( 'value' => 'weight', 'label' => 'Weight management', 'icon' => 'WEIGHT', 'fallback' => 'Weight' )
                        )
                    )
                );
                
            default:
                return array();
        }
    }
    
    /**
     * Enqueue assessment assets
     */
    public function enqueue_assessment_assets() {
        // Only enqueue on pages with assessment shortcodes
        if ( ! $this->page_has_assessment_shortcode() ) {
            return;
        }
        
        // Enqueue main assessment CSS
        wp_enqueue_style(
            'ennu-assessment-styles',
            ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-modern.css',
            array(),
            ENNU_LIFE_VERSION
        );
        
        // Enqueue assessment JavaScript
        wp_enqueue_script(
            'ennu-assessment-script',
            ENNU_LIFE_PLUGIN_URL . 'assets/js/assessment-modern.js',
            array( 'jquery' ),
            ENNU_LIFE_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script( 'ennu-assessment-script', 'ennuAssessment', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'ennu_assessment_' . $assessment_type ),
            'strings' => array(
                'loading' => __( 'Loading...', 'ennu-life' ),
                'error' => __( 'An error occurred. Please try again.', 'ennu-life' ),
                'success' => __( 'Assessment submitted successfully!', 'ennu-life' ),
                'required_field' => __( 'This field is required.', 'ennu-life' ),
                'invalid_email' => __( 'Please enter a valid email address.', 'ennu-life' )
            )
        ) );
    }
    
    /**
     * Check if current page has assessment shortcode
     * 
     * @return bool
     */
    private function page_has_assessment_shortcode() {
        global $post;
        
        if ( ! $post || ! $post->post_content ) {
            return false;
        }
        
        // Check for any ENNU assessment shortcode
        return has_shortcode( $post->post_content, 'ennu-hair-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-hair-restoration-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-ed-treatment-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-weight-loss-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-weight-loss-quiz' ) ||
               has_shortcode( $post->post_content, 'ennu-health-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-advanced-skin-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-skin-assessment-enhanced' ) ||
               has_shortcode( $post->post_content, 'ennu-hormone-assessment' );
    }
    
    /**
     * Handle assessment submission
     */
    public function handle_assessment_submission() {
        // Verify nonce
        $assessment_type = isset( $_POST['assessment_type'] ) ? sanitize_text_field( $_POST['assessment_type'] ) : '';
        if ( ! isset( $_POST['assessment_nonce'] ) || ! wp_verify_nonce( $_POST['assessment_nonce'], 'ennu_assessment_' . $assessment_type ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ennu-life' ) ) );
        }
        
        // Sanitize and validate input
        $assessment_data = $this->sanitize_assessment_data( $_POST );
        
        if ( ! $this->validate_assessment_data( $assessment_data ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'ennu-life' ) ) );
        }
        
        try {
            // Save assessment data
            $result = $this->save_assessment_data( $assessment_data );
            
            if ( $result ) {
                // Send notification email
                $this->send_assessment_notification( $assessment_data );
                
                wp_send_json_success( array( 
                    'message' => __( 'Assessment submitted successfully!', 'ennu-life' ),
                    'redirect' => $this->get_thank_you_url( $assessment_data['assessment_type'] )
                ) );
            } else {
                wp_send_json_error( array( 'message' => __( 'Failed to save assessment data.', 'ennu-life' ) ) );
            }
            
        } catch ( Exception $e ) {
            error_log( 'ENNU Assessment Submission Error: ' . $e->getMessage() );
            wp_send_json_error( array( 'message' => __( 'An error occurred while processing your assessment.', 'ennu-life' ) ) );
        }
    }
    
    /**
     * Sanitize assessment data
     * 
     * @param array $data Raw POST data
     * @return array
     */
    private function sanitize_assessment_data( $data ) {
        $sanitized = array(
            'assessment_type' => sanitize_key( $data['assessment_type'] ?? '' ),
            'contact_name' => sanitize_text_field( trim( $data['contact_name'] ?? '' ) ),
            'contact_email' => sanitize_email( trim( $data['contact_email'] ?? '' ) ),
            'contact_phone' => preg_replace( '/[^0-9+\-\(\)\s]/', '', $data['contact_phone'] ?? '' ),
            'answers' => array()
        );
        
        // Additional name sanitization
        $sanitized['contact_name'] = preg_replace( '/[^a-zA-Z\s\-\'\.]/u', '', $sanitized['contact_name'] );
        $sanitized['contact_name'] = trim( preg_replace( '/\s+/', ' ', $sanitized['contact_name'] ) );
        
        // Sanitize question answers with strict validation
        foreach ( $data as $key => $value ) {
            if ( strpos( $key, 'question_' ) === 0 ) {
                $clean_key = sanitize_key( $key );
                $clean_value = sanitize_text_field( trim( $value ) );
                
                // Additional validation for answer values
                if ( ! empty( $clean_value ) && strlen( $clean_value ) <= 200 ) {
                    $sanitized['answers'][ $clean_key ] = $clean_value;
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate assessment data
     * 
     * @param array $data Sanitized assessment data
     * @return bool
     */
    private function validate_assessment_data( $data ) {
        // Check required fields
        if ( empty( $data['assessment_type'] ) || 
             empty( $data['contact_name'] ) || 
             empty( $data['contact_email'] ) ) {
            return false;
        }
        
        // Validate email format and domain
        if ( ! is_email( $data['contact_email'] ) ) {
            return false;
        }
        
        // Additional email validation
        if ( ! filter_var( $data['contact_email'], FILTER_VALIDATE_EMAIL ) ) {
            return false;
        }
        
        // Validate name (no special characters except spaces, hyphens, apostrophes)
        if ( ! preg_match( '/^[a-zA-Z\s\-\'\.]+$/', $data['contact_name'] ) ) {
            return false;
        }
        
        // Validate phone if provided
        if ( ! empty( $data['contact_phone'] ) ) {
            $phone_clean = preg_replace( '/[^0-9+\-\(\)\s]/', '', $data['contact_phone'] );
            if ( strlen( $phone_clean ) < 10 ) {
                return false;
            }
        }
        
        // Check if assessment type is valid
        if ( ! isset( $this->assessments[ $data['assessment_type'] ] ) ) {
            return false;
        }
        
        // Validate minimum number of answers
        if ( count( $data['answers'] ) < 3 ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Save assessment data
     * 
     * @param array $data Assessment data
     * @return bool
     */
    private function save_assessment_data( $data ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ennu_assessments';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'assessment_type' => $data['assessment_type'],
                'assessment_data' => wp_json_encode( $data ),
                'user_id' => get_current_user_id(),
                'results' => wp_json_encode( array( 'status' => 'completed' ) ),
                'user_ip' => $this->get_client_ip(),
                'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
                'created_at' => current_time( 'mysql' )
            ),
            array( '%s', '%s', '%d', '%s', '%s', '%s', '%s' )
        );
        
        if ( $result ) {
            // Also save to user meta for quick access
            $this->save_user_assessment_meta( $data );
            
            // Create CPT post if CPT system is available
            $this->create_assessment_cpt_post( $data );
        }
        
        return $result !== false;
    }
    
    /**
     * Save assessment data to user meta
     * 
     * @param array $data Assessment data
     */
    private function save_user_assessment_meta( $data ) {
        $user_id = get_current_user_id();
        
        if ( ! $user_id ) {
            return;
        }
        
        $meta_key = 'ennu_latest_' . $data['assessment_type'];
        $meta_value = array(
            'data' => $data,
            'date' => current_time( 'mysql' ),
            'status' => 'completed'
        );
        
        update_user_meta( $user_id, $meta_key, $meta_value );
    }
    
    /**
     * Create assessment CPT post
     * 
     * @param array $data Assessment data
     */
    private function create_assessment_cpt_post( $data ) {
        $post_type = $data['assessment_type'];
        
        // Check if CPT exists
        if ( ! post_type_exists( $post_type ) ) {
            return;
        }
        
        $post_id = wp_insert_post( array(
            'post_type' => $post_type,
            'post_title' => sprintf( 
                '%s - %s', 
                $data['contact_name'], 
                current_time( 'Y-m-d H:i:s' ) 
            ),
            'post_status' => 'private',
            'post_content' => wp_json_encode( $data ),
            'meta_input' => array(
                'contact_name' => $data['contact_name'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'],
                'assessment_answers' => $data['answers'],
                'submission_date' => current_time( 'mysql' ),
                'user_id' => get_current_user_id()
            )
        ) );
        
        return $post_id;
    }
    
    /**
     * Send assessment notification email
     * 
     * @param array $data Assessment data
     */
    private function send_assessment_notification( $data ) {
        $to = $data['contact_email'];
        $subject = sprintf( 
            __( 'Your %s Results - ENNU Life', 'ennu-life' ),
            $this->assessments[ $data['assessment_type'] ]['title']
        );
        
        $message = $this->get_assessment_email_template( $data );
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ENNU Life <noreply@ennulife.com>'
        );
        
        wp_mail( $to, $subject, $message, $headers );
        
        // Also send admin notification
        $admin_email = get_option( 'admin_email' );
        $admin_subject = sprintf( 
            __( 'New %s Submission', 'ennu-life' ),
            $this->assessments[ $data['assessment_type'] ]['title']
        );
        $admin_message = $this->get_admin_notification_template( $data );
        
        wp_mail( $admin_email, $admin_subject, $admin_message, $headers );
    }
    
    /**
     * Get assessment email template
     * 
     * @param array $data Assessment data
     * @return string
     */
    private function get_assessment_email_template( $data ) {
        $assessment_title = $this->assessments[ $data['assessment_type'] ]['title'];
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php echo esc_html( $assessment_title ); ?> Results</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #667eea;">Thank you for completing your <?php echo esc_html( $assessment_title ); ?>!</h1>
                
                <p>Dear <?php echo esc_html( $data['contact_name'] ); ?>,</p>
                
                <p>Thank you for taking the time to complete your assessment. Our medical team will review your responses and provide personalized recommendations.</p>
                
                <h2>What happens next?</h2>
                <ul>
                    <li>Our medical team will review your responses within 24 hours</li>
                    <li>You'll receive personalized recommendations via email</li>
                    <li>A specialist may contact you to discuss treatment options</li>
                </ul>
                
                <p>If you have any questions, please don't hesitate to contact us.</p>
                
                <p>Best regards,<br>The ENNU Life Team</p>
                
                <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
                <p style="font-size: 12px; color: #666;">
                    This email was sent to <?php echo esc_html( $data['contact_email'] ); ?> because you completed an assessment on our website.
                </p>
            </div>
        </body>
        </html>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get admin notification template
     * 
     * @param array $data Assessment data
     * @return string
     */
    private function get_admin_notification_template( $data ) {
        $assessment_title = $this->assessments[ $data['assessment_type'] ]['title'];
        
        ob_start();
        ?>
        <h2>New <?php echo esc_html( $assessment_title ); ?> Submission</h2>
        
        <p><strong>Contact Information:</strong></p>
        <ul>
            <li>Name: <?php echo esc_html( $data['contact_name'] ); ?></li>
            <li>Email: <?php echo esc_html( $data['contact_email'] ); ?></li>
            <li>Phone: <?php echo esc_html( $data['contact_phone'] ); ?></li>
        </ul>
        
        <p><strong>Assessment Answers:</strong></p>
        <ul>
            <?php foreach ( $data['answers'] as $question => $answer ) : ?>
                <li><?php echo esc_html( ucwords( str_replace( '_', ' ', $question ) ) ); ?>: <?php echo esc_html( $answer ); ?></li>
            <?php endforeach; ?>
        </ul>
        
        <p><strong>Submission Time:</strong> <?php echo esc_html( current_time( 'Y-m-d H:i:s' ) ); ?></p>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get thank you page URL
     * 
     * @param string $assessment_type Assessment type
     * @return string
     */
    private function get_thank_you_url( $assessment_type ) {
        // Return current page URL by default
        return add_query_arg( 'assessment_completed', $assessment_type, wp_get_referer() );
    }
    
    /**
     * Get client IP address securely
     * 
     * @return string
     */
    private function get_client_ip() {
        $ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );
        
        foreach ( $ip_keys as $key ) {
            if ( array_key_exists( $key, $_SERVER ) === true ) {
                foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
                    $ip = trim( $ip );
                    
                    // Validate IP and exclude private/reserved ranges for security
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                        return sanitize_text_field( $ip );
                    }
                }
            }
        }
        
        // Fallback to REMOTE_ADDR with validation
        $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if ( filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
            return sanitize_text_field( $remote_addr );
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Adjust color brightness
     * 
     * @param string $hex_color Hex color code
     * @param int $percent Brightness adjustment percentage
     * @return string
     */
    private function adjust_color_brightness( $hex_color, $percent ) {
        $hex_color = ltrim( $hex_color, '#' );
        
        if ( strlen( $hex_color ) === 3 ) {
            $hex_color = str_repeat( substr( $hex_color, 0, 1 ), 2 ) . 
                        str_repeat( substr( $hex_color, 1, 1 ), 2 ) . 
                        str_repeat( substr( $hex_color, 2, 1 ), 2 );
        }
        
        $r = hexdec( substr( $hex_color, 0, 2 ) );
        $g = hexdec( substr( $hex_color, 2, 2 ) );
        $b = hexdec( substr( $hex_color, 4, 2 ) );
        
        $r = max( 0, min( 255, $r + ( $r * $percent / 100 ) ) );
        $g = max( 0, min( 255, $g + ( $g * $percent / 100 ) ) );
        $b = max( 0, min( 255, $b + ( $b * $percent / 100 ) ) );
        
        return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT ) . 
                    str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT ) . 
                    str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
    }
    
    /**
     * Render error message
     * 
     * @param string $message Error message
     * @return string
     */
    private function render_error_message( $message ) {
        return sprintf(
            '<div class="ennu-assessment-error" style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 8px; text-align: center;">
                <h3>%s</h3>
                <p>%s</p>
            </div>',
            esc_html__( 'Assessment Unavailable', 'ennu-life' ),
            esc_html( $message )
        );
    }
}

// Initialize the class
new ENNU_Assessment_Shortcodes();

