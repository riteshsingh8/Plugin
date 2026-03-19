<?php
/**
 * Direct Claude API Test
 * Upload this to your WordPress root and visit: yoursite.com/test-api.php
 * Then delete this file
 */

// Replace with your API key
$api_key = 'sk-ant-v01-YOUR-API-KEY-HERE';

// Simple test
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
                    'content' => 'Say hello in one word',
                ),
            ),
        )),
        'timeout' => 30,
        'sslverify' => false,
    )
);

echo '<h1>Claude API Test Results</h1>';
echo '<pre>';

if (is_wp_error($response)) {
    echo "❌ ERROR: " . $response->get_error_message();
} else {
    $status = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    echo "Status Code: " . $status . "\n";
    echo "Response: " . json_encode($body, JSON_PRETTY_PRINT) . "\n";

    if ($status === 200 && isset($body['content'][0]['text'])) {
        echo "\n✅ SUCCESS! API is working!\n";
        echo "Response: " . $body['content'][0]['text'];
    } else {
        echo "\n❌ FAILED!\n";
    }
}

echo '</pre>';
echo '<p><strong>Delete this file after testing!</strong></p>';
