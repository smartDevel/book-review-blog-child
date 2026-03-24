<?php
/**
 * Frontend JavaScript & CSS
 * 
 * - Suchformular Toggle
 * - Select2 Labels
 * - Auto-Submit
 * - Review Form Toggle
 */

if (!defined('ABSPATH')) exit;

add_action('wp_footer', function () { ?>
<style>
.rswpbs-books-search-form input[type="submit"] {
    background: #39b152;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
.rswpbs-books-search-form input[type="submit"]:hover {
    background: #2d8a41;
}
.search-form-toggle,
.review-form-toggle {
    display: inline-block;
    background: #39b152;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 15px;
    transition: background 0.3s;
}
.search-form-toggle:hover,
.review-form-toggle:hover {
    background: #2d8a41;
}
</style>
<script>
jQuery(document).ready(function($) {

    /* Toggle Suchmaske */
    var $formArea = $('.rswpbs-advanced-search-form-area');
    if ($formArea.length) {
        var isOpen = localStorage.getItem('searchFormOpen') === 'true';
        if (isOpen) { $formArea.show(); } else { $formArea.hide(); }
        var btnText = $formArea.is(':visible') ? '🔍 Suche ausblenden' : '🔍 Suche einblenden';
        var $toggle = $('<button type="button" class="search-form-toggle">' + btnText + '</button>');
        $formArea.before($toggle);
        $toggle.on('click', function() {
            $formArea.slideToggle(300, function() {
                if ($formArea.is(':visible')) {
                    $toggle.text('🔍 Suche ausblenden');
                    localStorage.setItem('searchFormOpen', 'true');
                } else {
                    $toggle.text('🔍 Suche einblenden');
                    localStorage.setItem('searchFormOpen', 'false');
                }
            });
        });
    }

    /* Buchname Placeholder */
    var bookName = document.querySelector('input[name="book_name"]');
    if (bookName) bookName.placeholder = 'Buchname suchen...';

    /* Select Labels */
    var labels = {
        'category': 'Kategorie', 'author': 'Autor', 'format': 'Format',
        'publish_year': 'Erscheinungsjahr', 'series': 'Serie',
        'publisher': 'Verlag', 'language': 'Sprache'
    };
    $('.search-field select').each(function() {
        var name = $(this).attr('name');
        if (labels[name]) {
            try { $(this).select2('destroy'); } catch(e) {}
            $(this).find('option[value="all"]').text(labels[name] + ' wählen...');
            $(this).select2({
                searchField: ['text', 'value'], persist: false, create: false,
                allowEmptyOption: true, allowClear: true,
                placeholder: labels[name] + ' wählen...'
            });
        }
    });

    /* Auto-Submit */
    $('.search-field select').on('change', function() {
        $('#rswpbs-books-search-form').submit();
    });

    /* Sort Labels */
    var sortLabels = {
        'default': 'Sortieren...', 'price_asc': 'Preis aufsteigend',
        'price_desc': 'Preis absteigend', 'title_asc': 'Titel A-Z',
        'title_desc': 'Titel Z-A', 'date_asc': 'Datum aufsteigend',
        'date_desc': 'Datum absteigend'
    };
    var $sort = $('#rswpbs-sort');
    $sort.find('option').each(function() {
        var val = $(this).val();
        if (sortLabels[val]) $(this).text(sortLabels[val]);
    });
    $sort.select2({ placeholder: 'Sortieren...', allowClear: false });
    $sort.off('change').on('change', function() {
        $('#rswpbs-sortby').val(this.value);
        $('#rswpbs-books-search-form').submit();
    });

    /* Review Form Labels */
    var reviewLabels = {
        'review_title': 'Bewertungstitel', 'reviewer_name': 'Dein Name',
        'reviewer_email': 'Deine E-Mail', 'rating': 'Bewertung',
        'reviewer_comment': 'Deine Meinung'
    };
    for (var fieldId in reviewLabels) {
        var $label = $('label[for="' + fieldId + '"]');
        if ($label.length && !$label.text().trim()) $label.text(reviewLabels[fieldId]);
    }

    /* Review Form Toggle */
    var $reviewForm = $('#rswpbs-review-form');
    if ($reviewForm.length) {
        $reviewForm.hide();
        var $reviewToggle = $('<button type="button" class="review-form-toggle">💬 Eigene Bewertung schreiben</button>');
        $reviewForm.before($reviewToggle);
        $reviewToggle.on('click', function() {
            $reviewForm.slideToggle(300, function() {
                if ($reviewForm.is(':visible')) {
                    $reviewToggle.text('✖ Bewertung ausblenden');
                } else {
                    $reviewToggle.text('💬 Eigene Bewertung schreiben');
                }
            });
        });
        var $reviewSubmit = $reviewForm.find('.submit-btn');
        if ($reviewSubmit.length) $reviewSubmit.val('💬 Bewertung speichern');
    }

    /* Nur ISBN-10 ausblenden (ISBN-13 behalten) */
    $('#book-isbn-10').closest('.rswpbs-col-lg-3, .rswpbs-col-lg-4, [class*="col"]').hide();
    $('#book-isbn-10').closest('.search-field').parent().hide();

    /* Rating-Filter: AJAX-basierte Client-Filterung */
    var $ratingSelect = $('<select id="filter-rating" style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:6px;">' +
        '<option value="all">Alle Bewertungen</option>' +
        '<option value="5">⭐⭐⭐⭐⭐ (5 Sterne)</option>' +
        '<option value="4">⭐⭐⭐⭐ (4 Sterne)</option>' +
        '<option value="3">⭐⭐⭐ (3 Sterne)</option>' +
        '<option value="2">⭐⭐ (2 Sterne)</option>' +
        '<option value="1">⭐ (1 Stern)</option>' +
        '</select>');

    /* Vor dem Submit-Button einfügen */
    var $searchForm = $('.rswpbs-advanced-search-form-area .rswpbs-search-form');
    if ($searchForm.length) {
        var $submitCol = $searchForm.find('input[type="submit"]').closest('[class*="col"]');
        if ($submitCol.length) {
            $submitCol.before('<div class="rswpbs-col-lg-4 rswpbs-col-6 rswpbs-col-md-4"><div class="search-field">' + $ratingSelect[0].outerHTML + '</div></div>');
        }
    }

    /* AJAX: Ratings laden und data-attribute setzen */
    var ajaxUrl = (typeof rswpbsData !== 'undefined' && rswpbsData.ajaxurl) ? rswpbsData.ajaxurl : '/wp-admin/admin-ajax.php';
    $.get(ajaxUrl, { action: 'get_book_ratings' }, function(response) {
        var bookRatings = {};
        try {
            bookRatings = typeof response === 'string' ? JSON.parse(response) : response;
        } catch(e) { return; }

        /* Jede Buchkarte mit data-book-rating versehen */
        $('.rswpbs-books-showcase-book-loop-container a[href*="book_id"]').each(function() {
            var href = $(this).attr('href');
            var match = href.match(/book_id=(\d+)/);
            if (match) {
                var bookId = match[1];
                if (bookRatings[bookId]) {
                    $(this).closest('[class*="rswpbs-col"]').attr('data-book-rating', bookRatings[bookId]);
                }
            }
        });
    });

    /* Filterung */
    $(document).on('change', '#filter-rating', function() {
        var rating = $(this).val();
        var $allCards = $('.rswpbs-books-showcase-book-loop-container > .rswpbs-row > [class*="rswpbs-col"]');
        if (rating === 'all') {
            $allCards.show();
        } else {
            $allCards.each(function() {
                var r = $(this).attr('data-book-rating');
                if (r === rating) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });

    /* Search Button */
    $('#rswpbs-books-search-form input[type="submit"]').each(function() {
        if (!$(this).val()) $(this).attr('value', '🔍 Suchen');
    });
});
</script>
<?php });

