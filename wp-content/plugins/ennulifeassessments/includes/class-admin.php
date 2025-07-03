<?php
/**
 * ENNU Life Admin Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Admin {
    
    /**
     * Constructor - Initialize admin functionality
     */
    public function __construct() {
        // Use more specific hook names to avoid conflicts
        // Admin menu removed per user request
        // add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 10 );
        // add_action( 'admin_init', array( $this, 'settings_init' ), 10 );
        add_action( 'wp_ajax_ennu_admin_action', array( $this, 'handle_admin_ajax' ) );
        
        // User profile fields with specific priority
        add_action( 'show_user_profile', array( $this, 'show_user_assessment_fields' ), 20 );
        add_action( 'edit_user_profile', array( $this, 'show_user_assessment_fields' ), 20 );
        add_action( 'personal_options_update', array( $this, 'save_user_assessment_fields' ), 10 );
        add_action('edit_user_profile_update', array($this, 'save_user_assessment_fields'));
    }
    
    /*
    // Admin menu removed per user request
    public function add_admin_menu() {
        // Add main menu page first
        add_menu_page(
            'ENNU Life',
            'ENNU Life',
            'manage_options',
            'ennu-life',
            array($this, 'main_page'),
            'dashicons-heart',
            58
        );
        
        // Add submenu pages
        add_submenu_page(
            'ennu-life',
            'Settings',
            'Settings',
            'manage_options',
            'ennu-life-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'ennu-life',
            'Submissions',
            'Submissions',
            'manage_options',
            'ennu-life-submissions',
            array($this, 'submissions_page')
        );
    }
    */
    
    /*
    // Admin page methods removed per user request
    public function main_page() {
        ?>
        <div class="wrap">
            <h1>ENNU Life Dashboard</h1>
            <div class="card">
                <h2>Welcome to ENNU Life</h2>
                <p>Manage your health assessments and submissions from this dashboard.</p>
                <p><a href="<?php echo admin_url('admin.php?page=ennu-life-submissions'); ?>" class="button button-primary">View Submissions</a></p>
                <p><a href="<?php echo admin_url('admin.php?page=ennu-life-settings'); ?>" class="button">Settings</a></p>
            </div>
        </div>
        <?php
    }
    
    public function settings_init() {
        register_setting('ennu_life_settings', 'ennu_life_options');
        
        add_settings_section(
            'ennu_life_main',
            'Main Settings',
            array($this, 'settings_section_callback'),
            'ennu_life_settings'
        );
        
        add_settings_field(
            'enable_analytics',
            'Enable Analytics',
            array($this, 'analytics_field_callback'),
            'ennu_life_settings',
            'ennu_life_main'
        );
    }
    
    public function settings_section_callback() {
        echo '<p>Configure your ENNU Life plugin settings.</p>';
    }
    
    public function analytics_field_callback() {
        $options = get_option('ennu_life_options', array());
        $value = isset($options['enable_analytics']) ? $options['enable_analytics'] : 1;
        echo '<input type="checkbox" name="ennu_life_options[enable_analytics]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label>Enable analytics tracking</label>';
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>ENNU Life Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('ennu_life_settings');
                do_settings_sections('ennu_life_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    public function submissions_page() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ennu_assessments';
        // Fixed: Use wpdb->prepare() to prevent SQL injection
        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `%1s` ORDER BY created_at DESC LIMIT %d",
            $table_name,
            50
        ));
        
        ?>
        <div class="wrap">
            <h1>Form Submissions</h1>
            <div class="card">
                <h2>Recent Submissions</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($submissions): ?>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <td><?php echo esc_html($submission->id); ?></td>
                                    <td><?php echo esc_html($submission->assessment_type); ?></td>
                                    <td><?php echo esc_html($submission->user_id); ?></td>
                                    <td><?php echo esc_html($submission->created_at); ?></td>
                                    <td>
                                        <a href="#" class="button">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No submissions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    */
    
    /**
     * Display assessment fields in user profile
     */
    public function show_user_assessment_fields($user) {
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }
        
        // Debug: Check if we have any assessment data
        $all_meta = get_user_meta($user->ID);
        $has_assessment_data = false;
        
        foreach ($all_meta as $key => $value) {
            if (strpos($key, 'ennu_') === 0) {
                $has_assessment_data = true;
                break;
            }
        }
        
        ?>
        <h3>ENNU Life Assessment Data</h3>
        
        <?php if (!$has_assessment_data): ?>
            <p><em>No assessment data found for this user. Complete an assessment to see data here.</em></p>
            <p><strong>Debug Info:</strong> Looking for user meta keys starting with 'ennu_'</p>
            
            <!-- Show a few sample meta keys for debugging -->
            <details>
                <summary>Debug: All User Meta Keys (click to expand)</summary>
                <ul style="max-height: 200px; overflow-y: auto; background: #f9f9f9; padding: 10px; margin: 10px 0;">
                    <?php foreach (array_keys($all_meta) as $meta_key): ?>
                        <li><code><?php echo esc_html($meta_key); ?></code></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>
        
        <table class="form-table">
            <?php
            // Get all assessment types - only the 5 core assessments
            $assessment_types = array(
                'hair_assessment' => 'Hair Assessment',
                'ed_treatment_assessment' => 'ED Treatment Assessment',
                'weight_loss_assessment' => 'Weight Loss Assessment',
                'health_assessment' => 'Health Assessment',
                'skin_assessment' => 'Skin Assessment'
            );
            
            foreach ($assessment_types as $type => $label) {
                $this->display_assessment_fields($user->ID, $type, $label);
            }
            ?>
        </table>
        <?php
    }
    
    /**
     * Display individual assessment fields
     */
    private function display_assessment_fields($user_id, $assessment_type, $assessment_label) {
        // Get all user meta for this assessment type - match form handler naming
        $clean_assessment_type = str_replace('-', '_', $assessment_type);
        $meta_prefix = 'ennu_' . $clean_assessment_type . '_';
        $all_meta = get_user_meta($user_id);
        
        // Enhanced debugging for field retrieval
        error_log("ENNU Admin Debug: Looking for fields with prefix: " . $meta_prefix);
        error_log("ENNU Admin Debug: Total user meta fields: " . count($all_meta));
        
        // Find all ENNU-related fields for debugging
        $ennu_fields = array();
        foreach ($all_meta as $key => $value) {
            if (strpos($key, 'ennu_') === 0) {
                $ennu_fields[$key] = $value[0];
            }
        }
        error_log("ENNU Admin Debug: Found ENNU fields: " . print_r(array_keys($ennu_fields), true));
        
        $assessment_fields = array();
        foreach ($all_meta as $key => $value) {
            if (strpos($key, $meta_prefix) === 0 && !strpos($key, '_date') && !strpos($key, '_label') && !strpos($key, '_completion_status') && !strpos($key, '_submission_date') && !strpos($key, '_version')) {
                $field_key = str_replace($meta_prefix, '', $key);
                $field_value = is_array($value) ? $value[0] : $value;
                
                // Skip empty values
                if (empty($field_value)) {
                    continue;
                }
                
                $assessment_fields[$field_key] = array(
                    'value' => $field_value,
                    'date' => get_user_meta($user_id, $key . '_date', true),
                    'label' => get_user_meta($user_id, $key . '_label', true),
                    'meta_key' => $key
                );
                
                error_log("ENNU Admin Debug: Found field: " . $key . " = " . $field_value);
            }
        }
        
        if (!empty($assessment_fields)) {
            echo '<tr><th colspan="2"><h4>' . esc_html($assessment_label) . '</h4></th></tr>';
            
            // Show completion status first
            $completion_status = get_user_meta($user_id, $meta_prefix . 'completion_status', true);
            $submission_date = get_user_meta($user_id, $meta_prefix . 'submission_date', true);
            
            if ($completion_status || $submission_date) {
                echo '<tr>';
                echo '<th><label>Assessment Status</label></th>';
                echo '<td>';
                if ($completion_status) {
                    echo '<strong>' . esc_html(ucfirst($completion_status)) . '</strong>';
                }
                if ($submission_date) {
                    echo ' - Completed: ' . date('M j, Y g:i A', strtotime($submission_date));
                }
                echo '</td>';
                echo '</tr>';
            }
            
            foreach ($assessment_fields as $field_key => $field_data) {
                $label = !empty($field_data['label']) ? $field_data['label'] : ucwords(str_replace('_', ' ', $field_key));
                $date = !empty($field_data['date']) ? ' (Updated: ' . date('M j, Y g:i A', strtotime($field_data['date'])) . ')' : '';
                
                echo '<tr>';
                echo '<th><label for="' . esc_attr($field_data['meta_key']) . '">' . esc_html($label) . ' (ID: ' . esc_html($field_key) . ')' . $date . '</label></th>';
                echo '<td>';
                echo '<input type="text" id="' . esc_attr($field_data['meta_key']) . '" name="' . esc_attr($field_data['meta_key']) . '" value="' . esc_attr($field_data['value']) . '" class="regular-text" readonly />';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '<tr><td colspan="2"><hr style="margin: 20px 0;"></td></tr>';
        } else {
            // Show debugging info when no fields are found
            echo '<tr><th colspan="2"><h4>' . esc_html($assessment_label) . '</h4></th></tr>';
            echo '<tr>';
            echo '<td colspan="2">';
            echo '<p><em>No assessment data found for this assessment type.</em></p>';
            echo '<p><strong>Debug Info:</strong> Looking for fields with prefix: <code>' . esc_html($meta_prefix) . '</code></p>';
            
            // Show all ENNU fields for debugging
            $all_ennu_fields = array();
            foreach ($all_meta as $key => $value) {
                if (strpos($key, 'ennu_') === 0) {
                    $all_ennu_fields[] = $key . ' = ' . (is_array($value) ? $value[0] : $value);
                }
            }
            
            if (!empty($all_ennu_fields)) {
                echo '<details style="margin: 10px 0;">';
                echo '<summary>Debug: All ENNU fields found (click to expand)</summary>';
                echo '<ul style="max-height: 200px; overflow-y: auto; background: #f9f9f9; padding: 10px; margin: 10px 0;">';
                foreach ($all_ennu_fields as $field_info) {
                    echo '<li><code>' . esc_html($field_info) . '</code></li>';
                }
                echo '</ul>';
                echo '</details>';
            } else {
                echo '<p><strong>No ENNU fields found at all.</strong> This suggests the assessment data was not saved properly.</p>';
            }
            
            echo '</td>';
            echo '</tr>';
            echo '<tr><td colspan="2"><hr style="margin: 20px 0;"></td></tr>';
        }
    }
    
    /**
     * Save user assessment fields (currently read-only, but structure for future)
     */
    public function save_user_assessment_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }
        
        // Currently read-only, but structure is here for future editable fields
        // Assessment data should primarily be updated through the assessment forms
    }
    
    /**
     * Handle admin AJAX requests with enhanced error handling
     */
    public function handle_admin_ajax() {
        try {
            // Verify nonce with standardized field name
            $nonce = $_POST['ennu_nonce'] ?? $_POST['nonce'] ?? '';
            if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'ennu_admin_nonce' ) ) {
                throw new Exception( 'Security verification failed' );
            }
            
            // Verify user capabilities
            if ( ! current_user_can( 'manage_options' ) ) {
                throw new Exception( 'Insufficient permissions' );
            }
            
            $action = sanitize_text_field( $_POST['admin_action'] ?? '' );
            if ( empty( $action ) ) {
                throw new Exception( 'No action specified' );
            }
            
            // Log admin action for debugging
            error_log( sprintf( 'ENNU Admin Action: %s by user %d', $action, get_current_user_id() ) );
            
            switch ( $action ) {
                case 'export_data':
                    $this->export_assessment_data();
                    break;
                case 'delete_submission':
                    $this->delete_submission();
                    break;
                case 'clear_cache':
                    $this->clear_plugin_cache();
                    break;
                default:
                    throw new Exception( 'Unknown action: ' . $action );
            }
            
        } catch ( Exception $e ) {
            error_log( 'ENNU Admin AJAX Error: ' . $e->getMessage() );
            wp_send_json_error( array(
                'message' => $e->getMessage(),
                'code' => 'admin_error'
            ) );
        }
    }
    
    /**
     * Export assessment data with error handling
     */
    private function export_assessment_data() {
        try {
            // Implementation for data export
            wp_send_json_success( array(
                'message' => 'Export functionality coming soon',
                'data' => array()
            ) );
        } catch ( Exception $e ) {
            throw new Exception( 'Export failed: ' . $e->getMessage() );
        }
    }
    
    /**
     * Delete submission with validation
     */
    private function delete_submission() {
        try {
            $submission_id = intval( $_POST['submission_id'] ?? 0 );
            if ( $submission_id <= 0 ) {
                throw new Exception( 'Invalid submission ID' );
            }
            
            // Implementation for submission deletion
            wp_send_json_success( array(
                'message' => 'Submission deletion functionality coming soon',
                'submission_id' => $submission_id
            ) );
        } catch ( Exception $e ) {
            throw new Exception( 'Deletion failed: ' . $e->getMessage() );
        }
    }
    
    /**
     * Clear plugin cache
     */
    private function clear_plugin_cache() {
        try {
            // Clear any plugin-specific caches
            delete_transient( 'ennu_assessment_cache' );
            delete_transient( 'ennu_settings_cache' );
            
            wp_send_json_success( array(
                'message' => 'Plugin cache cleared successfully'
            ) );
        } catch ( Exception $e ) {
            throw new Exception( 'Cache clearing failed: ' . $e->getMessage() );
        }
    }
}

