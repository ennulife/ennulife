<?php
/**
 * ENNU Life Enhanced Database Management Class
 * Handles individual field storage in both user meta and post meta
 * 
 * @package ENNU_Life
 * @version 14.1.11
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Life_Database {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Enhanced save assessment with individual field storage
     */
    public function save_assessment($assessment_type, $form_data, $user_id = null) {
        global $wpdb;
        
        try {
            // Get user ID (use parameter if provided, otherwise current user)
            if ($user_id === null) {
                $user_id = get_current_user_id();
            }
            $user_ip = $this->get_user_ip();
            
            // Sanitize assessment type
            $assessment_type = sanitize_text_field($assessment_type);
            
            // Create custom post for this assessment
            $post_data = array(
                'post_title' => $assessment_type . ' - ' . date('Y-m-d H:i:s'),
                'post_content' => 'Assessment submission for ' . $assessment_type,
                'post_status' => 'private',
                'post_type' => $this->get_post_type_for_assessment($assessment_type),
                'post_author' => $user_id ?: 1,
                'meta_input' => array(
                    'assessment_type' => $assessment_type,
                    'user_ip' => $user_ip,
                    'submission_date' => current_time('mysql'),
                    'form_data_json' => json_encode($form_data) // Keep JSON backup
                )
            );
            
            $post_id = wp_insert_post($post_data);
            
            if (is_wp_error($post_id)) {
                throw new Exception('Failed to create assessment post: ' . $post_id->get_error_message());
            }
            
            // Save each individual answer as separate meta fields
            $this->save_individual_fields($post_id, $user_id, $assessment_type, $form_data);
            
            // Save to custom table for reporting
            $table_name = $wpdb->prefix . 'ennu_assessments';
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'assessment_type' => $assessment_type,
                    'form_data' => json_encode($form_data),
                    'user_ip' => $user_ip,
                    'post_id' => $post_id,
                    'created_at' => current_time('mysql')
                ),
                array('%d', '%s', '%s', '%s', '%d', '%s')
            );
            
            if ($result === false) {
                error_log('ENNU Database Error: ' . $wpdb->last_error);
            }
            
            // Process assessment results
            $results = $this->process_assessment_results($assessment_type, $form_data);
            
            // Update user meta with latest assessment data
            if ($user_id) {
                $this->update_user_assessment_meta($user_id, $assessment_type, $form_data, $results);
            }
            
            return array(
                'success' => true,
                'post_id' => $post_id,
                'table_id' => $wpdb->insert_id,
                'results' => $results,
                'message' => 'Assessment saved successfully'
            );
            
        } catch (Exception $e) {
            error_log('ENNU Assessment Save Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Failed to save assessment: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Save individual form fields as separate meta fields
     */
    private function save_individual_fields($post_id, $user_id, $assessment_type, $form_data) {
        // Define field mappings for each assessment type
        $field_mappings = $this->get_assessment_field_mappings($assessment_type);
        
        foreach ($form_data as $field_key => $field_value) {
            // Skip system fields
            if (in_array($field_key, array('action', 'nonce', 'assessment_type'))) {
                continue;
            }
            
            // Sanitize field key
            $clean_key = sanitize_key($field_key);
            
            // Get human-readable field name
            $field_label = isset($field_mappings[$clean_key]) ? $field_mappings[$clean_key] : ucwords(str_replace('_', ' ', $clean_key));
            
            // Save to post meta with individual fields
            update_post_meta($post_id, 'field_' . $clean_key, $field_value);
            update_post_meta($post_id, 'field_' . $clean_key . '_label', $field_label);
            update_post_meta($post_id, 'field_' . $clean_key . '_timestamp', current_time('mysql'));
            
            // Save to user meta if user is logged in
            if ($user_id) {
                $user_meta_key = 'ennu_' . str_replace('-', '_', $assessment_type) . '_' . $clean_key;
                update_user_meta($user_id, $user_meta_key, $field_value);
                update_user_meta($user_id, $user_meta_key . '_date', current_time('mysql'));
                update_user_meta($user_id, $user_meta_key . '_label', $field_label);
            }
        }
        
        // Save field count and metadata
        update_post_meta($post_id, 'total_fields', count($form_data));
        update_post_meta($post_id, 'assessment_completed_at', current_time('mysql'));
        update_post_meta($post_id, 'assessment_version', ENNU_LIFE_VERSION);
        
        if ($user_id) {
            $user_meta_prefix = 'ennu_' . str_replace('-', '_', $assessment_type);
            update_user_meta($user_id, $user_meta_prefix . '_total_fields', count($form_data));
            update_user_meta($user_id, $user_meta_prefix . '_last_updated', current_time('mysql'));
            update_user_meta($user_id, $user_meta_prefix . '_completion_status', 'completed');
        }
    }
    
    /**
     * Get field mappings for human-readable labels
     */
    private function get_assessment_field_mappings($assessment_type) {
        $mappings = array(
            'hair-assessment' => array(
                'question_1' => 'Age Range',
                'question_2' => 'Gender',
                'question_3' => 'Hair Concern Type',
                'question_4' => 'Hair Loss Duration',
                'question_5' => 'Hair Loss Rate',
                'question_6' => 'Family History',
                'question_7' => 'Stress Level',
                'question_8' => 'Diet Quality',
                'question_9' => 'Previous Treatments',
                'question_10' => 'Treatment Goals',
                'name' => 'Full Name',
                'email' => 'Email Address',
                'mobile' => 'Mobile Phone'
            ),
            'hair-restoration-assessment' => array(
                'question_1' => 'Age Range',
                'question_2' => 'Gender',
                'question_3' => 'Hair Loss Stage',
                'question_4' => 'Restoration Goals',
                'question_5' => 'Budget Range',
                'question_6' => 'Timeline Preference',
                'question_7' => 'Previous Treatments',
                'question_8' => 'Donor Hair Quality',
                'question_9' => 'Lifestyle Factors',
                'question_10' => 'Expectations',
                'question_11' => 'Main Concerns',
                'question_12' => 'Contact Information',
                'name' => 'Full Name',
                'email' => 'Email Address',
                'mobile' => 'Mobile Phone'
            ),
            'hair_assessment' => array(
                'question_1' => 'Age Range',
                'question_2' => 'Gender',
                'question_3' => 'Hair Concern Type',
                'question_4' => 'Hair Loss Duration',
                'question_5' => 'Hair Loss Rate',
                'question_6' => 'Family History',
                'question_7' => 'Stress Level',
                'question_8' => 'Diet Quality',
                'question_9' => 'Previous Treatments',
                'question_10' => 'Treatment Goals',
                'name' => 'Full Name',
                'email' => 'Email Address',
                'mobile' => 'Mobile Phone'
            ),
            'hair_restoration_assessment' => array(
                'question_1' => 'Age Range',
                'question_2' => 'Gender',
                'question_3' => 'Hair Loss Stage',
                'question_4' => 'Restoration Goals',
                'question_5' => 'Budget Range',
                'question_6' => 'Timeline Preference',
                'question_7' => 'Previous Treatments',
                'question_8' => 'Donor Hair Quality',
                'question_9' => 'Lifestyle Factors',
                'question_10' => 'Expectations',
                'question_11' => 'Main Concerns',
                'question_12' => 'Contact Information',
                'name' => 'Full Name',
                'email' => 'Email Address',
                'mobile' => 'Mobile Phone'
            ),
            'ed-treatment-assessment' => array(
                'personal_info' => 'Age Range',
                'relationship_status' => 'Relationship Status',
                'severity_level' => 'Symptom Severity',
                'duration' => 'Duration of Symptoms',
                'health_conditions' => 'Health Conditions',
                'treatment_goals' => 'Treatment Goals',
                'full_name' => 'Full Name',
                'email' => 'Email Address',
                'phone' => 'Phone Number',
                'age' => 'Age',
                'medical_history' => 'Medical History',
                'current_medications' => 'Current Medications',
                'lifestyle_factors' => 'Lifestyle Factors'
            ),
            'weight-loss-assessment' => array(
                'current_weight' => 'Current Weight',
                'goal_weight' => 'Goal Weight',
                'height' => 'Height',
                'age' => 'Age',
                'gender' => 'Gender',
                'activity_level' => 'Activity Level',
                'diet_history' => 'Diet History',
                'medical_conditions' => 'Medical Conditions',
                'medications' => 'Current Medications',
                'weight_loss_goals' => 'Weight Loss Goals',
                'eating_habits' => 'Eating Habits',
                'exercise_routine' => 'Exercise Routine',
                'motivation_level' => 'Motivation Level',
                'support_system' => 'Support System'
            ),
            'health-assessment' => array(
                'age' => 'Age',
                'gender' => 'Gender',
                'height' => 'Height',
                'weight' => 'Weight',
                'exercise_frequency' => 'Exercise Frequency',
                'sleep_hours' => 'Sleep Hours',
                'stress_level' => 'Stress Level',
                'diet_quality' => 'Diet Quality',
                'smoking_status' => 'Smoking Status',
                'alcohol_consumption' => 'Alcohol Consumption',
                'chronic_conditions' => 'Chronic Conditions',
                'family_history' => 'Family History',
                'mental_health' => 'Mental Health Status',
                'preventive_care' => 'Preventive Care'
            ),
            'skin-assessment-enhanced' => array(
                'skin_type' => 'Skin Type',
                'skin_concerns' => 'Primary Skin Concerns',
                'age' => 'Age',
                'sun_exposure' => 'Sun Exposure',
                'skincare_routine' => 'Current Skincare Routine',
                'skin_sensitivity' => 'Skin Sensitivity',
                'previous_treatments' => 'Previous Treatments',
                'acne_history' => 'Acne History',
                'allergies' => 'Known Allergies',
                'hormonal_changes' => 'Hormonal Changes',
                'lifestyle_factors' => 'Lifestyle Factors'
            ),
            'hair-assessment' => array(
                'hair_loss_pattern' => 'Hair Loss Pattern',
                'hair_loss_duration' => 'Duration of Hair Loss',
                'family_history' => 'Family History',
                'stress_levels' => 'Stress Levels',
                'medications' => 'Current Medications',
                'hair_care_routine' => 'Hair Care Routine',
                'previous_treatments' => 'Previous Treatments',
                'hormonal_factors' => 'Hormonal Factors',
                'nutritional_status' => 'Nutritional Status',
                'scalp_condition' => 'Scalp Condition'
            ),
            'hormone-assessment' => array(
                'age' => 'Age',
                'gender' => 'Gender',
                'energy_levels' => 'Energy Levels',
                'sleep_quality' => 'Sleep Quality',
                'mood_changes' => 'Mood Changes',
                'weight_changes' => 'Weight Changes',
                'libido' => 'Libido',
                'menstrual_cycle' => 'Menstrual Cycle',
                'hot_flashes' => 'Hot Flashes',
                'muscle_mass' => 'Muscle Mass',
                'cognitive_function' => 'Cognitive Function',
                'bone_health' => 'Bone Health',
                'cardiovascular_health' => 'Cardiovascular Health'
            )
        );
        
        return isset($mappings[$assessment_type]) ? $mappings[$assessment_type] : array();
    }
    
    /**
     * Get post type for assessment
     */
    private function get_post_type_for_assessment($assessment_type) {
        $post_type_map = array(
            'ed-treatment-assessment' => 'ennu_ed_assess',
            'weight-loss-assessment' => 'ennu_weight_assess',
            'health-assessment' => 'ennu_health_assess',
            'skin-assessment-enhanced' => 'ennu_skin_assess',
            'advanced-skin-assessment' => 'ennu_skin_assess',
            'hair-assessment' => 'ennu_hair_assess',
            'hair-restoration-assessment' => 'ennu_hair_assess',
            'hormone-assessment' => 'ennu_hormone_assess'
        );
        
        return isset($post_type_map[$assessment_type]) ? $post_type_map[$assessment_type] : 'ennu_health_assess';
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
    }
    
    /**
     * Process assessment results
     */
    private function process_assessment_results($assessment_type, $form_data) {
        // Basic result processing - can be enhanced per assessment type
        $results = array(
            'score' => 0,
            'recommendations' => array(),
            'next_steps' => '',
            'severity_level' => 'normal'
        );
        
        // Calculate basic score based on completed fields
        $total_fields = count($form_data);
        $completed_fields = 0;
        
        foreach ($form_data as $value) {
            if (!empty($value)) {
                $completed_fields++;
            }
        }
        
        $results['score'] = $total_fields > 0 ? round(($completed_fields / $total_fields) * 100) : 0;
        
        // Add basic recommendations
        $results['recommendations'] = array(
            'Complete assessment submitted successfully',
            'Our medical team will review your responses',
            'You will receive personalized recommendations within 24 hours'
        );
        
        $results['next_steps'] = 'Schedule a consultation with our specialists to discuss your personalized treatment plan.';
        
        return $results;
    }
    
    /**
     * Update user assessment meta
     */
    private function update_user_assessment_meta($user_id, $assessment_type, $form_data, $results) {
        $meta_key = 'ennu_assessment_' . str_replace('-', '_', $assessment_type);
        
        // Save complete assessment data
        update_user_meta($user_id, $meta_key, json_encode($form_data));
        update_user_meta($user_id, $meta_key . '_date', current_time('mysql'));
        update_user_meta($user_id, $meta_key . '_score', $results['score'] ?? 0);
        update_user_meta($user_id, $meta_key . '_status', 'completed');
        
        // Update overall user health metrics
        $this->update_user_health_metrics($user_id, $assessment_type, $form_data, $results);
    }
    
    /**
     * Update user health metrics
     */
    private function update_user_health_metrics($user_id, $assessment_type, $form_data, $results) {
        // Calculate BMI if height and weight are available
        if (isset($form_data['height']) && isset($form_data['weight'])) {
            $height_m = floatval($form_data['height']) / 100; // Convert cm to meters
            $weight_kg = floatval($form_data['weight']);
            
            if ($height_m > 0 && $weight_kg > 0) {
                $bmi = round($weight_kg / ($height_m * $height_m), 1);
                update_user_meta($user_id, 'ennu_bmi', $bmi);
                
                // BMI category
                if ($bmi < 18.5) {
                    $bmi_category = 'Underweight';
                } elseif ($bmi < 25) {
                    $bmi_category = 'Normal weight';
                } elseif ($bmi < 30) {
                    $bmi_category = 'Overweight';
                } else {
                    $bmi_category = 'Obese';
                }
                update_user_meta($user_id, 'ennu_bmi_category', $bmi_category);
            }
        }
        
        // Update overall health score (average of all assessments)
        $all_scores = array();
        $assessment_types = array('ed_treatment', 'weight_loss', 'health', 'skin', 'hair', 'hormone');
        
        foreach ($assessment_types as $type) {
            $score = get_user_meta($user_id, "ennu_assessment_{$type}_assessment_score", true);
            if ($score) {
                $all_scores[] = intval($score);
            }
        }
        
        if (!empty($all_scores)) {
            $avg_score = round(array_sum($all_scores) / count($all_scores));
            update_user_meta($user_id, 'ennu_health_score', $avg_score);
        }
        
        // Update last assessment date
        update_user_meta($user_id, 'ennu_last_assessment_date', current_time('mysql'));
        update_user_meta($user_id, 'ennu_last_assessment_type', $assessment_type);
    }
}

