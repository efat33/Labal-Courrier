<?php

if (!is_user_logged_in()) {
    echo '
        <script>
            window.location.replace("' . esc_url(site_url()) . '");
        </script>
    ';
}

$user = wp_get_current_user();
$user_id = $user->ID;

$old_values = new stdClass();
$old_values->first_name = $user->user_firstname;
$old_values->last_name = $user->user_lastname;

$errors = [];
$form_updated = 0;

if (
    isset($_GET['request_status']) && $_GET['request_status'] == 'error' &&
    isset($_GET['request_id']) && !empty($_GET['request_id'])
) {
    $r_data = get_transient($_GET['request_id']);
    $old_values = (object) $r_data['old_values'];
    $errors = array_diff_key($r_data, array_flip(["old_values"]));
} else if (isset($_GET['request_status']) && $_GET['request_status'] == 'success') {
    $form_updated = 1;
}

?>

<script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>

<div x-data="component()" x-init="init()" class="lc-customer-dashboar lc-profile-page lc-form-container my-5">
    <div class="lc-dashboard-wrapper lc-grid grid-cols-1 md-grid-cols-4">
        <div class="lc-dashboard-left">
            <?php
            include LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-dashboard-menu.php';
            ?>
        </div>
        <div class="lc-dashboard-right py-3">
            <h1 class="lr-form-heading mb-4"><?= __("Profile Settings", "labal-courrier") ?></h1>

            <div class="lc-dashboard-right-wrapper p-4 rounded">
                <form id="lc_profile_setting_form" class=" w-100" action="<?php echo esc_url(site_url('wp-admin/admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="lc_profile_setting_action">
                    <?php wp_nonce_field('lc_profile_setting_nonce', 'lc_nonce'); ?>

                    <div class="profile-img lc-grid grid-cols-1 md-grid-cols-2 align-items-end mb-4">
                        <span><img src="<?= LABAL_COURRIER_PLUGIN_URL . '/public/img/default-profile-pic.jpg' ?>" alt=""></span>
                        <a href="#" class="lc-link text-center text-md-end text-decoration-underline mt-3 mt-md-0"><i class="fa-solid fa-upload"></i> <?= __("Change profile picture", "labal-courrier") ?></a>
                    </div>

                    <!-- show error message -->
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
                    <?php if ($form_updated == 1) { ?>
                        <div class="alert alert-success fade show" role="alert">
                            <?=
                            esc_html_e('Updated Successfully', 'labal-courrier');
                            ?>
                        </div>
                    <?php } ?>

                    <p class="fw-bold"><?= esc_html_e("Personal Information", "labal-courrier") ?></p>

                    <div class="lc-grid grid-cols-1 md-grid-cols-2 gap-3 mb-3">
                        <div class="lc-form-control bg-grey" id="first_name">
                            <label for="id_first_name"><?= esc_html_e("First Name", "labal-courrier") ?></label>
                            <input x-model="formData.first_name" type="text" name="first_name" class="" :class="{ ' is-invalid': !formvalidation.first_name.validated }" />
                            <div x-show="!formvalidation.first_name.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                        <div class="lc-form-control bg-grey" id="last_name">
                            <label for="id_last_name"><?= esc_html_e("Last Name", "labal-courrier") ?></label>
                            <input x-model="formData.last_name" type="text" name="last_name" class="" :class="{ 'is-invalid': !formvalidation.last_name.validated }" />
                            <div x-show="!formvalidation.last_name.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                    </div>

                    <p class="fw-bold mt-5"><?= esc_html_e("Change Password", "labal-courrier") ?></p>

                    <div class="lc-grid grid-cols-1 md-grid-cols-2 gap-3 mb-3">
                        <div class="lc-form-control bg-grey" id="password">
                            <label for="id_password"><?= esc_html_e("New Password", "labal-courrier") ?></label>
                            <input x-model="formData.password" type="password" name="password" class="" :class="{ 'is-invalid': !formvalidation.password.validated }" />
                            <div x-show="!formvalidation.password.validated" class="w-100 text-danger validation_message"><?= __("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                        <div class="lc-form-control bg-grey mb-3" id="confirm_password">
                            <label for="id_password"><?= esc_html_e("Confirm Password", "labal-courrier") ?></label>
                            <input x-model="formData.confirm_password" type="password" name="confirm_password" class="" :class="{ 'is-invalid': !formvalidation.confirm_password.validated }" />
                            <div x-show="!formvalidation.confirm_password.validated" x-text="formvalidation.confirm_password.message" class="w-100 text-danger validation_message"></div>
                        </div>
                    </div>


                    <div class="text-end mb-3 mb-md-0">
                        <button x-on:click.prevent="onSubmit()" type="submit" class="btn lc-button lc-btn-blue rounded px-4"><?= __("Update", "labal-courrier") ?></button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
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
                password: '',
                confirm_password: '',
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

                // password validation 
                if (this.formData.password != '' || this.formData.confirm_password != '') {
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
                }

                if (this.formvalidation.valid) {
                    // submit the form
                    jQuery('#lc_profile_setting_form').submit();
                } else {
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }
            }
        }
    }
</script>