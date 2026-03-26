<?php
/**
 * Yoast SEO: og:image für Buchseiten dynamisch setzen
 * 
 * Problem: /buchseite/?book_id=X ist eine einzige WP-Seite.
 * Yoast kennt das individuelle Buch nicht → kein og:image.
 * 
 * Lösung: Hook in wpseo_opengraph, der das Cover-Bild des
 * aktuellen Buches als og:image ausgibt.
 */

if (!defined('ABSPATH')) exit;

// og:image für Buchseiten (via ?book_id=)
add_action('wpseo_opengraph', function () {
    // Nur auf der Buchseite mit book_id Parameter
    if (!isset($_GET['book_id'])) return;
    
    $book_id = intval($_GET['book_id']);
    if (!$book_id || get_post_type($book_id) !== 'book') return;
    
    $image_url = get_the_post_thumbnail_url($book_id, 'large');
    if (!$image_url) return;
    
    // Yoast unterdrücken und eigenes og:image setzen
    // Wir entfernen Yoast's og:image und setzen unser eigenes
    echo '<meta property="og:image" content="' . esc_url($image_url) . '" />' . "\n";
    echo '<meta property="og:image:width" content="600" />' . "\n";
    echo '<meta property="og:image:height" content="900" />' . "\n";
    
    // Auch Twitter Card
    echo '<meta name="twitter:image" content="' . esc_url($image_url) . '" />' . "\n";
    
    // Buchtitel für og:title überschreiben
    $book_title = get_the_title($book_id);
    if ($book_title) {
        echo '<meta property="og:title" content="' . esc_attr($book_title) . ' - Buchversteher.de" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($book_title) . ' - Buchversteher.de" />' . "\n";
    }
    
    // og:description aus Buchbeschreibung
    $desc = get_post_meta($book_id, '_rsbs_short_description', true);
    if ($desc) {
        $desc = wp_trim_words($desc, 30);
        echo '<meta property="og:description" content="' . esc_attr($desc) . '" />' . "\n";
        echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
    }
});

// og:image für Blog-Posts: Buchcover aus verlinktem Buch übernehmen
add_filter('wpseo_opengraph_image', function ($image_url) {
    if ($image_url) return $image_url; // Bereits gesetzt (Featured Image)
    
    global $post;
    if (!$post) return $image_url;
    
    // 1. Prüfen ob Post ein verlinktes Buch hat → Cover verwenden
    $linked_book_id = get_post_meta($post->ID, '_linked_book_id', true);
    if ($linked_book_id) {
        $book_cover = get_the_post_thumbnail_url($linked_book_id, 'large');
        if ($book_cover) return $book_cover;
    }
    
    // 2. Fallback: Erstes Bild aus dem Content
    $content = $post->post_content;
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
        return $matches[1];
    }
    
    return $image_url;
}, 10, 1);
