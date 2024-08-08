<?php

/**
 * Class Custom_Plugin_Shortcode
 */
class Custom_Plugin_Shortcode
{

    /**
     * Custom_Plugin_Shortcode constructor.
     */
    public function __construct()
    {
        add_shortcode('search', array($this, 'search')); 
        add_shortcode('velocity-daftar-agen', array($this, 'velocity_daftar_agen'));
        add_shortcode('velocity-post-loop', array($this, 'velocity_post_loop'));
        add_shortcode('velocity-cat-frame', array($this, 'velocity_cat_frame')); 
        add_shortcode('velocity-author-properti', array($this, 'velocity_author_properti'));
        add_shortcode('custom-plugin', array($this, 'custom_plugin_text_shortcode_callback')); // [custom-plugin]
        add_shortcode('slider-properti', array($this, 'custom_gallery_slider_shortcode'));
        add_shortcode('harga-properti', array($this, 'format_price_shortcode'));
        add_shortcode('whatsapp-author', array($this, 'custom_format_phonne_number'));
        add_shortcode('spesifiaksi-property', array($this, 'custom_spesifiaksi_property'));
        add_shortcode('fasilitas-property', array($this, 'custom_fasilitas_property'));
        add_shortcode('kemudahan-akses', array($this, 'custom_kemudahan_akses'));
    }

    /**
     * Shortcode callback to display text.
     *
     * @param array $atts Shortcode attributes.
     * @param string $content Shortcode content.
     *
     * @return string
     */
    public function custom_plugin_text_shortcode_callback($atts, $content = null)
    {
        return '<p>Contoh Output shortcode</p>';
    }

    public function custom_gallery_slider_shortcode()
    {
        // Mengakses variabel global $post
        global $post;

        // Mengambil ID dari post saat ini
        $post_id = $post->ID;

        // Mulai output buffering
        ob_start();

        // Mengambil data galeri dari custom field atau metode lain
        $gallery_items = get_post_meta($post_id, 'cp_gallery', true);

        // Pastikan $gallery_items adalah array
        if (!empty($gallery_items) && is_array($gallery_items)) {
?>
            <div id="carouselExampleIndicators" class="carousel slide">
                <div class="carousel-indicators">
                    <?php $index = 0;
                    foreach ($gallery_items as $item) : ?>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?php echo esc_attr($index); ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo esc_attr($index + 1); ?>"></button>
                    <?php $index++;
                    endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php $index = 0;
                    foreach ($gallery_items as $item) : ?>
                        <?php if (isset($item) && !empty($item)) : ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <div class="ratio ratio-4x3">
                                    <img src="<?php echo esc_url($item); ?>" class="d-block w-100" alt="">
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php $index++;
                    endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>

        <?php
        } else {
            echo '<p>Tidak ada item galeri ditemukan.</p>';
        }

        // Ambil hasil buffer dan mengembalikannya sebagai string
        return ob_get_clean();
    }

    // Fungsi untuk shortcode
    public function format_price_shortcode($atts)
    {
        global $post;
        $atts = shortcode_atts(array(
            'post_id' => $post->ID,
        ), $atts);
        $price = get_post_meta($atts['post_id'], 'cp_price', true);
        return 'Rp. ' . number_format($price, 0, ',', '.');
    }

