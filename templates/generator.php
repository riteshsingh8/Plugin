<?php
/**
 * Article Generator Template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap aiag-generator">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="aiag-generator-main">
        <div class="aiag-generator-form">
            <div class="aiag-card">
                <h2>Create New Article</h2>
                <form id="aiag-generator-form">
                    <div class="form-group">
                        <label for="article-topic">
                            <strong>Article Topic</strong>
                            <span class="required">*</span>
                        </label>
                        <textarea
                            id="article-topic"
                            name="prompt"
                            class="widefat"
                            rows="4"
                            placeholder="Enter the topic you want to write about. Be specific for better results. Example: How to write engaging blog posts for your business"
                            required
                        ></textarea>
                        <small>Be specific about what you want the article to cover. The more details, the better the result.</small>
                    </div>

                    <div class="form-group">
                        <label for="article-title">
                            <strong>Article Title (Optional)</strong>
                        </label>
                        <input
                            type="text"
                            id="article-title"
                            name="title"
                            class="widefat"
                            placeholder="Leave empty to auto-generate a title"
                        />
                        <small>If left empty, AI will generate an SEO-friendly title for you.</small>
                    </div>

                    <div class="form-group">
                        <label for="article-category">
                            <strong>Category</strong>
                        </label>
                        <?php
                        wp_dropdown_categories(array(
                            'id' => 'article-category',
                            'name' => 'category',
                            'hide_empty' => false,
                            'show_option_none' => 'Uncategorized',
                        ));
                        ?>
                    </div>

                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" name="enable_plagiarism" value="1" checked />
                            Check for plagiarism
                        </label>
                        <label>
                            <input type="checkbox" name="enable_grammar" value="1" checked />
                            Check grammar
                        </label>
                        <label>
                            <input type="checkbox" name="generate_image" value="1" checked />
                            Generate featured image
                        </label>
                        <label>
                            <input type="checkbox" name="auto_publish" value="1" />
                            Auto-publish article
                        </label>
                    </div>

                    <button type="submit" class="button button-primary button-large">
                        <span class="button-text">Generate Article</span>
                        <span class="spinner" style="display: none;"></span>
                    </button>
                </form>
            </div>
        </div>

        <div class="aiag-generator-results" style="display: none;">
            <div class="aiag-card">
                <h2>Generation Results</h2>

                <div class="aiag-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <p class="progress-text">Initializing...</p>
                </div>

                <div class="aiag-results-content" style="display: none;">
                    <div class="aiag-alert alert-success" id="success-message"></div>

                    <div class="aiag-result-section">
                        <h3>Article Preview</h3>
                        <div class="article-preview">
                            <h2 id="preview-title"></h2>
                            <div id="preview-content"></div>
                        </div>
                    </div>

                    <div class="aiag-result-section">
                        <h3>Quality Checks</h3>
                        <div id="quality-results"></div>
                    </div>

                    <div class="aiag-result-section">
                        <h3>Plagiarism Check</h3>
                        <div id="plagiarism-results"></div>
                    </div>

                    <div class="aiag-result-section">
                        <h3>Grammar Check</h3>
                        <div id="grammar-results"></div>
                    </div>

                    <div class="aiag-result-actions">
                        <a href="#" class="button button-primary" id="edit-post-btn">Edit Post</a>
                        <a href="#" class="button" id="view-post-btn">View Post</a>
                        <button class="button" id="generate-another-btn">Generate Another</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
