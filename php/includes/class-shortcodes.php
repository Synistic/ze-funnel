<?php
/**
 * Shortcode handling for Ze Funnel
 * 
 * @package ZeFunnel
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZeFunnel_Shortcodes {
    
    private static $instance = null;
    private $rendered_funnels = [];
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'init_shortcodes']);
    }
    
    /**
     * Initialize shortcodes
     */
    public function init_shortcodes() {
        add_shortcode('ze_funnel', [$this, 'render_funnel_shortcode']);
        add_shortcode('ze-funnel', [$this, 'render_funnel_shortcode']); // Alternative syntax
    }
    
    /**
     * Render funnel shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_funnel_shortcode($atts) {
        $atts = shortcode_atts([
            'id' => '',
            'slug' => '',
            'anchor_id' => '',
            'class' => '',
            'style' => ''
        ], $atts, 'ze_funnel');
        
        // Get funnel by ID or slug
        $funnel = $this->get_funnel_data($atts['id'], $atts['slug']);
        
        if (!$funnel) {
            return $this->render_error(__('Funnel not found.', 'ze-funnel'));
        }
        
        // Check if funnel is active
        if ($funnel->status !== 'active') {
            return $this->render_error(__('Funnel is not active.', 'ze-funnel'));
        }
        
        // Generate unique anchor ID
        $anchor_id = $this->generate_anchor_id($atts['anchor_id'], $funnel->id);
        
        // Enqueue assets
        $this->enqueue_funnel_assets($funnel->id);
        
        // Prepare funnel data for frontend
        $funnel_data = $this->prepare_funnel_data($funnel);
        
        // Generate output
        return $this->render_funnel_html($funnel_data, $anchor_id, $atts);
    }
    
    /**
     * Get funnel data by ID or slug
     */
    private function get_funnel_data($id, $slug) {
        $db = ZeFunnel_Database::get_instance();
        
        if (!empty($id) && is_numeric($id)) {
            return $db->get_funnel($id);
        }
        
        if (!empty($slug)) {
            global $wpdb;
            return $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$db->get_table('funnels')} WHERE slug = %s AND status = 'active'",
                    $slug
                )
            );
        }
        
        return null;
    }
    
    /**
     * Generate unique anchor ID
     */
    private function generate_anchor_id($custom_id, $funnel_id) {
        if (!empty($custom_id)) {
            return sanitize_html_class($custom_id);
        }
        
        // Generate unique ID
        $base_id = 'ze-funnel-' . $funnel_id;
        $counter = 1;
        $anchor_id = $base_id;
        
        // Ensure uniqueness on page
        while (in_array($anchor_id, $this->rendered_funnels)) {
            $anchor_id = $base_id . '-' . $counter;
            $counter++;
        }
        
        $this->rendered_funnels[] = $anchor_id;
        
        return $anchor_id;
    }
    
    /**
     * Enqueue funnel assets
     */
    private function enqueue_funnel_assets($funnel_id) {
        // Enqueue frontend assets
        wp_enqueue_script(
            'ze-funnel-frontend',
            ZE_FUNNEL_PLUGIN_URL . 'dist/frontend/ze-funnel.js',
            [],
            ZE_FUNNEL_VERSION,
            true
        );
        
        wp_enqueue_style(
            'ze-funnel-frontend',
            ZE_FUNNEL_PLUGIN_URL . 'dist/frontend/ze-funnel.css',
            [],
            ZE_FUNNEL_VERSION
        );
        
        // Localize script with WordPress data
        wp_localize_script('ze-funnel-frontend', 'zeFunnelWP', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('ze-funnel/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'strings' => [
                'loading' => __('Loading...', 'ze-funnel'),
                'error' => __('An error occurred. Please try again.', 'ze-funnel'),
                'required' => __('This field is required.', 'ze-funnel'),
                'submit' => __('Submit', 'ze-funnel'),
                'next' => __('Next', 'ze-funnel'),
                'back' => __('Back', 'ze-funnel')
            ]
        ]);
    }
    
    /**
     * Prepare funnel data for frontend
     */
    private function prepare_funnel_data($funnel) {
        $settings = json_decode($funnel->settings, true) ?: [];
        
        // Get questions for this funnel
        $questions = $this->get_funnel_questions($funnel->id);
        
        return [
            'id' => $funnel->id,
            'name' => $funnel->name,
            'slug' => $funnel->slug,
            'questions' => $questions,
            'settings' => [
                'progressBar' => $settings['show_progress_bar'] ?? true,
                'allowBack' => $settings['allow_back_navigation'] ?? true,
                'theme' => $settings['theme'] ?? 'default',
                'animations' => $settings['enable_animations'] ?? true,
                'autoAdvance' => $settings['auto_advance'] ?? false
            ],
            'form' => [
                'submitUrl' => rest_url('ze-funnel/v1/submit'),
                'nonce' => wp_create_nonce('wp_rest')
            ],
            'tracking' => [
                'sessionId' => $this->generate_session_id(),
                'analyticsEnabled' => $settings['enable_analytics'] ?? true
            ]
        ];
    }
    
    /**
     * Get questions for funnel
     */
    private function get_funnel_questions($funnel_id) {
        global $wpdb;
        $db = ZeFunnel_Database::get_instance();
        
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
        
        return $formatted_questions;
    }
    
    /**
     * Generate session ID for tracking
     */
    private function generate_session_id() {
        if (!session_id()) {
            session_start();
        }
        
        return session_id() ?: wp_generate_uuid4();
    }
    
    /**
     * Render funnel HTML
     */
    private function render_funnel_html($funnel_data, $anchor_id, $atts) {
        $classes = ['ze-funnel-container'];
        
        if (!empty($atts['class'])) {
            $classes[] = sanitize_html_class($atts['class']);
        }
        
        $style_attr = '';
        if (!empty($atts['style'])) {
            $style_attr = ' style="' . esc_attr($atts['style']) . '"';
        }
        
        ob_start();
        ?>
        <div id="<?php echo esc_attr($anchor_id); ?>" 
             class="<?php echo esc_attr(implode(' ', $classes)); ?>"
             <?php echo $style_attr; ?>
             data-funnel-id="<?php echo esc_attr($funnel_data['id']); ?>">
            
            <!-- Funnel data as JSON -->
            <script type="application/json" id="<?php echo esc_attr($anchor_id); ?>-data">
                <?php echo wp_json_encode($funnel_data, JSON_UNESCAPED_SLASHES); ?>
            </script>
            
            <!-- Funnel will be mounted here by Svelte -->
            <div class="ze-funnel-app" data-options-id="<?php echo esc_attr($anchor_id); ?>-data">
                <div class="ze-funnel-loading">
                    <?php _e('Loading funnel...', 'ze-funnel'); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render error message
     */
    private function render_error($message) {
        if (!current_user_can('edit_posts')) {
            return ''; // Don't show errors to non-editors
        }
        
        return '<div class="ze-funnel-error">' . esc_html($message) . '</div>';
    }
}