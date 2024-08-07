<?php
class CMB2_Frontend_Form_Bs
{

    private $prefix = 'cp_';
    function initialize()
    {
        add_shortcode('cp-property', array($this, 'type'));
        add_action('init', array($this, 'allow_subscriber_uploads'));
        add_action('pre_get_posts', array($this, 'restrict_media_library'));
        add_action('cmb2_init', array($this, 'register_property_frontend_form'));
        add_action('admin_post_delete_property', array($this, 'handle_delete_property'));
    }

    /**
     * Shortcode to display a CMB2 form for a post ID.
     * Adding this shortcode to your WordPress editor would look something like this:
     *
     * [cmb-form id="test_metabox" post_id=2]
     *
     * The shortcode requires a metabox ID, and (optionally) can take
     * a WordPress post ID (or user/comment ID) to be editing.
     *
     * @param  array  $atts Shortcode attributes
     * @return string       Form HTML markup
     */

    function type($atts = array())
    {
        $type = $atts['type'] ?? 'list';
        if ($type === 'list') {
            return $this->archive_property();
        } else if ($type === 'submit') {
            return $this->form();
        }
    }

    function form($atts = array())
    {

        // Current user
        $user_id = get_current_user_id();

        // Use ID of metabox in wds_frontend_form_register
        $metabox_id = $atts['id'] ?? $this->prefix . 'property_frontend_form';

        // since post ID will not exist yet, just need to pass it something
        $object_id = $_POST['post_id'] ?? 'new-object-id';
        $object_id = $_GET['post_id'] ?? $object_id;

        // Get CMB2 metabox object
        $cmb = cmb2_get_metabox($metabox_id, $object_id);

        if (empty($cmb))
            return 'Metabox ID not found';

        // Get $cmb object_types
        $post_types = $cmb->prop('object_types');

        // Parse attributes. These shortcode attributes can be optionally overridden.
        $atts = shortcode_atts(array(
            'ID'            => $object_id !== 'new-object-id' ? $object_id : 0,
            'post_author'   => $user_id ? $user_id : 1,
            'post_status'   => 'publish',
            'post_type'     => reset($post_types),
        ), $atts, 'cmb-frontend-form');

        // Initiate our output variable
        $output = '';

        $new_id = $this->handle_submit($cmb, $atts);
        if ($new_id) {

            if (is_wp_error($new_id)) {

                // If there was an error with the submission, add it to our ouput.
                $output .= '<div class="alert alert-warning">' . sprintf(__('There was an error in the submission: %s', 'cmb2-post-submit'), '<strong>' . $new_id->get_error_message() . '</strong>') . '</div>';
            } else {

                // Add notice of submission
                return '<div class="alert alert-success">' . sprintf(__('<strong>%s</strong>, submitted successfully.  <a href="%s">View Property</a>.', 'cmb2-post-submit'), esc_html(get_the_title($new_id)), get_permalink($new_id)) . '</div>';
            }
        }

        // Get our form
        $form = cmb2_get_metabox_form($cmb, $object_id, array('save_button' => __('Submit', 'cmb2-post-submit')));

        // Format our form use Bootstrap 5
        $styling = [
            'regular-text'              => 'regular-text form-control',
            'cmb2-text-small'           => 'cmb2-text-small form-control',
            'cmb2-text-medium'          => 'cmb2-text-medium form-control',
            'cmb2-timepicker'           => 'cmb2-timepicker form-control d-inline-block',
            'cmb2-datepicker'           => 'cmb2-datepicker d-inline-block',
            'cmb2-text-money'           => 'cmb2-text-money form-control d-inline-block',
            'cmb2_textarea'             => 'cmb2_textarea form-control',
            'cmb2-textarea-small'       => 'cmb2-textarea-small form-control d-inline-block',
            'cmb2_select'               => 'cmb2_select form-select',
            'cmb2-upload-file regular-text'         => 'cmb2-upload-file regular-text d-none w-100',
            'type="radio" class="cmb2-option"'      => 'type="radio" class="cmb2-option form-check-input"',
            'type="checkbox" class="cmb2-option"'   => 'type="checkbox" class="cmb2-option form-check-input"',
            'class="button-primary"'                => 'class="button-primary btn btn-primary float-end"',
            'cmb2-metabox-description'              => 'cmb2-metabox-description fw-normal small',
            'class="cmb-th"'                        => 'class="cmb-th w-100 p-0"',
            'class="cmb-td"'                        => 'class="cmb-th w-100 p-0 pb-2"',
            'class="cmb-add-row"'                   => 'class="cmb-add-row text-end"',
            'button-secondary'                      => 'button-secondary btn-sm btn btn-outline-secondary',
            'cmb2-upload-button'                    => 'cmb2-upload-button ms-0 mt-1',
            'button-secondary btn-sm btn btn-outline-secondary cmb-remove-row-button'   => 'button-secondary btn btn-danger cmb-remove-row-button',
        ];
        foreach ($styling as $std => $newf) {
            $form = str_replace($std, $newf, $form);
        }

        $output .= $form;

        // jika tidak login
        if (!is_user_logged_in()) {
            return '<div class="alert alert-warning">Silahkan login untuk menambahkan properti.</div>';
        }
        return $output;
    }
    function handle_delete_property()
    {
        // Pastikan nonce dan parameter ID valid
        if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_property_nonce')) {
            wp_die('Invalid request');
        }