    public function custom_format_phonne_number()
    {
        global $post;
        $author = $post->post_author;

        // Mendapatkan nomor telepon dari post meta
        $phone_number = get_user_meta($author, 'cp_phone_number', true);
        // Jika diawalai dengan '0' dan ganti '62'
        $phone_number = str_replace('0', '62', $phone_number);
        // jika diawali dengan +62 dan ganti '62'
        $phone_number = str_replace('+62', '62', $phone_number);

        // Memastikan nomor telepon tidak kosong sebelum melakukan pemrosesan
        if (!empty($phone_number)) {
            // Menghapus semua karakter yang bukan angka
            $cleaned_phone_number = preg_replace('/[^0-9]/', '', $phone_number);

            // Menambahkan awalan kode negara Indonesia jika tidak dimulai dengan '62'
            if (substr($cleaned_phone_number, 0, 1) === '0') {
                $cleaned_phone_number = '62' . substr($cleaned_phone_number, 1);
            } elseif (substr($cleaned_phone_number, 0, 2) !== '62') {
                $cleaned_phone_number = '62' . $cleaned_phone_number;
            }

            // Mengembalikan nomor telepon dengan format yang sesuai untuk link WhatsApp
            $url = esc_url('https://wa.me/' . $cleaned_phone_number . '?text=Halo%20saya%20tertarik%20dengan%20properti%20' . get_the_title($post->ID));
            $link = '<a href="' . $url . '" target="_blank">';
            $link .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#198754" style="color: #198754;" class="bi bi-whatsapp" viewBox="0 0 16 16">
  <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
</svg>';
            $link .= '</a>';

            return $link;
        }

        // Jika nomor telepon tidak ditemukan, mengembalikan string kosong
        return '';
    }

    public function custom_spesifiaksi_property()
    {
        global $post;
        ob_start();
        ?>
        <p>Spesifikasi Property</p>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th class="text-center"><i class="fa fa-bed" aria-hidden="true"></i></th>
                    <td>Kamar Tidur</td>
                    <td><?php echo get_post_meta($post->ID, 'cp_jumlah-kamar-tidur', true); ?></td>
                </tr>
                <tr>
                    <th class="text-center"><i class="fa fa-bath" aria-hidden="true"></i></th>
                    <td>Kamar Mandi</td>
                    <td><?php echo get_post_meta($post->ID, 'cp_jumlah-kamar-mandi', true); ?></td>
                </tr>
                <tr>
                    <th class="text-center"><i class="fa fa-building" aria-hidden="true"></i></th>
                    <td>Luas Bangunan</td>
                    <td><?php echo get_post_meta($post->ID, 'cp_luas-bangunan', true); ?> m²</td>
                </tr>
                <tr>
                    <th class="text-center"><i class="fa fa-map-o" aria-hidden="true"></i></th>
                    <td>Luas Tanah</td>
                    <td><?php echo get_post_meta($post->ID, 'cp_luas-tanah', true); ?> m²</td>
                </tr>
                <tr>
                    <th class="text-center"><i class="fa fa-window-minimize" aria-hidden="true"></i></th>
                    <td>Jumlah Lantai</td>
                    <td><?php echo get_post_meta($post->ID, 'cp_jumlah-lantai', true); ?></td>
                </tr>
                <tr>
                    <th class="text-center"><i class="fa fa-calendar" aria-hidden="true"></i></th>
                    <td>Tanggal Ditambahkan</td>
                    <td><?php echo get_the_date('d F Y', $post->ID); ?></td>
                </tr>
            </tbody>
        </table>

    <?php
        return ob_get_clean();
    }
    public function vsstemmart_taxonomy_image_url($term_id = NULL, $size = 'full', $return_placeholder = FALSE) {
        if (!$term_id) {
            if (is_category())
                $term_id = get_query_var('categories_property');
            elseif (is_tag())
                $term_id = get_query_var('tag_id');
            elseif (is_tax()) {
                $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                $term_id = $current_term->term_id;
            }
        }
        
        $taxonomy_image_url = get_option('vsstemmart_taxonomy_image'.$term_id);
        if(!empty($taxonomy_image_url)) {
            $attachment_id = vsstemmart_get_attachment_id_by_url($taxonomy_image_url);
            if(!empty($attachment_id)) {
                $taxonomy_image_url = wp_get_attachment_image_src($attachment_id, $size);
                $taxonomy_image_url = $taxonomy_image_url[0];
            }
        }
        if ($return_placeholder)
            return ($taxonomy_image_url != '') ? $taxonomy_image_url : VSSTEMMART_PLACEHOLDER;
        else
            return $taxonomy_image_url;
    }
    // Fungsi untuk menampilkan fasilitas
    public function custom_fasilitas_property()
    {
        global $post;
        ob_start();
    ?>
        <ul class="list-unstyled">
            <?php
            $fasilitas = get_post_meta($post->ID, 'cp_fasilitas_group', true);
            if (is_array($fasilitas)) {
                foreach ($fasilitas as $fasilitas_item) {
                    echo '<li>' . $fasilitas_item['fasilitas'] . '</li>';
                }
            }
            ?>
        </ul>
    <?php
        return ob_get_clean();
    }

