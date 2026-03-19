<?php
/**
 * Main Plugin Class
 */

class AIAG_Plugin {

    public function init() {
        // Load admin interface if user is admin
        if (is_admin()) {
            $admin = new AIAG_Admin();
            $admin->init();
        }

        // Load public-facing functionality
        $this->load_public_hooks();
    }

    private function load_public_hooks() {
        // Add any public-facing hooks here
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
    }

    public function enqueue_public_assets() {
        wp_enqueue_style(
            'aiag-public',
            AIAG_PLUGIN_URL . 'assets/css/public.css',
            array(),
            AIAG_VERSION
        );
    }

    public static function activate() {
        // Create necessary database tables
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Create article logs table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}aiag_articles (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            prompt longtext NOT NULL,
            status varchar(20) DEFAULT 'draft',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Set default options
        if (!get_option('aiag_api_key')) {
            add_option('aiag_api_key', '');
        }
        if (!get_option('aiag_settings')) {
            add_option('aiag_settings', array(
                'enable_plagiarism_check' => 1,
                'enable_grammar_check' => 1,
                'auto_publish' => 0,
                'reference_count' => 100,
            ));
        }
    }

    public static function deactivate() {
        // Clean up scheduled events if any
        wp_clear_scheduled_hook('aiag_generate_articles');
    }

    public static function uninstall() {
        // Delete all plugin data
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aiag_articles");
        delete_option('aiag_api_key');
        delete_option('aiag_settings');
    }
}
