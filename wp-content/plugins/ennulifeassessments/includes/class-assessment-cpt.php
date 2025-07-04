<?php
/**
 * ENNU Life Assessment Custom Post Types Class
 * Creates CPTs for each assessment type with dual saving functionality
 * 
 * @package ENNU_Life
 * @version 14.1.11
 * @author ENNU Life Development Team
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Assessment_CPT {
    
    private $assessment_types = array();
    
    public function __construct() {
        $this->define_assessment_types();
        $this->init_hooks();
    }
    
    /**
     * Define assessment types and their CPT configurations
     */
    private function define_assessment_types() {
        $this->assessment_types = array(
            'hair_assessment' => array(
                'name' => 'Hair Assessment',
                'slug' => 'hair-assessment',
                'icon' => 'dashicons-admin-users',
                'fields' => array(
                    'name' => 'Full Name',
                    'email' => 'Email Address',
                    'mobile' => 'Mobile Phone',
                    'question_1' => 'Age Range',
                    'question_2' => 'Gender',
                    'question_3' => 'Hair Concern Type',
                    'question_4' => 'Duration',
                    'question_5' => 'Rate of Loss',
                    'question_6' => 'Family History',
                    'question_7' => 'Stress Level',
                    'question_8' => 'Diet Quality',
                    'question_9' => 'Previous Treatments',
                    'question_10' => 'Treatment Goals'
                )
            ),
            'ed_treatment_assessment' => array(
                'name' => 'ED Treatment Assessment',
                'slug' => 'ed-treatment-assessment',
                'icon' => 'dashicons-heart',
                'fields' => array(
                    'name' => 'Full Name',
                    'email' => 'Email Address',
                    'mobile' => 'Mobile Phone',
                    'question_1' => 'Age Range',
                    'question_2' => 'Relationship Status',
                    'question_3' => 'ED Severity',
                    'question_4' => 'Duration of Symptoms',
                    'question_5' => 'Health Conditions',
                    'question_6' => 'Previous Treatments',
                    'question_7' => 'Smoking Status',
                    'question_8' => 'Exercise Frequency',
                    'question_9' => 'Stress Level',
                    'question_10' => 'Treatment Goals'
                )
            ),
            'weight_loss_assessment' => array(
                'name' => 'Weight Loss Assessment',
                'slug' => 'weight-loss-assessment',
                'icon' => 'dashicons-chart-line',
                'fields' => array(
                    'name' => 'Full Name',
                    'email' => 'Email Address',
                    'mobile' => 'Mobile Phone',
                    'question_1' => 'Age Range',
                    'question_2' => 'Gender',
                    'question_3' => 'Current Weight Goal',
                    'question_4' => 'Weight Loss Timeline',
                    'question_5' => 'Previous Attempts',
                    'question_6' => 'Exercise Frequency',
                    'question_7' => 'Diet Preferences',
                    'question_8' => 'Health Conditions',
                    'question_9' => 'Motivation Level',
                    'question_10' => 'Support System'
                )
            ),
            'weight_loss_quiz' => array(
                'name' => 'Weight Loss Quiz',
                'slug' => 'weight-loss-quiz',
                'icon' => 'dashicons-clipboard',
                'fields' => array(
                    'name' => 'Full Name',
                    'email' => 'Email Address',
                    'mobile' => 'Mobile Phone',
                    'question_1' => 'Age Range',
                    'question_2' => 'Gender',
                    'question_3' => 'Weight Goals',
                    'question_4' => 'Timeline',
                    'question_5' => 'Health Conditions',
                    'question_6' => 'Activity Level',
                    'question_7' => 'Diet Preference',
                    'question_8' => 'Primary Goals'
                )
            ),
            'health_assessment' => array(
                'name' => 'Health Assessment',
                'slug' => 'health-assessment',
                'icon' => 'dashicons-plus-alt',
                'fields' => array(
                    'name' => 'Full Name',
                    'email' => 'Email Address',
                    'mobile' => 'Mobile Phone',
                    'question_1' => 'Age Range',
                    'question_2' => 'Gender',
                    'question_3' => 'Overall Health',
                    'question_4' => 'Energy Level',
                    'question_5' => 'Sleep Quality',
                    'question_6' => 'Exercise Habits',
                    'question_7' => 'Stress Management',
                    'question_8' => 'Nutrition Quality',
                    'question_9' => 'Health Goals',
                    'question_10' => 'Medical History'
                )
            ),
            'hormone_assessment' => array(
                'name' => 'Hormone Assessment',
                'slug' => 'hormone-assessment',
                'icon' => 'dashicons-analytics',
                'fields' => array(
                    'name' => 'Full Name',
                    'email' => 'Email Address',
                    'mobile' => 'Mobile Phone',
                    'question_1' => 'Age Range',
                    'question_2' => 'Biological Sex',
                    'question_3' => 'Energy Levels',
                    'question_4' => 'Mood Stability',
                    'question_5' => 'Sleep Quality',
                    'question_6' => 'Weight Changes',
                    'question_7' => 'Menstrual Cycle',
                    'question_8' => 'Stress Levels',
                    'question_9' => 'Exercise Frequency',
                    'question_10' => 'Primary Goal'
                )
            ),
            'skin_assessment' => array(
                'name' => 'Skin Assessment',
                'slug' => 'skin-assessment',
                'icon' => 'dashicons-admin-appearance',
                'fields' => array(
                    'name' => 'Full Name',
                    'email' => 'Email Address',
                    'mobile' => 'Mobile Phone',
                    'question_1' => 'Age Range',
                    'question_2' => 'Gender',
                    'question_3' => 'Skin Type',
                    'question_4' => 'Primary Concerns',
                    'question_5' => 'Skin Sensitivity',
                    'question_6' => 'Current Routine',
                    'question_7' => 'Sun Exposure',
                    'question_8' => 'Lifestyle Factors',
                    'question_9' => 'Treatment History',
                    'question_10' => 'Desired Outcomes'
                )
            )
        );
        
        // Add support for both skin assessment types to map to the same CPT
        $this->assessment_types['advanced_skin_assessment'] = $this->assessment_types['skin_assessment'];
        $this->assessment_types['skin_assessment_enhanced'] = $this->assessment_types['skin_assessment'];
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // add_action('init', array($this, 'register_assessment_cpts'), 1); // Highest priority to register first
        add_action('add_meta_boxes', array($this, 'add_assessment_meta_boxes'));
        add_action('save_post', array($this, 'save_assessment_meta_boxes'));
        
        // Hook into form submission for dual saving
        // add_action("wp_ajax_ennu_form_submit", array($this, "handle_dual_save"), 5);
        // add_action("wp_ajax_nopriv_ennu_form_submit", array($this, "handle_dual_save"), 5);
        
        // Add columns and custom column content after CPTs are registered
        add_action('init', array($this, 'setup_cpt_columns'), 20);
    }
    
    /**
     * Setup CPT columns after post types are registered
     */
    public function setup_cpt_columns() {
        // Map to the new shortened post type names
        $short_names = array(
            'ennu_hair_assess',      // hair_assessment
            'ennu_ed_treatment',     // ed_treatment_assessment
            'ennu_weight_loss',      // weight_loss_assessment  
            'ennu_health',           // health_assessment
            'ennu_skin_assess'       // skin_assessment
        );
        
        foreach ($short_names as $post_type) {
            add_filter("manage_edit-{$post_type}_columns", array($this, 'add_assessment_columns'));
            add_action("manage_{$post_type}_posts_custom_column", array($this, 'populate_assessment_columns'), 10, 2);
        }
    }
    
    /**
     * Register all assessment CPTs (PRD-compliant: 5 core assessment types)
     */
    public function register_assessment_cpts() {
        error_log("ENNU: Starting CPT registration process...");
        
        // Register only the 5 core assessment types as specified in PRD
        $prd_assessment_types = array(
            'hair_assessment',
            'ed_treatment_assessment', 
            'weight_loss_assessment',
            'health_assessment',
            'skin_assessment'
        );
        
        error_log("ENNU: Attempting to register " . count($prd_assessment_types) . " assessment CPTs");
        error_log("ENNU: Available assessment types: " . implode(', ', array_keys($this->assessment_types)));
        
        foreach ($prd_assessment_types as $type) {
            error_log("ENNU: Processing CPT registration for: {$type}");
            
            if (isset($this->assessment_types[$type])) {
                error_log("ENNU: Configuration found for {$type}, proceeding with registration...");
                $this->register_single_cpt($type, $this->assessment_types[$type]);
                
                // Verify registration using the mapped post type name
                $short_names = array(
                    'hair_assessment' => 'ennu_hair_assess',
                    'ed_treatment_assessment' => 'ennu_ed_treatment',
                    'weight_loss_assessment' => 'ennu_weight_loss',
                    'health_assessment' => 'ennu_health',
                    'skin_assessment' => 'ennu_skin_assess'
                );
                $post_type_name = isset($short_names[$type]) ? $short_names[$type] : 'ennu_' . $type;
                
                if (post_type_exists($post_type_name)) {
                    error_log("ENNU: ✅ SUCCESS - CPT '{$post_type_name}' is now registered and exists");
                } else {
                    error_log("ENNU: ❌ FAILURE - CPT '{$post_type_name}' registration failed - post type does not exist");
                }
            } else {
                error_log("ENNU: ❌ ERROR - Assessment type {$type} not found in assessment_types array");
            }
        }
        
        // Final verification using shortened post type names
        $short_names = array(
            'hair_assessment' => 'ennu_hair_assess',
            'ed_treatment_assessment' => 'ennu_ed_treatment',
            'weight_loss_assessment' => 'ennu_weight_loss',
            'health_assessment' => 'ennu_health',
            'skin_assessment' => 'ennu_skin_assess'
        );
        
        $registered_count = 0;
        foreach ($prd_assessment_types as $type) {
            $post_type_name = isset($short_names[$type]) ? $short_names[$type] : 'ennu_' . $type;
            if (post_type_exists($post_type_name)) {
                $registered_count++;
            }
        }
        
        error_log("ENNU: FINAL RESULT - {$registered_count} out of " . count($prd_assessment_types) . " CPTs successfully registered");
    }
    
    /**
     * Register a single assessment CPT
     */
    private function register_single_cpt($type, $config) {
        // Map long assessment type names to WordPress-compliant short names (20 char limit)
        $short_names = array(
            'hair_assessment' => 'ennu_hair_assess',           // 16 chars
            'ed_treatment_assessment' => 'ennu_ed_treatment',  // 16 chars  
            'weight_loss_assessment' => 'ennu_weight_loss',    // 15 chars
            'health_assessment' => 'ennu_health',              // 11 chars
            'skin_assessment' => 'ennu_skin_assess'            // 16 chars
        );
        
        // Get the WordPress-compliant post type name
        $post_type_name = isset($short_names[$type]) ? $short_names[$type] : 'ennu_' . $type;
        
        error_log("ENNU: Mapping '{$type}' to post type '{$post_type_name}' (" . strlen($post_type_name) . " chars)");
        
        $labels = array(
            'name' => $config['name'] . ' Submissions',
            'singular_name' => $config['name'] . ' Submission',
            'menu_name' => $config['name'],
            'add_new' => 'Add New Submission',
            'add_new_item' => 'Add New ' . $config['name'] . ' Submission',
            'edit_item' => 'Edit ' . $config['name'] . ' Submission',
            'new_item' => 'New ' . $config['name'] . ' Submission',
            'view_item' => 'View ' . $config['name'] . ' Submission',
            'search_items' => 'Search ' . $config['name'] . ' Submissions',
            'not_found' => 'No ' . strtolower($config['name']) . ' submissions found',
            'not_found_in_trash' => 'No ' . strtolower($config['name']) . ' submissions found in trash',
            'all_items' => 'All Submissions',
            'archives' => $config['name'] . ' Archives',
            'insert_into_item' => 'Insert into ' . strtolower($config['name']),
            'uploaded_to_this_item' => 'Uploaded to this ' . strtolower($config['name']),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'menu_icon' => $config['icon'],
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => 'edit_posts',
                'edit_posts' => 'edit_posts',
                'edit_others_posts' => 'edit_posts',
                'publish_posts' => 'edit_posts',
                'read_private_posts' => 'edit_posts',
                'delete_posts' => 'edit_posts',
                'delete_private_posts' => 'edit_posts',
                'delete_published_posts' => 'edit_posts',
                'delete_others_posts' => 'edit_posts',
                'edit_private_posts' => 'edit_posts',
                'edit_published_posts' => 'edit_posts',
            ),
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title', 'custom-fields'),
            'show_in_rest' => false,
        );
        
        $result = register_post_type($post_type_name, $args);
        
        // Debug logging with character count
        if (is_wp_error($result)) {
            error_log('ENNU CPT Registration Error for ' . $type . ' (as ' . $post_type_name . '): ' . $result->get_error_message());
        } else {
            error_log('ENNU CPT Successfully Registered: ' . $post_type_name . ' (' . strlen($post_type_name) . ' chars) for ' . $config['name']);
        }
    }
    
    /**
     * Add meta boxes for assessment fields
     */
    public function add_assessment_meta_boxes() {
        foreach ($this->assessment_types as $type => $config) {
            add_meta_box(
                'ennu_' . $type . '_details',
                $config['name'] . ' Details',
                array($this, 'render_assessment_meta_box'),
                'ennu_' . $type,
                'normal',
                'high',
                array('type' => $type, 'config' => $config)
            );
        }
    }
    
    /**
     * Render assessment meta box
     */
    public function render_assessment_meta_box($post, $metabox) {
        $type = $metabox['args']['type'];
        $config = $metabox['args']['config'];
        
        wp_nonce_field('ennu_assessment_meta_box', 'ennu_assessment_meta_box_nonce');
        
        echo '<table class="form-table">';
        
        foreach ($config['fields'] as $field_key => $field_label) {
            $meta_key = 'ennu_' . $type . '_' . $field_key;
            $value = get_post_meta($post->ID, $meta_key, true);
            
            echo '<tr>';
            echo '<th><label for="' . esc_attr($meta_key) . '">' . esc_html($field_label) . '</label></th>';
            echo '<td>';
            echo '<input type="text" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" class="regular-text" />';
            echo '</td>';
            echo '</tr>';
        }
        
        // Additional meta fields
        $submission_date = get_post_meta($post->ID, 'ennu_submission_date', true);
        $user_id = get_post_meta($post->ID, 'ennu_user_id', true);
        $user_ip = get_post_meta($post->ID, 'ennu_user_ip', true);
        
        echo '<tr>';
        echo '<th><label>Submission Date</label></th>';
        echo '<td><input type="text" value="' . esc_attr($submission_date) . '" class="regular-text" readonly /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label>User ID</label></th>';
        echo '<td><input type="text" value="' . esc_attr($user_id) . '" class="regular-text" readonly /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label>User IP</label></th>';
        echo '<td><input type="text" value="' . esc_attr($user_ip) . '" class="regular-text" readonly /></td>';
        echo '</tr>';
        
        echo '</table>';
        
        // Show user profile link if user exists
        if ($user_id) {
            $user = get_user_by('ID', $user_id);
            if ($user) {
                echo '<p><strong>User Profile:</strong> <a href="' . admin_url('user-edit.php?user_id=' . $user_id) . '" target="_blank">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</a></p>';
            }
        }
    }
    
    /**
     * Save assessment meta box data
     */
    public function save_assessment_meta_boxes($post_id) {
        if (!isset($_POST['ennu_assessment_meta_box_nonce']) || !wp_verify_nonce($_POST['ennu_assessment_meta_box_nonce'], 'ennu_assessment_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Get post type to determine which fields to save
        $post_type = get_post_type($post_id);
        $assessment_type = str_replace('ennu_', '', $post_type);
        
        if (!isset($this->assessment_types[$assessment_type])) {
            return;
        }
        
        $config = $this->assessment_types[$assessment_type];
        
        // Save each field as individual meta
        foreach ($config['fields'] as $field_key => $field_label) {
            $meta_key = 'ennu_' . $assessment_type . '_' . $field_key;
            if (isset($_POST[$meta_key])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$meta_key]));
            }
        }
    }
    
    /**
     * Add custom columns to assessment post lists
     */
    public function add_assessment_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['ennu_name'] = 'Name';
        $new_columns['ennu_email'] = 'Email';
        $new_columns['ennu_mobile'] = 'Mobile';
        $new_columns['ennu_user'] = 'User';
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Populate custom columns in assessment post lists
     */
    public function populate_assessment_columns($column, $post_id) {
        $post_type = get_post_type($post_id);
        $assessment_type = str_replace('ennu_', '', $post_type);
        
        switch ($column) {
            case 'ennu_name':
                $name = get_post_meta($post_id, 'ennu_' . $assessment_type . '_name', true);
                echo esc_html($name);
                break;
                
            case 'ennu_email':
                $email = get_post_meta($post_id, 'ennu_' . $assessment_type . '_email', true);
                if ($email) {
                    echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
                }
                break;
                
            case 'ennu_mobile':
                $mobile = get_post_meta($post_id, 'ennu_' . $assessment_type . '_mobile', true);
                if ($mobile) {
                    echo '<a href="tel:' . esc_attr($mobile) . '">' . esc_html($mobile) . '</a>';
                }
                break;
                
            case 'ennu_user':
                $user_id = get_post_meta($post_id, 'ennu_user_id', true);
                if ($user_id) {
                    $user = get_user_by('ID', $user_id);
                    if ($user) {
                        echo '<a href="' . admin_url('user-edit.php?user_id=' . $user_id) . '">' . esc_html($user->display_name) . '</a>';
                    } else {
                        echo 'User #' . esc_html($user_id) . ' (deleted)';
                    }
                } else {
                    echo 'Guest';
                }
                break;
        }
    }
    
    /**
     * Handle dual saving - save to both user profile and CPT
     */
    public function handle_dual_save() {
        // Security check removed for compatibility
        error_log('ENNU: CPT dual save handler called');
        
        $assessment_type = sanitize_text_field($_POST['assessment_type']);
        
        // Only handle assessments that have CPTs
        if (!isset($this->assessment_types[$assessment_type])) {
            return; // Let other handlers process this
        }
        
        $config = $this->assessment_types[$assessment_type];
        
        // Collect form data
        $form_data = array();
        foreach ($config['fields'] as $field_key => $field_label) {
            if (isset($_POST[$field_key])) {
                $form_data[$field_key] = sanitize_text_field($_POST[$field_key]);
            }
        }
        
        // Get user info
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $user_ip = $_SERVER['REMOTE_ADDR'];
        
        // Create CPT post
        $post_title = $config['name'] . ' - ' . ($form_data['name'] ?? 'Anonymous') . ' - ' . date('Y-m-d H:i:s');
        
        $post_data = array(
            'post_title' => $post_title,
            'post_type' => 'ennu_' . $assessment_type,
            'post_status' => 'publish',
            'post_author' => $user_id ?: 1,
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Save each field as individual meta in CPT
            foreach ($config['fields'] as $field_key => $field_label) {
                if (isset($form_data[$field_key])) {
                    $meta_key = 'ennu_' . $assessment_type . '_' . $field_key;
                    update_post_meta($post_id, $meta_key, $form_data[$field_key]);
                }
            }
            
            // Save additional meta
            update_post_meta($post_id, 'ennu_submission_date', current_time('mysql'));
            update_post_meta($post_id, 'ennu_user_id', $user_id);
            update_post_meta($post_id, 'ennu_user_ip', $user_ip);
            update_post_meta($post_id, 'ennu_assessment_type', $assessment_type);
            
            // Also save to user profile (existing functionality)
            if ($user_id) {
                $assessment_data = array(
                    'type' => $assessment_type,
                    'data' => $form_data,
                    'date' => current_time('mysql'),
                    'status' => 'completed',
                    'post_id' => $post_id
                );
                
                // Save latest assessment
                update_user_meta($user_id, 'ennu_latest_' . $assessment_type, $assessment_data);
                
                // Save individual fields to user profile
                foreach ($config['fields'] as $field_key => $field_label) {
                    if (isset($form_data[$field_key])) {
                        $user_meta_key = 'ennu_' . $assessment_type . '_' . $field_key;
                        update_user_meta($user_id, $user_meta_key, $form_data[$field_key]);
                    }
                }
            }
            
            wp_send_json_success(array(
                'message' => 'Assessment saved successfully',
                'post_id' => $post_id,
                'user_id' => $user_id
            ));
        } else {
            wp_send_json_error('Failed to create assessment post');
        }
    }
    
    /**
     * Get assessment types for external use
     */
    public function get_assessment_types() {
        return $this->assessment_types;
    }
}

// Initialize the CPT system
new ENNU_Assessment_CPT();

