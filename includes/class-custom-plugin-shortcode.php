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
