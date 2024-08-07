<?php

/**
 * Masukkan semua function tambahan disini
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Custom_Plugin
 * @subpackage Custom_Plugin/includes
 */
// membuat taxonomy kategori demo untuk post type demo
function create_taxonomy_kategori_property()
{
    register_taxonomy(
        'categories_property',
        'property',
        array(
            'label' => __('Categories Property'),
            'rewrite' => array('slug' => 'categories-property'),
            'hierarchical' => true,
            'show_in_rest' => true
        )
    );
}
add_action('init', 'create_taxonomy_kategori_property');

// POST POPULER
// add post view count (menambah)
function add_view()
{
    global $post;
    $post_id = $post->ID;
    // echo 'a';
    $count = get_post_meta($post_id, 'view_count', true);
    if (!$count) {
        // echo 'b';
        delete_post_meta($post_id, 'view_count');
        add_post_meta($post_id, 'view_count', '1');
    } else {
        // echo 'c';
        update_post_meta($post_id, 'view_count', $count + 1);
    }
    // echo 'd';
    // print_r(get_post_meta( $post_id));
}
add_action('wp_footer', 'add_view');


// show post view (menampilkan)
function show_view()
{
    ob_start();
    global $post;
    $post_id = $post->ID;
    $count = get_post_meta($post_id, 'view_count', true);
    $count = $count ? $count : 0;
    echo '<span class="view-count">' . $count . ' Kali</span>';
    return ob_get_clean();
}
add_shortcode('show-view', 'show_view');
