<?php
class Custom_Login_Shortcode {
    public function __construct() {
        add_shortcode('custom-login', array($this, 'custom_login_shortcode'));
        add_action('wp_footer', array($this, 'add_custom_login_styles'));
    }

    public function custom_login_shortcode($atts, $content = null) {
        if (is_user_logged_in()) {
            return '<div class="alert alert-success">Anda sudah login</div>';
        }

        ob_start();
        ?>
        <div class="custom-login-form">
            <div class="card">
                <div class="card-body">
                    <?php wp_login_form(array(
                        'label_username' => __('Username or Email'),
                        'label_log' => __('Username or Email'),
                        'label_password' => __('Password'),
                        'label_remember' => __('Remember Me'),
                        'id_form' => 'loginform',
                        'id_submit' => 'wp-submit',
                        'redirect' => get_permalink(),
                    )); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function add_custom_login_styles() {
        // Cek apakah shortcode ada di halaman
        global $post;
        if (has_shortcode($post->post_content, 'custom-login')) {
            echo '<style>
                .custom-login-form #user_login,
                .custom-login-form #user_pass {
                    border: 1px solid #ccc;
                    padding: 10px;
                    width: 100%;
                    border-radius: 5px;
                    margin-bottom: 10px;
                }
                .custom-login-form .button-primary {
                    background-color: #007bff;
                    border-color: #007bff;
                    border-radius: 5px !important;
                }
                .custom-login-form .g-recaptcha {
                    margin-bottom: 10px;
                }
              </style>';
        }
    }
}

new Custom_Login_Shortcode();