<?php
/**
 * Main Dashboard Template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap aiag-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="aiag-welcome-section">
        <div class="aiag-card">
            <h2>Welcome to AI Article Generator Pro</h2>
            <p>Automatically generate high-quality, SEO-optimized articles with AI. Get started by creating your first article.</p>
            <a href="<?php echo admin_url('admin.php?page=aiag-generator'); ?>" class="button button-primary button-hero">
                Generate Article
            </a>
        </div>
    </div>

    <div class="aiag-stats-section">
        <div class="aiag-stat-card">
            <h3>Total Articles Generated</h3>
            <p class="aiag-stat-number">
                <?php
                global $wpdb;
                $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aiag_articles");
                echo intval($count);
                ?>
            </p>
        </div>

        <div class="aiag-stat-card">
            <h3>Total Posts</h3>
            <p class="aiag-stat-number">
                <?php echo wp_count_posts()->publish ?? 0; ?>
            </p>
        </div>

        <div class="aiag-stat-card">
            <h3>API Status</h3>
            <p class="aiag-stat-number">
                <?php echo get_option('aiag_api_key') ? '✓ Active' : '✗ Not Configured'; ?>
            </p>
        </div>
    </div>

    <div class="aiag-features-section">
        <h2>Key Features</h2>
        <div class="aiag-features-grid">
            <div class="aiag-feature">
                <div class="aiag-feature-icon">📝</div>
                <h3>AI Article Writing</h3>
                <p>Generate complete articles based on your topic using Claude AI</p>
            </div>

            <div class="aiag-feature">
                <div class="aiag-feature-icon">🖼️</div>
                <h3>Featured Images</h3>
                <p>Automatically generate or find perfect featured images for your articles</p>
            </div>

            <div class="aiag-feature">
                <div class="aiag-feature-icon">🔍</div>
                <h3>Plagiarism Check</h3>
                <p>Ensure content is 100% original with automated plagiarism detection</p>
            </div>

            <div class="aiag-feature">
                <div class="aiag-feature-icon">✍️</div>
                <h3>Grammar Checker</h3>
                <p>Check grammar, spelling, and get suggestions for better writing</p>
            </div>

            <div class="aiag-feature">
                <div class="aiag-feature-icon">📊</div>
                <h3>Content Analysis</h3>
                <p>Get detailed analysis on readability, SEO, and engagement</p>
            </div>

            <div class="aiag-feature">
                <div class="aiag-feature-icon">🚀</div>
                <h3>Auto Publish</h3>
                <p>Optionally auto-publish articles directly to your site</p>
            </div>
        </div>
    </div>

    <div class="aiag-recent-articles">
        <h2>Recent Articles</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $wpdb;
                $articles = $wpdb->get_results("
                    SELECT a.*, p.post_status, p.post_date
                    FROM {$wpdb->prefix}aiag_articles a
                    LEFT JOIN {$wpdb->posts} p ON a.post_id = p.ID
                    ORDER BY a.created_at DESC
                    LIMIT 5
                ");

                if ($articles) {
                    foreach ($articles as $article) {
                        echo '<tr>';
                        echo '<td>' . get_the_title($article->post_id) . '</td>';
                        echo '<td><span class="badge badge-' . esc_attr($article->post_status) . '">' . esc_html($article->post_status) . '</span></td>';
                        echo '<td>' . esc_html(date_i18n('M d, Y', strtotime($article->created_at))) . '</td>';
                        echo '<td>';
                        echo '<a href="' . get_edit_post_link($article->post_id) . '" class="button button-small">Edit</a> ';
                        echo '<a href="' . get_permalink($article->post_id) . '" class="button button-small" target="_blank">View</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="4" class="text-center">No articles generated yet. <a href="' . admin_url('admin.php?page=aiag-generator') . '">Create one now!</a></td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="aiag-help-section">
        <h2>Need Help?</h2>
        <p>Check out our <a href="https://example.com/docs" target="_blank">documentation</a> or visit the <a href="<?php echo admin_url('admin.php?page=aiag-settings'); ?>">settings page</a> to configure your API keys.</p>
    </div>
</div>
