<?php
function sf_sync_salesforce_companies() {
    $token = sf_get_access_token();
    $instance_url = get_option('sf_instance_url');
    if (!$token || !$instance_url) return;

    $query = urlencode("SELECT Id, Name, Description__c, Logo_URL__c, Country__c, Certification_Year__c FROM Company__c");
    $url = "$instance_url/services/data/v57.0/query?q=$query";

    $response = wp_remote_get($url, [
        'headers' => ['Authorization' => 'Bearer ' . $token]
    ]);

    if (is_wp_error($response)) {
        error_log('Salesforce sync failed: ' . $response->get_error_message());
        return;
    }

    $records = json_decode(wp_remote_retrieve_body($response), true)['records'];
    $sf_ids = [];

    foreach ($records as $company) {
        $sf_ids[] = $company['Id'];
        $existing = get_posts([
            'post_type' => 'certified_company',
            'meta_key' => 'sf_id',
            'meta_value' => $company['Id'],
            'post_status' => ['publish', 'draft'],
            'numberposts' => 1
        ]);

        $post_data = [
            'post_title' => $company['Name'],
            'post_content' => $company['Description__c'],
            'post_type' => 'certified_company',
            'post_status' => 'publish'
        ];

        if ($existing) {
            $post_data['ID'] = $existing[0]->ID;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
            update_post_meta($post_id, 'sf_id', $company['Id']);
        }

        update_post_meta($post_id, 'country', $company['Country__c']);
        update_post_meta($post_id, 'cert_year', $company['Certification_Year__c']);
    }

    $existing_posts = get_posts([
        'post_type' => 'certified_company',
        'meta_query' => [['key' => 'sf_id', 'compare' => 'EXISTS']],
        'numberposts' => -1
    ]);

    foreach ($existing_posts as $post) {
        $sf_id = get_post_meta($post->ID, 'sf_id', true);
        if (!in_array($sf_id, $sf_ids)) {
            wp_update_post(['ID' => $post->ID, 'post_status' => 'draft']);
        }
    }
}