        $id = intval($_GET['id']);
        // Hapus post
        wp_delete_post($id, true);

        // Redirect kembali ke halaman daftar
        wp_redirect(admin_url('edit.php?post_type=property'));
        exit();
    }
    function archive_property()
    {
        $paged = isset($_GET['halaman']) ? $_GET['halaman'] : 1;
        $submit_page = get_option('submit_page');
        $list_page = get_option('list_page');
        echo $list_page;
        $user_id = get_current_user_id();
        $args = array(
            'post_type' => 'property',
            'posts_per_page' => 20,
            'paged' => $paged,
            'orderby' => 'title',
            'order' => 'ASC',
            'author' => $user_id
        );

        $query = new WP_Query($args);
        ob_start();

        // The Loop
?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $delete_url = wp_nonce_url(
                            add_query_arg(array(
                                'action' => 'delete_property',
                                'id' => get_the_ID(),
                            ), admin_url('admin-post.php')),
                            'delete_property_nonce',
                            '_wpnonce'
                        );
                ?>
                        <tr>
                            <th scope="row"><?php the_ID(); ?></th>
                            <td><?php the_title(); ?></td>
                            <td>
                                <?php
                                $harga = get_post_meta(get_the_ID(), 'cp_status-properti', true);
                                echo $harga;
                                ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
                                            <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z" />
                                            <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z" />
                                        </svg></a>
                                    <a href="<?php echo get_the_permalink($submit_page); ?>?post_id=<?php the_ID(); ?>" class="btn btn-warning btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                        </svg></a>
                                    <a href="<?php echo $delete_url; ?>" class="btn btn-danger btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                        </svg></a>
                                </div>
                            </td>
                        </tr>
                <?php
                    }
                }
                wp_reset_postdata();
                ?>
            </tbody>
        </table>
        <?php
        // custom pagination with foreach
        if ($query->max_num_pages > 1) {
        ?>
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <li class="page-item"><a class="page-link" href="?halaman=1">First</a></li>
                    <?php
                    for ($i = 1; $i <= $query->max_num_pages; $i++) {
                    ?>
                        <li class="page-item"><a class="page-link" href="?halaman=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                    <?php
                    }
                    ?>
                    <li class="page-item"><a class="page-link" href="?halaman=1">Last</a></li>
                </ul>
            </nav>
<?php
        }

        return ob_get_clean();
    }

    public function register_property_frontend_form()
    {
        global $post;

        $cmb_property = new_cmb2_box(array(
            'id'           => $this->prefix . 'property_frontend_form',
            'object_types' => array('property'), // Tipe objek adalah 'property'
            'hookup'       => false,
            'save_fields'  => false, // Kami akan menyimpan field secara manual
        ));

        $title = isset($post->ID) ? get_post_meta($post->ID, $this->prefix . 'title', true) : '';
        $property_meta_title = $_POST[$this->prefix . 'title'] ?? $title;
        $cmb_property->add_field(array(
            'name'    => 'Nama Properti',
            'id'      => $this->prefix . 'title',
            'type'    => 'text',
            'default' => $property_meta_title
        ));

        $cmb_property->add_field(array(
            'name'    => 'Deskripsi Properti',
            'id'      => $this->prefix . 'description',
            'type'    => 'wysiwyg',
        ));

        $cmb_property->add_field(array(
            'name'    => 'Harga Properti',
            'id'      => $this->prefix . 'price',
            'type'    => 'text',
        ));

        // Retrieve terms from the 'categories_property' taxonomy
        $terms = get_terms(array(
            'taxonomy'   => 'categories_property',
            'hide_empty' => false,
        ));

        $options = array();

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $options[$term->slug] = $term->name;
            }
        }

        // Add CMB2 field with dynamic options
        $cmb_property->add_field(array(
            'name'              => 'Kategori',
            'id'                => $this->prefix . 'kategori',
            'type'              => 'select',
            'show_option_none'  => 'Pilih Kategori',
            'options'           => $options,
        ));

        $cmb_property->add_field(array(
            'name'              => 'Status Properti',
            'id'                => $this->prefix . 'status-properti',
            'type'              => 'select',
            'show_option_none'  => 'Pilih Status',
            'options'           => array(
                'Tersedia'      => esc_html__('Tersedia', 'cmb2'),
                'Terjual'        => esc_html__('Terjual', 'cmb2'),
                'Tersewa'        => esc_html__('Tersewa', 'cmb2'),
            ),
        ));

        $cmb_property->add_field(array(
            'name'              => 'Jenis Properti',
            'id'                => $this->prefix . 'jenis-properti',
            'type'              => 'select',
            'show_option_none'  => 'Pilih Jenis Properti',
            'options'           => array(
                'Dijual'      => esc_html__('Dijual', 'cmb2'),
                'Disewakan'        => esc_html__('Disewakan', 'cmb2'),
            ),
        ));

        $province = isset($post->ID) ? get_post_meta($post->ID, $this->prefix . 'province', true) : '';
        $property_meta_province = $_POST[$this->prefix . 'province'] ?? $province;
        $cmb_property->add_field(array(
            'name'    => 'Povinsi',
            'id'      => $this->prefix . 'province',
            'type'    => 'select',
            'options' => ['' => 'Loading...'],
            'attributes' => [
                'data-current' => $property_meta_province
            ]
        ));

        $city = isset($post->ID) ? get_post_meta($post->ID, $this->prefix . 'city', true) : '';
        $property_meta_city = $_POST[$this->prefix . 'city'] ?? $city;
        $cmb_property->add_field(array(
            'name'    => 'Kota',
            'id'      => $this->prefix . 'city',
            'type'    => 'select',
            'options' => ['' => 'Loading...'],
            'attributes' => [
                'data-current' => $property_meta_city
            ]
        ));

        $district = isset($post->ID) ? get_post_meta($post->ID, $this->prefix . 'district', true) : '';
        $property_meta_district = $_POST[$this->prefix . 'district'] ?? $district;
        $cmb_property->add_field(array(
            'name'    => 'Kecamatan',
            'id'      => $this->prefix . 'district',
            'type'    => 'select',
            'options' => ['' => 'Loading...'],
            'attributes' => [
                'data-current' => $property_meta_district
            ]
        ));

        $cmb_property->add_field(array(
            'name'    => 'Address',
            'id'      => $this->prefix . 'address',
            'type'    => 'textarea',
            'attributes' => [
                'rows' => 2
            ]
        ));

        $cmb_property->add_field(array(
            'name' => __('Luas Tanah', 'theme-domain'),
            'desc' => __('m2', 'msft-newscenter'),
            'id'   => $this->prefix . 'luas-tanah',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
            ),
            'sanitization_cb' => 'absint',
            'escape_cb'       => 'absint',
        ));

        $cmb_property->add_field(array(
            'name' => __('Luas Bangunan', 'theme-domain'),
            'desc' => __('m2', 'msft-newscenter'),
            'id'   => $this->prefix . 'luas-bangunan',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
            ),
            'sanitization_cb' => 'absint',
            'escape_cb'       => 'absint',
        ));

        $cmb_property->add_field(array(
            'name' => __('Jumlah Kamar Tidur', 'theme-domain'),
            'desc' => __('contoh: 2', 'msft-newscenter'),
            'id'   => $this->prefix . 'jumlah-kamar-tidur',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
            ),
            'sanitization_cb' => 'absint',
            'escape_cb'       => 'absint',
        ));

        $cmb_property->add_field(array(
            'name' => __('Jumlah Kamar Mandi', 'theme-domain'),
            'desc' => __('contoh: 2', 'msft-newscenter'),
            'id'   => $this->prefix . 'jumlah-kamar-mandi',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
            ),
            'sanitization_cb' => 'absint',
            'escape_cb'       => 'absint',
        ));

        $cmb_property->add_field(array(
            'name' => __('Jumlah Lantai', 'theme-domain'),
            'desc' => __('contoh: 2', 'msft-newscenter'),
            'id'   => $this->prefix . 'jumlah-lantai',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
            ),
            'sanitization_cb' => 'absint',
            'escape_cb'       => 'absint',
        ));

        $cmb_property->add_field(array(
            'name' => __('Fasilitas', 'theme-domain'),
            'desc' => __('Tambahkan fasilitas yang tersedia', 'msft-newscenter'),
            'id'   => $this->prefix . 'fasilitas_group',
            'type' => 'group',
            'options' => array(
                'group_title'   => __('Fasilitas {#}', 'theme-domain'), // {#} akan digantikan dengan nomor item
                'add_button'    => __('Tambah Fasilitas', 'theme-domain'),
                'remove_button' => __('Hapus Fasilitas', 'theme-domain'),
                'sortable'      => true, // membuat item bisa diurutkan
            ),
            'fields' => array(
                array(
                    'name' => __('Fasilitas', 'theme-domain'),
                    'id'   => 'fasilitas',
                    'type' => 'text',
                    'attributes' => array(
                        'placeholder' => 'Tuliskan fasilitas yang tersedia.',
                    ),
                ),
            ),
        ));

        $cmb_property->add_field(array(
            'name' => __('Kemudahan Akses Ke', 'theme-domain'),
            'desc' => __('Tambahkan kemudahan akses nya', 'msft-newscenter'),
            'id'   => $this->prefix . 'kemudahan_akses_ke_group',
            'type' => 'group',
            'options' => array(
                'group_title'   => __('Kemudahan Akses Ke {#}', 'theme-domain'), // {#} akan digantikan dengan nomor item
                'add_button'    => __('Tambah Kemudahan', 'theme-domain'),
                'remove_button' => __('Hapus Kemudahan', 'theme-domain'),
                'sortable'      => true, // membuat item bisa diurutkan
            ),
            'fields' => array(
                array(
                    'name' => __('Kemudahan Akses Ke', 'theme-domain'),
                    'id'   => 'kemudahan_akses_ke',
                    'type' => 'text',
                    'attributes' => array(
                        'placeholder' => 'Tuliskan kemudahan akses nya.',
                    ),
                ),
            ),
        ));

        // $cmb_property->add_field(array(
        //     'name' => __('Kemudahan Akses Ke', 'theme-domain'),
        //     'desc' => __('Gunakan tanda koma untuk memisahkan item', 'msft-newscenter'),
        //     'id'   => $this->prefix . 'kemudahan-akses-ke',
        //     'type' => 'text',
        //     'attributes' => array(
        //         'placeholder' => 'Jalan Tol, Sekolah, Rumah Sakit',
        //     ),
        // ));

        $cmb_property->add_field(array(
            'name'    => 'Featured Image',
            'id'      => $this->prefix . 'featured_image',
            'type'    => 'file',
            'text'    => array(
                'add_more' => 'Add image',
                'remove'   => 'Remove image',
                'add_upload_files_text' => 'Add image',
            )
        ));

        $cmb_property->add_field(array(
            'name'    => 'Gallery',
            'id'      => $this->prefix . 'gallery',
            'type'    => 'file_list',
            'query_args' => array(
                'type' => 'image',
            ),
            'text'    => array(
                'add_more' => 'Add image',
                'remove'   => 'Remove image',
                'add_upload_files_text' => 'Add image',
            )
        ));
    }

    function handle_submit($cmb, $post_data = array())
    {

        // If no form submission, bail
        if (empty($_POST)) {
            return false;
        }
        // Fetch sanitized values
        $sanitized_values = $cmb->get_sanitized_values($_POST);
        // echo '<pre>'. print_r( $sanitized_values, true ) .'</pre>';
        // Set our post data arguments
        $post_data['post_title']   = $sanitized_values[$this->prefix . 'title'];
        $post_data['post_content'] = $sanitized_values[$this->prefix . 'description'];

        // Create the new post
        $new_post_id = wp_insert_post($post_data, true);

        if (is_wp_error($new_post_id)) {
            return $new_post_id;
        }

        //thumbnail
        if (!empty($sanitized_values[$this->prefix . 'featured_image_id'])) {
            set_post_thumbnail($new_post_id, $sanitized_values[$this->prefix . 'featured_image_id']);
        }

        // Loop through remaining (sanitized) data, and save to post-meta
        foreach ($sanitized_values as $key => $value) {
            update_post_meta($new_post_id, $key, $value);
        }

        return $new_post_id;
    }

    /**
     * Replace 'subscriber' with the required role to update, can also be contributor
     */
    function allow_subscriber_uploads()
    {
        if (is_admin()) {
            return;
        }
        /**
         * Replace 'subscriber' with the required role to update, can also be contributor
         */
        $subscriber = get_role('subscriber');

        // This is the only cap needed to upload files.
        $subscriber->add_cap('upload_files');
    }

    /**
     * Restricts the media library based on the current user's capabilities and the current page.
     *
     * @param object $wp_query_obj The WordPress query object.
     */
    function restrict_media_library($wp_query_obj)
    {
        if (is_admin()) {
            return;
        }

        global $current_user, $pagenow;

        if (!is_a($current_user, 'WP_User')) {
            return;
        }

        if ('admin-ajax.php' != $pagenow || 'query-attachments' != $_REQUEST['action']) {
            return;
        }

        if (!current_user_can('manage_media_library')) {
            $wp_query_obj->set('author', $current_user->ID);
        }
    }
}

$CMB2_Frontend_Form_Bs = new CMB2_Frontend_Form_Bs;
$CMB2_Frontend_Form_Bs->initialize();

// Remove the action hook
// remove_action('cmb2_init', array($CMB2_Frontend_Form_Bs, 'register_property_frontend_form'));