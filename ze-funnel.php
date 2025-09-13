<?php
/**
 * Plugin Name: Ze Funnel
 * Plugin URI: https://github.com/your-org/ze-funnel
 * Description: Modular conversational funnel plugin for WordPress. Create engaging qualification funnels with flexible question types and advanced styling options.
 * Version: 1.0.0
 * Author: Ze-Funnel Team
 * Author URI: https://your-domain.com
 * Text Domain: ze-funnel
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 * 
 * @package ZeFunnel
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('ZE_FUNNEL_VERSION', '1.0.0');
define('ZE_FUNNEL_PLUGIN_FILE', __FILE__);
define('ZE_FUNNEL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ZE_FUNNEL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ZE_FUNNEL_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Minimum requirements
define('ZE_FUNNEL_MIN_WP_VERSION', '5.0');
define('ZE_FUNNEL_MIN_PHP_VERSION', '7.4');

/**
 * Check plugin requirements
 */
function ze_funnel_check_requirements() {
    $errors = [];
    
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), ZE_FUNNEL_MIN_WP_VERSION, '<')) {
        $errors[] = sprintf(
            __('Ze Funnel requires WordPress %s or higher. You are running %s.', 'ze-funnel'),
            ZE_FUNNEL_MIN_WP_VERSION,
            get_bloginfo('version')
        );
    }
    
    // Check PHP version
    if (version_compare(PHP_VERSION, ZE_FUNNEL_MIN_PHP_VERSION, '<')) {
        $errors[] = sprintf(
            __('Ze Funnel requires PHP %s or higher. You are running %s.', 'ze-funnel'),
            ZE_FUNNEL_MIN_PHP_VERSION,
            PHP_VERSION
        );
    }
    
    return $errors;
}

/**
 * Display admin notice for requirement errors
 */
function ze_funnel_requirements_notice() {
    $errors = ze_funnel_check_requirements();
    
    if (!empty($errors)) {
        ?>
        <div class="notice notice-error">
            <p><strong><?php _e('Ze Funnel Plugin Error:', 'ze-funnel'); ?></strong></p>
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        
        // Deactivate plugin
        deactivate_plugins(ZE_FUNNEL_PLUGIN_BASENAME);
    }
}

// Check requirements on activation and admin_notices
register_activation_hook(__FILE__, 'ze_funnel_check_requirements');
add_action('admin_notices', 'ze_funnel_requirements_notice');

// Don't initialize if requirements not met
$requirement_errors = ze_funnel_check_requirements();
if (!empty($requirement_errors)) {
    return;
}

/**
 * Main plugin class
 */
class ZeFunnel {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'init']);
        add_action('init', [$this, 'load_textdomain']);
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core includes
        require_once ZE_FUNNEL_PLUGIN_DIR . 'php/includes/class-database.php';
        require_once ZE_FUNNEL_PLUGIN_DIR . 'php/includes/class-shortcodes.php';
        require_once ZE_FUNNEL_PLUGIN_DIR . 'php/includes/class-assets.php';
        
        // API includes
        require_once ZE_FUNNEL_PLUGIN_DIR . 'php/api/class-rest-api.php';
        
        // Admin includes
        if (is_admin()) {
            require_once ZE_FUNNEL_PLUGIN_DIR . 'php/admin/class-admin.php';
            require_once ZE_FUNNEL_PLUGIN_DIR . 'php/admin/class-admin-pages.php';
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize database
        ZeFunnel_Database::get_instance();
        
        // Initialize shortcodes
        ZeFunnel_Shortcodes::get_instance();
        
        // Initialize assets
        ZeFunnel_Assets::get_instance();
        
        // Initialize REST API
        ZeFunnel_Rest_API::get_instance();
        
        // Initialize admin (if in admin)
        if (is_admin()) {
            ZeFunnel_Admin::get_instance();
            ZeFunnel_Admin_Pages::get_instance();
        }
        
        do_action('ze_funnel_loaded');
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'ze-funnel',
            false,
            dirname(ZE_FUNNEL_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        if (class_exists('ZeFunnel_Database')) {
            ZeFunnel_Database::create_tables();
        }
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        do_action('ze_funnel_activated');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        do_action('ze_funnel_deactivated');
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = [
            'ze_funnel_version' => ZE_FUNNEL_VERSION,
            'ze_funnel_db_version' => '1.0.0',
            'ze_funnel_settings' => [
                'enable_analytics' => true,
                'enable_debug' => false,
                'cache_enabled' => true,
                'default_theme' => 'default'
            ]
        ];
        
        foreach ($defaults as $option => $value) {
            if (!get_option($option)) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * Get plugin info
     */
    public static function get_plugin_info() {
        return [
            'version' => ZE_FUNNEL_VERSION,
            'plugin_dir' => ZE_FUNNEL_PLUGIN_DIR,
            'plugin_url' => ZE_FUNNEL_PLUGIN_URL,
            'plugin_file' => ZE_FUNNEL_PLUGIN_FILE,
            'plugin_basename' => ZE_FUNNEL_PLUGIN_BASENAME
        ];
    }
}

/**
 * Initialize plugin
 */
function ze_funnel() {
    return ZeFunnel::get_instance();
}

// Start the plugin
ze_funnel();