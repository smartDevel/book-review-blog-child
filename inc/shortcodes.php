<?php
/**
 * Shortcodes
 * 
 * - [book_details] — Buchdetailseite mit Sternebewertung
 * - [buchrezensionen] — Rezensionen mit AJAX-Filter
 */

if (!defined('ABSPATH')) exit;

// ═══════════════════════════════════════════
// BOOK DETAILS SHORTCODE
// ═══════════════════════════════════════════
add_shortcode('book_details', function ($atts) {
    $atts = shortcode_atts([
        'id'            => isset($_GET['book_id']) ? intval($_GET['book_id']) : get_the_ID(),
        'show_reviews'  => 'true',
        'show_content'  => 'false',
        'reviews_page'  => '',
    ], $atts);

    $post_id = intval($atts['id']);
    if (!$post_id || get_post_type($post_id) !== 'book') {
        return '<p>Buch nicht gefunden.</p>';
    }

    $cover_url = get_the_post_thumbnail_url($post_id, 'large');
    $buy_link  = get_post_meta($post_id, '_rsbs_buy_btn_link', true);
    $buy_text  = get_post_meta($post_id, '_rsbs_buy_btn_text', true) ?: 'Auf Amazon kaufen';
    $price     = get_post_meta($post_id, '_rsbs_book_price', true);
    $desc      = get_post_meta($post_id, '_rsbs_short_description', true);
    $orig_name = get_post_meta($post_id, '_rsbs_original_book_name', true);
    $orig_url  = get_post_meta($post_id, '_rsbs_original_book_url', true);

    $avg_rating = floatval(get_post_meta($post_id, 'average_book_rating', true));

    $review_count = count(get_posts([
        'post_type'      => 'book_reviews',
        'meta_key'       => '_rswpbs_reviewed_book',
        'meta_value'     => $post_id,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ]));

    $stars_html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($avg_rating)) {
            $stars_html .= '<i class="fa-solid fa-star" style="color:#f59e0b;font-size:20px;"></i>';
        } elseif ($i - 0.5 <= $avg_rating) {
            $stars_html .= '<i class="fa-solid fa-star-half-stroke" style="color:#f59e0b;font-size:20px;"></i>';
        } else {
            $stars_html .= '<i class="fa-regular fa-star" style="color:#f59e0b;font-size:20px;"></i>';
        }
    }

    $reviews_link = $atts['reviews_page'] ?: get_permalink($post_id) . '#leserbewertungen';

    $fields = [
        'book_publisher_name'      => ['Verlag',          '📚'],
        'book_publish_date'        => ['Erschienen',      '📅'],
        'book_pages'               => ['Seiten',          '📖'],
        'book_format'              => ['Format',          '📦'],
        'book_isbn_13'             => ['ISBN-13',         '🔢'],
        'book_isbn_10'             => ['ISBN-10',         '🔢'],
        'book_asin'                => ['ASIN',            '🏷️'],
        'book_language'            => ['Sprache',         '🌐'],
        'book_country'             => ['Land',            '🌍'],
        'book_dimension'           => ['Abmessungen',     '📐'],
        'print_length'             => ['Drucklänge',      '📏'],
        'book_availability_status' => ['Verfügbarkeit',   '✅'],
    ];

    ob_start();
    ?>
    <div class="book-details-shortcode" style="display:flex;flex-wrap:wrap;gap:30px;">
        <div style="flex:0 0 300px;max-width:300px;">
            <?php if ($cover_url) : ?>
                <?php if ($buy_link) echo '<a href="' . esc_url($buy_link) . '" target="_blank" rel="nofollow">'; ?>
                <img src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" style="width:100%;border-radius:8px;box-shadow:0 4px 15px rgba(0,0,0,0.2);">
                <?php if ($buy_link) echo '</a>'; ?>
            <?php endif; ?>
            <?php if ($price) : ?>
                <div style="text-align:center;margin-top:15px;"><span style="font-size:24px;font-weight:700;color:#39b152;">€<?php echo esc_html($price); ?></span></div>
            <?php endif; ?>
            <?php if ($buy_link) : ?>
                <div style="text-align:center;margin-top:10px;">
                    <a href="<?php echo esc_url($buy_link); ?>" target="_blank" rel="nofollow" style="display:inline-block;background:#39b152;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;font-size:16px;">🛒 <?php echo esc_html($buy_text); ?></a>
                </div>
            <?php endif; ?>
        </div>
        <div style="flex:1;min-width:300px;">
            <h2 style="margin:0 0 10px;font-size:28px;line-height:1.3;"><?php echo get_the_title($post_id); ?></h2>
            <div style="margin-bottom:15px;">
                <a href="<?php echo esc_url($reviews_link); ?>" style="text-decoration:none;display:inline-flex;align-items:center;gap:5px;" title="Bewertungen anzeigen">
                    <?php echo $stars_html; ?>
                    <span style="color:#666;font-size:14px;margin-left:5px;">
                        <?php if ($avg_rating > 0) : ?>
                            <?php echo esc_html($avg_rating); ?>/5 (<?php echo $review_count; ?> Bewertung<?php echo $review_count !== 1 ? 'en' : ''; ?>)
                        <?php else : ?>
                            Noch keine Bewertungen
                        <?php endif; ?>
                    </span>
                </a>
            </div>
            <?php $authors = get_the_terms($post_id, 'book-author');
            if ($authors && !is_wp_error($authors)) : foreach ($authors as $author) : ?>
                <p style="font-size:18px;color:#666;margin-bottom:15px;">Von <a href="<?php echo get_term_link($author); ?>" style="color:#39b152;text-decoration:none;font-weight:600;"><?php echo esc_html($author->name); ?></a></p>
            <?php endforeach; endif; ?>
            <?php if ($desc) : ?>
                <p style="font-size:16px;line-height:1.6;margin-bottom:20px;color:#333;"><?php echo nl2br(esc_html($desc)); ?></p>
            <?php endif; ?>
            <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                <?php foreach ($fields as $key => $info) :
                    $value = get_post_meta($post_id, '_rsbs_' . $key, true);
                    if ($value) :
                        if ($key === 'book_availability_status') { $value = ($value === 'available') ? 'Verfügbar' : 'Demnächst'; }
                ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:8px 0;font-weight:600;color:#555;width:40%;"><?php echo $info[1] . ' ' . $info[0]; ?></td>
                        <td style="padding:8px 0;color:#333;"><?php echo esc_html($value); ?></td>
                    </tr>
                <?php endif; endforeach; ?>
            </table>
            <?php if ($orig_name) : ?>
                <div style="background:#f9f9f9;padding:15px;border-radius:8px;margin-bottom:20px;">
                    <strong>Originaltitel:</strong> <?php if ($orig_url) : ?><a href="<?php echo esc_url($orig_url); ?>"><?php echo esc_html($orig_name); ?></a><?php else : ?><?php echo esc_html($orig_name); ?><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php $categories = get_the_terms($post_id, 'book-category');
            if ($categories && !is_wp_error($categories)) : ?>
                <div style="margin-bottom:20px;"><strong>Kategorien:</strong>
                    <?php foreach ($categories as $cat) : ?>
                        <a href="<?php echo get_term_link($cat); ?>" style="display:inline-block;background:#e8f5e9;color:#39b152;padding:4px 12px;border-radius:20px;margin:2px;text-decoration:none;font-size:14px;"><?php echo esc_html($cat->name); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $rezensionen = get_posts([
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'meta_key'       => '_linked_book_id',
            'meta_value'     => $post_id,
            'posts_per_page' => 5,
            'fields'         => 'ids',
        ]);
        if (!empty($rezensionen)) :
        ?>
            <div style="margin-top:30px;padding:20px;background:#f9f9f9;border-radius:8px;">
                <h3 style="margin:0 0 15px;font-size:22px;">📖 Rezensionen</h3>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($rezensionen as $rid) : ?>
                        <li style="margin-bottom:8px;">
                            <a href="<?php echo esc_url(get_permalink($rid)); ?>" style="color:#39b152;text-decoration:none;font-weight:600;font-size:16px;">
                                <?php echo esc_html(get_the_title($rid)); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($atts['show_reviews'] === 'true') : ?>
        <div id="leserbewertungen">
            <?php echo do_shortcode('[rswpbs_reviews book_ids="' . $post_id . '" review_layout="grid" layout_style="classic" section_heading="Leserbewertungen"]'); ?>
        </div>
    <?php endif; ?>
    <?php if ($atts['show_content'] === 'true') : ?>
        <div style="margin-top:30px;" class="awt-dynamic-content"><?php echo apply_filters('the_content', get_post_field('post_content', $post_id)); ?></div>
    <?php endif; ?>
    <?php return ob_get_clean();
});


