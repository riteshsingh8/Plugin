<?php
/**
 * Image Generator Class for Featured Images
 */

class AIAG_Image_Generator {

    /**
     * Generate featured image for article
     */
    public function generate($title, $content) {
        // Get image suggestions from API
        $api_handler = new AIAG_API_Handler();
        $suggestions = $api_handler->generate_image_suggestions($title, $content);

        if (!$suggestions['success']) {
            return array(
                'success' => false,
                'message' => 'Could not generate image suggestions'
            );
        }

        // Use Unsplash API to find relevant image
        $image_url = $this->find_image_from_unsplash($title);

        if (!$image_url) {
            return array(
                'success' => false,
                'message' => 'Could not find suitable image'
            );
        }

        // Download and attach image to post
        $image_id = $this->download_and_attach_image($image_url, $title);

        if (!$image_id) {
            return array(
                'success' => false,
                'message' => 'Could not attach image to post'
            );
        }

        return array(
            'success' => true,
            'image_id' => $image_id,
            'image_url' => wp_get_attachment_url($image_id)
        );
    }

    /**
     * Find image from Unsplash API
     */
    private function find_image_from_unsplash($query) {
        $response = wp_remote_get(
            'https://api.unsplash.com/search/photos',
            array(
                'headers' => array(
                    'Accept-Version' => 'v1',
                ),
                'body' => array(
                    'query' => $query,
                    'client_id' => get_option('aiag_unsplash_key', ''),
                    'per_page' => 1,
                    'order_by' => 'relevant',
                ),
                'timeout' => 15,
            )
        );

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($data['results'])) {
            return false;
        }

        return $data['results'][0]['urls']['regular'] ?? false;
    }

    /**
     * Download and attach image to media library
     */
    private function download_and_attach_image($image_url, $title) {
        $image_url = esc_url_raw($image_url);

        // Download image
        $response = wp_remote_get($image_url, array('timeout' => 15));

        if (is_wp_error($response)) {
            return false;
        }

        $image_data = wp_remote_retrieve_body($response);

        // Create temporary file
        $upload_dir = wp_upload_dir();
        $filename = sanitize_file_name($title . '-' . time() . '.jpg');
        $filepath = $upload_dir['path'] . '/' . $filename;

        file_put_contents($filepath, $image_data);

        // Prepare attachment
        $attachment = array(
            'post_mime_type' => 'image/jpeg',
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'inherit',
        );

        // Insert attachment
        $attachment_id = wp_insert_attachment($attachment, $filepath);

        if (is_wp_error($attachment_id)) {
            return false;
        }

        // Generate attachment metadata
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata($attachment_id, $filepath);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        return $attachment_id;
    }

    /**
     * Alternative: Generate image using DALL-E (requires API key)
     */
    private function generate_with_dalle($description) {
        $api_key = get_option('aiag_openai_key', '');

        if (!$api_key) {
            return false;
        }

        $response = wp_remote_post(
            'https://api.openai.com/v1/images/generations',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'prompt' => $description,
                    'n' => 1,
                    'size' => '1024x1024',
                )),
                'timeout' => 60,
            )
        );

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['data'][0]['url'] ?? false;
    }
}
