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

// Rating-Filter: über rswpbs_search_fields Plugin-Hook
// Das Plugin liest $_GET['rating'] NICHT — wir haken uns in den WP_Query ein
add_filter('posts_where', function ($where, $query) {
    if (is_admin()) return $where;
    if (empty($_GET['rating']) || $_GET['rating'] === 'all' || $_GET['rating'] === '') return $where;
    $rating = intval($_GET['rating']);
    if ($rating < 1 || $rating > 5) return $where;
    global $wpdb;
    $where .= " AND {$wpdb->posts}.ID IN (
        SELECT post_id FROM {$wpdb->postmeta}
        WHERE meta_key = 'average_book_rating'
        AND CAST(meta_value AS DECIMAL) >= {$rating}
        AND CAST(meta_value AS DECIMAL) < " . ($rating + 1) . "
    )";
    return $where;
}, 10, 2);

