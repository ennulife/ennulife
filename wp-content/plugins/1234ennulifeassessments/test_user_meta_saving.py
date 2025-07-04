import json
import re

def sanitize_key(key):
    return re.sub(r'[^a-z0-9_\-]', '', key.lower())

def sanitize_text_field(text):
    return str(text).strip()

def sanitize_email(email):
    return str(email).strip()

def current_time(format):
    # Simulate WordPress current_time function
    import datetime
    if format == 'mysql':
        return datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    return datetime.datetime.now().isoformat()

class MockENNUAssessmentShortcodes:
    def __init__(self):
        self.assessments = {
            'hair_assessment': {
                'title': 'Hair Assessment',
                'questions': 10
            },
            'ed_treatment_assessment': {
                'title': 'ED Treatment Assessment',
                'questions': 11
            },
            'weight_loss_assessment': {
                'title': 'Weight Loss Assessment',
                'questions': 12
            },
            'health_assessment': {
                'title': 'Health Assessment',
                'questions': 10
            },
            'skin_assessment': {
                'title': 'Skin Assessment',
                'questions': 8
            },
            'advanced_skin_assessment': {
                'title': 'Advanced Skin Assessment',
                'questions': 9
            },
            'skin_assessment_enhanced': {
                'title': 'Skin Assessment Enhanced',
                'questions': 8
            },
            'hormone_assessment': {
                'title': 'Hormone Assessment',
                'questions': 12
            },
            'weight_loss_quiz': {
                'title': 'Weight Loss Quiz',
                'questions': 8
            }
        }

    def sanitize_assessment_data(self, data):
        sanitized = {
            'assessment_type': sanitize_key(data.get('assessment_type', '')),
            'contact_name': sanitize_text_field(data.get('contact_name', '')),
            'contact_email': sanitize_email(data.get('contact_email', '')),
            'contact_phone': re.sub(r'[^0-9+\-\(\)\s]', '', data.get('contact_phone', '')),
            'answers': {}
        }

        # Additional name sanitization
        sanitized['contact_name'] = re.sub(r'[^a-zA-Z\s\-\'\.]', '', sanitized['contact_name'])
        sanitized['contact_name'] = sanitize_text_field(re.sub(r'\s+', ' ', sanitized['contact_name']))

        # Sanitize question answers with strict validation
        for key, value in data.items():
            if sanitized['assessment_type'] and key.startswith(sanitized['assessment_type'] + '.q'):
                clean_key = sanitize_key(key)
                sanitized['answers'][clean_key] = sanitize_text_field(value)
            elif key.startswith('contact_') or key.startswith('dob_'):
                # Handle contact and DOB fields that might not follow the qX naming
                clean_key = sanitize_key(key)
                if 'email' in clean_key:
                    sanitized[clean_key] = sanitize_email(value)
                elif 'phone' in clean_key:
                    sanitized[clean_key] = re.sub(r'[^0-9+\-\(\)\s]', '', value)
                else:
                    sanitized[clean_key] = sanitize_text_field(value)
            elif key.startswith('first_name') or key.startswith('last_name'):
                clean_key = sanitize_key(key)
                sanitized[clean_key] = sanitize_text_field(value)

        return sanitized

    def save_user_assessment_meta(self, data):
        # Simulate get_current_user_id() - for testing, we'll assume a user ID exists
        user_id = 1 # Mock user ID

        if not user_id:
            return

        meta_key = 'ennu_latest_' + data['assessment_type']
        meta_value = {
            'data': data,
            'date': current_time('mysql'),
            'status': 'completed'
        }

        print(f"Simulating update_user_meta for user_id: {user_id}")
        print(f"meta_key: {meta_key}")
        print(f"meta_value: {json.dumps(meta_value, indent=4)}")

# --- Test Cases ---
if __name__ == '__main__':
    shortcodes_instance = MockENNUAssessmentShortcodes()

    print("\n--- Test Case 1: Hair Assessment ---")
    sample_post_data_hair = {
        'assessment_type': 'hair_assessment',
        'hair_assessment.q1': 'male',
        'hair_assessment.q2': 'thinning',
        'hair_assessment.q3': 'recent',
        'contact_name': 'John Doe',
        'contact_email': 'john.doe@example.com',
        'contact_phone': '+1 (555) 123-4567',
        'dob_month': '01',
        'dob_day': '15',
        'dob_year': '1990',
        'first_name': 'John',
        'last_name': 'Doe'
    }
    sanitized_data_hair = shortcodes_instance.sanitize_assessment_data(sample_post_data_hair)
    shortcodes_instance.save_user_assessment_meta(sanitized_data_hair)

    print("\n--- Test Case 2: ED Treatment Assessment (with dob_combined and age) ---")
    sample_post_data_ed = {
        'assessment_type': 'ed_treatment_assessment',
        'ed_treatment_assessment.q1_month': '05',
        'ed_treatment_assessment.q1_day': '20',
        'ed_treatment_assessment.q1_year': '1985',
        'ed_treatment_assessment.q1_dob_combined': '1985-05-20',
        'ed_treatment_assessment.q1_age': '39',
        'ed_treatment_assessment.q2': 'single',
        'ed_treatment_assessment.q3': 'mild',
        'contact_name': 'Jane Smith',
        'contact_email': 'jane.smith@example.com',
        'contact_phone': '987-654-3210',
        'first_name': 'Jane',
        'last_name': 'Smith'
    }
    sanitized_data_ed = shortcodes_instance.sanitize_assessment_data(sample_post_data_ed)
    shortcodes_instance.save_user_assessment_meta(sanitized_data_ed)

    print("\n--- Test Case 3: Weight Loss Assessment (minimal fields) ---")
    sample_post_data_weight = {
        'assessment_type': 'weight_loss_assessment',
        'weight_loss_assessment.q1': 'lose_10',
        'contact_email': 'test@example.com'
    }
    sanitized_data_weight = shortcodes_instance.sanitize_assessment_data(sample_post_data_weight)
    shortcodes_instance.save_user_assessment_meta(sanitized_data_weight)

    print("\n--- Test Case 4: Advanced Skin Assessment (with special characters in name) ---")
    sample_post_data_skin = {
        'assessment_type': 'advanced_skin_assessment',
        'advanced_skin_assessment.q1': 'oily',
        'contact_name': 'O\'Malley-Smith, Jr.',
        'contact_email': 'o.malley@example.com'
    }
    sanitized_data_skin = shortcodes_instance.sanitize_assessment_data(sample_post_data_skin)
    shortcodes_instance.save_user_assessment_meta(sanitized_data_skin)


