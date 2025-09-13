<?php
/**
 * Admin functionality for Ze Funnel
 * 
 * @package ZeFunnel
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZeFunnel_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'init_admin']);
    }
    
    /**
     * Initialize admin
     */
    public function init_admin() {
        // Add admin notices
        add_action('admin_notices', [$this, 'admin_notices']);
        
        // Handle admin actions
        $this->handle_admin_actions();
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Ze Funnel', 'ze-funnel'),
            __('Ze Funnel', 'ze-funnel'),
            'manage_options',
            'ze-funnel',
            [$this, 'render_main_page'],
            'dashicons-feedback',
            30
        );
        
        // Submenu: All Funnels
        add_submenu_page(
            'ze-funnel',
            __('All Funnels', 'ze-funnel'),
            __('All Funnels', 'ze-funnel'),
            'manage_options',
            'ze-funnel',
            [$this, 'render_main_page']
        );
        
        // Submenu: Add New
        add_submenu_page(
            'ze-funnel',
            __('Add New Funnel', 'ze-funnel'),
            __('Add New', 'ze-funnel'),
            'manage_options',
            'ze-funnel-create',
            [$this, 'render_create_page']
        );
        
        // Submenu: Analytics
        add_submenu_page(
            'ze-funnel',
            __('Analytics', 'ze-funnel'),
            __('Analytics', 'ze-funnel'),
            'manage_options',
            'ze-funnel-analytics',
            [$this, 'render_analytics_page']
        );
        
        // Submenu: Settings
        add_submenu_page(
            'ze-funnel',
            __('Settings', 'ze-funnel'),
            __('Settings', 'ze-funnel'),
            'manage_options',
            'ze-funnel-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Render main admin page (funnel list)
     */
    public function render_main_page() {
        $db = ZeFunnel_Database::get_instance();
        
        // Handle bulk actions
        $this->handle_bulk_actions();
        
        // Get funnels
        $funnels = $db->get_funnels();
        
        include ZE_FUNNEL_PLUGIN_DIR . 'php/admin/views/main-page.php';
    }
    
    /**
     * Render create/edit funnel page
     */
    public function render_create_page() {
        $funnel_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        $funnel = null;
        
        if ($funnel_id) {
            $db = ZeFunnel_Database::get_instance();
            $funnel = $db->get_funnel($funnel_id);
            
            if (!$funnel) {
                wp_die(__('Funnel not found.', 'ze-funnel'));
            }
        }
        
        include ZE_FUNNEL_PLUGIN_DIR . 'php/admin/views/create-page.php';
    }
    
    /**
     * Render analytics page
     */
    public function render_analytics_page() {
        include ZE_FUNNEL_PLUGIN_DIR . 'php/admin/views/analytics-page.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Handle settings save
        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['_wpnonce'], 'ze_funnel_settings')) {
            $this->save_settings();
        }
        
        include ZE_FUNNEL_PLUGIN_DIR . 'php/admin/views/settings-page.php';
    }
    
    /**
     * Handle admin actions
     */
    private function handle_admin_actions() {
        if (!isset($_GET['action']) || !current_user_can('manage_options')) {
            return;
        }
        
        $action = sanitize_key($_GET['action']);
        $funnel_id = isset($_GET['funnel']) ? intval($_GET['funnel']) : 0;
        
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'ze_funnel_action_' . $action)) {
            wp_die(__('Security check failed.', 'ze-funnel'));
        }
        
        switch ($action) {
            case 'delete':
                $this->delete_funnel($funnel_id);
                break;
            case 'duplicate':
                $this->duplicate_funnel($funnel_id);
                break;
            case 'activate':
                $this->change_funnel_status($funnel_id, 'active');
                break;
            case 'deactivate':
                $this->change_funnel_status($funnel_id, 'inactive');
                break;
        }
    }
    
    /**
     * Handle bulk actions
     */
    private function handle_bulk_actions() {
        if (!isset($_POST['action']) || !isset($_POST['funnels']) || !current_user_can('manage_options')) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['_wpnonce'], 'bulk-funnels')) {
            wp_die(__('Security check failed.', 'ze-funnel'));
        }
        
        $action = sanitize_key($_POST['action']);
        $funnel_ids = array_map('intval', $_POST['funnels']);
        
        switch ($action) {
            case 'delete':
                foreach ($funnel_ids as $funnel_id) {
                    $this->delete_funnel($funnel_id, false);
                }
                add_settings_error('ze_funnel', 'bulk_delete', __('Funnels deleted successfully.', 'ze-funnel'), 'success');
                break;
            case 'activate':
                foreach ($funnel_ids as $funnel_id) {
                    $this->change_funnel_status($funnel_id, 'active', false);
                }
                add_settings_error('ze_funnel', 'bulk_activate', __('Funnels activated successfully.', 'ze-funnel'), 'success');
                break;
            case 'deactivate':
                foreach ($funnel_ids as $funnel_id) {
                    $this->change_funnel_status($funnel_id, 'inactive', false);
                }
                add_settings_error('ze_funnel', 'bulk_deactivate', __('Funnels deactivated successfully.', 'ze-funnel'), 'success');
                break;
        }
    }
    
    /**
     * Delete funnel
     */
    private function delete_funnel($funnel_id, $redirect = true) {
        if (!$funnel_id) return;
        
        global $wpdb;
        $db = ZeFunnel_Database::get_instance();
        
        // Delete funnel (cascade will handle questions, submissions, analytics)
        $result = $wpdb->delete(
            $db->get_table('funnels'),
            ['id' => $funnel_id],
            ['%d']
        );
        
        if ($result === false) {
            add_settings_error('ze_funnel', 'delete_failed', __('Failed to delete funnel.', 'ze-funnel'), 'error');
        } else {
            add_settings_error('ze_funnel', 'delete_success', __('Funnel deleted successfully.', 'ze-funnel'), 'success');
        }
        
        if ($redirect) {
            wp_redirect(admin_url('admin.php?page=ze-funnel'));
            exit;
        }
    }
    
    /**
     * Duplicate funnel
     */
    private function duplicate_funnel($funnel_id) {
        if (!$funnel_id) return;
        
        $db = ZeFunnel_Database::get_instance();
        $funnel = $db->get_funnel($funnel_id);
        
        if (!$funnel) {
            add_settings_error('ze_funnel', 'duplicate_failed', __('Funnel not found.', 'ze-funnel'), 'error');
            return;
        }
        
        // Create duplicate
        $new_funnel_id = $db->create_funnel([
            'name' => $funnel->name . ' (Copy)',
            'slug' => $funnel->slug . '-copy-' . time(),
            'description' => $funnel->description,
            'settings' => $funnel->settings,
            'status' => 'draft'
        ]);
        
        if ($new_funnel_id) {
            // Copy questions
            global $wpdb;
            $questions = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$db->get_table('questions')} WHERE funnel_id = %d ORDER BY position ASC",
                    $funnel_id
                )
            );
            
            foreach ($questions as $question) {
                $wpdb->insert(
                    $db->get_table('questions'),
                    [
                        'funnel_id' => $new_funnel_id,
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'options' => $question->options,
                        'validation_rules' => $question->validation_rules,
                        'conditional_logic' => $question->conditional_logic,
                        'position' => $question->position,
                        'required' => $question->required
                    ]
                );
            }
            
            add_settings_error('ze_funnel', 'duplicate_success', __('Funnel duplicated successfully.', 'ze-funnel'), 'success');
            wp_redirect(admin_url('admin.php?page=ze-funnel-create&edit=' . $new_funnel_id));
            exit;
        } else {
            add_settings_error('ze_funnel', 'duplicate_failed', __('Failed to duplicate funnel.', 'ze-funnel'), 'error');
        }
    }
    
    /**
     * Change funnel status
     */
    private function change_funnel_status($funnel_id, $status, $redirect = true) {
        if (!$funnel_id) return;
        
        global $wpdb;
        $db = ZeFunnel_Database::get_instance();
        
        $result = $wpdb->update(
            $db->get_table('funnels'),
            ['status' => $status],
            ['id' => $funnel_id],
            ['%s'],
            ['%d']
        );
        
        if ($result !== false) {
            $message = $status === 'active' ? __('Funnel activated.', 'ze-funnel') : __('Funnel deactivated.', 'ze-funnel');
            add_settings_error('ze_funnel', 'status_changed', $message, 'success');
        } else {
            add_settings_error('ze_funnel', 'status_failed', __('Failed to change funnel status.', 'ze-funnel'), 'error');
        }
        
        if ($redirect) {
            wp_redirect(admin_url('admin.php?page=ze-funnel'));
            exit;
        }
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        $settings = [
            'enable_analytics' => isset($_POST['enable_analytics']),
            'enable_debug' => isset($_POST['enable_debug']),
            'cache_enabled' => isset($_POST['cache_enabled']),
            'default_theme' => sanitize_text_field($_POST['default_theme'] ?? 'default')
        ];
        
        update_option('ze_funnel_settings', $settings);
        add_settings_error('ze_funnel', 'settings_saved', __('Settings saved successfully.', 'ze-funnel'), 'success');
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        settings_errors('ze_funnel');
    }
    
    /**
     * Get action URL
     */
    public function get_action_url($action, $funnel_id = 0) {
        return wp_nonce_url(
            admin_url('admin.php?page=ze-funnel&action=' . $action . '&funnel=' . $funnel_id),
            'ze_funnel_action_' . $action
        );
    }
}