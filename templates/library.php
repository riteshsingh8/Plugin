<?php
/**
 * Articles Library Template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap aiag-library">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="aiag-filters">
        <input type="text" id="search-articles" placeholder="Search articles..." />
        <select id="filter-status">
            <option value="">All Status</option>
            <option value="publish">Published</option>
            <option value="draft">Draft</option>
            <option value="pending">Pending</option>
        </select>
        <button class="button" id="apply-filters">Filter</button>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Category</th>
                <th>Created</th>
                <th>Views</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="articles-list">
            <?php
            global $wpdb;
            $articles = $wpdb->get_results("
                SELECT a.*, p.post_status, p.post_date, p.post_views_count
                FROM {$wpdb->prefix}aiag_articles a
                LEFT JOIN {$wpdb->posts} p ON a.post_id = p.ID
                ORDER BY a.created_at DESC
            ");

            if ($articles) {
                foreach ($articles as $article) {
                    $post_id = $article->post_id;
                    $categories = get_the_category($post_id);
                    $cat_names = wp_list_pluck($categories, 'name');
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo get_the_title($post_id); ?></strong>
                            <br />
                            <small><?php echo wp_trim_words($article->prompt, 15); ?></small>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo esc_attr($article->post_status); ?>">
                                <?php echo esc_html($article->post_status); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(implode(', ', $cat_names)); ?></td>
                        <td><?php echo esc_html(date_i18n('M d, Y', strtotime($article->created_at))); ?></td>
                        <td>0</td>
                        <td>
                            <a href="<?php echo get_edit_post_link($post_id); ?>" class="button button-small">Edit</a>
                            <a href="<?php echo get_permalink($post_id); ?>" class="button button-small" target="_blank">View</a>
                            <a href="<?php echo get_delete_post_link($post_id); ?>" class="button button-small button-link-delete" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="6" class="text-center">No articles found. <a href="' . admin_url('admin.php?page=aiag-generator') . '">Generate your first article</a></td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