// ═══════════════════════════════════════════
// BUCHREZENSIONEN SHORTCODE + AJAX
// ═══════════════════════════════════════════

// AJAX Handler
add_action('wp_ajax_filter_buchrezensionen', 'filter_buchrezensionen_handler');
add_action('wp_ajax_nopriv_filter_buchrezensionen', 'filter_buchrezensionen_handler');

function filter_buchrezensionen_handler() {
    $rating = sanitize_text_field($_GET['rating'] ?? 'all');

    // Wenn Rating gefiltert wird: Bücher mit Reviews dieser Bewertung finden
    $linked_book_ids = [];
    if ($rating !== 'all') {
        $rating_int = intval($rating);
        $review_ids = get_posts([
            'post_type'      => 'book_reviews',
            'meta_key'       => '_rswpbs_rating',
            'meta_value'     => $rating_int,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ]);
        foreach ($review_ids as $rid) {
            $bid = get_post_meta($rid, '_rswpbs_reviewed_book', true);
            if ($bid) $linked_book_ids[] = $bid;
        }
        $linked_book_ids = array_unique($linked_book_ids);

        if (empty($linked_book_ids)) {
            echo '<p>Keine Rezensionen mit dieser Bewertung gefunden.</p>';
            wp_die();
        }
    }

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => intval($_GET['per_page'] ?? 8),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    // Rating-Filter auf Posts anwenden (verlinkte Bücher)
    if (!empty($linked_book_ids)) {
        $args['meta_key']   = '_linked_book_id';
        $args['meta_value'] = implode(',', $linked_book_ids);
        $args['meta_compare'] = 'IN';
    }

    // Kategorie filtern
    if (!empty($_GET['category']) && $_GET['category'] !== 'all') {
        $args['category_name'] = sanitize_text_field($_GET['category']);
    } else {
        $args['category_name'] = 'book-review';
    }

    // Autor filtern
    if (!empty($_GET['author']) && $_GET['author'] !== 'all') {
        $args['author'] = intval($_GET['author']);
    }

    // Suche
    if (!empty($_GET['s'])) {
        $args['s'] = sanitize_text_field($_GET['s']);
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        echo '<p>Keine Rezensionen gefunden.</p>';
        wp_die();
    }

    echo '<div class="rswpbs-row" style="display:flex;flex-wrap:wrap;gap:20px;">';
    while ($query->have_posts()) {
        $query->the_post();
        $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');

        // Bewertung des verlinkten Buches holen
        $book_id = get_post_meta(get_the_ID(), '_linked_book_id', true);
        $review_rating = '';
        if ($book_id) {
            $reviews = get_posts([
                'post_type'      => 'book_reviews',
                'meta_key'       => '_rswpbs_reviewed_book',
                'meta_value'     => $book_id,
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'fields'         => 'ids',
            ]);
            if (!empty($reviews)) {
                $rating_val = get_post_meta($reviews[0], '_rswpbs_rating', true);
                if ($rating_val) {
                    for ($s = 1; $s <= 5; $s++) {
                        $review_rating .= ($s <= $rating_val) ? '⭐' : '☆';
                    }
                }
            }
        }

        echo '<div class="rezension-card" style="flex:1;min-width:280px;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.1);">';
        if ($thumb) {
            echo '<a href="' . get_permalink() . '"><img src="' . esc_url($thumb) . '" style="width:100%;height:200px;object-fit:cover;"></a>';
        }
        echo '<div style="padding:15px;">';
        echo '<h3 style="margin:0 0 10px;"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
        if ($review_rating) echo '<p style="margin-bottom:8px;">' . $review_rating . '</p>';
        echo '<p style="color:#666;font-size:14px;">' . get_the_date() . '</p>';
        echo '<p>' . wp_trim_words(get_the_excerpt(), 20) . '</p>';
        echo '<a href="' . get_permalink() . '" style="color:#39b152;font-weight:600;">Weiterlesen &rarr;</a>';
        echo '</div></div>';
    }
    echo '</div>';
    wp_reset_postdata();
    wp_die();
}

