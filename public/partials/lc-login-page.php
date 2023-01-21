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

if (
    isset($_GET['request_status']) && $_GET['request_status'] == 'error' &&
    isset($_GET['request_id']) && !empty($_GET['request_id'])
) {
    $errors = get_transient($_GET['request_id']);
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
            <form id="lc_login_form" class="lc-form w-100 p-3 p-md-5" action="<?php echo esc_url(site_url('wp-admin/admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="lc_login_action">
                <?php wp_nonce_field('lc_login_nonce', 'lc_nonce'); ?>

                <div class="lc-grid grid-cols-1 md-grid-cols-2">
                    <span><?= __("Sign In", "labal-courrier") ?></span>
                    <a href="<?= esc_url(site_url('register')) ?>" class="lc-link d-none d-md-block text-end text-decoration-underline"><?= __("Sign Up", "labal-courrier") ?></a>
                </div>
                <h1 class="lr-form-heading mb-4"><?= __("Login", "labal-courrier") ?></h1>

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

                <div class="lc-form-control bg-grey mb-3" id="email">
                    <label for="id_email"><?= esc_html_e("Email", "labal-courrier") ?></label>
                    <input x-model="formData.email" type="text" name="email" class="" :class="{ 'is-invalid': !formvalidation.email.validated }" />
                    <div x-show="!formvalidation.email.validated" class="w-100 text-danger validation_message"><?= __("Please provide a valid email address", "labal-courrier") ?></div>
                </div>

                <div class="lc-form-control bg-grey mb-3" id="password">
                    <label for="id_password"><?= esc_html_e("Password", "labal-courrier") ?></label>
                    <input x-model="formData.password" type="password" name="password" class="" :class="{ 'is-invalid': !formvalidation.password.validated }" />
                    <div x-show="!formvalidation.password.validated" class="w-100 text-danger validation_message"><?= __("This field cannot be empty", "labal-courrier") ?></div>
                </div>

                <div class="lc-form-control ">
                    <div class="lc-form-control d-flex align-items-center">
                        <input x-model="formData.remember_me" type="checkbox" class="form-check-input" name="remember_me" id="id_remember_me" value="1">
                        <label class="" for="id_remember_me">
                            <?= __("Remember me", "labal-courrier") ?>
                        </label>
                    </div>
                </div>

                <div class="lc-form-control ">
                    <a class="lc-link link-forgot-password mt-3 d-block" href="<?= esc_url(site_url('forgot-password')) ?>">Lost your password?</a>
                </div>

                <div class="lr-form-footer lc-grid grid-cols-1 md-grid-cols-2 mt-4 mb-3 mb-md-0">
                    <div class="lr-form-footer-left">
                        <a class="lr-facebook lr-social-btn rounded-5" href=""><i class="fa-brands fa-facebook"></i> <span class="d-md-none ms-3 ms-md-0"><?= __("Connect with Facebook", "labal-courrier") ?></span></a>
                        <a class="lr-google lr-social-btn rounded-5" href=""><i class="fa-brands fa-google"></i> <span class="d-md-none ms-3 ms-md-0"><?= __("Connect with Google", "labal-courrier") ?></span></a>
                    </div>
                    <div class="lr-form-footer-right text-end mb-3 mb-md-0">
                        <button x-on:click.prevent="onSubmit()" type="submit" class="btn lc-button lc-btn-blue rounded px-4"><?= __("Login", "labal-courrier") ?></button>
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
                email: '',
                password: '',
                remember_me: '',
            },
            formvalidation: {
                valid: true,
                email: {
                    validated: true,
                    message: ''
                },
                password: {
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

                if (this.formvalidation.valid) {
                    // submit the form
                    jQuery('#lc_login_form').submit();
                } else {
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }
            }
        }
    }
</script>