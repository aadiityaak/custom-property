<?php

function create_wishlist_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wishlist';

    // Membuat query SQL untuk membuat tabel wishlist
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        id_user bigint(20) NOT NULL,
        id_property bigint(20) NOT NULL,
        status tinyint(1) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Menggunakan dbDelta untuk membuat tabel
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Menambahkan kode untuk memastikan tabel dibuat hanya sekali
$wishlist_table_version = get_option('wishlist_table_version');
if ($wishlist_table_version !== '1.0') {
    create_wishlist_table();
    add_option('wishlist_table_version', '1.0');
}


// Fungsi untuk menambahkan wishlist
function add_to_wishlist($id_user, $id_property, $status)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wishlist';

    $wpdb->insert(
        $table_name,
        array(
            'id_user' => $id_user,
            'id_property' => $id_property,
            'status' => $status
        ),
        array(
            '%d',
            '%d',
            '%d'
        )
    );
}

// Fungsi untuk mendapatkan wishlist
function get_wishlist_by_user($id_user)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wishlist';

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id_user = %d",
            $id_user
        )
    );

    return $results;
}

// Fungsi untuk menghapus wishlist
function delete_wishlist($id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wishlist';

    $wpdb->delete(
        $table_name,
        array('id' => $id),
        array('%d')
    );
}

// Fungsi AJAX untuk menambahkan wishlist
function ajax_add_to_wishlist()
{
    // Memastikan pengguna sudah login
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in to add to wishlist.', 'status' => 2));
        wp_die();
    }

    // Mengambil data dari AJAX request
    $id_user = get_current_user_id();
    $id_property = intval($_POST['id_property']);
    $status = get_wishlist_by_user($id_user, $id_property);

    if ($status) {
        delete_wishlist($status[0]->id);
        wp_send_json_success(array('message' => 'Property removed from wishlist.', 'status' => 0));
        wp_die();
    } else {
        $status = 1; // Status default untuk wishlist
    }

    // Memanggil fungsi untuk menambahkan wishlist
    add_to_wishlist($id_user, $id_property, $status);

    wp_send_json_success(array('message' => 'Property added to wishlist.', 'status' => 1));
    wp_die();
}
add_action('wp_ajax_add_to_wishlist', 'ajax_add_to_wishlist');


// Shortcode untuk tombol wishlist
function wishlist_button_shortcode($atts)
{
    global $post;
    $atts = shortcode_atts(array(
        'property_id' => $post->ID
    ), $atts, 'wishlist_button');

    if (!is_user_logged_in()) {
        // return '<p>You must be logged in to add to wishlist.</p>';
    }

    $property_id = intval($atts['property_id']);
    $status = get_wishlist_by_user(get_current_user_id(), $property_id);
    $icon = $status ? 'fas fa-heart' : 'far fa-heart';
    $button = '<button class="wishlist-button btn btn-outline-primary" data-property-id="' . esc_attr($property_id) . '"><i class="' . $icon . '" style="color: #D0036E;"></i> Add to Wishlist</button>';

    return $button;
}
add_shortcode('wishlist_button', 'wishlist_button_shortcode');
