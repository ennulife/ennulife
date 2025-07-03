<?php
/**
 * ENNU Life Live Content Manager
 * Auto-creates pages, templates, and content
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Live_Content_Manager {
    
    private static $instance = null;
    private $page_templates = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_ennu_auto_create_all_content', array($this, 'ajax_auto_create_all_content'));
        add_action('wp_ajax_ennu_auto_delete_all_content', array($this, 'ajax_auto_delete_all_content'));
        add_action('wp_ajax_ennu_get_content_status', array($this, 'ajax_get_content_status'));
        add_action('wp_ajax_ennu_create_sample_posts', array($this, 'ajax_create_sample_posts'));
        add_action('wp_ajax_ennu_delete_sample_posts', array($this, 'ajax_delete_sample_posts'));
        
        $this->init_page_templates();
    }
    
    private function init_page_templates() {
        $this->page_templates = array(
            'ennu-main-landing' => array(
                'title' => 'ENNU Health Platform - Main Landing',
                'slug' => 'ennu-health-platform',
                'content' => '[ennu_main_landing]',
                'meta_description' => 'Comprehensive health assessments and wellness solutions'
            ),
            'ennu-ed-treatment-assessment' => array(
                'title' => 'ED Treatment Assessment',
                'slug' => 'ed-treatment-assessment',
                'content' => '[ennu_ed_treatment_assessment]',
                'meta_description' => 'Confidential erectile dysfunction assessment and treatment options'
            ),
            'ennu-hair-restoration-assessment' => array(
                'title' => 'Hair Restoration Assessment',
                'slug' => 'hair-restoration-assessment',
                'content' => '[ennu_hair_restoration_assessment]',
                'meta_description' => 'Professional hair loss evaluation and restoration solutions'
            ),
            'ennu-weight-loss-quiz' => array(
                'title' => 'Weight Loss Assessment Quiz',
                'slug' => 'weight-loss-quiz',
                'content' => '[ennu_weight_loss_quiz]',
                'meta_description' => 'Personalized weight loss assessment and program recommendations'
            ),
            'ennu-personalized-health-survey' => array(
                'title' => 'Personalized Health Survey',
                'slug' => 'personalized-health-survey',
                'content' => '[ennu_personalized_health_survey]',
                'meta_description' => 'Comprehensive health evaluation and personalized recommendations'
            ),
            'ennu-advanced-skin-assessment' => array(
                'title' => 'Advanced Skin Assessment',
                'slug' => 'advanced-skin-assessment',
                'content' => '[ennu_advanced_skin_assessment]',
                'meta_description' => 'Professional dermatological assessment and skincare solutions'
            ),
            'ennu-health-assessment' => array(
                'title' => 'Comprehensive Health Assessment ($599)',
                'slug' => 'health-assessment',
                'content' => '[ennu_health_assessment]',
                'meta_description' => 'Complete health evaluation with professional consultation'
            ),
            'ennu-skin-assessment-enhanced' => array(
                'title' => 'Enhanced Skin Assessment',
                'slug' => 'enhanced-skin-assessment',
                'content' => '[ennu_skin_assessment_enhanced]',
                'meta_description' => 'Advanced skin analysis with treatment recommendations'
            ),
            'ennu-weight-loss-assessment' => array(
                'title' => 'Weight Loss Assessment',
                'slug' => 'weight-loss-assessment',
                'content' => '[ennu_weight_loss_assessment]',
                'meta_description' => 'Detailed weight management evaluation and planning'
            ),
            'ennu-membership-calculator' => array(
                'title' => 'Membership Calculator',
                'slug' => 'membership-calculator',
                'content' => '[ennu_membership_calculator]',
                'meta_description' => 'Calculate your optimal health membership plan'
            ),
            'ennu-health-investment-calculator' => array(
                'title' => 'Health Investment Calculator',
                'slug' => 'health-investment-calculator',
                'content' => '[ennu_health_investment_calculator]',
                'meta_description' => 'Calculate the value of your health investments'
            ),
            'ennu-medical-booking' => array(
                'title' => 'Medical Appointment Booking',
                'slug' => 'medical-booking',
                'content' => '[ennu_medical_booking]',
                'meta_description' => 'Schedule your medical consultation appointment'
            ),
            'ennu-aesthetic-booking' => array(
                'title' => 'Aesthetic Treatment Booking',
                'slug' => 'aesthetic-booking',
                'content' => '[ennu_aesthetic_booking]',
                'meta_description' => 'Book your aesthetic treatment consultation'
            ),
            'ennu-wellness-booking' => array(
                'title' => 'Wellness Service Booking',
                'slug' => 'wellness-booking',
                'content' => '[ennu_wellness_booking]',
                'meta_description' => 'Schedule wellness and preventive care services'
            ),
            'ennu-smart-booking' => array(
                'title' => 'Smart Booking System',
                'slug' => 'smart-booking',
                'content' => '[ennu_smart_booking]',
                'meta_description' => 'Intelligent appointment scheduling system'
            ),
            'ennu-products-store' => array(
                'title' => 'Health Products Store',
                'slug' => 'products-store',
                'content' => '[ennu_products_store]',
                'meta_description' => 'Premium health and wellness products'
            ),
            'ennu-membership-purchase' => array(
                'title' => 'Membership Purchase',
                'slug' => 'membership-purchase',
                'content' => '[ennu_membership_purchase]',
                'meta_description' => 'Join our exclusive health membership program'
            )
        );
    }
    
    public function ajax_auto_create_all_content() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $results = array(
            'pages_created' => 0,
            'pages_updated' => 0,
            'errors' => array(),
            'success_pages' => array()
        );
        
        foreach ($this->page_templates as $template_key => $template_data) {
            $result = $this->create_or_update_page($template_key, $template_data);
            
            if ($result['success']) {
                if ($result['action'] === 'created') {
                    $results['pages_created']++;
                } else {
                    $results['pages_updated']++;
                }
                $results['success_pages'][] = $template_data['title'];
            } else {
                $results['errors'][] = $template_data['title'] . ': ' . $result['message'];
            }
        }
        
        // Create sample blog posts
        $this->create_sample_posts();
        
        wp_send_json_success(array(
            'message' => sprintf(
                'Content creation complete! Created: %d pages, Updated: %d pages',
                $results['pages_created'],
                $results['pages_updated']
            ),
            'details' => $results
        ));
    }
    
    public function ajax_auto_delete_all_content() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $deleted_count = 0;
        $errors = array();
        
        foreach ($this->page_templates as $template_key => $template_data) {
            $page = get_page_by_path($template_data['slug']);
            
            if ($page) {
                $result = wp_delete_post($page->ID, true);
                if ($result) {
                    $deleted_count++;
                } else {
                    $errors[] = 'Failed to delete: ' . $template_data['title'];
                }
            }
        }
        
        // Delete sample posts
        $this->delete_sample_posts();
        
        wp_send_json_success(array(
            'message' => sprintf('Deleted %d pages successfully', $deleted_count),
            'deleted_count' => $deleted_count,
            'errors' => $errors
        ));
    }
    
    public function ajax_get_content_status() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $status = array(
            'pages' => array(),
            'total_pages' => count($this->page_templates),
            'existing_pages' => 0
        );
        
        foreach ($this->page_templates as $template_key => $template_data) {
            $page = get_page_by_path($template_data['slug']);
            $exists = $page ? true : false;
            
            if ($exists) {
                $status['existing_pages']++;
            }
            
            $status['pages'][] = array(
                'title' => $template_data['title'],
                'slug' => $template_data['slug'],
                'exists' => $exists,
                'url' => $exists ? get_permalink($page->ID) : '',
                'edit_url' => $exists ? get_edit_post_link($page->ID) : ''
            );
        }
        
        wp_send_json_success($status);
    }
    
    private function create_or_update_page($template_key, $template_data) {
        // Check if page already exists
        $existing_page = get_page_by_path($template_data['slug']);
        
        $page_data = array(
            'post_title' => $template_data['title'],
            'post_name' => $template_data['slug'],
            'post_content' => $template_data['content'],
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => get_current_user_id(),
            'meta_input' => array(
                '_wp_page_template' => 'ennu-template',
                '_ennu_template_key' => $template_key,
                '_yoast_wpseo_metadesc' => $template_data['meta_description'],
                '_ennu_auto_created' => current_time('mysql')
            )
        );
        
        if ($existing_page) {
            // Update existing page
            $page_data['ID'] = $existing_page->ID;
            $result = wp_update_post($page_data, true);
            
            if (is_wp_error($result)) {
                return array(
                    'success' => false,
                    'message' => $result->get_error_message()
                );
            }
            
            return array(
                'success' => true,
                'action' => 'updated',
                'page_id' => $result
            );
        } else {
            // Create new page
            $result = wp_insert_post($page_data, true);
            
            if (is_wp_error($result)) {
                return array(
                    'success' => false,
                    'message' => $result->get_error_message()
                );
            }
            
            return array(
                'success' => true,
                'action' => 'created',
                'page_id' => $result
            );
        }
    }
    
    public function ajax_create_sample_posts() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $this->create_sample_posts();
        
        wp_send_json_success(array(
            'message' => 'Sample blog posts created successfully'
        ));
    }
    
    public function ajax_delete_sample_posts() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $this->delete_sample_posts();
        
        wp_send_json_success(array(
            'message' => 'Sample blog posts deleted successfully'
        ));
    }
    
    private function create_sample_posts() {
        $sample_posts = array(
            array(
                'title' => '5 Essential Health Assessments Every Adult Should Take',
                'content' => 'Regular health assessments are crucial for maintaining optimal wellness and preventing serious health issues. Here are the five most important assessments that every adult should consider...',
                'category' => 'Health Tips'
            ),
            array(
                'title' => 'The Science Behind Personalized Medicine',
                'content' => 'Personalized medicine is revolutionizing healthcare by tailoring treatments to individual genetic profiles, lifestyle factors, and health history...',
                'category' => 'Medical Science'
            ),
            array(
                'title' => 'Understanding Your Health Investment: ROI of Preventive Care',
                'content' => 'Investing in preventive healthcare not only improves your quality of life but also provides significant financial returns over time...',
                'category' => 'Health Economics'
            )
        );
        
        foreach ($sample_posts as $post_data) {
            // Check if post already exists
            $existing_post = get_page_by_title($post_data['title'], OBJECT, 'post');
            
            if (!$existing_post) {
                $post_id = wp_insert_post(array(
                    'post_title' => $post_data['title'],
                    'post_content' => $post_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'post',
                    'post_author' => get_current_user_id(),
                    'meta_input' => array(
                        '_ennu_sample_post' => true,
                        '_ennu_auto_created' => current_time('mysql')
                    )
                ));
                
                if ($post_id && !is_wp_error($post_id)) {
                    // Add category
                    $category = get_category_by_slug(sanitize_title($post_data['category']));
                    if (!$category) {
                        $category_id = wp_create_category($post_data['category']);
                    } else {
                        $category_id = $category->term_id;
                    }
                    
                    if ($category_id) {
                        wp_set_post_categories($post_id, array($category_id));
                    }
                }
            }
        }
    }
    
    private function delete_sample_posts() {
        $sample_posts = get_posts(array(
            'post_type' => 'post',
            'meta_key' => '_ennu_sample_post',
            'meta_value' => true,
            'posts_per_page' => -1
        ));
        
        foreach ($sample_posts as $post) {
            wp_delete_post($post->ID, true);
        }
    }
    
    public function get_page_templates() {
        return $this->page_templates;
    }
}

