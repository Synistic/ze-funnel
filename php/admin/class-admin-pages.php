<?php
/**
 * Admin page handler for Ze Funnel
 * 
 * @package ZeFunnel
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZeFunnel_Admin_Pages {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Constructor intentionally empty
        // All functionality handled through ZeFunnel_Admin
    }
    
    /**
     * Render funnel status badge
     */
    public static function render_status_badge($status) {
        $statuses = [
            'active' => ['class' => 'success', 'label' => __('Active', 'ze-funnel')],
            'inactive' => ['class' => 'secondary', 'label' => __('Inactive', 'ze-funnel')],
            'draft' => ['class' => 'warning', 'label' => __('Draft', 'ze-funnel')]
        ];
        
        $status_info = $statuses[$status] ?? $statuses['draft'];
        
        echo '<span class="ze-status-badge ze-status-' . esc_attr($status_info['class']) . '">';
        echo esc_html($status_info['label']);
        echo '</span>';
    }
    
    /**
     * Render funnel actions
     */
    public static function render_funnel_actions($funnel) {
        $admin = ZeFunnel_Admin::get_instance();
        ?>
        <div class="row-actions">
            <span class="edit">
                <a href="<?php echo esc_url(admin_url('admin.php?page=ze-funnel-create&edit=' . $funnel->id)); ?>">
                    <?php _e('Edit', 'ze-funnel'); ?>
                </a>
            </span>
            
            <?php if ($funnel->status === 'active'): ?>
            <span class="deactivate"> | 
                <a href="<?php echo esc_url($admin->get_action_url('deactivate', $funnel->id)); ?>">
                    <?php _e('Deactivate', 'ze-funnel'); ?>
                </a>
            </span>
            <?php else: ?>
            <span class="activate"> | 
                <a href="<?php echo esc_url($admin->get_action_url('activate', $funnel->id)); ?>">
                    <?php _e('Activate', 'ze-funnel'); ?>
                </a>
            </span>
            <?php endif; ?>
            
            <span class="duplicate"> | 
                <a href="<?php echo esc_url($admin->get_action_url('duplicate', $funnel->id)); ?>">
                    <?php _e('Duplicate', 'ze-funnel'); ?>
                </a>
            </span>
            
            <span class="shortcode"> | 
                <a href="#" onclick="navigator.clipboard.writeText('[ze_funnel id=&quot;<?php echo esc_attr($funnel->id); ?>&quot;]'); alert('<?php esc_attr_e('Shortcode copied to clipboard!', 'ze-funnel'); ?>');">
                    <?php _e('Copy Shortcode', 'ze-funnel'); ?>
                </a>
            </span>
            
            <span class="delete"> | 
                <a href="<?php echo esc_url($admin->get_action_url('delete', $funnel->id)); ?>" 
                   class="ze-delete-link" 
                   onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this funnel? This action cannot be undone.', 'ze-funnel'); ?>');">
                    <?php _e('Delete', 'ze-funnel'); ?>
                </a>
            </span>
        </div>
        <?php
    }
    
    /**
     * Render submissions count
     */
    public static function render_submissions_count($funnel_id) {
        global $wpdb;
        $db = ZeFunnel_Database::get_instance();
        
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$db->get_table('submissions')} WHERE funnel_id = %d AND status = 'completed'",
                $funnel_id
            )
        );
        
        echo '<span class="submissions-count">' . intval($count) . '</span>';
    }
    
    /**
     * Render conversion rate
     */
    public static function render_conversion_rate($funnel_id) {
        global $wpdb;
        $db = ZeFunnel_Database::get_instance();
        
        // Get views and completions
        $stats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    SUM(CASE WHEN event_type = 'funnel_start' THEN 1 ELSE 0 END) as views,
                    SUM(CASE WHEN event_type = 'complete' THEN 1 ELSE 0 END) as completions
                FROM {$db->get_table('analytics')} 
                WHERE funnel_id = %d",
                $funnel_id
            )
        );
        
        if ($stats && $stats->views > 0) {
            $rate = round(($stats->completions / $stats->views) * 100, 1);
            echo '<span class="conversion-rate">' . $rate . '%</span>';
        } else {
            echo '<span class="conversion-rate">-</span>';
        }
    }
    
    /**
     * Get question types for dropdown
     */
    public static function get_question_types() {
        return [
            'text_input' => __('Text Input', 'ze-funnel'),
            'text_selection' => __('Multiple Choice', 'ze-funnel'),
            'image_selection' => __('Image Selection', 'ze-funnel'),
            'icon_selection' => __('Icon Selection', 'ze-funnel'),
            'multi_input' => __('Multiple Fields', 'ze-funnel')
        ];
    }
    
    /**
     * Render question type badge
     */
    public static function render_question_type_badge($type) {
        $types = self::get_question_types();
        $label = $types[$type] ?? $type;
        
        echo '<span class="question-type-badge question-type-' . esc_attr($type) . '">';
        echo esc_html($label);
        echo '</span>';
    }
    
    /**
     * Format date for display
     */
    public static function format_date($date_string) {
        $date = new DateTime($date_string);
        $now = new DateTime();
        
        $diff = $now->diff($date);
        
        if ($diff->days === 0) {
            return __('Today', 'ze-funnel');
        } elseif ($diff->days === 1) {
            return __('Yesterday', 'ze-funnel');
        } elseif ($diff->days < 7) {
            return sprintf(_n('%d day ago', '%d days ago', $diff->days, 'ze-funnel'), $diff->days);
        } else {
            return date_i18n(get_option('date_format'), strtotime($date_string));
        }
    }
    
    /**
     * Render admin header
     */
    public static function render_admin_header($title, $add_new_url = null) {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <?php echo esc_html($title); ?>
            </h1>
            
            <?php if ($add_new_url): ?>
                <a href="<?php echo esc_url($add_new_url); ?>" class="page-title-action">
                    <?php _e('Add New', 'ze-funnel'); ?>
                </a>
            <?php endif; ?>
            
            <hr class="wp-header-end">
        </div>
        <?php
    }
    
    /**
     * Render bulk actions dropdown
     */
    public static function render_bulk_actions() {
        ?>
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">
                <?php _e('Select bulk action', 'ze-funnel'); ?>
            </label>
            <select name="action" id="bulk-action-selector-top">
                <option value="-1"><?php _e('Bulk actions', 'ze-funnel'); ?></option>
                <option value="activate"><?php _e('Activate', 'ze-funnel'); ?></option>
                <option value="deactivate"><?php _e('Deactivate', 'ze-funnel'); ?></option>
                <option value="delete"><?php _e('Delete', 'ze-funnel'); ?></option>
            </select>
            <input type="submit" class="button action" value="<?php esc_attr_e('Apply', 'ze-funnel'); ?>">
        </div>
        <?php
    }
}