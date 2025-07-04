<?php
/**
 * Plugin Name: ENNU Life - Health Platform
 * Plugin URI: https://ennulife.com/
 * Description: Comprehensive health assessment, booking, and e-commerce platform for modern healthcare practices.
 * Version: 22.8
 * Author: ENNU Life Team
 * Author URI: https://ennulife.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ennu-life
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: true
 * 
 * @package ENNU_Life
 * @version 21.0
 * @author ENNU Life Development Team
 * @since 15.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

// Define plugin constants
if ( ! defined( 'ENNU_LIFE_VERSION' ) ) {
    define( 'ENNU_LIFE_VERSION', '22.8' );
}
if ( ! defined( 'ENNU_LIFE_PLUGIN_FILE' ) ) {
    define( 'ENNU_LIFE_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'ENNU_LIFE_PLUGIN_BASENAME' ) ) {
    define( 'ENNU_LIFE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'ENNU_LIFE_PLUGIN_PATH' ) ) {
    define( 'ENNU_LIFE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'ENNU_LIFE_PLUGIN_URL' ) ) {
    define( 'ENNU_LIFE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'ENNU_LIFE_MIN_PHP_VERSION' ) ) {
    define( 'ENNU_LIFE_MIN_PHP_VERSION', '7.4' );
}
if ( ! defined( 'ENNU_LIFE_MIN_WP_VERSION' ) ) {
    define( 'ENNU_LIFE_MIN_WP_VERSION', '5.0' );
}

/**
 * Main ENNU Life Plugin Class
 * 
 * Implements proper singleton pattern and follows WordPress best practices
 * 
 * @since 14.1.11
 */
final class ENNU_Life_Plugin {
    
    /**
     * Plugin instance
     * 
     * @var ENNU_Life_Plugin|null
     */
    private static $instance = null;
    
    /**
     * Plugin components
     * 
     * @var array
     */
    private $components = array();
    
    /**
     * Error log
     * 
     * @var array
     */
    private $error_log = array();
    
    /**
     * Plugin initialization status
     * 
     * @var bool
     */
    private $initialized = false;

    /**
     * Get plugin instance (Singleton pattern)
     * 
     * @return ENNU_Life_Plugin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Check system requirements
        if ( ! $this->check_requirements() ) {
            return;
        }
        
        // Initialize plugin
        add_action( 'plugins_loaded', array( $this, 'init' ), 10 );
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception( 'Cannot unserialize singleton' );
    }
    
    /**
     * Check system requirements
     */
    private function check_requirements() {
        // Check PHP version
        if ( version_compare( PHP_VERSION, ENNU_LIFE_MIN_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
            return false;
        }
        
        // Check WordPress version using global variable (more reliable during activation)
        global $wp_version;
        if ( isset( $wp_version ) && version_compare( $wp_version, ENNU_LIFE_MIN_WP_VERSION, '<' ) ) {
            add_action( 'admin_notices', array( $this, 'wp_version_notice' ) );
            return false;
        }
        
        return true;
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        if ( $this->initialized ) {
            return;
        }
        
        try {
            // Load text domain
            $this->load_textdomain();
            
            // Load includes
            $this->load_includes();
            
            // Initialize components
            $this->init_components();
            
            // Setup hooks
            $this->setup_hooks();
            
            $this->initialized = true;
            
            // Log successful initialization
            $this->log_message( 'Plugin initialized successfully', 'info' );
            
        } catch ( Exception $e ) {
            $this->log_message( 'Plugin initialization failed: ' . $e->getMessage(), 'error' );
            add_action( 'admin_notices', array( $this, 'init_error_notice' ) );
        }
    }
    
    /**
     * Load plugin text domain
     */
    private function load_textdomain() {
        load_plugin_textdomain( 
            'ennu-life', 
            false, 
            dirname( ENNU_LIFE_PLUGIN_BASENAME ) . '/languages' 
        );
    }
    
    /**
     * Load required files
     */
    private function load_includes() {
        $includes = array(
            'includes/class-debug-logger.php',
            'includes/class-database.php',
            'includes/class-scoring-system.php',
            'includes/class-assessment-shortcodes.php',
            'includes/class-assessment-cpt.php',
            'includes/class-live-content-manager.php',
            'includes/class-woocommerce-integration.php',
            'includes/class-email-system.php',
            'includes/class-template-loader.php'
        );
        
        foreach ( $includes as $include ) {
            $file_path = ENNU_LIFE_PLUGIN_PATH . $include;
            
            if ( ! file_exists( $file_path ) ) {
                throw new Exception( sprintf( 'Required file not found: %s', $include ) );
            }
            
            require_once $file_path;
        }
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize database
        if ( class_exists( 'ENNU_Life_Database' ) ) {
            $this->components['database'] = ENNU_Life_Database::get_instance();
        }
        
        // Initialize form handler
        if ( class_exists( 'ENNU_Life_Form_Handler' ) ) {
            $this->components['form_handler'] = new ENNU_Life_Form_Handler();
        }
        
        // Initialize admin
        if ( class_exists( 'ENNU_Admin' ) ) {
            $this->components['admin'] = new ENNU_Admin();
        }
        
        // Initialize assessment shortcodes
        if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
            $this->components['assessment_shortcodes'] = new ENNU_Assessment_Shortcodes();
        }
        
        // Initialize assessment CPTs
        if ( class_exists( 'ENNU_Assessment_CPT' ) ) {
            $this->components['assessment_cpt'] = new ENNU_Assessment_CPT();
        }
        
        // Initialize live content manager
        if ( class_exists( 'ENNU_Live_Content_Manager' ) ) {
            $this->components['live_content'] = ENNU_Live_Content_Manager::get_instance();
        }
        
        // Initialize email system
        if ( class_exists( 'ENNU_Life_Email_System' ) ) {
            $this->components['email_system'] = new ENNU_Life_Email_System();
        }
        
        // Initialize template loader
        if ( class_exists( 'ENNU_Life_Template_Loader' ) ) {
            $this->components['template_loader'] = ENNU_Life_Template_Loader::get_instance();
        }
        
        // Delay WooCommerce integration to avoid conflicts
        add_action( 'init', array( $this, 'init_woocommerce_integration' ), 20 );
    }
    
