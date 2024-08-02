<?php
class CMB2_Frontend_User_Meta_Bs
{
    private $prefix = 'cp_';

    public function __construct()
    {
        add_shortcode('cmb-user-meta-form', array($this, 'render_user_meta_form'));
        add_action('cmb2_init', array($this, 'register_user_frontend_form'));
    }

    public function register_user_frontend_form()
    {
        $cmb_user = new_cmb2_box(array(
            'id'           => $this->prefix . 'user_frontend_form',
            'object_types' => array('user'), // Tipe objek adalah 'user'
            'hookup'       => false,
            'save_fields'  => false, // Kami akan menyimpan field secara manual
        ));

        $cmb_user->add_field(array(
            'name'    => 'Full Name',
            'id'      => $this->prefix . 'full_name',
            'type'    => 'text',
        ));

        $cmb_user->add_field(array(
            'name'    => 'Address',
            'id'      => $this->prefix . 'address',
            'type'    => 'textarea',
        ));

        $cmb_user->add_field(array(
            'name'    => 'Phone Number',
            'id'      => $this->prefix . 'phone_number',
            'type'    => 'text',
        ));

        $cmb_user->add_field(array(
            'name'    => 'Email',
            'id'      => $this->prefix . 'email',
            'type'    => 'text_email',
        ));
    }

    public function render_user_meta_form($atts = array())
    {
        if (!is_user_logged_in()) {
            return '<p>You need to be logged in to edit your profile.</p>';
        }

        // Current user
        $user_id = get_current_user_id();

        // Use ID of metabox in wds_frontend_form_register
        $metabox_id = isset($atts['id']) ? esc_attr($atts['id']) : $this->prefix . 'user_frontend_form';

        // Get CMB2 metabox object
        $cmb = cmb2_get_metabox($metabox_id, $user_id);

        if (empty($cmb)) {
            return 'Metabox ID not found';
        }

        // Initiate our output variable
        $output = '';

        $updated = $this->handle_submit($cmb, $user_id);
        if ($updated) {

            if (is_wp_error($updated)) {

                // If there was an error with the submission, add it to our output.
                $output .= '<div class="alert alert-warning">' . sprintf(__('There was an error in the submission: %s', 'cmb2-user-submit'), '<strong>' . $updated->get_error_message() . '</strong>') . '</div>';
            } else {

                // Add notice of submission
                $output .= '<div class="alert alert-success">' . __('Your profile has been updated successfully.', 'cmb2-user-submit') . '</div>';
            }
        }

        // Get our form
        $form = cmb2_get_metabox_form($cmb, $user_id, array('save_button' => __('Update Profile', 'cmb2-user-submit')));

        // Format our form use Bootstrap 5
        $styling = [
            'regular-text'                              => 'regular-text form-control',
            'cmb2-text-small'                           => 'cmb2-text-small form-control',
            'cmb2-text-medium'                          => 'cmb2-text-medium form-control',
            'cmb2-timepicker'                           => 'cmb2-timepicker form-control d-inline-block',
            'cmb2-datepicker'                           => 'cmb2-datepicker d-inline-block',
            'cmb2-text-money'                           => 'cmb2-text-money form-control d-inline-block',
            'cmb2_textarea'                             => 'cmb2_textarea form-control',
            'cmb2-textarea-small'                       => 'cmb2-textarea-small form-control d-inline-block',
            'cmb2_select'                               => 'cmb2_select form-select',
            'cmb2-upload-file regular-text'             => 'cmb2-upload-file regular-text d-block w-100',
            'type="radio" class="cmb2-option"'          => 'type="radio" class="cmb2-option form-check-input"',
            'type="checkbox" class="cmb2-option"'       => 'type="checkbox" class="cmb2-option form-check-input"',
            'class="button-primary"'                    => 'class="button-primary btn btn-primary float-end"',
            'cmb2-metabox-description'                  => 'cmb2-metabox-description fw-normal small',
            'class="cmb-th"'                            => 'class="cmb-th w-100 p-0"',
            'class="cmb-td"'                            => 'class="cmb-th w-100 p-0 pb-2"',
            'class="cmb-add-row"'                       => 'class="cmb-add-row text-end"',
            'button-secondary'                          => 'button-secondary btn-sm btn btn-outline-secondary',
            'cmb2-upload-button'                        => 'cmb2-upload-button float-end mt-1',
            'button-secondary btn-sm btn btn-outline-secondary cmb-remove-row-button' => 'button-secondary btn btn-danger cmb-remove-row-button',
        ];
        foreach ($styling as $std => $newf) {
            $form = str_replace($std, $newf, $form);
        }

        $output .= $form;

        return $output;
    }

    function handle_submit($cmb, $user_id)
    {

        // If no form submission, bail
        if (empty($_POST)) {
            return false;
        }
        // Fetch sanitized values
        $sanitized_values = $cmb->get_sanitized_values($_POST);

        // Loop through remaining (sanitized) data, and save to user-meta
        foreach ($sanitized_values as $key => $value) {
            update_user_meta($user_id, $key, $value);
        }

        return true;
    }
}

// Inisialisasi kelas
$CMB2_Frontend_User_Meta_Bs = new CMB2_Frontend_User_Meta_Bs();
