<?php
class Property_Admin_Options {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=property', // Parent menu slug
            'Pengaturan Property',          // Page title
            'Pengaturan Property',          // Menu title
            'manage_options',               // Capability
            'property-settings',            // Menu slug
            array($this, 'settings_page')   // Callback function
        );
    }

    public function register_settings() {
        // Register a setting for our settings page
        register_setting(
            'property_options_group', // Option group
            'submit_page'  // Option name
        );
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Pengaturan Property</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('property_options_group');
                do_settings_sections('property-settings');
                $pages = get_pages();
                $submit_page = get_option('submit_page');
                $list_page = get_option('list_page');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Halaman Submit</th>
                        <td>
                            <select name="submit_page">
                                <option value="">Pilih Halaman</option>
                                <?php foreach ($pages as $page): ?>
                                    <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($submit_page, $page->ID); ?>>
                                        <?php echo esc_html($page->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Pilih halaman yang akan digunakan untuk form submit. 
                                <br>Pastikan ada shortcode [cp-property type="submit"] di halaman.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">List Property</th>
                        <td>
                            <select name="list_page">
                                <option value="">Pilih Halaman</option>
                                <?php foreach ($pages as $page): ?>
                                    <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($list_page, $page->ID); ?>>
                                        <?php echo esc_html($page->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Pilih halaman yang akan digunakan untuk list property.
                                <br>Pastikan ada shortcode [cp-property type="list"] di halaman.
                            </p>
                        </td>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

new Property_Admin_Options();