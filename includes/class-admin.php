<?php
/**
 * Admin Interface Class
 */

class AIAG_Admin {

    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_aiag_generate_article', array($this, 'handle_generate_article'));
        add_action('wp_ajax_aiag_check_plagiarism', array($this, 'handle_plagiarism_check'));
        add_action('wp_ajax_aiag_check_grammar', array($this, 'handle_grammar_check'));
        add_action('wp_ajax_aiag_analyze_content', array($this, 'handle_content_analysis'));
        add_action('wp_ajax_aiag_test_api', array($this, 'handle_test_api'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'AI Article Generator',
            'AI Article Generator',
            'manage_options',
            'aiag-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-edit',
            3
        );

        add_submenu_page(
            'aiag-dashboard',
            'Generate Article',
            'Generate Article',
            'manage_options',
            'aiag-generator',
            array($this, 'render_generator')
        );

        add_submenu_page(
            'aiag-dashboard',
            'Articles Library',
            'Articles Library',
            'manage_options',
            'aiag-library',
            array($this, 'render_library')
        );

        add_submenu_page(
            'aiag-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'aiag-settings',
            array($this, 'render_settings')
        );
    }

    public function register_settings() {
        register_setting('aiag_settings_group', 'aiag_api_key');
        register_setting('aiag_settings_group', 'aiag_settings');
    }

    public function enqueue_admin_assets() {
        $screen = get_current_screen();
        if (strpos($screen->id, 'aiag-') !== false) {
            wp_enqueue_style(
                'aiag-admin',
                AIAG_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                AIAG_VERSION
            );
            wp_enqueue_script(
                'aiag-admin',
                AIAG_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                AIAG_VERSION,
                true
            );

            wp_localize_script('aiag-admin', 'aiagAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('aiag_nonce'),
            ));
        }
    }

    public function render_dashboard() {
        include AIAG_PLUGIN_DIR . 'templates/dashboard.php';
    }

    public function render_generator() {
        include AIAG_PLUGIN_DIR . 'templates/generator.php';
    }

    public function render_library() {
        include AIAG_PLUGIN_DIR . 'templates/library.php';
    }

    public function render_settings() {
        include AIAG_PLUGIN_DIR . 'templates/settings.php';
    }

    public function handle_generate_article() {
        check_ajax_referer('aiag_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $prompt = sanitize_textarea_field($_POST['prompt']);
        $title = sanitize_text_field($_POST['title']);
        $category = intval($_POST['category']);

        $generator = new AIAG_Article_Generator();
        $result = $generator->generate($prompt, $title, $category);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    public function handle_plagiarism_check() {
        check_ajax_referer('aiag_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $content = sanitize_textarea_field($_POST['content']);
        $checker = new AIAG_Plagiarism_Checker();
        $result = $checker->check($content);

        wp_send_json_success($result);
    }

    public function handle_grammar_check() {
        check_ajax_referer('aiag_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $content = sanitize_textarea_field($_POST['content']);
        $checker = new AIAG_Grammar_Checker();
        $result = $checker->check($content);

        wp_send_json_success($result);
    }

    public function handle_content_analysis() {
        check_ajax_referer('aiag_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $content = sanitize_textarea_field($_POST['content']);
        $analyzer = new AIAG_Content_Analyzer();
        $result = $analyzer->analyze($content);

        wp_send_json_success($result);
    }

    public function handle_test_api() {
        check_ajax_referer('aiag_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $test_type = sanitize_text_field($_POST['test_type'] ?? 'claude');

        if ($test_type === 'claude') {
            $result = AIAG_Diagnostics::test_claude_api();
        } elseif ($test_type === 'unsplash') {
            $result = AIAG_Diagnostics::test_unsplash_api();
        } else {
            wp_send_json_error('Invalid test type');
        }

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}
