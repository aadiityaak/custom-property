<?php
class Custom_Register_Shortcode {

    public function __construct() {
        add_shortcode('custom-register', array($this, 'custom_register_shortcode'));
        add_action('wp_footer', array($this, 'add_custom_register_styles'));
    }

    public function custom_register_shortcode($atts, $content = null) {
        if (is_user_logged_in()) {
            return '<div class="alert alert-success">Anda sudah terdaftar</div>';
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['custom_register_nonce'])) {
            $this->handle_registration();
        }

        ob_start();
        ?>
        <div class="custom-register-form">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="username"><?php _e('Username', 'textdomain'); ?></label>
                            <input type="text" name="username" id="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email"><?php _e('Email', 'textdomain'); ?></label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password"><?php _e('Password', 'textdomain'); ?></label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <?php $this->captcha_display(); ?>
                        </div>
                        <div class="form-group">
                            <?php wp_nonce_field('custom_register_action', 'custom_register_nonce'); ?>
                            <input type="submit" name="submit" class="button button-primary" value="<?php _e('Register', 'textdomain'); ?>">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    prifate function captcha_display() {
        
    }
    private function handle_registration() {
        if (!isset($_POST['custom_register_nonce']) || !wp_verify_nonce($_POST['custom_register_nonce'], 'custom_register_action')) {
            return;
        }

        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        $gresponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

        $errors = new WP_Error();

        // Validasi reCAPTCHA
        $recaptcha_verify = $this->captcha->verify($gresponse);
        if (!$recaptcha_verify['success']) {
            $errors->add('recaptcha_error', $recaptcha_verify['message']);
        }

        if (username_exists($username)) {
            $errors->add('username_exists', __('Username already exists.'));
        }

        if (!is_email($email)) {
            $errors->add('invalid_email', __('Invalid email.'));
        }

        if (email_exists($email)) {
            $errors->add('email_exists', __('Email already exists.'));
        }

        if (strlen($password) < 6) {
            $errors->add('weak_password', __('Password must be at least 6 characters.'));
        }

        if (!empty($errors->errors)) {
            foreach ($errors->get_error_messages() as $error) {
                echo '<div class="alert alert-danger">' . esc_html($error) . '</div>';
            }
            return;
        }

        $user_id = wp_create_user($username, $password, $email);

        if (!is_wp_error($user_id)) {
            echo '<div class="alert alert-success">' . __('Registration complete.') . '</div>';
        } else {
            echo '<div class="alert alert-danger">' . $user_id->get_error_message() . '</div>';
        }
    }

    public function add_custom_register_styles() {
        global $post;
        if (has_shortcode($post->post_content, 'custom-register')) {
            echo '<style>
                .custom-register-form #username,
                .custom-register-form #email,
                .custom-register-form #password {
                    border: 1px solid #ccc;
                    padding: 10px;
                    width: 100%;
                    border-radius: 5px;
                    margin-bottom: 10px;
                }
                .custom-register-form .button-primary {
                    background-color: #007bff;
                    border-color: #007bff;
                    border-radius: 5px !important;
                }
              </style>';
        }
    }
}

new Custom_Register_Shortcode();