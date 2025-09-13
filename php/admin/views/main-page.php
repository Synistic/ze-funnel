<?php
/**
 * Admin main page template
 * 
 * @package ZeFunnel
 */

if (!defined('ABSPATH')) {
    exit;
}

ZeFunnel_Admin_Pages::render_admin_header(
    __('Ze Funnel', 'ze-funnel'),
    admin_url('admin.php?page=ze-funnel-create')
);
?>

<style>
.ze-status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}
.ze-status-success { background: #d1fae5; color: #065f46; }
.ze-status-warning { background: #fef3c7; color: #92400e; }
.ze-status-secondary { background: #f3f4f6; color: #374151; }

.submissions-count { font-weight: 600; color: #3b82f6; }
.conversion-rate { font-weight: 600; color: #10b981; }
.ze-delete-link { color: #dc2626; }
</style>

<form method="post" action="">
    <?php wp_nonce_field('bulk-funnels'); ?>
    
    <div class="tablenav top">
        <?php ZeFunnel_Admin_Pages::render_bulk_actions(); ?>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <td class="manage-column column-cb check-column">
                    <input type="checkbox" id="cb-select-all-1">
                </td>
                <th class="manage-column column-primary">
                    <?php _e('Name', 'ze-funnel'); ?>
                </th>
                <th class="manage-column">
                    <?php _e('Status', 'ze-funnel'); ?>
                </th>
                <th class="manage-column">
                    <?php _e('Submissions', 'ze-funnel'); ?>
                </th>
                <th class="manage-column">
                    <?php _e('Conversion Rate', 'ze-funnel'); ?>
                </th>
                <th class="manage-column">
                    <?php _e('Created', 'ze-funnel'); ?>
                </th>
                <th class="manage-column">
                    <?php _e('Shortcode', 'ze-funnel'); ?>
                </th>
            </tr>
        </thead>
        
        <tbody>
            <?php if (empty($funnels)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px;">
                        <p><?php _e('No funnels found.', 'ze-funnel'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=ze-funnel-create')); ?>" class="button button-primary">
                            <?php _e('Create Your First Funnel', 'ze-funnel'); ?>
                        </a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($funnels as $funnel): ?>
                    <tr>
                        <th scope="row" class="check-column">
                            <input type="checkbox" name="funnels[]" value="<?php echo esc_attr($funnel->id); ?>">
                        </th>
                        
                        <td class="column-primary">
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=ze-funnel-create&edit=' . $funnel->id)); ?>">
                                    <?php echo esc_html($funnel->name); ?>
                                </a>
                            </strong>
                            
                            <?php if ($funnel->description): ?>
                                <p style="margin: 4px 0 0; color: #666; font-size: 13px;">
                                    <?php echo esc_html(wp_trim_words($funnel->description, 15)); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php ZeFunnel_Admin_Pages::render_funnel_actions($funnel); ?>
                        </td>
                        
                        <td>
                            <?php ZeFunnel_Admin_Pages::render_status_badge($funnel->status); ?>
                        </td>
                        
                        <td>
                            <?php ZeFunnel_Admin_Pages::render_submissions_count($funnel->id); ?>
                        </td>
                        
                        <td>
                            <?php ZeFunnel_Admin_Pages::render_conversion_rate($funnel->id); ?>
                        </td>
                        
                        <td>
                            <?php echo esc_html(ZeFunnel_Admin_Pages::format_date($funnel->created_at)); ?>
                        </td>
                        
                        <td>
                            <code style="background: #f1f1f1; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                [ze_funnel id="<?php echo esc_attr($funnel->id); ?>"]
                            </code>
                            <button type="button" 
                                    class="button button-small" 
                                    style="margin-left: 8px;"
                                    onclick="navigator.clipboard.writeText('[ze_funnel id=&quot;<?php echo esc_attr($funnel->id); ?>&quot;]'); this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy', 2000);">
                                <?php _e('Copy', 'ze-funnel'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="tablenav bottom">
        <?php ZeFunnel_Admin_Pages::render_bulk_actions(); ?>
    </div>
</form>

<script>
jQuery(document).ready(function($) {
    // Handle select all checkbox
    $('#cb-select-all-1').on('change', function() {
        $('input[name="funnels[]"]').prop('checked', this.checked);
    });
});
</script>