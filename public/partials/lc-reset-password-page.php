<?php

// if user is loggedin, then redirect to dashboard page
if (is_user_logged_in()) {
?>
    <script>
        window.location.replace("<?= esc_url(site_url('lc-profile')) ?>");
    </script>
<?php
}

$current_language = get_locale();
$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

$errors = [];

if (
    isset($_GET['request_status']) && $_GET['request_status'] == 'error' &&
    isset($_GET['request_id']) && !empty($_GET['request_id'])
) {
    $errors = get_transient($_GET['request_id']);
}

$msg_token = '';
$is_token_valid = true;

if (isset($_GET['key']) && isset($_GET['login'])) {
    $user = check_password_reset_key($_GET['key'], $_GET['login']);

    if (!$user || is_wp_error($user)) {
        $is_token_valid = false;
        if ($user && $user->get_error_code() === 'expired_key') {
            $msg_token = __("Token has expired.", "labal-courrier");
        } else {
            $msg_token = __("Token is not valid.", "labal-courrier");
        }
    }
} else {
    if (isset($_GET['request_status']) && $_GET['request_status'] == 'success') {
        // do nothing 
    } else {
        $msg_token = __("Token is not valid.", "labal-courrier");
        $is_token_valid = false;
    }
}

?>

<script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>

<div x-data="component()" class="lc-login-registration lc-login-page lc-form-container">
    <div class="lc-lr-wrapper lc-grid grid-cols-1 md-grid-cols-3 lg-grid-cols-2 shadow rounded my-5">
        <div class="lc-lr-left">
            <img class="d-none d-md-block" src="<?= LABAL_COURRIER_PLUGIN_URL . '/public/img/login-man.svg' ?>" alt="<?= esc_attr_e("Login Man", "labal-courrier") ?>">
            <img class="d-md-none" src="<?= LABAL_COURRIER_PLUGIN_URL . '/public/img/login-mbl-man.svg' ?>" alt="<?= esc_attr_e("Login Man", "labal-courrier") ?>">
        </div>
        <div class="lc-lr-right <?= $is_token_valid ? 'd-flex align-items-center' : 'm-3' ?> ">

            <?php
            if (!$is_token_valid) {
            ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $msg_token ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php
            } else {
            ?>
                <form id="lc_reset_password_form" class="lc-form w-100 p-3 p-md-5" action="<?php echo esc_url(site_url('wp-admin/admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="lc_reset_password_action">
                    <input type="hidden" name="token_key" value="<?= $_GET['key'] ?>">
                    <input type="hidden" name="user_login" value="<?= $_GET['login'] ?>">
                    <?php wp_nonce_field('lc_reset_password_nonce', 'lc_nonce'); ?>

                    <h1 class="lr-form-heading mb-4"><?= __("Reset Password", "labal-courrier") ?></h1>

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

                    <?php
                    if (isset($_GET['request_status']) && $_GET['request_status'] == 'success') {
                    ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?=
                            sprintf(
                                __('Password updated successfully. You can now <a class="lc-link text-decoration-underline" href="%s">login</a>', 'labal-courrier'),
                                esc_url($lc_site_url . '/login')
                            );
                            ?>
                        </div>
                    <?php
                    }
                    ?>

                    <div class="lc-form-control bg-grey mb-3" id="new_password">
                        <label for="id_password"><?= esc_html_e("New Password", "labal-courrier") ?></label>
                        <input x-model="formData.new_password" type="password" name="new_password" class="" :class="{ 'is-invalid': !formvalidation.new_password.validated }" />
                        <div x-show="!formvalidation.new_password.validated" class="w-100 text-danger validation_message"><?= __("This field cannot be empty", "labal-courrier") ?></div>
                    </div>

                    <div class="lc-form-control bg-grey mb-3" id="confirm_password">
                        <label for="id_password"><?= esc_html_e("Confirm Password", "labal-courrier") ?></label>
                        <input x-model="formData.confirm_password" type="password" name="confirm_password" class="" :class="{ 'is-invalid': !formvalidation.confirm_password.validated }" />
                        <div x-show="!formvalidation.confirm_password.validated" class="w-100 text-danger validation_message"><?= __("This field cannot be empty", "labal-courrier") ?></div>
                        <div x-show="!formvalidation.mismatch_password.validated" class="w-100 text-danger validation_message"><?= __("New password and confirm password do not match", "labal-courrier") ?></div>
                    </div>


                    <div class="lr-form-footer mt-4 mb-3 mb-md-0">
                        <div class="lr-form-footer-right text-end mb-3 mb-md-0">
                            <button x-on:click.prevent="onSubmit()" type="submit" class="btn lc-button lc-btn-blue rounded px-4"><?= __("Update Password", "labal-courrier") ?></button>
                        </div>
                    </div>

                </form>

            <?php } ?>
        </div>
    </div>
</div>



<script>
    function component() {
        return {
            formData: {
                new_password: '',
                confirm_password: '',
                mismatch_password: '',
            },
            formvalidation: {
                valid: true,
                new_password: {
                    validated: true,
                    message: ''
                },
                confirm_password: {
                    validated: true,
                    message: ''
                },
                mismatch_password: {
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

                // new password validation 
                if (this.formData.new_password == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.new_password.validated = false;
                    this.validationIds.push('new_password');
                }

                // confirm password validation 
                if (this.formData.confirm_password == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.confirm_password.validated = false;
                    this.validationIds.push('confirm_password');
                }

                // new and confirm password not match 
                if (this.formData.new_password != '' && this.formData.confirm_password != '' && this.formData.new_password != this.formData.confirm_password) {
                    this.formvalidation.valid = false;
                    this.formvalidation.mismatch_password.validated = false;
                    this.validationIds.push('confirm_password');
                }

                if (this.formvalidation.valid) {
                    // submit the form
                    jQuery('#lc_reset_password_form').submit();
                } else {
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }
            }
        }
    }
</script>