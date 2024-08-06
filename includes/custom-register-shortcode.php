<?php
class Custom_Register_Shortcode {
    private $sitekey;
    private $secretkey;

    public function __construct() {
        $captcha_velocity = get_option('captcha_velocity', []);
        $captcha_aktif = isset($captcha_velocity['aktif']) ? $captcha_velocity['aktif'] : '';
        $this->sitekey = isset($captcha_velocity['sitekey']) ? $captcha_velocity['sitekey'] : '';
        $this->secretkey = isset($captcha_velocity['secretkey']) ? $captcha_velocity['secretkey'] : '';

        add_shortcode('custom-register', [$this, 'render_register_form']);
        add_shortcode('velocity_recaptcha', [$this, 'render_recaptcha']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_post_nopriv_custom_register', [$this, 'handle_custom_register']);
        add_action('admin_post_custom_register', [$this, 'handle_custom_register']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
    }

    public function render_register_form() {
        ob_start();
        $status = $_GET['status'] ?? '';
        if ($status == 'success') {
            ?>
            <div class="alert alert-success d-flex align-items-center" role="alert">
                Registration successful!
            </div>
            <?php
        } elseif ($status == 'failed') {
            ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                Registration failed! Try another username or email.
                <a href="?" class="ms-auto btn btn-primary">Coba Lagi</a>
            </div>
            <?php
        } else if ($status == 'captcha') {
            ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                Captcha failed!
                <a href="?" class="ms-auto btn btn-primary">Coba Lagi</a>
            </div>
            <?php
        } else if ($status == '') {
        ?>
        <form id="custom-register-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <p>
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </p>
            <p>
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </p>
            <p>
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </p>
            <p>
                <?php echo do_shortcode('[velocity_recaptcha]'); ?>
            </p>
            <p>
                <button type="submit" class="btn btn-primary" id="submit-button">Register</button>
            </p>
            <input type="hidden" name="action" value="custom_register">
        </form>
        <?php
        }
        return ob_get_clean();
    }

    public function render_recaptcha() {
        $node = 'recaptcha-' . uniqid();
        ob_start();
        ?>
        <div class="<?php echo $node; ?>">
            <div id="g-<?php echo $node; ?>" data-size="normal"></div>
            <script type="text/javascript">
                function onloadCallback<?php echo $node; ?>() {
                    grecaptcha.render('g-<?php echo $node; ?>', {
                        'sitekey': '<?php echo $this->sitekey; ?>',
                        'callback': function() {
                            var form = document.querySelector('.<?php echo $node; ?>').closest('form');
                            form.querySelector('button[type="submit"]').disabled = false;
                        },
                        'expired-callback': function() {
                            alert('Captcha expired, please refresh the page');
                        }
                    });
                }
                document.addEventListener('DOMContentLoaded', function() {
                    var form = document.querySelector('.<?php echo $node; ?>').closest('form');
                    form.querySelector('button[type="submit"]').disabled = true;
                });
            </script>
            <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback<?php echo $node; ?>&render=explicit" async defer></script>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_custom_register() {
        if (isset($_POST['g-recaptcha-response'])) {
            $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);
            $response = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret={$this->secretkey}&response={$recaptcha_response}");
            $response_body = wp_remote_retrieve_body($response);
            $result = json_decode($response_body, true);
            $current_url = wp_get_referer();

            if ($result['success']) {
                $username = sanitize_user($_POST['username']);
                $email = sanitize_email($_POST['email']);
                $password = $_POST['password'];

                $user_id = wp_create_user($username, $password, $email);

                if (!is_wp_error($user_id)) {
                    // Redirect to success page
                    wp_redirect($current_url . '?status=success');
                    exit;

                } else if(is_wp_error($user_id)) { //berikan pesan error sesuai kondisi
                    // Handle user creation error
                    wp_redirect($current_url . '?status=failed');
                    exit;
                }
            } else {
                // Handle reCAPTCHA failure
                wp_redirect($current_url . '?status=captcha');
                exit;
            }
        } else {
            wp_redirect(wp_get_referer() . '?status=failed');
            exit;
        }
    }
}

// Initialize the class
new Custom_Register_Shortcode();