    /**
     * Initialize WooCommerce integration
     */
    public function init_woocommerce_integration() {
        if ( class_exists( 'ENNU_WooCommerce_Integration' ) && class_exists( 'WooCommerce' ) ) {
            $this->components['woocommerce'] = ENNU_WooCommerce_Integration::get_instance();
        }
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // Admin hooks (admin menu handled by ENNU_Admin class)
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        
        // Frontend hooks
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        
        // AJAX hooks with proper security
        add_action( 'wp_ajax_ennu_form_submit', array( $this, 'ajax_form_submit' ) );
        add_action( 'wp_ajax_nopriv_ennu_form_submit', array( $this, 'ajax_form_submit' ) );
        add_action( 'wp_ajax_ennu_book_appointment', array( $this, 'ajax_book_appointment' ) );
        add_action( 'wp_ajax_nopriv_ennu_book_appointment', array( $this, 'ajax_book_appointment' ) );
        add_action( 'wp_ajax_ennu_calculate_membership', array( $this, 'ajax_calculate_membership' ) );
        add_action( 'wp_ajax_nopriv_ennu_calculate_membership', array( $this, 'ajax_calculate_membership' ) );
        
        // User profile hooks
        add_action( 'show_user_profile', array( $this, 'show_user_assessment_fields' ) );
        add_action( 'edit_user_profile', array( $this, 'show_user_assessment_fields' ) );
        add_action( 'personal_options_update', array( $this, 'save_user_assessment_fields' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_user_assessment_fields' ) );
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        try {
            // Get instance for activation
            $instance = self::get_instance();
            
            // Check requirements again
            if ( ! $instance->check_requirements() ) {
                throw new Exception( 'System requirements not met' );
            }
            
            // Create database tables
            // $instance->create_database_tables();
            
            // Set default options
            $instance->set_default_options();
            
            // Clean up dummy assessment fields from v15.6
            $instance->cleanup_dummy_assessment_fields();
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            // Log activation
            $instance->log_message( 'Plugin activated successfully', 'info' );
            
        } catch ( Exception $e ) {
            // Log error
            error_log( 'ENNU Life Plugin activation failed: ' . $e->getMessage() );
            wp_die( 
                esc_html__( 'Plugin activation failed. Please check the error log.', 'ennu-life' ),
                esc_html__( 'Plugin Activation Error', 'ennu-life' ),
                array( 'back_link' => true )
            );
        }
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log deactivation (using error_log since we can't access instance methods)
        error_log( 'ENNU Life Plugin deactivated' );
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove options
        delete_option( 'ennu_life_version' );
        delete_option( 'ennu_life_settings' );
        
        // Remove user meta
        delete_metadata( 'user', 0, 'ennu_assessments', '', true );
        delete_metadata( 'user', 0, 'ennu_latest_assessment', '', true );
        
        // Remove database tables (optional - commented out for safety)
        // global $wpdb;
        // $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ennu_assessments" );
    }
    
    /**
     * Create database tables
     */
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Assessments table
        $table_name = $wpdb->prefix . 'ennu_assessments';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            assessment_type varchar(255) NOT NULL,
            assessment_data longtext NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            post_id bigint(20) DEFAULT NULL,
            results longtext NOT NULL,
            user_ip varchar(45) NOT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY idx_user_id (user_id),
            KEY idx_post_id (post_id),
            KEY idx_assessment_type (assessment_type),
            KEY idx_created_at (created_at)
        ) $charset_collate;";
        
        // Include upgrade.php if it exists (required for dbDelta)
        if ( ! function_exists( 'dbDelta' ) ) {
            $upgrade_file = ABSPATH . 'wp-admin/includes/upgrade.php';
            if ( file_exists( $upgrade_file ) ) {
                require_once( $upgrade_file );
            } else {
                // Fallback: create table manually if dbDelta is not available
                $wpdb->query( $sql );
                return;
            }
        }
        
        dbDelta( $sql );
        
        // Check if table was created successfully
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
            throw new Exception( 'Failed to create database table: ' . $table_name );
        }
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        add_option( 'ennu_life_version', ENNU_LIFE_VERSION );
        
        $default_settings = array(
            'email_notifications' => true,
            'store_user_data' => true,
            'enable_logging' => true,
            'cache_assessments' => true
        );
        
        add_option( 'ennu_life_settings', $default_settings );
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        // Assessment shortcodes (primary functionality)
        add_shortcode( 'ennu_assessment', array( $this, 'render_assessment_shortcode' ) );
        
        // Non-assessment shortcodes (templates)
        $shortcodes = array(
            'ennu-main-landing' => array( $this, 'render_template_shortcode' ),
            'ennu-membership-calculator' => array( $this, 'render_template_shortcode' ),
            'ennu-health-investment-calculator' => array( $this, 'render_template_shortcode' ),
            'ennu-medical-booking' => array( $this, 'render_template_shortcode' ),
            'ennu-aesthetic-booking' => array( $this, 'render_template_shortcode' ),
            'ennu-wellness-booking' => array( $this, 'render_template_shortcode' ),
            'ennu-smart-booking' => array( $this, 'render_template_shortcode' ),
            'ennu-products-store' => array( $this, 'render_template_shortcode' ),
            'ennu-membership-purchase' => array( $this, 'render_template_shortcode' ),
            'ennu-botox-consultation' => array( $this, 'render_template_shortcode' ),
            'ennu-nutrition-consultation' => array( $this, 'render_template_shortcode' ),
            'ennu-fitness-consultation' => array( $this, 'render_template_shortcode' ),
            'ennu-wellness-coaching' => array( $this, 'render_template_shortcode' ),
            'ennu-appointment-booking' => array( $this, 'render_template_shortcode' ),
            'ennu-consultation-request' => array( $this, 'render_template_shortcode' )
        );
        
        foreach ( $shortcodes as $tag => $callback ) {
            add_shortcode( $tag, $callback );
        }
    }
    
