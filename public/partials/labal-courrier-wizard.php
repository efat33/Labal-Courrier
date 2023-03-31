<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_language = get_locale();
$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

$quote_id = $_GET['quote_id'];
$rates = get_transient($quote_id); // TODO: validate quote if quote_id exists

$requirment = $rates['quote_request'];
// echo '<pre>';
// print_r($rates['quote_result']);
?>

<div x-data="component()" class="lc-form-container shipping-offers-page mb-5">

    <?php if ($requirment['quote_type'] == 'full') { ?>
        <div class="lc-shipping-details-area shadow-sm rounded p-4 mb-5">
            <div class="lc-shipping-details-wrapper lc-grid gap-3 md-gap-4">
                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("From", "labal-courrier") ?></label>
                    <span><?= lc_get_country_by_code($requirment['col_country']) ?></span>
                    <span><?= $requirment['col_city'] ?>, <?= $requirment['col_postcode'] ?></span>
                </div>
                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("To", "labal-courrier") ?></label>
                    <span><?= lc_get_country_by_code($requirment['del_country']) ?></span>
                    <span><?= $requirment['del_city'] ?>, <?= $requirment['del_postcode'] ?></span>
                </div>
                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("Dimensions", "labal-courrier") ?></label>
                    <?php foreach ($requirment['package'] as $package) { ?>
                        <span><?= $package['length'] ?><?= esc_html_e("cm", "labal-courrier") ?> | <?= $package['width'] ?><?= esc_html_e("cm", "labal-courrier") ?> | <?= $package['height'] ?><?= esc_html_e("cm", "labal-courrier") ?></span>
                    <?php } ?>
                </div>
                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("Insurance", "labal-courrier") ?></label>
                    <span><?= $requirment['insurance'] == 1 ? esc_html_e("Yes", "labal-courrier") : esc_html_e("No", "labal-courrier") ?></span>
                </div>
                <?php
                if ($requirment['insurance'] == 1 && $requirment['insurance_value'] != '') {
                ?>
                    <div class="single-shipping-details">
                        <label for=""><?= esc_html_e("Item Value", "labal-courrier") ?></label>
                        <span>€<?= $requirment['insurance_value'] ?></span>
                    </div>
                <?php } ?>
                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("Weight", "labal-courrier") ?></label>
                    <?php foreach ($requirment['package'] as $package) { ?>
                        <span><?= $package['weight'] ?><?= esc_html_e("kg", "labal-courrier") ?></span>
                    <?php } ?>
                </div>
                <div class="single-shipping-details single-shipping-details-edit-btn text-md-end">
                    <a href="<?= esc_url($lc_site_url) ?>/shipping-calculator?quote_id=<?= $quote_id ?>" class="btn lc-button lc-btn-blue rounded"><i class="fa-regular fa-pen-to-square"></i> <?= esc_html_e("Edit Preference", "labal-courrier") ?></a>
                </div>
            </div>
        </div>
    <?php } ?>

    <div class="lc-shipping-offers-area lc-grid md-gap-3">
        <?php
        $dispatch_date = $requirment['dispatch_date'];
        foreach ($rates['quote_result'] as $key => $rate) {

            $carrier_id = '';
            $carrier_logo = '';
            if ($rate['carrierName'] == 'DHL Express') {
                $carrier_logo = 'dhl_logo.png';
                $carrier_id = 'CARRIER_DHL';
            } else if ($rate['carrierName'] == 'UPS') {
                $carrier_logo = 'ups_logo.png';
                $carrier_id = 'CARRIER_UPS';
            }

            // calculate estimated delivery days
            $delivery_date = $rate['deliveryTime'];
            $estimated_days = '';
            if ($delivery_date != '') {
                $earlier = new DateTime($dispatch_date);
                $later = new DateTime($delivery_date);
                $estimated_days = $later->diff($earlier)->format("%a");
            }

            // calculate total vat 
            $total_vat = 0;
            if ($rate['vat_amount'] > 0 && $rate['is_vat_applicable_to_carrier'] == 1) {
                $margin = $rate['labal_margin'];
                $margin_with_vat = $margin * 1.2;
                $carrier_total_without_vat = $rate['carrier_rate'] - $rate['carrier_vat'];
                $carrier_total_with_vat = $rate['carrier_rate'];

                $total_without_vat = $carrier_total_without_vat +  $margin;
                $total_with_vat = $carrier_total_with_vat +  $margin_with_vat;
                $total_vat = number_format((float)($total_with_vat - $total_without_vat), 2, '.', '');
            } else if ($rate['vat_amount'] > 0) {
                $total_vat = $rate['vat_amount'];
            }

        ?>
            <div class="single-shipping-offer rounded mb-3">
                <div class="shipping-offer-top px-4 py-3">
                    <img class="shipping-offer-logo mb-2 mb-lg-0" src="<?php echo LABAL_COURRIER_PLUGIN_URL . 'public/img/' . $carrier_logo; ?>" alt="">

                    <form action="<?php echo site_url('wp-admin/admin-post.php'); ?>" method="POST" style="height: 100%;" class="courier-form">
                        <input type="hidden" name="quote_id" value="<?= $_GET['quote_id'] ?>">
                        <input type="hidden" name="current_language" value="<?= $current_language ?>">
                        <input type="hidden" name="carrier_arr_id" value="<?= $key ?>">
                        <input type="hidden" name="action" value="get_additional_information_form">
                        <input type="hidden" name="carrier_id" value="<?= $carrier_id ?>">

                        <div class="shipping-offer-info-details lc-grid grid-align-items-center gap-2">
                            <div class="shipping-info-estimated-days">
                                <?php if (isset($estimated_days) && $estimated_days != '') { ?>
                                    <h3><?= sprintf(_n('%s Day', '%s Days', $estimated_days, 'labal-courrier'), $estimated_days) ?></h3>
                                    <span class="lc-text-note"><?= esc_html_e("Estimated Business Days", "labal-courrier") ?></span>
                                <?php } ?>
                            </div>
                            <div class="shipping-info-estimated-arrival">
                                <p><i class="fa-solid fa-paper-plane"></i> &nbsp;&nbsp;<?= sprintf(__("Departure: %s", "labal-courrier"), date('d/m', strtotime($dispatch_date))) ?></p>
                                <?php if ($delivery_date != '') { ?>
                                    <p><i class="fa-solid fa-plane-arrival"></i> &nbsp;&nbsp;<?= sprintf(__("Arrival: %s", "labal-courrier"), date('d/m', strtotime($delivery_date))) ?></p>
                                <?php } ?>
                            </div>
                            <div class="shipping-info-estimated-service-type">
                                <?= $requirment['is_pickup_required'] == 1 ? __("Pick Up Service", "labal-courrier") : __("Drop Off", "labal-courrier") ?>
                            </div>
                            <div class="shipping-info-estimated-pricing lg-grid-justify-self-end">
                                <h3>€<?= $rate['amount'] ?></h3>
                                <?php if ($requirment['quote_type'] == 'full') { ?>
                                    <button type="submit" class="btn lc-button lc-yellow-btn rounded d-none d-lg-block"><?= esc_html_e("Book Now", "labal-courrier") ?></button>
                                <?php } else { ?>
                                    <a href="<?= esc_url($lc_site_url) ?>/shipping-calculator?quote_id=<?= $quote_id ?>" class="btn lc-button lc-yellow-btn rounded d-none d-lg-block"><?= esc_html_e("Book Now", "labal-courrier") ?></a>
                                <?php } ?>

                                <span class="lc-text-note"><?= esc_html_e("Taxes included", "labal-courrier") ?></span>
                            </div>
                        </div>
                        <div class="shipping-offer-more-info mt-2 mt-lg-0">
                            <?php if ($requirment['quote_type'] == 'full') { ?>
                                <button type="submit" class="btn lc-button lc-yellow-btn rounded w-100 d-lg-none"><?= esc_html_e("Book Now", "labal-courrier") ?></button>
                            <?php } else { ?>
                                <a href="<?= esc_url($lc_site_url) ?>/shipping-calculator?quote_id=<?= $quote_id ?>" class="btn lc-button lc-yellow-btn rounded w-100 d-lg-none"><?= esc_html_e("Book Now", "labal-courrier") ?></a>
                            <?php } ?>
                        </div>
                    </form>
                </div>
                <div class="shipping-offer-bottom">
                    <div class="shipping-offer-bottom-wrapper lc-grid grid-cols-1 <?= ((isset($rate['insurance']) && $rate['insurance']) > 0 || (isset($rate['pickup']) && $rate['pickup']) > 0) ? 'lg-grid-cols-3' : 'lg-grid-cols-2' ?> ">
                        <!-- <div>
                            <p><?= sprintf(__("Transportation (fee): %s", "labal-courrier"), $rate['service_charge'] > 0 ? '€' . $rate['service_charge'] : 0) ?></p>
                            <p><?= sprintf(__("Fuel Surcharge: %s", "labal-courrier"), $rate['fuel_surcharge'] > 0 ? '€' . $rate['fuel_surcharge'] : 0) ?></p>
                            <p><?= sprintf(__("Emergency Situation: %s", "labal-courrier"), $rate['emergency_situation'] > 0 ? '€' . $rate['emergency_situation'] : 0) ?></p>
                        </div> -->
                        <?php
                        if ($rate['insurance'] > 0 || $rate['pickup'] > 0) {
                        ?>
                            <div>
                                <?php if ($rate['insurance'] > 0) { ?> <p><?= sprintf(__("Insurance (fee): %s", "labal-courrier"), $rate['insurance'] > 0 ? '€' . $rate['insurance'] : 0) ?></p> <?php } ?>
                                <?php if ($rate['pickup'] > 0) { ?> <p><?= sprintf(__("Pick-up (fee): %s", "labal-courrier"), $rate['pickup'] > 0 ? '€' . $rate['pickup'] : 0) ?> </p><?php } ?>
                            </div>
                        <?php } ?>
                        <div>
                            <p><?= sprintf(__("VAT: %s", "labal-courrier"), $total_vat) ?></p>
                        </div>
                        <div class="lg-grid-justify-self-end">
                            <p><strong><?= sprintf(__("Total: €%s", "labal-courrier"), $rate['amount']) ?></strong></p>
                        </div>
                    </div>
                </div>
                <div class="shipping-offer-footer text-center"><i class="fa-solid fa-angles-down"></i></div>
            </div>
        <?php } ?>
    </div>
</div>


<?php
include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-faq.php';
?>


<script>
    jQuery(document).ready(function() {
        jQuery('.shipping-offer-footer').click(function() {
            jQuery(this).prev('.shipping-offer-bottom').slideToggle(300);
        });
    });

    // jQuery('.single-shipping-offer button').click(function(e) {
    //     e.preventDefault();
    //     jQuery(this).closest('form').submit();
    //     jQuery(".lc-loading-modal").fadeIn();
    // });
</script>