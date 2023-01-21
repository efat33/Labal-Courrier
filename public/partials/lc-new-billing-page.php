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

// get the existing data in case of editing 
if (isset($_GET['id'])) {
    global $wpdb;
    $table = $wpdb->prefix . 'lc_billing_addresses';
    $address = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $_GET['id']));

    $old_values = $address;
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
            <h1 class="lr-form-heading mb-4"><?= isset($_GET['id']) ? esc_html_e("Edit Billing Address", "labal-courrier") : esc_html_e("Create Billing Address", "labal-courrier") ?></h1>

            <div class="lc-dashboard-right-wrapper p-4 rounded">
                <form id="lc_billing_address_form" class=" w-100" action="<?php echo esc_url(site_url('wp-admin/admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="lc_billing_address_action">
                    <?php if (isset($_GET['id'])) { ?>
                        <input type="hidden" name="address_id" value="<?= $_GET['id'] ?>">
                    <?php } ?>
                    <?php wp_nonce_field('lc_billing_address_nonce', 'lc_nonce'); ?>

                    <!-- show error message -->
                    <div style="<?php echo (count($errors) > 0) ? "" : "display: none;" ?>" class="w-100 common_error text-danger validation_message mb-3">
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
                        <div class="alert alert-success fade show mb-3" role="alert">
                            <?=
                            isset($_GET['edited']) ? esc_html_e('Updated Successfully', 'labal-courrier') : esc_html_e('Created Successfully', 'labal-courrier');
                            ?>
                        </div>
                    <?php } ?>


                    <div class="lc-form-control bg-grey mb-3" id="company">
                        <label for="id_company"><?= esc_html_e("Full Name or Company", "labal-courrier") ?></label>
                        <input x-model="formData.company" type="text" name="company" class="" />
                        <div x-show="!formvalidation.company.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                    </div>
                    <div class="lc-form-control bg-grey mb-3" id="address">
                        <label for="id_address"><?= esc_html_e("Address", "labal-courrier") ?></label>
                        <input x-model="formData.address" type="text" name="address" class="" />
                        <div x-show="!formvalidation.address.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                    </div>
                    <div class="lc-form-control bg-grey mb-3" id="email">
                        <label for="id_email"><?= esc_html_e("Email", "labal-courrier") ?></label>
                        <input x-model="formData.email" type="text" name="email" class="" :class="{ 'is-invalid': !formvalidation.email.validated }" />
                        <div x-show="!formvalidation.email.validated" class="w-100 text-danger validation_message"><?= esc_html_e("Please provide a valid email address", "labal-courrier") ?></div>
                    </div>


                    <div class="text-end mb-3 mb-md-0">
                        <button x-on:click.prevent="onSubmit()" type="submit" class="btn lc-button lc-btn-blue rounded px-4"><?= isset($_GET['id']) ? __("Update", "labal-courrier") : __("Create", "labal-courrier") ?></button>
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
                company: '<?= isset($old_values->company) ? $old_values->company : "" ?>',
                address: '<?= isset($old_values->address) ? $old_values->address : "" ?>',
                email: '<?= isset($old_values->email) ? $old_values->email : "" ?>',
            },
            formvalidation: {
                valid: true,
                company: {
                    validated: true,
                    message: ''
                },
                address: {
                    validated: true,
                    message: ''
                },
                email: {
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

                if (this.formData.company == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.company.validated = false;
                    this.validationIds.push('company');
                }
                if (this.formData.address == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.address.validated = false;
                    this.validationIds.push('address');
                }

                // password validation 
                if (this.formData.email == '' || !validateEmail(this.formData.email)) {
                    this.formvalidation.valid = false;
                    this.formvalidation.email.validated = false;
                    this.validationIds.push('email');
                }

                if (this.formvalidation.valid) {
                    jQuery(".lc-common-loading").fadeIn();

                    // submit the form
                    jQuery('#lc_billing_address_form').submit();
                } else {
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }
            }
        }
    }
</script>