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
