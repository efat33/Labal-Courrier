<?php

global $wpdb;

if (!is_user_logged_in()) {
    echo '
        <script>
            window.location.replace("' . esc_url(site_url()) . '");
        </script>
    ';
}

$table_billing = $wpdb->prefix . 'lc_billing_addresses';
$table_shipping = $wpdb->prefix . 'lc_shipping_addresses';

$user = wp_get_current_user();
$user_id = $user->ID;

$available_credit = get_user_meta($user_id, 'mnfr_referral_credit', true);
$available_credit = $available_credit != '' ? $available_credit : 0;

// get default billing address 
$default_billing_id = get_user_meta($user_id, 'lc_billing_address_default', true);
if ($default_billing_id != '') {
    $default_billing_address = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_billing WHERE id = %d", $default_billing_id));

    $billing_addresses = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_billing WHERE `user_id` = %d AND id != %d", $user_id, $default_billing_id));
} else {
    $billing_addresses = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_billing WHERE `user_id` = %d", $user_id));
}

$shipping_addresses = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_shipping WHERE `user_id` = %d", $user_id));
?>

<script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>

<div x-data="component()" x-init="init()" class="lc-customer-dashboar lc-promo-code-page lc-form-container my-5">
    <div class="lc-dashboard-wrapper lc-grid grid-cols-1 md-grid-cols-4">
        <div class="lc-dashboard-left">
            <?php
            include LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-dashboard-menu.php';
            ?>
        </div>
        <div class="lc-dashboard-right py-3">
            <h1 class="lr-form-heading mb-4"><?= __("Address Book", "labal-courrier") ?></h1>

            <div class="address-book-response-msg">

            </div>

            <div class="billing-information-area">
                <p class="address-book-subheading mb-2">
                    <?= esc_html_e("Billing Information", "labal-courrier") ?>
                </p>
                <p class="text-end mb-3">
                    <a href="<?= esc_url(site_url('lc-new-billing')) ?>" class="lc-link text-decoration-underline"><i class="fa-solid fa-plus"></i> <?= esc_html_e("New Address", "labal-courrier") ?></a>
                </p>

                <div class="address-book-grid lc-grid grid-cols-1 md-grid-cols-2 lg-grid-cols-3 gap-5">

                    <?php
                    if (isset($default_billing_address) && !empty($default_billing_address)) {
                    ?>
                        <div class="single-address-book">
                            <div class="address-info-name has-default">
                                <p class="fw-bold"><?= $default_billing_address->company ?></p>
                                <span><i class="fa-solid fa-circle-check"></i> <?= esc_html_e("Default", "labal-courrier") ?></span>
                            </div>
                            <p><?= $default_billing_address->address ?></p>
                            <p><i class="fa-solid fa-envelope me-1"></i> <?= $default_billing_address->email ?></p>

                            <div class="address-book-footer-links mt-2">
                                <p class="">
                                    <a href="<?= esc_url(site_url('lc-new-billing/?id=' . $default_billing_address->id)) ?>" class="lc-link text-decoration-underline me-2"><i class="fa-regular fa-pen-to-square"></i> <?= esc_html_e("Edit", "labal-courrier") ?></a>
                                    <a href="<?= esc_url(site_url('lc-new-billing/?action=lc_delete_billing_address&id=' . $default_billing_address->id)) ?>" class="lc-link text-decoration-underline"><i class="fa-regular fa-trash-can"></i> <?= esc_html_e("Delete", "labal-courrier") ?></a>
                                </p>
                            </div>
                        </div>
                    <?php } ?>

                    <?php
                    if (!empty($billing_addresses)) {
                        foreach ($billing_addresses as $key => $item) {
                    ?>
                            <div class="single-address-book">
                                <div class="address-info-name">
                                    <p class="fw-bold"><?= $item->company ?></p>
                                </div>
                                <p><?= $item->address ?></p>
                                <p><i class="fa-solid fa-envelope"></i> <?= $item->email ?></p>

                                <div class="address-book-footer-links mt-2">
                                    <p class="">
                                        <a href="<?= esc_url(site_url('lc-new-billing/?id=' . $item->id)) ?>" class="lc-link text-decoration-underline me-2"><i class="fa-regular fa-pen-to-square"></i> <?= esc_html_e("Edit", "labal-courrier") ?></a>
                                        <a href="<?= esc_url(site_url('lc-new-billing/?action=lc_delete_billing_address&id=' . $item->id)) ?>" class="lc-link text-decoration-underline"><i class="fa-regular fa-trash-can"></i> <?= esc_html_e("Delete", "labal-courrier") ?></a>
                                    </p>
                                    <p>
                                        <a href="<?= esc_url(site_url('lc-new-billing/?action=lc_default_billing_address&id=' . $item->id)) ?>" class="lc-link text-decoration-underline"><i class="fa-regular fa-user"></i> <?= esc_html_e("Set as Default", "labal-courrier") ?></a>
                                    </p>
                                </div>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="shipping-information-area mt-5 pt-3">
                <p class="address-book-subheading mb-2">
                    <?= esc_html_e("Shipping Information", "labal-courrier") ?>
                </p>
                <p class="text-end mb-3">
                    <a href="<?= esc_url(site_url('lc-new-shipping')) ?>" class="lc-link text-decoration-underline"><i class="fa-solid fa-plus"></i> <?= esc_html_e("New Address", "labal-courrier") ?></a>
                </p>

                <div class="address-book-grid lc-grid grid-cols-1 md-grid-cols-2 lg-grid-cols-3 gap-5">
                    <?php
                    if (!empty($shipping_addresses)) {
                        foreach ($shipping_addresses as $key => $item) {
                    ?>
                            <div class="single-address-book">
                                <div class="address-info-name">
                                    <p class="fw-bold"><?= $item->sender_first_name ?> <?= $item->sender_last_name ?></p>
                                </div>
                                <p><?= $item->sender_address ?></p>
                                <p>
                                    <?php
                                    $post_arr = explode('--', $item->col_postcode_or_city);
                                    $post_arr_filterd = array_filter($post_arr, fn ($value) => $value != '');

                                    echo implode(", ", $post_arr_filterd);
                                    ?>
                                </p>
                                <p><?= lc_get_country_by_code($item->col_country) ?></p>
                                <p><i class="fa-solid fa-phone me-1"></i> <?= $item->sender_phone_number ?></p>
                                <p><i class="fa-solid fa-envelope me-1"></i> <?= $item->sender_email ?></p>

                                <div class="address-book-footer-links mt-2">
                                    <p class="">
                                        <a href="<?= esc_url(site_url('lc-new-shipping/?id=' . $item->id)) ?>" class="lc-link text-decoration-underline me-2"><i class="fa-regular fa-pen-to-square"></i> <?= esc_html_e("Edit", "labal-courrier") ?></a>
                                        <a href="<?= esc_url(site_url('lc-new-shipping/?action=lc_delete_shipping_address&id=' . $item->id)) ?>" class="lc-link text-decoration-underline"><i class="fa-regular fa-trash-can"></i> <?= esc_html_e("Delete", "labal-courrier") ?></a>
                                    </p>
                                </div>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function component() {
        return {

        }
    }
</script>