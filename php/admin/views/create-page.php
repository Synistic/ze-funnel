<?php
/**
 * Admin create/edit funnel page template
 * 
 * @package ZeFunnel
 */

if (!defined('ABSPATH')) {
    exit;
}

$page_title = $funnel ? __('Edit Funnel', 'ze-funnel') : __('Create New Funnel', 'ze-funnel');
ZeFunnel_Admin_Pages::render_admin_header($page_title);
?>

<div id="ze-funnel-admin-app" data-funnel-id="<?php echo $funnel ? esc_attr($funnel->id) : '0'; ?>">
    <div class="ze-funnel-loading">
        <p><?php _e('Loading funnel builder...', 'ze-funnel'); ?></p>
        <div class="spinner is-active" style="float: none; margin: 16px auto;"></div>
    </div>
    
    <!-- Fallback form for non-JS environments -->
    <noscript>
        <div class="notice notice-error">
            <p><?php _e('The funnel builder requires JavaScript to be enabled. Please enable JavaScript in your browser and refresh the page.', 'ze-funnel'); ?></p>
        </div>
        
        <!-- Basic funnel form -->
        <form method="post" action="">
            <?php wp_nonce_field('ze_funnel_save'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="funnel_name"><?php _e('Funnel Name', 'ze-funnel'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="funnel_name" 
                               name="funnel_name" 
                               value="<?php echo $funnel ? esc_attr($funnel->name) : ''; ?>" 
                               class="regular-text" 
                               required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="funnel_description"><?php _e('Description', 'ze-funnel'); ?></label>
                    </th>
                    <td>
                        <textarea id="funnel_description" 
                                  name="funnel_description" 
                                  rows="3" 
                                  class="large-text"><?php echo $funnel ? esc_textarea($funnel->description) : ''; ?></textarea>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="funnel_status"><?php _e('Status', 'ze-funnel'); ?></label>
                    </th>
                    <td>
                        <select id="funnel_status" name="funnel_status">
                            <option value="draft" <?php selected($funnel ? $funnel->status : 'draft', 'draft'); ?>>
                                <?php _e('Draft', 'ze-funnel'); ?>
                            </option>
                            <option value="active" <?php selected($funnel ? $funnel->status : '', 'active'); ?>>
                                <?php _e('Active', 'ze-funnel'); ?>
                            </option>
                            <option value="inactive" <?php selected($funnel ? $funnel->status : '', 'inactive'); ?>>
                                <?php _e('Inactive', 'ze-funnel'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" 
                       name="save_funnel" 
                       class="button button-primary" 
                       value="<?php esc_attr_e('Save Funnel', 'ze-funnel'); ?>">
                <a href="<?php echo esc_url(admin_url('admin.php?page=ze-funnel')); ?>" 
                   class="button button-secondary">
                    <?php _e('Cancel', 'ze-funnel'); ?>
                </a>
            </p>
        </form>
    </noscript>
</div>

<style>
.ze-funnel-loading {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

#ze-funnel-admin-app {
    margin-top: 20px;
}

/* Hide fallback form when JS is enabled */
html.js noscript {
    display: none;
}
</style>

<script>
// Add JS class to html element
document.documentElement.className += ' js';

// Initialize Vue admin app when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ZeFunnelAdmin !== 'undefined') {
        ZeFunnelAdmin.init();
    } else {
        console.warn('Ze Funnel Admin JavaScript not loaded');
    }
});
</script>