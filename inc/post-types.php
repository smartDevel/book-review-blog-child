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

// Rating-Filter: AJAX Endpoint für Buch-Ratings
add_action('wp_ajax_get_book_ratings', 'rswpbs_get_book_ratings_ajax');
add_action('wp_ajax_nopriv_get_book_ratings', 'rswpbs_get_book_ratings_ajax');
function rswpbs_get_book_ratings_ajax() {
    $books = get_posts(['post_type' => 'book', 'numberposts' => -1, 'fields' => 'ids']);
    $ratings = [];
    foreach ($books as $bid) {
        $avg = get_post_meta($bid, 'average_book_rating', true);
        if ($avg !== '' && $avg !== 'nan') $ratings[$bid] = round(floatval($avg));
    }
    wp_die(json_encode($ratings));
}

// AJAX-URL für Frontend bereitstellen
add_action('wp_enqueue_scripts', function () {
    wp_localize_script('jquery', 'rswpbsData', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
});

