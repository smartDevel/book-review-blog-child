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

// Rating-Filter für Bücher
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query()) return;
    if (empty($_GET['rating']) || $_GET['rating'] === 'all') return;
    if ($query->get('post_type') !== 'book' && !is_post_type_archive('book')) return;

    $rating = intval($_GET['rating']);
    if ($rating < 1 || $rating > 5) return;

    $meta_query = $query->get('meta_query') ?: [];
    $meta_query[] = [
        'key'     => 'average_book_rating',
        'value'   => $rating,
        'compare' => '=',
        'type'    => 'NUMERIC',
    ];
    $query->set('meta_query', $meta_query);
});

