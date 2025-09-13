<?php
/**
 * REST API endpoints for Ze Funnel
 * 
 * @package ZeFunnel
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZeFunnel_Rest_API {
    
    private static $instance = null;
    private $namespace = 'ze-funnel/v1';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Submit funnel response
        register_rest_route($this->namespace, '/submit', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'submit_funnel'],
            'permission_callback' => '__return_true',
            'args' => $this->get_submit_args()
        ]);
        
        // Get funnel data
        register_rest_route($this->namespace, '/funnel/(?P<id>\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_funnel'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ]
            ]
        ]);
        
        // Analytics tracking
        register_rest_route($this->namespace, '/analytics', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'track_analytics'],
            'permission_callback' => '__return_true',
            'args' => $this->get_analytics_args()
        ]);
        
        // Admin endpoints (protected)
        register_rest_route($this->namespace, '/admin/funnels', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_admin_funnels'],
            'permission_callback' => [$this, 'check_admin_permissions']
        ]);
        
        register_rest_route($this->namespace, '/admin/funnel', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'create_funnel'],
            'permission_callback' => [$this, 'check_admin_permissions']
        ]);
    }
    
    /**
     * Submit funnel response
     */
    public function submit_funnel(WP_REST_Request $request) {
        try {
            $funnel_id = $request->get_param('funnelId');
            $answers = $request->get_param('answers');
            $session_id = $request->get_param('sessionId');
            $completed_at = $request->get_param('completedAt');
            
            // Validate funnel exists and is active
            $db = ZeFunnel_Database::get_instance();
            $funnel = $db->get_funnel($funnel_id);
            
            if (!$funnel || $funnel->status !== 'active') {
                return new WP_Error('invalid_funnel', 'Funnel not found or inactive', ['status' => 404]);
            }
            
            // Prepare submission data
            $submission_data = [
                'funnel_id' => $funnel_id,
                'session_id' => $session_id,
                'answers' => wp_json_encode($answers),
                'user_data' => wp_json_encode([
                    'ip_address' => $this->get_client_ip(),
                    'user_agent' => $request->get_header('user-agent'),
                    'referrer' => $request->get_header('referer')
                ]),
                'status' => 'completed',
                'ip_address' => $this->get_client_ip(),
                'user_agent' => $request->get_header('user-agent'),
                'referrer' => $request->get_header('referer'),
                'completed_at' => $completed_at ? date('Y-m-d H:i:s', strtotime($completed_at)) : current_time('mysql')
            ];
            
            // Calculate completion time if we have start time
            if ($session_id) {
                $start_time = get_transient('ze_funnel_start_' . $session_id);
                if ($start_time) {
                    $submission_data['completion_time'] = time() - $start_time;
                    delete_transient('ze_funnel_start_' . $session_id);
                }
            }
            
            // Save submission
            $submission_id = $db->create_submission($submission_data);
            
            if (!$submission_id) {
                return new WP_Error('submission_failed', 'Failed to save submission', ['status' => 500]);
            }
            
            // Log analytics
            $db->log_analytics([
                'funnel_id' => $funnel_id,
                'event_type' => 'complete',
                'event_data' => wp_json_encode([
                    'submission_id' => $submission_id,
                    'question_count' => count($answers)
                ]),
                'session_id' => $session_id
            ]);
            
            // Send notifications/webhooks if configured
            $this->handle_post_submission_actions($funnel, $submission_data, $answers);
            
            return new WP_REST_Response([
                'success' => true,
                'submission_id' => $submission_id,
                'message' => 'Thank you for your submission!'
            ], 200);
            
        } catch (Exception $e) {
            error_log('Ze Funnel submission error: ' . $e->getMessage());
            return new WP_Error('submission_error', 'An error occurred while processing your submission', ['status' => 500]);
        }
    }
    
    /**
     * Get funnel data
     */
    public function get_funnel(WP_REST_Request $request) {
        $funnel_id = $request->get_param('id');
        
        $db = ZeFunnel_Database::get_instance();
        $funnel = $db->get_funnel($funnel_id);
        
        if (!$funnel || $funnel->status !== 'active') {
            return new WP_Error('funnel_not_found', 'Funnel not found', ['status' => 404]);
        }
        
        // Get questions
        global $wpdb;
        $questions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$db->get_table('questions')} 
                WHERE funnel_id = %d 
                ORDER BY position ASC",
                $funnel_id
            )
        );
        
        $formatted_questions = [];
        foreach ($questions as $question) {
            $formatted_questions[] = [
                'id' => $question->id,
                'text' => $question->question_text,
                'type' => $question->question_type,
                'options' => json_decode($question->options, true) ?: [],
                'validation' => json_decode($question->validation_rules, true) ?: [],
                'conditional' => json_decode($question->conditional_logic, true) ?: [],
                'required' => (bool) $question->required,
                'position' => $question->position
            ];
        }
        
        $settings = json_decode($funnel->settings, true) ?: [];
        
        return new WP_REST_Response([
            'id' => $funnel->id,
            'name' => $funnel->name,
            'slug' => $funnel->slug,
            'questions' => $formatted_questions,
            'settings' => $settings
        ], 200);
    }
    
    /**
     * Track analytics event
     */
    public function track_analytics(WP_REST_Request $request) {
        $event_type = $request->get_param('type');
        $event_data = $request->get_param('data');
        $session_id = $request->get_param('sessionId');
        
        // Store start time for completion tracking
        if ($event_type === 'funnel_start' && $session_id) {
            set_transient('ze_funnel_start_' . $session_id, time(), HOUR_IN_SECONDS);
        }
        
        $db = ZeFunnel_Database::get_instance();
        $result = $db->log_analytics([
            'funnel_id' => $event_data['funnelId'] ?? 0,
            'question_id' => $event_data['questionId'] ?? null,
            'event_type' => $event_type,
            'event_data' => wp_json_encode($event_data),
            'session_id' => $session_id
        ]);
        
        return new WP_REST_Response(['success' => (bool) $result], 200);
    }
    
    /**
     * Get admin funnels
     */
    public function get_admin_funnels(WP_REST_Request $request) {
        $db = ZeFunnel_Database::get_instance();
        $funnels = $db->get_funnels([
            'limit' => $request->get_param('per_page') ?: 20,
            'offset' => $request->get_param('offset') ?: 0,
            'status' => $request->get_param('status')
        ]);
        
        return new WP_REST_Response($funnels, 200);
    }
    
    /**
     * Create new funnel
     */
    public function create_funnel(WP_REST_Request $request) {
        $name = sanitize_text_field($request->get_param('name'));
        $description = sanitize_textarea_field($request->get_param('description'));
        $settings = $request->get_param('settings') ?: [];
        
        if (empty($name)) {
            return new WP_Error('missing_name', 'Funnel name is required', ['status' => 400]);
        }
        
        $db = ZeFunnel_Database::get_instance();
        $funnel_id = $db->create_funnel([
            'name' => $name,
            'description' => $description,
            'settings' => wp_json_encode($settings),
            'status' => 'draft'
        ]);
        
        if (!$funnel_id) {
            return new WP_Error('creation_failed', 'Failed to create funnel', ['status' => 500]);
        }
        
        return new WP_REST_Response([
            'id' => $funnel_id,
            'name' => $name,
            'status' => 'draft',
            'message' => 'Funnel created successfully'
        ], 201);
    }
    
    /**
     * Check admin permissions
     */
    public function check_admin_permissions() {
        return current_user_can('manage_options');
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    /**
     * Handle post-submission actions
     */
    private function handle_post_submission_actions($funnel, $submission_data, $answers) {
        $settings = json_decode($funnel->settings, true) ?: [];
        
        // Send emails if configured
        if (!empty($settings['notifications']['email'])) {
            $this->send_notification_email($funnel, $submission_data, $answers);
        }
        
        // Call webhooks if configured
        if (!empty($settings['webhooks'])) {
            $this->call_webhooks($funnel, $submission_data, $answers);
        }
        
        do_action('ze_funnel_submission_complete', $funnel, $submission_data, $answers);
    }
    
    /**
     * Send notification email
     */
    private function send_notification_email($funnel, $submission_data, $answers) {
        // Simple notification implementation
        $to = get_option('admin_email');
        $subject = sprintf('New submission for funnel: %s', $funnel->name);
        $message = sprintf("New submission received for funnel '%s'\n\nAnswers:\n%s", 
            $funnel->name, 
            print_r($answers, true)
        );
        
        wp_mail($to, $subject, $message);
    }
    
    /**
     * Call webhooks
     */
    private function call_webhooks($funnel, $submission_data, $answers) {
        $settings = json_decode($funnel->settings, true) ?: [];
        $webhooks = $settings['webhooks'] ?? [];
        
        foreach ($webhooks as $webhook) {
            if (empty($webhook['url'])) continue;
            
            $payload = [
                'funnel_id' => $funnel->id,
                'funnel_name' => $funnel->name,
                'answers' => $answers,
                'submitted_at' => $submission_data['completed_at'] ?? current_time('mysql')
            ];
            
            wp_remote_post($webhook['url'], [
                'body' => wp_json_encode($payload),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Ze-Funnel-Webhook/1.0'
                ],
                'timeout' => 30
            ]);
        }
    }
    
    /**
     * Submit endpoint arguments
     */
    private function get_submit_args() {
        return [
            'funnelId' => [
                'required' => true,
                'type' => 'integer'
            ],
            'answers' => [
                'required' => true,
                'type' => 'object'
            ],
            'sessionId' => [
                'type' => 'string'
            ],
            'completedAt' => [
                'type' => 'string'
            ]
        ];
    }
    
    /**
     * Analytics endpoint arguments
     */
    private function get_analytics_args() {
        return [
            'type' => [
                'required' => true,
                'type' => 'string'
            ],
            'data' => [
                'type' => 'object'
            ],
            'sessionId' => [
                'type' => 'string'
            ]
        ];
    }
}