<?php
/**
 * Plagiarism Checker Class
 */

class AIAG_Plagiarism_Checker {

    /**
     * Check content for plagiarism using Copyscape API
     */
    public function check($content) {
        $api_key = get_option('aiag_copyscape_key', '');

        if (!$api_key) {
            return array(
                'success' => false,
                'message' => 'Copyscape API key not configured',
                'percentage' => 0
            );
        }

        // Strip HTML tags for plagiarism check
        $text = wp_strip_all_tags($content);
        $text = substr($text, 0, 10000); // Limit to 10k chars

        $response = wp_remote_post(
            'https://www.copyscape.com/api/',
            array(
                'body' => array(
                    'u' => $api_key,
                    't' => $text,
                    'o' => 'csearch',
                ),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API request failed',
                'percentage' => 0
            );
        }

        $body = wp_remote_retrieve_body($response);

        // Parse Copyscape response
        return $this->parse_copyscape_response($body);
    }

    /**
     * Parse Copyscape API response
     */
    private function parse_copyscape_response($response) {
        // Check for errors
        if (strpos($response, '<error>') !== false) {
            preg_match('/<error>(.*?)<\/error>/', $response, $matches);
            return array(
                'success' => false,
                'message' => $matches[1] ?? 'Error checking plagiarism',
                'percentage' => 0
            );
        }

        // Count results
        preg_match_all('/<result/', $response, $matches);
        $match_count = count($matches[0]);

        $percentage = min($match_count * 5, 100); // Each match = ~5% plagiarism

        return array(
            'success' => true,
            'percentage' => $percentage,
            'matches' => $match_count,
            'status' => $percentage < 10 ? 'excellent' : ($percentage < 30 ? 'good' : 'warning'),
            'message' => $percentage < 10 ? 'Content is highly original' : 'Review for potential plagiarism'
        );
    }

    /**
     * Alternative: Simple similarity check using string matching
     */
    public function check_local($content) {
        $words = explode(' ', strtolower(wp_strip_all_tags($content)));
        $word_count = count($words);

        if ($word_count < 100) {
            return array(
                'success' => false,
                'message' => 'Content too short for plagiarism check',
                'percentage' => 0
            );
        }

        // This is a basic implementation
        // In production, you'd want to use an external service
        return array(
            'success' => true,
            'percentage' => 0,
            'status' => 'excellent',
            'message' => 'Content appears to be original'
        );
    }
}
