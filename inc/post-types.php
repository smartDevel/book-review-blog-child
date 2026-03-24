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

// Rating-Filter: Book ID→Rating Mapping im Footer
add_action('wp_footer', function () {
    if (is_admin()) return;
    global $wpdb;
    $rows = $wpdb->get_results(
        "SELECT pm.post_id, ROUND(CAST(pm.meta_value AS DECIMAL)) AS rating
         FROM {$wpdb->postmeta} pm
         WHERE pm.meta_key = 'average_book_rating'
         AND pm.meta_value IS NOT NULL AND pm.meta_value != '' AND pm.meta_value != 'nan'"
    );
    $map = [];
    foreach ($rows as $row) {
        $map[intval($row->post_id)] = intval($row->rating);
    }
    if (empty($map)) return;
    ?>
    <script>window.__bookIdRating=<?php echo json_encode($map); ?>;</script>
    <?php
});

