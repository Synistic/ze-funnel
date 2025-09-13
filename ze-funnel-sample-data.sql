-- Sample data for Ze Funnel Plugin Testing
-- Run this after plugin activation to insert test funnels

-- Insert sample funnel
INSERT INTO wp_ze_funnels (name, slug, description, settings, status, created_at, updated_at) VALUES
('Lead Qualification Funnel', 'lead-qualification', 'A sample funnel to qualify potential leads for our service', 
'{
  "progressBar": true,
  "allowBack": true,
  "theme": "default",
  "animations": true,
  "thankYouTitle": "Vielen Dank für Ihr Interesse!",
  "thankYouMessage": "Wir haben Ihre Antworten erhalten und werden uns bald bei Ihnen melden.",
  "notifications": {
    "email": true
  }
}', 
'active', NOW(), NOW());

-- Get the funnel ID (this would be the last inserted ID)
SET @funnel_id = LAST_INSERT_ID();

-- Insert sample questions
INSERT INTO wp_ze_questions (funnel_id, question_text, question_type, options, validation_rules, conditional_logic, position, required, created_at, updated_at) VALUES

-- Question 1: Text Selection (Company Size)
(@funnel_id, 'Wie groß ist Ihr Unternehmen?', 'text_selection', 
'{
  "multiple": false,
  "choices": [
    {"value": "startup", "label": "Startup (1-10 Mitarbeiter)"},
    {"value": "small", "label": "Kleinunternehmen (11-50 Mitarbeiter)"},
    {"value": "medium", "label": "Mittelstand (51-250 Mitarbeiter)"},
    {"value": "large", "label": "Großunternehmen (250+ Mitarbeiter)"}
  ]
}', 
'{"required": true}', 
'[]',
1, 1, NOW(), NOW()),

-- Question 2: Icon Selection (Budget Range)
(@funnel_id, 'Welches Budget haben Sie für digitale Lösungen eingeplant?', 'icon_selection',
'{
  "multiple": false,
  "choices": [
    {"value": "low", "label": "Unter 5.000€", "icon": "💰"},
    {"value": "medium", "label": "5.000€ - 20.000€", "icon": "💳"},
    {"value": "high", "label": "20.000€ - 50.000€", "icon": "💎"},
    {"value": "enterprise", "label": "Über 50.000€", "icon": "🏆"}
  ]
}',
'{"required": true}',
'[]',
2, 1, NOW(), NOW()),

-- Question 3: Image Selection (Services)
(@funnel_id, 'Welche Dienstleistungen interessieren Sie am meisten?', 'image_selection',
'{
  "multiple": true,
  "choices": [
    {"value": "web_design", "label": "Webdesign", "image": ""},
    {"value": "seo", "label": "SEO & Marketing", "image": ""},
    {"value": "ecommerce", "label": "E-Commerce", "image": ""},
    {"value": "consulting", "label": "Beratung", "image": ""}
  ]
}',
'{"required": true}',
'[]',
3, 1, NOW(), NOW()),

-- Question 4: Text Input (Current Website)
(@funnel_id, 'Haben Sie bereits eine Website? Wenn ja, teilen Sie uns die URL mit:', 'text_input',
'{
  "placeholder": "https://www.ihre-website.de"
}',
'{
  "required": false,
  "pattern": "https?:\\/\\/(www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b([-a-zA-Z0-9()@:%_\\+.~#?&//=]*)",
  "patternMessage": "Bitte geben Sie eine gültige URL ein"
}',
'[]',
4, 0, NOW(), NOW()),

-- Question 5: Multi Input (Contact Details)
(@funnel_id, 'Bitte teilen Sie uns Ihre Kontaktdaten mit:', 'multi_input',
'{
  "fields": [
    {
      "name": "first_name",
      "label": "Vorname",
      "type": "text",
      "placeholder": "Max",
      "required": true
    },
    {
      "name": "last_name", 
      "label": "Nachname",
      "type": "text",
      "placeholder": "Mustermann",
      "required": true
    },
    {
      "name": "email",
      "label": "E-Mail-Adresse",
      "type": "email",
      "placeholder": "max@mustermann.de",
      "required": true,
      "validation": {
        "email": true
      }
    },
    {
      "name": "phone",
      "label": "Telefonnummer",
      "type": "tel",
      "placeholder": "+49 123 456789",
      "required": false
    },
    {
      "name": "company",
      "label": "Unternehmen",
      "type": "text",
      "placeholder": "Mustermann GmbH",
      "required": false
    },
    {
      "name": "privacy",
      "label": "Datenschutz",
      "type": "checkbox",
      "text": "Ich stimme der Verarbeitung meiner Daten gemäß der Datenschutzerklärung zu.",
      "required": true
    }
  ]
}',
'{"required": true}',
'[]',
5, 1, NOW(), NOW());