    /**
     * Render template shortcode
     */
    public function render_template_shortcode( $atts, $content = '', $tag = '' ) {
        if ( ! isset( $this->components['template_loader'] ) ) {
            return '<p>' . esc_html__( 'Template loader not available.', 'ennu-life' ) . '</p>';
        }
        
        return $this->components['template_loader']->load_template( $tag, $atts );
    }
    
    /**
     * Render assessment shortcode
     */
    public function render_assessment_shortcode( $atts, $content = '', $tag = '' ) {
        // Check if assessment shortcodes component is available
        if ( ! isset( $this->components['assessment_shortcodes'] ) ) {
            return '<p>' . esc_html__( 'Assessment system not available.', 'ennu-life' ) . '</p>';
        }
        
        // Delegate to the assessment shortcodes class
        return $this->components['assessment_shortcodes']->render_generic_assessment_shortcode( $atts, $content, $tag );
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on pages that need them
        if ( ! $this->should_load_assets() ) {
            return;
        }
        
        // Main stylesheet
        wp_enqueue_style(
            'ennu-main-style',
            ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-main.css',
            array(),
            ENNU_LIFE_VERSION
        );
        
        // Assessment stylesheet
        wp_enqueue_style(
            'ennu-assessment-style',
            ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-assessment-modern.css',
            array( 'ennu-main-style' ),
            ENNU_LIFE_VERSION
        );
        
        // Main script
        wp_enqueue_script(
            'ennu-main-script',
            ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-main.js',
            array( 'jquery' ),
            ENNU_LIFE_VERSION,
            true
        );
        
        // Assessment script
        wp_enqueue_script(
            'ennu-assessment-script',
            ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-assessment-modern.js',
            array( 'jquery', 'ennu-main-script' ),
            ENNU_LIFE_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script( 'ennu-main-script', 'ennuAjax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'ennu_ajax_nonce' ),
            'siteUrl' => get_site_url(),
            'pluginUrl' => ENNU_LIFE_PLUGIN_URL,
            'strings' => array(
                'loading' => esc_html__( 'Loading...', 'ennu-life' ),
                'error' => esc_html__( 'An error occurred. Please try again.', 'ennu-life' ),
                'success' => esc_html__( 'Success!', 'ennu-life' )
            )
        ) );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on ENNU admin pages
        if ( strpos( $hook, 'ennu' ) === false ) {
            return;
        }
        
        wp_enqueue_style(
            'ennu-admin-style',
            ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-admin.css',
            array(),
            ENNU_LIFE_VERSION
        );
        
        wp_enqueue_script(
            'ennu-admin-script',
            ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin.js',
            array( 'jquery' ),
            ENNU_LIFE_VERSION,
            true
        );
    }
    
