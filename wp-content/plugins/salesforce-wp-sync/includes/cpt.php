<?php
add_action('init', function () {
    register_post_type('certified_company', [
        'labels' => [
            'name' => 'Certified Companies',
            'singular_name' => 'Certified Company'
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'company'],
        'supports' => ['title', 'editor', 'thumbnail']
    ]);
});