-- Insert a second sample funnel (Product Interest Survey)
INSERT INTO wp_ze_funnels (name, slug, description, settings, status, created_at, updated_at) VALUES
('Produkt-Interesse Umfrage', 'product-interest-survey', 'Kurze Umfrage um Kundeninteressen zu verstehen',
'{
  "progressBar": true,
  "allowBack": true,
  "theme": "default",
  "animations": true,
  "thankYouTitle": "Danke für Ihr Feedback!",
  "thankYouMessage": "Ihre Meinung hilft uns, bessere Produkte zu entwickeln."
}',
'draft', NOW(), NOW());

SET @funnel_id_2 = LAST_INSERT_ID();

-- Questions for second funnel
INSERT INTO wp_ze_questions (funnel_id, question_text, question_type, options, validation_rules, conditional_logic, position, required, created_at, updated_at) VALUES

-- Simple satisfaction survey
(@funnel_id_2, 'Wie zufrieden sind Sie mit unserem aktuellen Service?', 'icon_selection',
'{
  "multiple": false,
  "choices": [
    {"value": "very_dissatisfied", "label": "Sehr unzufrieden", "icon": "😞"},
    {"value": "dissatisfied", "label": "Unzufrieden", "icon": "😐"},
    {"value": "neutral", "label": "Neutral", "icon": "😊"},
    {"value": "satisfied", "label": "Zufrieden", "icon": "😄"},
    {"value": "very_satisfied", "label": "Sehr zufrieden", "icon": "🤩"}
  ]
}',
'{"required": true}',
'[
  {
    "operator": "equals",
    "value": "very_dissatisfied",
    "action": "jump_to",
    "target": 3
  },
  {
    "operator": "equals", 
    "value": "dissatisfied",
    "action": "jump_to",
    "target": 3
  }
]',
1, 1, NOW(), NOW()),

(@funnel_id_2, 'Welche Features würden Sie sich für die Zukunft wünschen?', 'text_selection',
'{
  "multiple": true,
  "choices": [
    {"value": "mobile_app", "label": "Mobile App"},
    {"value": "api_access", "label": "API Zugang"},
    {"value": "advanced_analytics", "label": "Erweiterte Analytics"},
    {"value": "team_collaboration", "label": "Team-Funktionen"}
  ]
}',
'{"required": false}',
'[]',
2, 0, NOW(), NOW()),

(@funnel_id_2, 'Was können wir besser machen?', 'text_input',
'{
  "placeholder": "Teilen Sie uns Ihr Feedback mit..."
}',
'{
  "required": true,
  "minLength": 10
}',
'[]',
3, 1, NOW(), NOW());

-- Insert some sample analytics data
INSERT INTO wp_ze_analytics (funnel_id, question_id, event_type, event_data, session_id, created_at) VALUES
(@funnel_id, NULL, 'funnel_start', '{"funnelId": ' + CAST(@funnel_id AS CHAR) + '}', 'test_session_1', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(@funnel_id, NULL, 'complete', '{"funnelId": ' + CAST(@funnel_id AS CHAR) + ', "completionTime": 180}', 'test_session_1', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(@funnel_id, NULL, 'funnel_start', '{"funnelId": ' + CAST(@funnel_id AS CHAR) + '}', 'test_session_2', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(@funnel_id, NULL, 'funnel_start', '{"funnelId": ' + CAST(@funnel_id AS CHAR) + '}', 'test_session_3', NOW());

-- Insert sample submission
INSERT INTO wp_ze_submissions (funnel_id, session_id, answers, user_data, status, completion_time, ip_address, user_agent, referrer, created_at, completed_at) VALUES
(@funnel_id, 'test_session_1', 
'{
  "1": "medium",
  "2": "medium", 
  "3": ["web_design", "seo"],
  "4": "https://www.example.com",
  "5": {
    "first_name": "Max",
    "last_name": "Mustermann", 
    "email": "max@mustermann.de",
    "phone": "+49 123 456789",
    "company": "Mustermann GmbH",
    "privacy": true
  }
}',
'{"ip_address": "192.168.1.1", "user_agent": "Mozilla/5.0", "referrer": "https://google.com"}',
'completed', 180, '192.168.1.1', 'Mozilla/5.0 (Test Browser)', 'https://google.com', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));