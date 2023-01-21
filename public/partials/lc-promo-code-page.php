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

$available_credit = get_user_meta($user_id, 'mnfr_referral_credit', true);
$available_credit = $available_credit != '' ? $available_credit : 0;

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
            <h1 class="lr-form-heading mb-4"><?= __("Promo Code", "labal-courrier") ?></h1>

            <div class="lc-dashboard-right-wrapper p-4">
                <div class="promo-code-wrapper">
                    <p><i class="fa-solid fa-gift"></i> <?= sprintf(__("Available credits %s euros", "labal-courrier"), $available_credit) ?></p>
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