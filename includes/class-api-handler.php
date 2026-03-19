<?php
/**
 * API Handler for integrating with AI services
 */

class AIAG_API_Handler {

    private $api_key;
    private $api_endpoint = 'https://api.anthropic.com/v1';

    public function __construct() {
        $this->api_key = get_option('aiag_api_key');
    }

    /**
     * Generate article content using Claude API
     */
    public function generate_article_content($prompt, $title) {
        if (!$this->api_key) {
            return array(
                'success' => false,
                'message' => 'API key not configured. Please add your Claude API key in Settings.'
            );
        }

        $enhanced_prompt = $this->build_article_prompt($prompt, $title);

        $response = wp_remote_post(
            $this->api_endpoint . '/messages',
            array(
                'headers' => array(
                    'x-api-key' => $this->api_key,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'model' => 'claude-3-5-sonnet-20241022',
                    'max_tokens' => 4000,
                    'messages' => array(
                        array(
                            'role' => 'user',
                            'content' => $enhanced_prompt,
                        ),
                    ),
                )),
                'timeout' => 60,
            )
        );

        if (is_wp_error($response)) {
            $error_msg = $response->get_error_message();
            $this->log_error('API Request Error: ' . $error_msg);
            return array(
                'success' => false,
                'message' => 'API request failed: ' . $error_msg
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Check for API error responses
        if ($status_code !== 200) {
            $error_message = $body['error']['message'] ?? 'Unknown error';
            $this->log_error('API Error (Status ' . $status_code . '): ' . $error_message);
            return array(
                'success' => false,
                'message' => 'API Error: ' . $error_message . ' (Status: ' . $status_code . ')'
            );
        }

        if (!isset($body['content'][0]['text'])) {
            $this->log_error('Invalid API response structure: ' . json_encode($body));
            return array(
                'success' => false,
                'message' => 'Invalid API response. Please check your API key and try again.'
            );
        }

        return array(
            'success' => true,
            'content' => $body['content'][0]['text']
        );
    }

    /**
     * Log errors for debugging
     */
    private function log_error($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AIAG Error] ' . $message);
        }
    }

    /**
     * Build enhanced prompt for article generation
     */
    private function build_article_prompt($topic, $title) {
        return "You are an expert content writer. Write a comprehensive, engaging article with the following requirements:

Topic: {$topic}
Title: {$title}

Requirements:
1. Write in a storytelling format that keeps readers engaged
2. Include an introduction with a hook
3. Structure content with clear sections and subheadings
4. Use transitions between paragraphs for better flow
5. Include relevant examples and case studies
6. Write at least 1500 words
7. Use active voice and varied sentence structure
8. Include practical tips and actionable insights
9. End with a strong conclusion and call-to-action
10. Use simple, clear language while maintaining professionalism
11. Ensure 100% original, humanized content
12. Reference current industry best practices

Format the response in HTML with proper h2 and h3 tags for headings. Do not include h1 tag (that will be the title).";
    }

    /**
     * Generate image suggestions for featured image
     */
    public function generate_image_suggestions($title, $content_snippet) {
        if (!$this->api_key) {
            return array('success' => false);
        }

        $prompt = "Based on this article title and content snippet, suggest 3 detailed image descriptions for a featured image that would work well with this article:

Title: {$title}
Content snippet: " . substr($content_snippet, 0, 300) . "

Provide 3 image descriptions that are:
- Visually appealing and relevant
- Suitable for blog featured images
- Professional and high-quality
- Specific enough to use for AI image generation

Format as JSON with array of objects containing 'description' and 'keywords' fields.";

        $response = wp_remote_post(
            $this->api_endpoint . '/messages',
            array(
                'headers' => array(
                    'x-api-key' => $this->api_key,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'model' => 'claude-3-5-sonnet-20241022',
                    'max_tokens' => 1000,
                    'messages' => array(
                        array(
                            'role' => 'user',
                            'content' => $prompt,
                        ),
                    ),
                )),
                'timeout' => 60,
            )
        );

        if (is_wp_error($response)) {
            return array('success' => false);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $text = $body['content'][0]['text'] ?? '';

        return array(
            'success' => true,
            'suggestions' => json_decode($text, true)
        );
    }
}
