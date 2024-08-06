<?php
class CMB2_Frontend_Form_Bs {

    private $prefix = 'cp_';
    function initialize() {
        add_shortcode( 'cp-property', array( $this, 'type' ) );
        add_action( 'init', array( $this, 'allow_subscriber_uploads' ) );
        add_action( 'pre_get_posts', array( $this, 'restrict_media_library' ) );
        add_action( 'cmb2_init', array( $this, 'register_property_frontend_form' ) );
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

    function type( $atts = array() ) {
        $type = $atts['type'] ?? 'list';
        if($type === 'list'){
            return $this->archive_property();
        } else if ($type === 'submit') {
            return $this->form();
        }
    }

    function form( $atts = array() ) {
        
        // Current user
        $user_id = get_current_user_id();

        // Use ID of metabox in wds_frontend_form_register
        $metabox_id = $atts['id'] ?? $this->prefix . 'property_frontend_form';

        // since post ID will not exist yet, just need to pass it something
        $object_id = $_GET['post_id'] ?? 'new-object-id';

        // Get CMB2 metabox object
        $cmb = cmb2_get_metabox( $metabox_id, $object_id );

        if(empty($cmb))
        return 'Metabox ID not found';

        // Get $cmb object_types
        $post_types = $cmb->prop( 'object_types' );

        // Parse attributes. These shortcode attributes can be optionally overridden.
        $atts = shortcode_atts( array(
            'ID'            => $object_id!=='new-object-id'?$object_id:0,
            'post_author'   => $user_id ? $user_id : 1,
            'post_status'   => 'publish',
            'post_type'     => reset( $post_types ),
        ), $atts, 'cmb-frontend-form' );

        // Initiate our output variable
        $output = '';
        
        $new_id = $this->handle_submit( $cmb, $atts );
        if ( $new_id ) {

            if ( is_wp_error( $new_id ) ) {

                // If there was an error with the submission, add it to our ouput.
                $output .= '<div class="alert alert-warning">' . sprintf( __( 'There was an error in the submission: %s', 'cmb2-post-submit' ), '<strong>'. $new_id->get_error_message() .'</strong>' ) . '</div>';

            } else {

                // Add notice of submission
                $output .= '<div class="alert alert-success">' . sprintf( __( '<strong>%s</strong>, submitted successfully.', 'cmb2-post-submit' ), esc_html( get_the_title($new_id) ) ) . '</div>';
            }

        }

        // Get our form
        $form = cmb2_get_metabox_form( $cmb, $object_id, array( 'save_button' => __( 'Submit', 'cmb2-post-submit' ) ) );
        
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
    function handle_delete_property() {
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
    function archive_property() {
        $paged = isset($_GET['halaman']) ? $_GET['halaman'] : 1;
        $submit_page = get_option('submit_page');
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

        // The Loop
        ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Action</th>
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
                        <a href="<?php the_permalink(); ?>" class="btn btn-primary">View</a>
                        <a href="<?php echo get_the_permalink($submit_page); ?>?post_id=<?php the_ID(); ?>" class="btn btn-warning">Edit</a>
                        <a href="<?php echo $delete_url; ?>" class="btn btn-danger">Delete</a>
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
                    <li class="page-item"><a class="page-link" href="?halaman=1">Previous</a></li>
                    <?php
                    for ($i = 1; $i <= $query->max_num_pages; $i++) {
                        ?>
                        <li class="page-item"><a class="page-link" href="?halaman=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                        <?php
                    }
                    ?>
                    <li class="page-item"><a class="page-link" href="?halaman=1">Next</a></li>
                    <li class="page-item"><a class="page-link" href="?halaman=1">Last</a></li>
                </ul>
            </nav>
            <?php
        }
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

        $title = isset($post->ID) ? get_post_meta( $post->ID, $this->prefix . 'title', true ) : '';
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

        $province = isset($post->ID) ? get_post_meta( $post->ID, $this->prefix . 'province', true ) : '';
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

        $city = isset($post->ID) ? get_post_meta( $post->ID, $this->prefix . 'city', true ) : '';
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

        $district = isset($post->ID) ? get_post_meta( $post->ID, $this->prefix . 'district', true ) : '';
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

    function handle_submit($cmb, $post_data = array()){

        // If no form submission, bail
        if ( empty( $_POST ) ) {
            return false;
        }
        // Fetch sanitized values
        $sanitized_values = $cmb->get_sanitized_values( $_POST );
        // echo '<pre>'. print_r( $sanitized_values, true ) .'</pre>';
        // Set our post data arguments
        $post_data['post_title']   = $sanitized_values[$this->prefix . 'title'];
        $post_data['post_content'] = $sanitized_values[$this->prefix . 'description'];

        // Create the new post
        $new_post_id = wp_insert_post( $post_data, true );

        if(is_wp_error($new_post_id)){
            return $new_post_id;
        }    

        //thumbnail
        if(!empty($sanitized_values[$this->prefix . 'featured_image_id'])){
            set_post_thumbnail( $new_post_id, $sanitized_values[$this->prefix . 'featured_image_id'] );
        }

        // Loop through remaining (sanitized) data, and save to post-meta
        foreach ( $sanitized_values as $key => $value ) {
            update_post_meta( $new_post_id, $key, $value );
        }

        return $new_post_id;

    }

    /**
     * Replace 'subscriber' with the required role to update, can also be contributor
     */
    function allow_subscriber_uploads(){
        if ( is_admin() ) {
            return;
        }
        /**
         * Replace 'subscriber' with the required role to update, can also be contributor
         */
        $subscriber = get_role( 'subscriber' );

        // This is the only cap needed to upload files.
        $subscriber->add_cap( 'upload_files' );
    }

    /**
     * Restricts the media library based on the current user's capabilities and the current page.
     *
     * @param object $wp_query_obj The WordPress query object.
     */
    function restrict_media_library($wp_query_obj){
        if ( is_admin() ) {
            return;
        }
        
        global $current_user, $pagenow;

        if ( ! is_a( $current_user, 'WP_User' ) ) {
            return;
        }

        if ( 'admin-ajax.php' != $pagenow || 'query-attachments' != $_REQUEST['action'] ) {
            return;
        }

        if ( ! current_user_can( 'manage_media_library' ) ) {
            $wp_query_obj->set( 'author', $current_user->ID );
        }
    }

}

$CMB2_Frontend_Form_Bs = new CMB2_Frontend_Form_Bs;
$CMB2_Frontend_Form_Bs->initialize();

// Remove the action hook
// remove_action('cmb2_init', array($CMB2_Frontend_Form_Bs, 'register_property_frontend_form'));