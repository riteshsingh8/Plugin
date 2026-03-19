<?php
/**
 * Plugin Settings Template
 */
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('aiag_settings', array());
$api_key = get_option('aiag_api_key', '');
?>

<div class="wrap aiag-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="options.php" class="aiag-settings-form">
        <?php settings_fields('aiag_settings_group'); ?>

        <div class="aiag-card">
            <h2>API Configuration</h2>

            <div class="form-group">
                <label for="aiag-api-key">
                    <strong>Claude API Key</strong>
                    <span class="required">*</span>
                </label>
                <input
                    type="password"
                    id="aiag-api-key"
                    name="aiag_api_key"
                    class="widefat"
                    value="<?php echo esc_attr($api_key); ?>"
                    placeholder="sk-ant-..."
                />
                <small>
                    Get your API key from <a href="https://console.anthropic.com/account/keys" target="_blank">Anthropic Console</a>
                </small>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="show-api-key" />
                    Show API Key
                </label>
            </div>

            <div class="form-group">
                <button type="button" class="button button-primary" id="test-claude-api">Test Claude API Connection</button>
                <div id="api-test-result" style="margin-top: 10px; display: none;"></div>
            </div>
        </div>

        <div class="aiag-card">
            <h2>Article Generation Settings</h2>

            <div class="form-group">
                <label>
                    <input
                        type="checkbox"
                        name="aiag_settings[enable_plagiarism_check]"
                        value="1"
                        <?php checked(!empty($settings['enable_plagiarism_check']), 1); ?>
                    />
                    <strong>Enable Plagiarism Check</strong>
                </label>
                <small>Check each generated article for plagiarism (requires Copyscape API)</small>
            </div>

            <div class="form-group">
                <label>
                    <input
                        type="checkbox"
                        name="aiag_settings[enable_grammar_check]"
                        value="1"
                        <?php checked(!empty($settings['enable_grammar_check']), 1); ?>
                    />
                    <strong>Enable Grammar Check</strong>
                </label>
                <small>Automatically check grammar and provide suggestions</small>
            </div>

            <div class="form-group">
                <label>
                    <input
                        type="checkbox"
                        name="aiag_settings[auto_publish]"
                        value="1"
                        <?php checked(!empty($settings['auto_publish']), 1); ?>
                    />
                    <strong>Auto-Publish Articles</strong>
                </label>
                <small>Automatically publish generated articles instead of saving as drafts</small>
            </div>

            <div class="form-group">
                <label for="reference-count">
                    <strong>Reference Count for Research</strong>
                </label>
                <input
                    type="number"
                    id="reference-count"
                    name="aiag_settings[reference_count]"
                    class="small-text"
                    value="<?php echo intval($settings['reference_count'] ?? 100); ?>"
                    min="10"
                    max="500"
                />
                <small>Number of top websites to consider during article generation (10-500)</small>
            </div>
        </div>

        <div class="aiag-card">
            <h2>Optional API Keys</h2>

            <div class="form-group">
                <label for="unsplash-key">
                    <strong>Unsplash API Key (for images)</strong>
                </label>
                <input
                    type="password"
                    id="unsplash-key"
                    name="aiag_unsplash_key"
                    class="widefat"
                    value="<?php echo esc_attr(get_option('aiag_unsplash_key', '')); ?>"
                    placeholder="Leave empty to use default image service"
                />
                <small>Get free API key from <a href="https://unsplash.com/developers" target="_blank">Unsplash</a></small>
            </div>

            <div class="form-group">
                <label for="copyscape-key">
                    <strong>Copyscape API Key (for plagiarism check)</strong>
                </label>
                <input
                    type="password"
                    id="copyscape-key"
                    name="aiag_copyscape_key"
                    class="widefat"
                    value="<?php echo esc_attr(get_option('aiag_copyscape_key', '')); ?>"
                    placeholder="Leave empty for local plagiarism check"
                />
                <small>Get API key from <a href="https://www.copyscape.com/api/" target="_blank">Copyscape</a></small>
            </div>

            <div class="form-group">
                <label for="openai-key">
                    <strong>OpenAI API Key (for DALL-E image generation)</strong>
                </label>
                <input
                    type="password"
                    id="openai-key"
                    name="aiag_openai_key"
                    class="widefat"
                    value="<?php echo esc_attr(get_option('aiag_openai_key', '')); ?>"
                    placeholder="Optional - for advanced image generation"
                />
                <small>Get API key from <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI</a></small>
            </div>
        </div>

        <div class="aiag-card">
            <h2>Content Preferences</h2>

            <div class="form-group">
                <label for="article-tone">
                    <strong>Article Tone</strong>
                </label>
                <select id="article-tone" name="aiag_settings[article_tone]" class="widefat">
                    <option value="professional" <?php selected($settings['article_tone'] ?? 'professional', 'professional'); ?>>Professional</option>
                    <option value="casual" <?php selected($settings['article_tone'] ?? 'professional', 'casual'); ?>>Casual</option>
                    <option value="conversational" <?php selected($settings['article_tone'] ?? 'professional', 'conversational'); ?>>Conversational</option>
                    <option value="formal" <?php selected($settings['article_tone'] ?? 'professional', 'formal'); ?>>Formal</option>
                </select>
            </div>

            <div class="form-group">
                <label for="min-word-count">
                    <strong>Minimum Article Length (words)</strong>
                </label>
                <input
                    type="number"
                    id="min-word-count"
                    name="aiag_settings[min_word_count]"
                    class="small-text"
                    value="<?php echo intval($settings['min_word_count'] ?? 1500); ?>"
                    min="500"
                    max="10000"
                />
            </div>
        </div>

        <?php submit_button(); ?>
    </form>

    <div class="aiag-card">
        <h2>Documentation</h2>
        <p>
            For detailed setup instructions and API configuration, visit our
            <a href="https://example.com/docs" target="_blank">documentation</a>.
        </p>
    </div>
</div>

<script>
document.getElementById('show-api-key').addEventListener('change', function() {
    const input = document.getElementById('aiag-api-key');
    input.type = this.checked ? 'text' : 'password';
});
</script>
