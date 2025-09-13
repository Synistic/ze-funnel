<?php
/**
 * Database operations for Ze Funnel
 * 
 * @package ZeFunnel
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZeFunnel_Database {
    
    private static $instance = null;
    private $wpdb;
    
    /**
     * Table names
     */
    public $tables = [
        'funnels' => 'ze_funnels',
        'questions' => 'ze_questions', 
        'submissions' => 'ze_submissions',
        'analytics' => 'ze_analytics'
    ];
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        // Add table prefixes
        foreach ($this->tables as $key => $table) {
            $this->tables[$key] = $wpdb->prefix . $table;
        }
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = [];
        
        // Funnels table
        $sql[] = "CREATE TABLE {$wpdb->prefix}ze_funnels (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            settings longtext,
            status enum('active','inactive','draft') DEFAULT 'draft',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY status (status)
        ) $charset_collate;";
        
        // Questions table
        $sql[] = "CREATE TABLE {$wpdb->prefix}ze_questions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            funnel_id bigint(20) unsigned NOT NULL,
            question_text text NOT NULL,
            question_type enum('text_input','image_selection','icon_selection','text_selection','multi_input') NOT NULL,
            options longtext,
            validation_rules longtext,
            conditional_logic longtext,
            position int(11) DEFAULT 0,
            required tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY funnel_id (funnel_id),
            KEY position (position),
            FOREIGN KEY (funnel_id) REFERENCES {$wpdb->prefix}ze_funnels(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Submissions table
        $sql[] = "CREATE TABLE {$wpdb->prefix}ze_submissions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            funnel_id bigint(20) unsigned NOT NULL,
            session_id varchar(255),
            answers longtext,
            user_data longtext,
            status enum('completed','incomplete','abandoned') DEFAULT 'incomplete',
            completion_time int(11),
            ip_address varchar(45),
            user_agent text,
            referrer text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime,
            PRIMARY KEY (id),
            KEY funnel_id (funnel_id),
            KEY session_id (session_id),
            KEY status (status),
            KEY created_at (created_at),
            FOREIGN KEY (funnel_id) REFERENCES {$wpdb->prefix}ze_funnels(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Analytics table
        $sql[] = "CREATE TABLE {$wpdb->prefix}ze_analytics (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            funnel_id bigint(20) unsigned NOT NULL,
            question_id bigint(20) unsigned,
            event_type enum('view','answer','complete','abandon') NOT NULL,
            event_data longtext,
            session_id varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY funnel_id (funnel_id),
            KEY question_id (question_id),
            KEY event_type (event_type),
            KEY session_id (session_id),
            KEY created_at (created_at),
            FOREIGN KEY (funnel_id) REFERENCES {$wpdb->prefix}ze_funnels(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($sql as $query) {
            dbDelta($query);
        }
        
        // Update database version
        update_option('ze_funnel_db_version', '1.0.0');
    }
    
    /**
     * Get table name
     */
    public function get_table($table_key) {
        return isset($this->tables[$table_key]) ? $this->tables[$table_key] : null;
    }
    
    /**
     * Insert funnel
     */
    public function create_funnel($data) {
        $defaults = [
            'name' => '',
            'slug' => '',
            'description' => '',
            'settings' => '{}',
            'status' => 'draft'
        ];
        
        $data = wp_parse_args($data, $defaults);
        
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = sanitize_title($data['name']);
        }
        
        $result = $this->wpdb->insert(
            $this->tables['funnels'],
            $data,
            ['%s', '%s', '%s', '%s', '%s']
        );
        
        return $result ? $this->wpdb->insert_id : false;
    }
    
    /**
     * Get funnel by ID
     */
    public function get_funnel($id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tables['funnels']} WHERE id = %d",
                $id
            )
        );
    }
    
    /**
     * Get all funnels
     */
    public function get_funnels($args = []) {
        $defaults = [
            'status' => null,
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $where = '';
        if ($args['status']) {
            $where = $this->wpdb->prepare(' WHERE status = %s', $args['status']);
        }
        
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tables['funnels']} 
                {$where}
                ORDER BY {$args['orderby']} {$args['order']}
                LIMIT %d OFFSET %d",
                $args['limit'],
                $args['offset']
            )
        );
    }
    
    /**
     * Save submission
     */
    public function create_submission($data) {
        $defaults = [
            'funnel_id' => 0,
            'session_id' => '',
            'answers' => '{}',
            'user_data' => '{}',
            'status' => 'incomplete',
            'ip_address' => '',
            'user_agent' => '',
            'referrer' => ''
        ];
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $this->wpdb->insert(
            $this->tables['submissions'],
            $data
        );
        
        return $result ? $this->wpdb->insert_id : false;
    }
    
    /**
     * Update submission
     */
    public function update_submission($id, $data) {
        return $this->wpdb->update(
            $this->tables['submissions'],
            $data,
            ['id' => $id]
        );
    }
    
    /**
     * Log analytics event
     */
    public function log_analytics($data) {
        return $this->wpdb->insert(
            $this->tables['analytics'],
            $data
        );
    }
}