    public function search(){
        $s = isset($_GET['s'])?$_GET['s']:'';
        $jenis = isset($_GET['jenis'])?$_GET['jenis']:'';
        if ($jenis=='beli'){
            $checkbeli = 'checked';
        } elseif($jenis=='sewa'){
            $checksewa = 'checked';
        } else {
            $checkbeli = 'checked';
            $checksewa = '';
        }
        $html = '';
        $html .= '<form action="'.get_home_url().'" class="needs-validation" novalidate>';
            $html .= '<div class="form-atas row text-center">';
            $html .= '<div class="col-md-6"><input id="jual" type="radio" name="jenis" value="jual" '.$checkbeli.'><label for="jual">BELI PROPERTY</label></div>';
            $html .= '<div class="col-md-6"><input id="sewa" type="radio" name="jenis" value="sewa" '.$checksewa.'><label for="sewa">SEWA PROPERTY</label></div>';
            $html .= '<div style="clear:both"></div>';
            $html .= '</div>';
          $html .= '<div class="form-bawah mt-3">';
            $html .= '<input type="text" name="s" placeholder="Cari Properti" value="'.$s.'" required="required">';
            $html .= '<button type="submit" class="text-dark"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="black" class="bi bi-search text-dark" viewBox="0 0 16 16"> <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/> </svg></button>';
          $html .= '</div>';
        $html .= '</form>';
        
        return $html;
    }
    public function velocity_cat_frame($atts) {
        // Ambil atribut dan tetapkan default jika tidak ada
        $atts = shortcode_atts(array(
            'slugs' => '', // Slugs kategori, dipisahkan dengan koma
        ), $atts);

        // $categories ambil dari terms categories_property
        $categories = get_terms(array(
            'taxonomy' => 'categories_property',
            'hide_empty' => 0
        ));
    
        // Inisialisasi output
        $output = '<div class="velocity-category-frame">';
        
        // Nav tabs
        $output .= '<div class="row justify-content-center text-center mx-auto">';
        foreach($categories as $index => $category) {
            // echo '<pre>'.print_r(get_taxonomy_image($category), 1).'</pre>';
            $img_icon = z_taxonomy_image_url($category->term_id);
            $output .= '<div class="col-3 mb-4" role="presentation">';
            $output .= '<a class="d-block" href="'.get_category_link( $category->term_id ).'" role="tab" aria-controls="content-' . $category->term_id . '" aria-selected="false">';
            if($img_icon){
                $output .= '<img class=" " src="'.$img_icon.'" />';
            } else {
                $output .= '<img src="'.get_template_directory_uri().'/img/all.png" />';
            }
            $output .= '<div class="mt-2 text-dark textt">'.$category->name.'</div>';
            $output .= '</a>';
            $output .= '</div>';
        }
        $output .= '</div>';
    
        $output .= '</div>';
    
        return $output;
    }
    function velocity_post_loop($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'post_id' => get_the_ID(),
            ),
            $atts
        );
    
        // Get post meta data
        $post_id = $atts['post_id'];
        $lokasi = get_post_meta($post_id, 'cp_city', true);
        $kamartidur = get_post_meta($post_id, 'jumlahkamartidur', true);
        $kamarmandi = get_post_meta($post_id, 'jumlahkamarmandi', true);
        $luasbangunan = get_post_meta($post_id, 'luasbangunan', true);
    
        // Build the output
        ob_start();
        ?>
        <div class="velocity-post-loop" id="post-<?php echo $post_id; ?>">
                <div class="velocity-post-thumbnail">
                <div class="ratio ratio-16x9">
                    <img src="<?php echo get_the_post_thumbnail_url($post_id, 'large'); ?>" alt="" />
                </div>
                </div>
                <div class="p-3 position-relative">
                    <h6 class="mb-2"><a class="text-dark" href="<?php echo get_the_permalink($post_id); ?>"><strong><?php echo get_the_title($post_id); ?></strong></a></h6>
                    <div class="property-content mb-2">
                        <div class="mb-2 text-primary"><strong><?php echo 'Rp. ' . number_format((int)get_post_meta($post_id, 'cp_price', true)); ?></strong></div>
                        <div class="mb-2"><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo $lokasi; ?></div>
                        <i class="fa fa-bed"></i> <small><?php echo $kamartidur; ?></small> | <i class="fa fa-bath"></i> <small><?php echo $kamarmandi; ?></small> | <i class="fa fa-home"></i> <small><?php echo $luasbangunan; ?> m2</small>
                    </div>
                    <?php echo do_shortcode("[velocity-author-properti post_id='".$post_id."' contact='true']"); ?>
                </div>
        </div>
        <?php
        return ob_get_clean();
    }

    function velocity_author_properti($atts) {
        global $post;
        
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'post_id' => $post->ID,
                'contact' => false,
            ),
            $atts
        );
    
        // Get post meta data
        $contact = $atts['contact'];
        $post_id = $atts['post_id'];
        $this_post = get_post($post_id);
        $user_id = $this_post->post_author;
        
        $udata	= get_userdata( $user_id );
        $hp   = get_user_meta($user_id,'cp_phone_number',true);
        if (substr($hp, 0, 1) === '0') {
            $wa    = '62' . substr($hp, 1);
        } else if (substr($hp, 0, 1) === '+') {
            $wa    = '' . substr($hp, 1);
        }
    
        // Ambil URL avatar
        $url_ava = get_user_meta($user_id, 'cp_poto_profil', true);
    
        // Ambil nama pengguna
        $user_name = get_user_meta($user_id, 'namatoko', true);
        if (!$user_name) {
            $user_name = get_the_author_meta('display_name', $user_id);
        }
        
        $html = '<div class="mb-0 d-flex align-items-center">';
    
        // Buat output HTML
        //$html .= '<div class="velocity-author-properti col">';
            $html .= '<div class="justify-content-start">';
                $html .= '<a class="text-dark" href="' . esc_url(get_author_posts_url($user_id)) . '"><img class="rounded-circle mr-2" width="40" src="' . esc_url($url_ava) . '" alt="' . esc_attr($user_name) . '" /></a>';
            $html .= '</div>';
            $html .= '<div class="flex-fill ms-1 me-1">';
                $html .= '<b><a class="text-dark" href="' . esc_url(get_author_posts_url($user_id)) . '">' . esc_html($user_name) . '</a></b>';
            $html .= '</div>';
        //$html .= '</div>';
        
        if($contact == true){
            $html .= '<div class="d-flex mt-2">
            <a class="velocity-author-contact btn btn-sm btn-info btn-icon-only rounded-circle me-1" target="_blank" href="mailto:'.$udata->user_email.'"><i class="fa fa-envelope"></i></a>
            <a class="velocity-author-contact btn btn-sm btn-success btn-icon-only rounded-circle me-1" target="_blank" href="https://wa.me/'.$wa.'"><i class="fa fa-whatsapp"></i></a>
            <a class="velocity-author-contact btn btn-sm btn-dark btn-icon-only rounded-circle me-1" target="_blank" href="tel:'.$hp.'"><i class="fa fa-phone"></i></a>
            </div>';
        }
        $html .= '</div>';
    
        return $html;
    }


    function velocity_daftar_agen($atts) {
        // Set default atribut
        $atts = shortcode_atts(array(
            'count' => 4, // Default jumlah pengguna yang ditampilkan adalah 5
        ), $atts);
        $count = $atts['count'];
    
        // Dapatkan pengguna dengan peran subscriber secara acak
        $args = array(
            'role'    => 'subscriber',
            'orderby' => 'rand',
        );
        if($count){
            $args['number']  = $count;
        }
        $users = get_users($args);
    
        // Inisialisasi output
        $output = '<div class="velocity-daftar-agen row">';
    
        // Loop melalui pengguna dan buat output HTML
        foreach ($users as $user) {
            
            $profile_url = esc_url(get_author_posts_url($user->ID));
            
            $display_name = get_user_meta($user->ID, 'namatoko', true);
            if (!$display_name) {
                $display_name = esc_html($user->display_name);
            }
            $profile_image_id = get_user_meta($user->ID, 'profile_image', true);
            $url_img = wp_get_attachment_image_url($profile_image_id, 'full');
            if ($url_img) {
                $url_ava = aq_resize($url_img, 200, 200, true, true, true);
            } else {
                $url_ava = get_template_directory_uri() . "/img/user.png";
            }
    
            $output .= '<div class="col-6 col-md-3 mb-4 p-1 p-md-2">';
            $output .= '<div class="border bg-white shadow-md py-3 px-2 text-center">';
                $output .= '<div class="mb-2"><a href="' . $profile_url . '"><img src="' . $url_ava . '" /></a></div>';
                $output .= '<div class="font-weight-bold"><a class="text-dark" href="' . $profile_url . '">' . $display_name . '</a></div>';
                $output .= do_shortcode('[alamat id="' . $user_id . '" kosong="yes"]');
                $hp   = get_user_meta($user_id,'nohp',true);
                $nowa = get_user_meta($user_id,'nowa',true);
                if (substr($nowa, 0, 1) === '0') {
                    $wa    = '62' . substr($nowa, 1);
                } else if (substr($nowa, 0, 1) === '+') {
                    $wa    = '' . substr($nowa, 1);
                }
                $output .= '<div class="mt-2 d-flex justify-content-center">
                <a class="velocity-author-contact btn btn-sm btn-info btn-icon-only rounded-circle mr-1" target="_blank" href="mailto:'.$user->user_email.'"><i class="fa fa-envelope"></i></a>
                <a class="velocity-author-contact btn btn-sm btn-success btn-icon-only rounded-circle mr-1" target="_blank" href="https://wa.me/'.$wa.'"><i class="fa fa-whatsapp"></i></a>
                <a class="velocity-author-contact btn btn-sm btn-dark btn-icon-only rounded-circle mr-1" target="_blank" href="tel:'.$hp.'"><i class="fa fa-phone"></i></a>
                </div>';
            $output .= '</div>';
            $output .= '</div>';
        }
    
        $output .= '</div>';
    
        return $output;
    }

    // Fungsi untuk menampilkan Kemudahan Akses
    public function custom_kemudahan_akses()
    {
        global $post;
        ob_start();
    ?>
        <ul class="list-unstyled">
            <?php
            $kemudahan = get_post_meta($post->ID, 'cp_kemudahan_akses_ke_group', true);
            if (is_array($kemudahan)) {
                foreach ($kemudahan as $kemudahan_item) {
                    echo '<li>' . $kemudahan_item['kemudahan_akses_ke'] . '</li>';
                }
            }
            ?>
        </ul>
<?php
        return ob_get_clean();
    }
}

// Inisialisasi class Custom_Plugin_Shortcode
new Custom_Plugin_Shortcode();
