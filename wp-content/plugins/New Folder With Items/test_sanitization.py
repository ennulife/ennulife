import re

def sanitize_assessment_data(data):
    sanitized = {
        'assessment_type': data.get('assessment_type', ''),
        'contact_name': re.sub(r'[^a-zA-Z\s\-\'\.]', '', data.get('contact_name', '')).strip(),
        'contact_email': data.get('contact_email', '').strip(),
        'contact_phone': re.sub(r'[^0-9+\-\(\)\s]', '', data.get('contact_phone', '')),
        'answers': {}
    }

    # Additional name sanitization to remove extra spaces
    sanitized['contact_name'] = re.sub(r'\s+', ' ', sanitized['contact_name'])

    for key, value in data.items():
        if key.startswith(sanitized['assessment_type'] + '.q'):
            clean_key = key.replace(sanitized['assessment_type'] + '.q', 'q')
            sanitized['answers'][clean_key] = str(value).strip()
        elif key.startswith('contact_') or key.startswith('dob_'):
            sanitized[key] = str(value).strip()

    return sanitized

# Sample data to test
sample_data = {
    'assessment_type': 'hair_assessment',
    'contact_name': '  John Doe!@#  ',
    'contact_email': '  john.doe@example.com  ',
    'contact_phone': '  +1 (555) 123-4567  ',
    'hair_assessment.q1.month': '01',
    'hair_assessment.q1.day': '15',
    'hair_assessment.q1.year': '1990',
    'hair_assessment.q1.dob_combined': '1990-01-15',
    'hair_assessment.q2': 'male',
    'hair_assessment.q3': 'thinning',
    'dob_month': '05',
    'dob_day': '20',
    'dob_year': '1985'
}

sanitized_output = sanitize_assessment_data(sample_data)

with open('/home/ubuntu/ennu-life-plugin/sanitized_output.txt', 'w') as f:
    for key, value in sanitized_output.items():
        f.write(f'{key}: {value}\n')



