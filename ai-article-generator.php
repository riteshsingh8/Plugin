<?php
/**
 * Plugin Name: AI Article Generator Pro
 * Plugin URI: https://example.com/ai-article-generator
 * Description: Generate high-quality, SEO-optimized articles automatically using AI. Includes plagiarism checker, grammar checker, and featured image generation.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-article-generator
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AIAG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIAG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIAG_VERSION', '1.0.0');

// Load plugin files
require_once AIAG_PLUGIN_DIR . 'includes/class-plugin.php';
require_once AIAG_PLUGIN_DIR . 'includes/class-admin.php';
require_once AIAG_PLUGIN_DIR . 'includes/class-api-handler.php';
require_once AIAG_PLUGIN_DIR . 'includes/class-article-generator.php';
require_once AIAG_PLUGIN_DIR . 'includes/class-image-generator.php';
require_once AIAG_PLUGIN_DIR . 'includes/class-plagiarism-checker.php';
require_once AIAG_PLUGIN_DIR . 'includes/class-grammar-checker.php';
require_once AIAG_PLUGIN_DIR . 'includes/class-content-analyzer.php';

// Initialize plugin
function aiag_init() {
    $plugin = new AIAG_Plugin();
    $plugin->init();
}
add_action('plugins_loaded', 'aiag_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    AIAG_Plugin::activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    AIAG_Plugin::deactivate();
});

// Uninstall hook
register_uninstall_hook(__FILE__, function() {
    AIAG_Plugin::uninstall();
});
