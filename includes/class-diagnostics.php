<?php
/**
 * API Testing/Diagnostic Class
 */

class AIAG_Diagnostics {

    /**
     * Test Claude API configuration
     */
    public static function test_claude_api() {
        $api_key = get_option('aiag_api_key', '');

        if (!$api_key) {
            return array(
                'success' => false,
                'message' => 'API key not configured',
                'details' => 'Please add your Claude API key in the Settings page'
            );
        }

        // Make a simple test request
        $response = wp_remote_post(
            'https://api.anthropic.com/v1/messages',
            array(
                'headers' => array(
                    'x-api-key' => $api_key,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'model' => 'claude-3-5-sonnet-20241022',
                    'max_tokens' => 100,
                    'messages' => array(
                        array(
                            'role' => 'user',
                            'content' => 'Say "API connection successful" in one sentence.',
                        ),
                    ),
                )),
                'timeout' => 30,
                'sslverify' => false,
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Connection failed: ' . $response->get_error_message(),
                'details' => 'Unable to reach the Claude API. Check your internet connection.'
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code === 401) {
            return array(
                'success' => false,
                'message' => 'Authentication failed',
                'details' => 'Invalid API key. Please check your key in Anthropic Console and update it in Settings.'
            );
        }

        if ($status_code !== 200) {
            $error = $body['error']['message'] ?? 'Unknown error';
            return array(
                'success' => false,
                'message' => 'API Error (Status ' . $status_code . ')',
                'details' => $error
            );
        }

        if (!isset($body['content'][0]['text'])) {
            return array(
                'success' => false,
                'message' => 'Unexpected response format',
                'details' => 'The API response was not in the expected format'
            );
        }

        return array(
            'success' => true,
            'message' => 'API connection successful!',
            'details' => 'Your Claude API is properly configured and working.',
            'response' => $body['content'][0]['text']
        );
    }

    /**
     * Test Unsplash API configuration (optional)
     */
    public static function test_unsplash_api() {
        $api_key = get_option('aiag_unsplash_key', '');

        if (!$api_key) {
            return array(
                'success' => false,
                'message' => 'Unsplash API key not configured',
                'details' => 'Optional - Image generation will still work without it'
            );
        }

        $response = wp_remote_get(
            'https://api.unsplash.com/search/photos',
            array(
                'headers' => array(
                    'Accept-Version' => 'v1',
                ),
                'body' => array(
                    'query' => 'test',
                    'client_id' => $api_key,
                    'per_page' => 1,
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Unsplash connection failed',
                'details' => $response->get_error_message()
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            return array(
                'success' => false,
                'message' => 'Unsplash API Error (Status ' . $status_code . ')',
                'details' => 'Check your Unsplash API key'
            );
        }

        return array(
            'success' => true,
            'message' => 'Unsplash API is properly configured'
        );
    }

    /**
     * Get system information
     */
    public static function get_system_info() {
        global $wp_version;

        return array(
            'wordpress_version' => $wp_version,
            'php_version' => phpversion(),
            'wp_debug' => defined('WP_DEBUG') ? (WP_DEBUG ? 'Enabled' : 'Disabled') : 'Not defined',
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'post_max_size' => ini_get('post_max_size'),
        );
    }
}