// Shortcode
add_shortcode('buchrezensionen', function ($atts) {
    $atts = shortcode_atts(['per_page' => 8], $atts);

    $categories = get_categories(['taxonomy' => 'category', 'hide_empty' => true, 'orderby' => 'name']);
    $authors = get_users(['role__in' => ['author', 'editor', 'administrator']]);

    ob_start();
    ?>
    <div id="rezensionen-wrapper">
        <div class="rezensionen-filter" style="background:#f9f9f9;padding:20px;border-radius:8px;margin-bottom:20px;">
            <div style="display:flex;flex-wrap:wrap;gap:15px;align-items:end;">
                <div style="flex:1;min-width:200px;">
                    <label for="rez-filter-search" style="display:block;font-weight:600;margin-bottom:5px;">Suche</label>
                    <input type="text" id="rez-filter-search" placeholder="Buchtitel oder Autor suchen..." style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:6px;">
                </div>
                <div style="flex:1;min-width:150px;">
                    <label for="rez-filter-rating" style="display:block;font-weight:600;margin-bottom:5px;">Bewertung</label>
                    <select id="rez-filter-rating" style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:6px;">
                        <option value="all">Alle Bewertungen</option>
                        <option value="5">⭐⭐⭐⭐⭐ (5 Sterne)</option>
                        <option value="4">⭐⭐⭐⭐ (4 Sterne)</option>
                        <option value="3">⭐⭐⭐ (3 Sterne)</option>
                        <option value="2">⭐⭐ (2 Sterne)</option>
                        <option value="1">⭐ (1 Stern)</option>
                    </select>
                </div>
                <div style="flex:1;min-width:150px;">
                    <label for="rez-filter-cat" style="display:block;font-weight:600;margin-bottom:5px;">Kategorie</label>
                    <select id="rez-filter-cat" style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:6px;">
                        <option value="book-review">Buchrezension</option>
                        <option value="all">Alle Kategorien</option>
                        <?php foreach ($categories as $cat) : ?>
                            <?php if ($cat->slug !== 'book-review') : ?>
                                <option value="<?php echo esc_attr($cat->slug); ?>"><?php echo esc_html($cat->name); ?> (<?php echo $cat->count; ?>)</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1;min-width:150px;">
                    <label for="rez-filter-author" style="display:block;font-weight:600;margin-bottom:5px;">Autor</label>
                    <select id="rez-filter-author" style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:6px;">
                        <option value="all">Alle Autoren</option>
                        <?php foreach ($authors as $author) : ?>
                            <option value="<?php echo esc_attr($author->ID); ?>"><?php echo esc_html($author->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="button" id="rez-filter-btn" style="background:#39b152;color:#fff;border:none;border-radius:6px;padding:10px 20px;font-size:14px;font-weight:600;cursor:pointer;">Filtern</button>
                    <button type="button" id="rez-filter-reset" style="background:#666;color:#fff;border:none;border-radius:6px;padding:10px 20px;font-size:14px;font-weight:600;cursor:pointer;">Zurücksetzen</button>
                </div>
            </div>
        </div>
        <div id="rezensionen-results">
            <p style="text-align:center;color:#999;">Laden...</p>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        var perPage = <?php echo intval($atts['per_page']); ?>;
        var ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';

        function loadReviews(params) {
            var data = {
                action: 'filter_buchrezensionen',
                per_page: perPage,
                category: params.category || 'book-review',
                author: params.author || 'all',
                rating: params.rating || 'all',
                s: params.s || ''
            };
            $('#rezensionen-results').html('<p style="text-align:center;color:#999;">Laden...</p>');
            $.get(ajaxUrl, data, function(response) {
                $('#rezensionen-results').html(response);
            });
        }

        loadReviews({ category: 'book-review' });

        $('#rez-filter-btn').on('click', function() {
            loadReviews({
                category: $('#rez-filter-cat').val(),
                author: $('#rez-filter-author').val(),
                rating: $('#rez-filter-rating').val(),
                s: $('#rez-filter-search').val()
            });
        });

        $('#rez-filter-search').on('keypress', function(e) {
            if (e.which === 13) $('#rez-filter-btn').click();
        });

        $('#rez-filter-reset').on('click', function() {
            $('#rez-filter-search').val('');
            $('#rez-filter-cat').val('book-review');
            $('#rez-filter-author').val('all');
            $('#rez-filter-rating').val('all');
            loadReviews({ category: 'book-review' });
        });
    });
    </script>
    <?php
    return ob_get_clean();
});