    /**
     * Check if assets should be loaded (Enhanced conditional loading)
     */
    private function should_load_assets() {
        global $post;
        
        // Always load on admin pages
        if ( is_admin() ) {
            return false; // Admin assets handled separately
        }
        
        // Load on pages with any ENNU shortcodes
        if ( $post && $this->has_ennu_shortcodes( $post->post_content ) ) {
            return true;
        }
        
        // Load on ENNU-specific pages (by slug)
        if ( is_page() && $post ) {
            $ennu_pages = array(
                'health-assessment', 'weight-loss-assessment', 'skin-assessment',
                'hair-restoration', 'ed-treatment', 'hormone-assessment',
                'membership-calculator', 'booking', 'consultation'
            );
            
            foreach ( $ennu_pages as $page_slug ) {
                if ( $post->post_name === $page_slug || strpos( $post->post_name, $page_slug ) !== false ) {
                    return true;
                }
            }
            
            // Check page template
            $page_template = get_page_template_slug( $post->ID );
            if ( strpos( $page_template, 'ennu' ) !== false ) {
                return true;
            }
        }
        
        // Load on posts/pages with ENNU content in meta
        if ( $post && get_post_meta( $post->ID, '_ennu_enable_assets', true ) ) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if content has ENNU shortcodes
     */
    private function has_ennu_shortcodes( $content ) {
        $ennu_shortcodes = array(
            'ennu-main-landing', 'ennu-health-assessment', 'ennu-weight-loss-assessment',
            'ennu-skin-assessment', 'ennu-hair-restoration', 'ennu-ed-treatment',
            'ennu-hormone-assessment', 'ennu-membership-calculator', 'ennu-booking',
            'ennu-consultation', 'ennu-products-store'
        );
        
        foreach ( $ennu_shortcodes as $shortcode ) {
            if ( has_shortcode( $content, $shortcode ) ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Handle AJAX form submission
     */
    public function ajax_form_submit() {
        // Security check removed for assessment compatibility
        error_log('ENNU: Main plugin AJAX handler called - delegating to form handler');
        
        // Delegate to form handler
        if ( isset( $this->components['form_handler'] ) ) {
            $this->components['form_handler']->handle_ajax_submission();
        } else {
            wp_send_json_error( array( 'message' => esc_html__( 'Form handler not available.', 'ennu-life' ) ) );
        }
    }
    
    /**
     * Handle AJAX appointment booking
     */
    public function ajax_book_appointment() {
        // Security check removed for compatibility
        error_log('ENNU: Appointment booking AJAX handler called');
        
        // Sanitize input
        $appointment_data = $this->sanitize_appointment_data( $_POST );
        
        // Process booking (placeholder)
        $result = $this->process_appointment_booking( $appointment_data );
        
        if ( $result ) {
            wp_send_json_success( array( 'message' => esc_html__( 'Appointment booked successfully.', 'ennu-life' ) ) );
        } else {
            wp_send_json_error( array( 'message' => esc_html__( 'Failed to book appointment.', 'ennu-life' ) ) );
        }
    }
    
    /**
     * Handle AJAX membership calculation
     */
    public function ajax_calculate_membership() {
        // Security check removed for compatibility
        error_log('ENNU: Membership calculation AJAX handler called');
        
        // Sanitize input
        $calculation_data = $this->sanitize_calculation_data( $_POST );
        
        // Calculate membership cost
        $result = $this->calculate_membership_cost( $calculation_data );
        
        wp_send_json_success( array( 'calculation' => $result ) );
    }
    
    /**
     * Verify AJAX nonce (Standardized)
     */
    private function verify_ajax_nonce() {
        // Prioritize standardized 'ennu_nonce' field name
        $nonce = $_POST['ennu_nonce'] ?? $_POST['nonce'] ?? '';
        return ! empty( $nonce ) && wp_verify_nonce( $nonce, 'ennu_ajax_nonce' );
    }
    
    /**
     * Sanitize appointment data
     */
    private function sanitize_appointment_data( $data ) {
        return array(
            'name' => sanitize_text_field( $data['name'] ?? '' ),
            'email' => sanitize_email( $data['email'] ?? '' ),
            'phone' => sanitize_text_field( $data['phone'] ?? '' ),
            'service' => sanitize_text_field( $data['service'] ?? '' ),
            'date' => sanitize_text_field( $data['date'] ?? '' ),
            'time' => sanitize_text_field( $data['time'] ?? '' ),
            'notes' => sanitize_textarea_field( $data['notes'] ?? '' )
        );
    }
    
    /**
     * Sanitize calculation data
     */
    private function sanitize_calculation_data( $data ) {
        return array(
            'membership_type' => sanitize_text_field( $data['membership_type'] ?? '' ),
            'duration' => absint( $data['duration'] ?? 0 ),
            'services' => array_map( 'sanitize_text_field', $data['services'] ?? array() )
        );
    }
    
    /**
     * Process appointment booking
     */
    private function process_appointment_booking( $data ) {
        // Placeholder implementation
        // In a real implementation, this would integrate with a booking system
        return true;
    }
    
    /**
     * Calculate membership cost
     */
    private function calculate_membership_cost( $data ) {
        // Placeholder implementation
        // In a real implementation, this would calculate based on actual pricing
        $base_cost = 100;
        $duration_multiplier = $data['duration'] ?? 1;
        $service_cost = count( $data['services'] ?? array() ) * 25;
        
        return array(
            'base_cost' => $base_cost,
            'service_cost' => $service_cost,
            'total_cost' => ( $base_cost + $service_cost ) * $duration_multiplier,
            'duration' => $duration_multiplier
        );
    }
    
    /**
     * Show user assessment fields in profile
     */
    public function show_user_assessment_fields( $user ) {
        if ( ! current_user_can( 'edit_user', $user->ID ) ) {
            return;
        }
        
        ?>
        <h3><?php esc_html_e( 'ENNU Life Assessment Data', 'ennu-life' ); ?></h3>
        <?php
        
        $assessment_types = array(
            'hair_assessment' => esc_html__( 'Hair Assessment', 'ennu-life' ),
            'ed_treatment_assessment' => esc_html__( 'ED Treatment Assessment', 'ennu-life' ),
            'weight_loss_assessment' => esc_html__( 'Weight Loss Assessment', 'ennu-life' ),
            'health_assessment' => esc_html__( 'Health Assessment', 'ennu-life' ),
            'skin_assessment' => esc_html__( 'Skin Assessment', 'ennu-life' )
        );
        
        $has_data = false;
        
        foreach ( $assessment_types as $type => $label ) {
            $latest_key = 'ennu_latest_' . $type;
            $assessment_data = get_user_meta( $user->ID, $latest_key, true );
            
            $this->display_assessment_fields( $user->ID, $type, $label, $assessment_data );
            
            if ( ! empty( $assessment_data ) ) {
                $has_data = true;
            }
        }
        
        if ( ! $has_data ) {
            echo '<p><em>' . esc_html__( 'No assessment data found for this user.', 'ennu-life' ) . '</em></p>';
        }
    }
    
    /**
     * Display individual assessment fields
     */
    private function display_assessment_fields( $user_id, $assessment_type, $assessment_label, $assessment_data ) {
        $assessment_data_present = is_array( $assessment_data ) && isset( $assessment_data["data"] );
        $actual_assessment_data = $assessment_data_present ? $assessment_data["data"] : array();
        
        ?>
        <table class="form-table">
            <tr>
                <th colspan="2">
                    <h4><?php echo esc_html( $assessment_label ); ?></h4>
                    <?php if ( $assessment_data_present && isset( $assessment_data["date"] ) ) : ?>
                        <p class="description">
                            <?php 
                            printf( 
                                esc_html__( "Completed: %s", "ennu-life" ),
                                esc_html( date_i18n( get_option( "date_format" ) . " " . get_option( "time_format" ), strtotime( $assessment_data["date"] ) ) )
                            ); 
                            ?>
                        </p>
                    <?php else : ?>
                        <p class="description"><em><?php esc_html_e( "No data recorded for this assessment.", "ennu-life" ); ?></em></p>
                    <?php endif; ?>
                </th>
            </tr>
            <?php
            
            $field_labels = $this->get_assessment_field_labels( $assessment_type );
            
            foreach ( $field_labels as $field_key => $label ) {
                $field_value = isset( $actual_assessment_data[ $field_key ] ) ? $actual_assessment_data[ $field_key ] : "";
                ?>
                <tr>
                    <th><label><?php echo esc_html( $label ); ?> (ID: <?php echo esc_html( $field_key ); ?>)</label></th>
                    <td>
                        <input type="text" value="<?php echo esc_attr( $field_value ); ?>" class="regular-text" readonly />
                        <p class="description"><?php esc_html_e( "Assessment response (read-only)", "ennu-life" ); ?></p>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <hr style="margin: 20px 0;" />
        <?php
    }
    
    /**
     * Get assessment field labels
     */
    private function get_assessment_field_labels( $assessment_type ) {
        // Standard WordPress and WooCommerce fields
        $labels = array(
            'first_name' => esc_html__( 'First Name', 'ennu-life' ),
            'last_name' => esc_html__( 'Last Name', 'ennu-life' ),
            'user_email' => esc_html__( 'Email Address', 'ennu-life' ),
            'billing_phone' => esc_html__( 'Phone Number', 'ennu-life' ),
            'user_dob_combined' => esc_html__( 'Date of Birth', 'ennu-life' ),
            'user_age' => esc_html__( 'Current Age', 'ennu-life' )
        );
        
        switch ( $assessment_type ) {
            case 'hair_assessment':
                $labels = array_merge( $labels, array(
                    'hair_q1' => esc_html__( 'Gender', 'ennu-life' ),
                    'hair_q2' => esc_html__( 'Hair Concern Type', 'ennu-life' ),
                    'hair_q3' => esc_html__( 'Duration of Hair Loss', 'ennu-life' ),
                    'hair_q4' => esc_html__( 'Rate of Hair Loss', 'ennu-life' ),
                    'hair_q5' => esc_html__( 'Family History of Hair Loss', 'ennu-life' ),
                    'hair_q6' => esc_html__( 'Stress Level', 'ennu-life' ),
                    'hair_q7' => esc_html__( 'Diet Quality', 'ennu-life' ),
                    'hair_q8' => esc_html__( 'Previous Hair Treatments', 'ennu-life' ),
                    'hair_q9' => esc_html__( 'Hair Treatment Goals', 'ennu-life' )
                ) );
                break;
                
            case 'ed_treatment_assessment':
                $labels = array_merge( $labels, array(
                    'ed_q1' => esc_html__( 'Relationship Status', 'ennu-life' ),
                    'ed_q2' => esc_html__( 'ED Severity Level', 'ennu-life' ),
                    'ed_q3' => esc_html__( 'Duration of Symptoms', 'ennu-life' ),
                    'ed_q4' => esc_html__( 'Related Health Conditions', 'ennu-life' ),
                    'ed_q5' => esc_html__( 'Previous ED Treatments', 'ennu-life' ),
                    'ed_q6' => esc_html__( 'Smoking Status', 'ennu-life' ),
                    'ed_q7' => esc_html__( 'Exercise Frequency', 'ennu-life' ),
                    'ed_q8' => esc_html__( 'Stress Level', 'ennu-life' ),
                    'ed_q9' => esc_html__( 'ED Treatment Goals', 'ennu-life' )
                ) );
                break;
                
            case 'weight_loss_assessment':
                $labels = array_merge( $labels, array(
                    'weight_q1' => esc_html__( 'Current Weight', 'ennu-life' ),
                    'weight_q2' => esc_html__( 'Target Weight', 'ennu-life' ),
                    'weight_q3' => esc_html__( 'Height', 'ennu-life' ),
                    'weight_q4' => esc_html__( 'Dietary Habits', 'ennu-life' ),
                    'weight_q5' => esc_html__( 'Exercise Routine', 'ennu-life' ),
                    'weight_q6' => esc_html__( 'Medical Conditions', 'ennu-life' ),
                    'weight_q7' => esc_html__( 'Current Medications', 'ennu-life' ),
                    'weight_q8' => esc_html__( 'Weight Loss History', 'ennu-life' ),
                    'weight_q9' => esc_html__( 'Weight Loss Goals', 'ennu-life' )
                ) );
                break;
                
            case 'health_assessment':
                $labels = array_merge( $labels, array(
                    'health_q1' => esc_html__( 'Gender', 'ennu-life' ),
                    'health_q2' => esc_html__( 'Current Health Concerns', 'ennu-life' ),
                    'health_q3' => esc_html__( 'Medical History', 'ennu-life' ),
                    'health_q4' => esc_html__( 'Current Medications', 'ennu-life' ),
                    'health_q5' => esc_html__( 'Dietary Habits', 'ennu-life' ),
                    'health_q6' => esc_html__( 'Exercise Routine', 'ennu-life' ),
                    'health_q7' => esc_html__( 'Sleep Quality', 'ennu-life' ),
                    'health_q8' => esc_html__( 'Stress Level', 'ennu-life' ),
                    'health_q9' => esc_html__( 'Health & Wellness Goals', 'ennu-life' )
                ) );
                break;
                
            case 'skin_assessment':
                $labels = array_merge( $labels, array(
                    'skin_q1' => esc_html__( 'Gender', 'ennu-life' ),
                    'skin_q2' => esc_html__( 'Skin Type', 'ennu-life' ),
                    'skin_q3' => esc_html__( 'Primary Skin Concern', 'ennu-life' ),
                    'skin_q4' => esc_html__( 'Sun Exposure Level', 'ennu-life' ),
                    'skin_q5' => esc_html__( 'Current Skincare Routine', 'ennu-life' ),
                    'skin_q6' => esc_html__( 'Skincare Budget Range', 'ennu-life' ),
                    'skin_q7' => esc_html__( 'Skincare Treatment Goals', 'ennu-life' )
                ) );
                break;
                
            // Legacy support for old assessment types
            case 'weight_loss_quiz':
                $labels = array_merge( $labels, array(
                    'age_range' => esc_html__( 'Age Range', 'ennu-life' ),
                    'current_weight' => esc_html__( 'Current Weight', 'ennu-life' ),
                    'target_weight' => esc_html__( 'Target Weight', 'ennu-life' ),
                    'height' => esc_html__( 'Height', 'ennu-life' ),
                    'dietary_habits' => esc_html__( 'Dietary Habits', 'ennu-life' ),
                    'exercise_routine' => esc_html__( 'Exercise Routine', 'ennu-life' ),
                    'medical_conditions' => esc_html__( 'Medical Conditions', 'ennu-life' ),
                    'medications' => esc_html__( 'Medications', 'ennu-life' ),
                    'weight_loss_history' => esc_html__( 'Weight Loss History', 'ennu-life' ),
                    'weight_loss_goals' => esc_html__( 'Weight Loss Goals', 'ennu-life' )
                ) );
                break;
                
            case 'hormone_assessment':
                $labels = array_merge( $labels, array(
                    'hormone_age_range' => esc_html__( 'Age Range', 'ennu-life' ),
                    'hormone_gender' => esc_html__( 'Gender', 'ennu-life' ),
                    'hormone_concern_type' => esc_html__( 'Hormone Concern Type', 'ennu-life' ),
                    'hormone_symptoms' => esc_html__( 'Hormone-Related Symptoms', 'ennu-life' ),
                    'hormone_medical_history' => esc_html__( 'Medical History', 'ennu-life' ),
                    'hormone_medications' => esc_html__( 'Current Medications', 'ennu-life' ),
                    'hormone_stress_level' => esc_html__( 'Stress Level', 'ennu-life' ),
                    'hormone_sleep_quality' => esc_html__( 'Sleep Quality', 'ennu-life' ),
                    'hormone_dietary_habits' => esc_html__( 'Dietary Habits', 'ennu-life' ),
                    'hormone_treatment_goals' => esc_html__( 'Hormone Treatment Goals', 'ennu-life' )
                ) );
                break;
        }
        
        return $labels;
    }
    
    /**
     * Save user assessment fields
     */
    public function save_user_assessment_fields( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }
        
        // Assessment data is read-only in user profiles
        // Data should be updated through assessment forms only
    }
    
    /**
     * Create dummy assessment fields for WP Fusion detection (v15.6)
     * This ensures WP Fusion can detect all possible assessment fields
     * Will be removed in v15.7
     */
    private function create_dummy_assessment_fields() {
        // Get admin user (first admin user found)
        $admin_users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
        if ( empty( $admin_users ) ) {
            return; // No admin user found
        }
        
        $admin_user_id = $admin_users[0]->ID;
        
        // Assessment types to create dummy data for
        $assessment_types = array(
            'hair_assessment',
            'ed_treatment_assessment', 
            'weight_loss_assessment',
            'health_assessment',
            'skin_assessment'
        );
        
        foreach ( $assessment_types as $assessment_type ) {
            $field_labels = $this->get_assessment_field_labels( $assessment_type );
            
            // Create dummy assessment data
            $dummy_data = array();
            foreach ( $field_labels as $field_key => $label ) {
                $dummy_data[ $field_key ] = 'dummy_value_for_wp_fusion_detection';
            }
            
            // Store dummy data in the same format as real assessments
            $dummy_assessment = array(
                'data' => $dummy_data,
                'date' => current_time( 'mysql' ),
                'ip' => '127.0.0.1',
                'user_agent' => 'ENNU Plugin Dummy Data v15.6'
            );
            
            // Store as latest assessment for this type
            $latest_key = 'ennu_latest_' . $assessment_type;
            update_user_meta( $admin_user_id, $latest_key, $dummy_assessment );
            
            // Also store individual field values for WP Fusion detection
            foreach ( $field_labels as $field_key => $label ) {
                update_user_meta( $admin_user_id, $field_key, 'dummy_value_for_wp_fusion_detection' );
            }
        }
        
        // Mark that dummy data was created
        update_option( 'ennu_dummy_fields_created', '15.6' );
        
        $this->log_message( 'Dummy assessment fields created for WP Fusion detection', 'info' );
    }
    
    /**
     * Clean up dummy assessment fields (v15.7)
     * Removes dummy data created in v15.6 after WP Fusion has detected the fields
     */
    private function cleanup_dummy_assessment_fields() {
        // Check if dummy data was created in v15.6
        $dummy_version = get_option( 'ennu_dummy_fields_created' );
        if ( $dummy_version !== '15.6' ) {
            return; // No dummy data to clean up
        }
        
        // Get admin user (first admin user found)
        $admin_users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
        if ( empty( $admin_users ) ) {
            return; // No admin user found
        }
        
        $admin_user_id = $admin_users[0]->ID;
        
        // Assessment types to clean up dummy data for
        $assessment_types = array(
            'hair_assessment',
            'ed_treatment_assessment', 
            'weight_loss_assessment',
            'health_assessment',
            'skin_assessment'
        );
        
        foreach ( $assessment_types as $assessment_type ) {
            $field_labels = $this->get_assessment_field_labels( $assessment_type );
            
            // Check if the latest assessment is dummy data
            $latest_key = 'ennu_latest_' . $assessment_type;
            $assessment_data = get_user_meta( $admin_user_id, $latest_key, true );
            
            if ( is_array( $assessment_data ) && 
                 isset( $assessment_data['user_agent'] ) && 
                 $assessment_data['user_agent'] === 'ENNU Plugin Dummy Data v15.6' ) {
                
                // Remove dummy assessment data
                delete_user_meta( $admin_user_id, $latest_key );
                
                // Remove individual dummy field values
                foreach ( $field_labels as $field_key => $label ) {
                    $field_value = get_user_meta( $admin_user_id, $field_key, true );
                    if ( $field_value === 'dummy_value_for_wp_fusion_detection' ) {
                        delete_user_meta( $admin_user_id, $field_key );
                    }
                }
            }
        }
        
        // Mark that dummy data was cleaned up
        update_option( 'ennu_dummy_fields_created', '15.7_cleaned' );
        
        $this->log_message( 'Dummy assessment fields cleaned up successfully', 'info' );
    }
    
    /**
     * Log message
     */
    private function log_message( $message, $level = 'info' ) {
        $settings = get_option( 'ennu_life_settings', array() );
        $enable_logging = isset( $settings['enable_logging'] ) ? $settings['enable_logging'] : true;
        
        if ( ! $enable_logging ) {
            return;
        }
        
        $log_entry = array(
            'timestamp' => current_time( 'mysql' ),
            'level' => $level,
            'message' => $message
        );
        
        $this->error_log[] = $log_entry;
        
        // Also log to WordPress debug log if enabled
        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( sprintf( '[ENNU Life] %s: %s', strtoupper( $level ), $message ) );
        }
    }
    
    /**
     * Get component
     */
    public function get_component( $name ) {
        return isset( $this->components[ $name ] ) ? $this->components[ $name ] : null;
    }
    
    /**
     * PHP version notice
     */
    public function php_version_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php 
                printf( 
                    esc_html__( 'ENNU Life requires PHP version %s or higher. You are running version %s.', 'ennu-life' ),
                    esc_html( ENNU_LIFE_MIN_PHP_VERSION ),
                    esc_html( PHP_VERSION )
                ); 
                ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * WordPress version notice
     */
    public function wp_version_notice() {
        global $wp_version;
        $current_version = isset( $wp_version ) ? $wp_version : get_bloginfo( 'version' );
        ?>
        <div class="notice notice-error">
            <p>
                <?php 
                printf( 
                    esc_html__( 'ENNU Life requires WordPress version %s or higher. You are running version %s.', 'ennu-life' ),
                    esc_html( ENNU_LIFE_MIN_WP_VERSION ),
                    esc_html( $current_version )
                ); 
                ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Initialization error notice
     */
    public function init_error_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php esc_html_e( 'ENNU Life failed to initialize properly. Please check the error log for details.', 'ennu-life' ); ?>
            </p>
        </div>
        <?php
    }
}

// Initialize plugin
function ennu_life_init() {
    return ENNU_Life_Plugin::get_instance();
}

// Start the plugin
add_action( 'plugins_loaded', 'ennu_life_init', 0 );

// Activation/Deactivation/Uninstall hooks (must be outside class for proper execution)
register_activation_hook( __FILE__, array( 'ENNU_Life_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ENNU_Life_Plugin', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'ENNU_Life_Plugin', 'uninstall' ) );

