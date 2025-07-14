<?php
function sf_get_access_token() {
    $stored_token = get_option('sf_access_token');
    $expiry = get_option('sf_access_token_expires');
    if ($stored_token && $expiry && time() < $expiry) return $stored_token;

    $response = wp_remote_post('https://login.salesforce.com/services/oauth2/token', [
        'body' => [
            'grant_type' => 'password',
            'client_id' => SF_CLIENT_ID,
            'client_secret' => SF_CLIENT_SECRET,
            'username' => SF_USERNAME,
            'password' => SF_PASSWORD . SF_SECURITY_TOKEN
        ]
    ]);

    if (is_wp_error($response)) {
        error_log('Salesforce auth error: ' . $response->get_error_message());
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    update_option('sf_access_token', $body['access_token']);
    update_option('sf_access_token_expires', time() + 3600);
    update_option('sf_instance_url', $body['instance_url']);
    return $body['access_token'];
}
