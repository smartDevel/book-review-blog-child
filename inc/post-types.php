<?php
/**
 * Post Type Redirects
 * 
 * - Book-Links auf /buchseite/?book_id=ID umleiten
 */

if (!defined('ABSPATH')) exit;

// Book-Links umschreiben
add_filter('post_type_link', function ($post_link, $post) {
    if ($post->post_type === 'book') {
        return home_url('/buchseite/?book_id=' . $post->ID);
    }
    return $post_link;
}, 10, 2);

// Singular Book auf Buchseite umleiten
add_action('template_redirect', function () {
    if (is_singular('book')) {
        $book_id = get_queried_object_id();
        wp_redirect(home_url('/buchseite/?book_id=' . $book_id), 301);
        exit;
    }
});

// Rating-Filter: entfernt — Plugin unterstützt serverseitiges Rating-Filtering nicht

