<?php
/**
 * Article Generator Class
 */

class AIAG_Article_Generator {

    private $api_handler;
    private $image_generator;
    private $plagiarism_checker;
    private $grammar_checker;
    private $content_analyzer;

    public function __construct() {
        $this->api_handler = new AIAG_API_Handler();
        $this->image_generator = new AIAG_Image_Generator();
        $this->plagiarism_checker = new AIAG_Plagiarism_Checker();
        $this->grammar_checker = new AIAG_Grammar_Checker();
        $this->content_analyzer = new AIAG_Content_Analyzer();
    }

    /**
     * Generate a complete article with all features
     */
    public function generate($prompt, $title = '', $category = 0) {
        // Generate title if not provided
        if (empty($title)) {
            $title = $this->generate_title($prompt);
        }

        // Step 1: Generate article content
        $content_result = $this->api_handler->generate_article_content($prompt, $title);
        if (!$content_result['success']) {
            return array(
                'success' => false,
                'message' => $content_result['message']
            );
        }

        $content = $content_result['content'];

        // Step 2: Check plagiarism
        $settings = get_option('aiag_settings', array());
        $plagiarism_result = array();
        if (!empty($settings['enable_plagiarism_check'])) {
            $plagiarism_result = $this->plagiarism_checker->check($content);
        }

        // Step 3: Check grammar
        $grammar_result = array();
        if (!empty($settings['enable_grammar_check'])) {
            $grammar_result = $this->grammar_checker->check($content);
        }

        // Step 4: Analyze content quality
        $quality_analysis = $this->content_analyzer->analyze($content);

        // Step 5: Generate featured image
        $image_result = $this->image_generator->generate($title, $content);

        // Step 6: Create WordPress post
        $post_id = $this->create_post(
            $title,
            $content,
            $category,
            $image_result['image_id'] ?? 0
        );

        if (!$post_id) {
            return array(
                'success' => false,
                'message' => 'Failed to create post'
            );
        }

        // Save article metadata
        $this->save_article_metadata($post_id, $prompt);

        // Auto-publish if enabled
        if (!empty($settings['auto_publish'])) {
            wp_publish_post($post_id);
        }

        return array(
            'success' => true,
            'post_id' => $post_id,
            'title' => $title,
            'plagiarism' => $plagiarism_result,
            'grammar' => $grammar_result,
            'quality' => $quality_analysis,
            'featured_image' => $image_result,
            'edit_link' => edit_post_link('', '', '', $post_id, false)
        );
    }

    /**
     * Generate title using AI
     */
    private function generate_title($prompt) {
        $api_handler = new AIAG_API_Handler();
        $result = wp_remote_post(
            'https://api.anthropic.com/v1/messages',
            array(
                'headers' => array(
                    'x-api-key' => get_option('aiag_api_key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'model' => 'claude-3-5-sonnet-20241022',
                    'max_tokens' => 200,
                    'messages' => array(
                        array(
                            'role' => 'user',
                            'content' => "Generate 5 engaging, SEO-friendly article titles for the topic: {$prompt}\n\nProvide only the titles, one per line, without numbering.",
                        ),
                    ),
                )),
                'timeout' => 30,
                'sslverify' => false,
            )
        );

        if (is_wp_error($result)) {
            return $prompt;
        }

        $body = json_decode(wp_remote_retrieve_body($result), true);
        $titles = explode("\n", $body['content'][0]['text'] ?? $prompt);
        return trim($titles[0]);
    }

    /**
     * Create WordPress post
     */
    private function create_post($title, $content, $category = 0, $featured_image_id = 0) {
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'draft',
            'post_type' => 'post',
        );

        if ($category) {
            $post_data['post_category'] = array($category);
        }

        $post_id = wp_insert_post($post_data);

        if ($featured_image_id && $post_id) {
            set_post_thumbnail($post_id, $featured_image_id);
        }

        return $post_id;
    }

    /**
     * Save article metadata
     */
    private function save_article_metadata($post_id, $prompt) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'aiag_articles',
            array(
                'post_id' => $post_id,
                'prompt' => $prompt,
                'status' => 'generated',
            ),
            array('%d', '%s', '%s')
        );
    }
}
