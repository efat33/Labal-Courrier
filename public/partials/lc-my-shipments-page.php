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

global $wpdb;

$limit = 100;

if (isset($_GET['wpcfes']) && $_GET['wpcfes'] != '') {
    $sql_prepare = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}lc_shipments WHERE tracking_number = '%s' LIMIT 1", $_GET['wpcfes']);
} else {
    $sql = "SELECT * FROM {$wpdb->prefix}lc_shipments";

    $params = [];
    $params_value = [];

    // $params[] = "(dispatch_date BETWEEN '%s' AND '%s')";
    // $params_value[] = $date_start;
    // $params_value[] = $date_end;

    $params[] = "tracking_number IS NOT NULL";

    // if loggedin user is not admin, show the shipments only for that user, otherwise show all the shipments
    if (!current_user_can('administrator')) {
        $params[] = "user_id = %d";
        $params_value[] = get_current_user_id();
    }

    if (isset($_GET['shipper']) && $_GET['shipper'] != '') {
        $params[] = "sender_first_name LIKE '%s'";
        $params_value[] = urldecode($_GET['shipper']);
    }
    if (isset($_GET['receiver']) && $_GET['receiver'] != '') {
        $params[] = "receiver_first_name LIKE '%s'";
        $params_value[] = urldecode($_GET['receiver']);
    }

    if (count($params) > 0) {
        $sql .= " WHERE " . implode(" AND ", $params);
    }

    $sql .= " ORDER BY dispatch_date DESC LIMIT {$limit}";

    $sql_prepare = $wpdb->prepare($sql, $params_value);
}

$results = $wpdb->get_results($sql_prepare);

if (count($results) > 0) {

    // prepare all the lc_shipment_ID in an array so as to query all the invoices 
    $shipment_ids = array_map(fn ($item) => $item->lc_shipment_ID, $results);
    $shipment_ids_str = implode(',', $shipment_ids);

    // get all invoices, in order to get invoice number 
    $sql_invoice = "SELECT * FROM {$wpdb->prefix}lc_invoices  WHERE `shipment_id` IN ($shipment_ids_str)";
    $sql_invoice_prepare = $wpdb->prepare($sql_invoice);
    $invoices = $wpdb->get_results($sql_invoice_prepare, ARRAY_A);

    $invoices_arr = [];
    if (count($invoices) > 0) {
        $invoices_arr = array_combine(array_column($invoices, 'shipment_id'), array_column($invoices, 'inv_number'));
    }
}

?>

<script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>

<div x-data="component()" x-init="init()" class="lc-customer-dashboar lc-shipment-page lc-form-container my-5">
    <div class="lc-dashboard-wrapper lc-grid grid-cols-1 md-grid-cols-4">
        <div class="lc-dashboard-left">
            <?php
            include LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-dashboard-menu.php';
            ?>
        </div>
        <div class="lc-dashboard-right py-3">
            <h1 class="lr-form-heading mb-4 pb-4"><?= __("My Shipments", "labal-courrier") ?></h1>
            <div class="lc-dashboard-right-wrapper rounded">
                <div class="lc-shipment-table">
                    <div class="lc-shipment-table-heading lc-grid gap-3 p-3">
                        <span><?= esc_html_e("Tracking Number", "labal-courrier") ?></span>
                        <span><?= esc_html_e("Shipper Name", "labal-courrier") ?></span>
                        <span><?= esc_html_e("Receiver Name", "labal-courrier") ?></span>
                        <span><?= esc_html_e("Shipment Type", "labal-courrier") ?></span>
                        <span><?= esc_html_e("Est. Charge", "labal-courrier") ?></span>
                        <span><?= esc_html_e("Print", "labal-courrier") ?></span>
                    </div>
                    <?php
                    foreach ($results as $key => $item) {
                        $waybill_number = isset($item->tracking_number) ? $item->tracking_number : '';
                        $only_carrier_dir =  strtolower(str_replace('CARRIER_', '', $item->selected_carrier_id));
                    ?>
                        <div class="lc-shipment-table-body lc-grid gap-3 p-3 grid-align-items-center">
                            <span><a href="<?= esc_url(site_url()) ?>/tracking/?num=<?= $item->tracking_number ?>" class="lc-link"><?= $item->tracking_number ?></a></span>
                            <span><?= $item->sender_first_name . ' ' . $item->sender_last_name ?></span>
                            <span><?= $item->receiver_first_name . ' ' . $item->receiver_last_name ?></span>
                            <span><?= $item->shipment_type ?></span>
                            <span><?= $item->refn_discount != '' && $item->refn_discount > 0 ? $item->total_amount - $item->refn_discount : $item->total_amount ?></span>
                            <span class="print-shipment">
                                <div class="dropdown" style="display:inline-block !important;">
                                    <!--Trigger-->
                                    <button class="btn btn-default btn-sm dropdown-toggle m-0 py-1 px-2 waves-effect waves-light" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-print"></i></button>
                                    <!--Menu-->
                                    <div class="dropdown-menu dropdown-primary px-2">

                                        <?php if ($item->selected_carrier_id == 'CARRIER_UPS') { ?>
                                            <a target="_blank" data-elementor-open-lightbox="no" class="dropdown-docs print-invoice py-1" data-type="invoice" href="<?= esc_url(site_url()) ?>/wp-content/plugins/labal-courrier/docs/<?= $only_carrier_dir ?>/<?= $item->tracking_number ?>/Documents-expedition-<?= $waybill_number ?>.png"><?= esc_html_e('Label', 'labal-courrier') ?></a>
                                        <?php } else { ?>
                                            <a target="_blank" class="dropdown-docs print-invoice py-1" data-type="invoice" href="<?= esc_url(site_url()) ?>/wp-content/plugins/labal-courrier/docs/<?= $only_carrier_dir ?>/<?= $item->tracking_number ?>/Documents-expedition-<?= $waybill_number ?>.pdf"><?= esc_html_e('Label', 'labal-courrier') ?></a>
                                        <?php } ?>

                                        <?php if ($item->package_type == 'Package') { ?>
                                            <a target="_blank" class="dropdown-docs print-label py-1" data-type="label" href="<?= esc_url(site_url()) ?>/wp-content/plugins/labal-courrier/docs/dhl/<?= $item->tracking_number ?>/Facture-en-douane-<?= $waybill_number ?>.pdf"><?= esc_html_e('Custom Invoice', 'labal-courrier') ?></a>
                                        <?php } ?>

                                        <?php if (isset($invoices_arr[$item->lc_shipment_ID])) { ?>
                                            <a target="_blank" class="dropdown-docs print-waybill py-1" data-type="waybill" href="<?= esc_url(site_url()) ?>/wp-content/plugins/labal-courrier/docs/dhl/<?= $item->tracking_number ?>/Facture-<?= $invoices_arr[$item->lc_shipment_ID] ?>.pdf"><?= esc_html_e('Invoice', 'labal-courrier') ?></a>
                                        <?php } ?>

                                    </div>
                                </div>
                            </span>
                        </div>
                    <?php } ?>
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