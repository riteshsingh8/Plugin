<?php
/**
 * Grammar Checker Class
 */

class AIAG_Grammar_Checker {

    /**
     * Check grammar and return suggestions
     */
    public function check($content) {
        $api_key = get_option('aiag_api_key', '');

        if (!$api_key) {
            return array(
                'success' => false,
                'message' => 'API key not configured'
            );
        }

        // Use Claude API to check grammar and provide suggestions
        $prompt = "Please review this article content for grammar, spelling, readability, and style. Provide specific suggestions for improvement.

Content:
{$content}

Format your response as JSON with the following structure:
{
    \"overall_score\": (0-100),
    \"issues\": [
        {
            \"type\": \"grammar|spelling|style|clarity\",
            \"line\": \"exact text with issue\",
            \"suggestion\": \"corrected version\",
            \"explanation\": \"why this needs to be changed\"
        }
    ],
    \"strengths\": [\"list\", \"of\", \"strengths\"],
    \"readability_score\": (0-100),
    \"improvements\": [\"suggestion1\", \"suggestion2\"]
}";

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
                    'max_tokens' => 2000,
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
            return array(
                'success' => false,
                'message' => 'API request failed: ' . $response->get_error_message()
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $result_text = $body['content'][0]['text'] ?? '';

        // Extract JSON from response
        $json_data = $this->extract_json($result_text);

        if (!$json_data) {
            return array(
                'success' => false,
                'message' => 'Could not parse grammar check results'
            );
        }

        return array(
            'success' => true,
            'score' => $json_data['overall_score'] ?? 0,
            'readability' => $json_data['readability_score'] ?? 0,
            'issues' => $json_data['issues'] ?? array(),
            'strengths' => $json_data['strengths'] ?? array(),
            'improvements' => $json_data['improvements'] ?? array(),
            'status' => $this->get_status($json_data['overall_score'] ?? 0)
        );
    }

    /**
     * Extract JSON from text response
     */
    private function extract_json($text) {
        $pattern = '/\{[\s\S]*\}/';
        if (preg_match($pattern, $text, $matches)) {
            return json_decode($matches[0], true);
        }
        return null;
    }

    /**
     * Get readability status
     */
    private function get_status($score) {
        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 75) {
            return 'good';
        } elseif ($score >= 60) {
            return 'fair';
        } else {
            return 'needs_improvement';
        }
    }

    /**
     * Get phrase suggestions for better writing
     */
    public function get_phrase_suggestions($content) {
        $api_key = get_option('aiag_api_key', '');

        if (!$api_key) {
            return array('success' => false);
        }

        $prompt = "Review this article and suggest better phrases to improve readability and engagement. Provide specific examples.

Content:
{$content}

Format as JSON:
{
    \"suggestions\": [
        {
            \"original\": \"phrase from text\",
            \"improved\": \"better version\",
            \"reason\": \"why it's better\"
        }
    ]
}";

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
                    'max_tokens' => 1500,
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
        $result_text = $body['content'][0]['text'] ?? '';
        $json_data = $this->extract_json($result_text);

        return array(
            'success' => true,
            'suggestions' => $json_data['suggestions'] ?? array()
        );
    }
}
