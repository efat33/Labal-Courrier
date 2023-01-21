<?php

// if user is loggedin, then redirect to dashboard page
if (is_user_logged_in()) {
?>
    <script>
        window.location.replace("<?= esc_url(site_url('lc-profile')) ?>");
    </script>
<?php
}

$errors = [];
$user_created = 0;

if (
    isset($_GET['request_status']) && $_GET['request_status'] == 'error' &&
    isset($_GET['request_id']) && !empty($_GET['request_id'])
) {
    $r_data = get_transient($_GET['request_id']);
    $old_values = (object) $r_data['old_values'];
    $errors = array_diff_key($r_data, array_flip(["old_values"]));
} else if (isset($_GET['request_status']) && $_GET['request_status'] == 'success') {
    $user_created = 1;
}

?>

<script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>

<div x-data="component()" class="lc-login-registration lc-login-page lc-form-container">
    <div class="lc-lr-wrapper lc-grid grid-cols-1 md-grid-cols-3 lg-grid-cols-2 shadow rounded my-5">
        <div class="lc-lr-left">
            <img class="d-none d-md-block" src="<?= LABAL_COURRIER_PLUGIN_URL . '/public/img/login-man.svg' ?>" alt="<?= esc_attr_e("Login Man", "labal-courrier") ?>">
            <img class="d-md-none" src="<?= LABAL_COURRIER_PLUGIN_URL . '/public/img/login-mbl-man.svg' ?>" alt="<?= esc_attr_e("Login Man", "labal-courrier") ?>">
        </div>
        <div class="lc-lr-right d-flex align-items-center">
            <form id="lc_register_form" class="lc-form w-100 p-3 p-md-5" action="<?php echo esc_url(site_url('wp-admin/admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="lc_register_action">
                <?php if (isset($_GET['r'])) { ?>
                    <input type="hidden" name="referred_by" value="<?= $_GET['r'] ?>">
                <?php } ?>
                <?php wp_nonce_field('lc_register_nonce', 'lc_nonce'); ?>

                <div class="lc-grid grid-cols-1 md-grid-cols-2">
                    <span><?= __("Create Account", "labal-courrier") ?></span>
                    <a href="<?= esc_url(site_url('login')) ?>" class="lc-link d-none d-md-block text-end text-decoration-underline"><?= __("Login", "labal-courrier") ?></a>
                </div>
                <h1 class="lr-form-heading mb-4"><?= __("Sign Up", "labal-courrier") ?></h1>

                <div style="<?php echo (count($errors) > 0) ? "" : "display: none;" ?>" class="w-100 common_error text-danger validation_message">
                    <?php
                    if (count($errors) > 0) {
                        foreach ($errors as $key => $value) {
                    ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $value ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>

                <!-- show success message -->
                <?php if ($user_created == 1) { ?>
                    <div class="alert alert-success fade show" role="alert">
                        <?=
                        sprintf(
                            __('Successfully Registered. You can now <a class="lc-link text-decoration-underline" href="%s">login</a>', 'labal-courrier'),
                            esc_url(site_url('login'))
                        );
                        ?>
                    </div>
                <?php } ?>

                <div class="lc-grid grid-cols-1 md-grid-cols-2 gap-3 mb-3">
                    <div class="lc-form-control bg-grey" id="first_name">
                        <label for="id_first_name"><?= esc_html_e("First Name", "labal-courrier") ?></label>
                        <input x-model="formData.first_name" type="text" name="first_name" class="" :class="{ 'is-invalid': !formvalidation.first_name.validated }" />
                        <div x-show="!formvalidation.first_name.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                    </div>
                    <div class="lc-form-control bg-grey" id="last_name">
                        <label for="id_last_name"><?= esc_html_e("Last Name", "labal-courrier") ?></label>
                        <input x-model="formData.last_name" type="text" name="last_name" class="" :class="{ 'is-invalid': !formvalidation.last_name.validated }" />
                        <div x-show="!formvalidation.last_name.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                    </div>
                </div>

                <div class="lc-form-control bg-grey mb-3" id="username">
                    <label for="id_username"><?= esc_html_e("Username", "labal-courrier") ?></label>
                    <input x-model="formData.username" type="text" name="username" class="" :class="{ 'is-invalid': !formvalidation.username.validated }" />
                    <div x-show="!formvalidation.username.validated" class="w-100 text-danger validation_message"><?= __("This field cannot be empty", "labal-courrier") ?></div>
                </div>
                <div class="lc-form-control bg-grey mb-3" id="email">
                    <label for="id_email"><?= esc_html_e("Email", "labal-courrier") ?></label>
                    <input x-model="formData.email" type="text" name="email" class="" :class="{ 'is-invalid': !formvalidation.email.validated }" />
                    <div x-show="!formvalidation.email.validated" class="w-100 text-danger validation_message"><?= __("Please provide a valid email address", "labal-courrier") ?></div>
                </div>

                <div class="lc-form-control bg-grey mb-3" id="password">
                    <label for="id_password"><?= esc_html_e("Choose a Password", "labal-courrier") ?></label>
                    <input x-model="formData.password" type="password" name="password" placeholder="<?= __("Password", "labal-courrier") ?>" class="" :class="{ 'is-invalid': !formvalidation.password.validated }" />
                    <div x-show="!formvalidation.password.validated" class="w-100 text-danger validation_message"><?= __("This field cannot be empty", "labal-courrier") ?></div>

                    <input x-model="formData.confirm_password" type="password" name="confirm_password" placeholder="<?= __("Confirm Password", "labal-courrier") ?>" class="mt-3" :class="{ 'is-invalid': !formvalidation.confirm_password.validated }" />
                    <div x-show="!formvalidation.confirm_password.validated" x-text="formvalidation.confirm_password.message" class="w-100 text-danger validation_message"></div>
                </div>


                <div class="lr-form-footer lc-grid grid-cols-1 md-grid-cols-2 mt-4 mb-3 mb-md-0">
                    <div class="lr-form-footer-left">
                        <a class="lr-facebook lr-social-btn rounded-5" href=""><i class="fa-brands fa-facebook"></i> <span class="d-md-none ms-3 ms-md-0"><?= __("Connect with Facebook", "labal-courrier") ?></span></a>
                        <a class="lr-google lr-social-btn rounded-5" href=""><i class="fa-brands fa-google"></i> <span class="d-md-none ms-3 ms-md-0"><?= __("Connect with Google", "labal-courrier") ?></span></a>
                    </div>
                    <div class="lr-form-footer-right text-end mb-3 mb-md-0">
                        <button x-on:click.prevent="onSubmit()" type="submit" class="btn lc-button lc-btn-blue rounded px-4"><?= __("Create Account", "labal-courrier") ?></button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<div style="display: none;">
    <?php echo do_shortcode('[TheChamp-Login]') ?>
</div>

<script>
    jQuery(document).ready(function() {
        jQuery('.lr-facebook').click(function(e) {
            e.preventDefault();
            jQuery('.the_champ_login_ul').find('.theChampFacebookLogin').trigger('click');
        });

        jQuery('.lr-google').click(function(e) {
            e.preventDefault();
            jQuery('.the_champ_login_ul').find('.theChampGoogleLogin').trigger('click');
        });
    });

    const validateEmail = (email) => {
        return String(email)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    };

    function component() {
        return {
            formData: {
                first_name: '<?= isset($old_values->first_name) ? $old_values->first_name : "" ?>',
                last_name: '<?= isset($old_values->last_name) ? $old_values->last_name : "" ?>',
                username: '<?= isset($old_values->username) ? $old_values->username : "" ?>',
                email: '<?= isset($old_values->email) ? $old_values->email : "" ?>',
                password: '<?= isset($old_values->password) ? $old_values->password : "" ?>',
                confirm_password: '<?= isset($old_values->confirm_password) ? $old_values->confirm_password : "" ?>',
            },
            formvalidation: {
                valid: true,
                first_name: {
                    validated: true,
                    message: ''
                },
                last_name: {
                    validated: true,
                    message: ''
                },
                username: {
                    validated: true,
                    message: ''
                },
                email: {
                    validated: true,
                    message: ''
                },
                password: {
                    validated: true,
                    message: ''
                },
                confirm_password: {
                    validated: true,
                    message: ''
                }
            },

            validationIds: [],

            resetFormError() {
                this.validationIds = [];

                Object.entries(this.formvalidation).forEach(([key, item]) => {
                    if (key == 'valid') {
                        this.formvalidation[key] = true;
                    } else {
                        item.validated = true;
                        item.message = '';
                    }
                });
            },

            onSubmit() {
                this.resetFormError();
                const offsetMinus = 200;

                if (this.formData.first_name == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.first_name.validated = false;
                    this.validationIds.push('first_name');
                }
                if (this.formData.last_name == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.last_name.validated = false;
                    this.validationIds.push('last_name');
                }

                if (this.formData.username == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.username.validated = false;
                    this.validationIds.push('username');
                }

                // email validation
                if (!validateEmail(this.formData.email)) {
                    this.formvalidation.valid = false;
                    this.formvalidation['email']['validated'] = false;
                    this.validationIds.push('email');
                }

                // password validation 
                if (this.formData.password == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.password.validated = false;
                    this.validationIds.push('password');
                }
                if (this.formData.confirm_password == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.confirm_password.validated = false;
                    this.formvalidation.confirm_password.message = "<?= __("This field cannot be empty", "labal-courrier") ?>";
                    this.validationIds.push('password');
                } else if (this.formData.password != this.formData.confirm_password) {
                    this.formvalidation.valid = false;
                    this.formvalidation.confirm_password.validated = false;
                    this.formvalidation.confirm_password.message = "<?= __("Password and confirm password do not match", "labal-courrier") ?>";
                    this.validationIds.push('password');
                }

                if (this.formvalidation.valid) {
                    // submit the form
                    jQuery('#lc_register_form').submit();
                } else {
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }
            }
        }
    }
</script>