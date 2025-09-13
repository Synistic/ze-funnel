<?php
/**
 * Asset management for Ze Funnel
 * 
 * @package ZeFunnel
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZeFunnel_Assets {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only enqueue on pages with funnels
        if (!$this->should_enqueue_frontend()) {
            return;
        }
        
        $this->enqueue_frontend_scripts();
        $this->enqueue_frontend_styles();
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only on Ze Funnel admin pages
        if (!$this->is_ze_funnel_admin_page($hook)) {
            return;
        }
        
        $this->enqueue_admin_scripts();
        $this->enqueue_admin_styles();
    }
    
    /**
     * Check if we should enqueue frontend assets
     */
    private function should_enqueue_frontend() {
        global $post;
        
        // Check if current post contains ze_funnel shortcode
        if ($post && has_shortcode($post->post_content, 'ze_funnel')) {
            return true;
        }
        
        // Check widgets/blocks (future implementation)
        return apply_filters('ze_funnel_should_enqueue_frontend', false);
    }
    
    /**
     * Enqueue frontend JavaScript
     */
    private function enqueue_frontend_scripts() {
        $js_file = ZE_FUNNEL_PLUGIN_URL . 'dist/frontend/ze-funnel.js';
        $js_version = $this->get_asset_version('frontend/ze-funnel.js');
        
        wp_enqueue_script(
            'ze-funnel-frontend',
            $js_file,
            [],
            $js_version,
            true
        );
        
        // Localize script with WordPress data
        wp_localize_script('ze-funnel-frontend', 'zeFunnelWP', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('ze-funnel/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginUrl' => ZE_FUNNEL_PLUGIN_URL,
            'strings' => $this->get_frontend_strings()
        ]);
    }
    
    /**
     * Enqueue frontend CSS
     */
    private function enqueue_frontend_styles() {
        $css_file = ZE_FUNNEL_PLUGIN_URL . 'dist/frontend/ze-funnel.css';
        $css_version = $this->get_asset_version('frontend/ze-funnel.css');
        
        wp_enqueue_style(
            'ze-funnel-frontend',
            $css_file,
            [],
            $css_version
        );
    }
    
    /**
     * Enqueue admin JavaScript
     */
    private function enqueue_admin_scripts() {
        $js_file = ZE_FUNNEL_PLUGIN_URL . 'dist/admin/ze-funnel-admin.js';
        $js_version = $this->get_asset_version('admin/ze-funnel-admin.js');
        
        wp_enqueue_script(
            'ze-funnel-admin',
            $js_file,
            ['jquery'],
            $js_version,
            true
        );
        
        // Localize script with admin data
        wp_localize_script('ze-funnel-admin', 'zeFunnelAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('ze-funnel/v1/admin/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginUrl' => ZE_FUNNEL_PLUGIN_URL,
            'currentScreen' => get_current_screen()->id ?? '',
            'strings' => $this->get_admin_strings()
        ]);
    }
    
    /**
     * Enqueue admin CSS
     */
    private function enqueue_admin_styles() {
        $css_file = ZE_FUNNEL_PLUGIN_URL . 'dist/admin/ze-funnel-admin.css';
        $css_version = $this->get_asset_version('admin/ze-funnel-admin.css');
        
        wp_enqueue_style(
            'ze-funnel-admin',
            $css_file,
            [],
            $css_version
        );
    }
    
    /**
     * Check if current admin page is Ze Funnel page
     */
    private function is_ze_funnel_admin_page($hook) {
        $ze_funnel_pages = [
            'toplevel_page_ze-funnel',
            'ze-funnel_page_ze-funnel-create',
            'ze-funnel_page_ze-funnel-analytics'
        ];
        
        return in_array($hook, $ze_funnel_pages) || strpos($hook, 'ze-funnel') !== false;
    }
    
    /**
     * Get asset version for cache busting
     */
    private function get_asset_version($asset_path) {
        $file_path = ZE_FUNNEL_PLUGIN_DIR . 'dist/' . $asset_path;
        
        if (file_exists($file_path)) {
            return filemtime($file_path);
        }
        
        return ZE_FUNNEL_VERSION;
    }
    
    /**
     * Get frontend localized strings
     */
    private function get_frontend_strings() {
        return [
            'loading' => __('Loading...', 'ze-funnel'),
            'error' => __('An error occurred. Please try again.', 'ze-funnel'),
            'required' => __('This field is required.', 'ze-funnel'),
            'submit' => __('Submit', 'ze-funnel'),
            'next' => __('Next', 'ze-funnel'),
            'back' => __('Back', 'ze-funnel'),
            'sending' => __('Sending...', 'ze-funnel'),
            'thank_you' => __('Thank you!', 'ze-funnel'),
            'submission_error' => __('There was an error submitting your responses. Please try again.', 'ze-funnel'),
            'validation_error' => __('Please check your answers and try again.', 'ze-funnel'),
            'network_error' => __('Network error. Please check your connection and try again.', 'ze-funnel'),
            'email_invalid' => __('Please enter a valid email address.', 'ze-funnel'),
            'required_field' => __('This field is required.', 'ze-funnel'),
            'min_length' => __('Please enter at least %d characters.', 'ze-funnel'),
            'max_length' => __('Please enter no more than %d characters.', 'ze-funnel'),
            'pattern_error' => __('Please enter a valid value.', 'ze-funnel')
        ];
    }
    
    /**
     * Get admin localized strings
     */
    private function get_admin_strings() {
        return [
            'save' => __('Save', 'ze-funnel'),
            'cancel' => __('Cancel', 'ze-funnel'),
            'delete' => __('Delete', 'ze-funnel'),
            'edit' => __('Edit', 'ze-funnel'),
            'create' => __('Create', 'ze-funnel'),
            'loading' => __('Loading...', 'ze-funnel'),
            'saving' => __('Saving...', 'ze-funnel'),
            'saved' => __('Saved!', 'ze-funnel'),
            'error' => __('Error', 'ze-funnel'),
            'success' => __('Success', 'ze-funnel'),
            'confirm_delete' => __('Are you sure you want to delete this funnel?', 'ze-funnel'),
            'unsaved_changes' => __('You have unsaved changes. Are you sure you want to leave?', 'ze-funnel'),
            'add_question' => __('Add Question', 'ze-funnel'),
            'remove_question' => __('Remove Question', 'ze-funnel'),
            'question_title' => __('Question Title', 'ze-funnel'),
            'question_type' => __('Question Type', 'ze-funnel'),
            'required_field' => __('Required Field', 'ze-funnel'),
            'optional_field' => __('Optional Field', 'ze-funnel')
        ];
    }
    
    /**
     * Register inline styles for customization
     */
    public function add_inline_styles($funnel_settings = []) {
        if (empty($funnel_settings['custom_css'])) {
            return;
        }
        
        $custom_css = wp_strip_all_tags($funnel_settings['custom_css']);
        
        if (!empty($custom_css)) {
            wp_add_inline_style('ze-funnel-frontend', $custom_css);
        }
    }
    
    /**
     * Preload critical assets
     */
    public function preload_assets() {
        if ($this->should_enqueue_frontend()) {
            echo '<link rel="preload" href="' . esc_url(ZE_FUNNEL_PLUGIN_URL . 'dist/frontend/ze-funnel.css') . '" as="style">';
            echo '<link rel="preload" href="' . esc_url(ZE_FUNNEL_PLUGIN_URL . 'dist/frontend/ze-funnel.js') . '" as="script">';
        }
    }
}