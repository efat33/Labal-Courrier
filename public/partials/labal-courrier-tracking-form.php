<div x-data="component()" x-init="init()" class="lc-track-shipment lc-form-container my-5 mx-auto">
    <h1 class="heading-track-shipment mb-4"><?= esc_html_e("Track Shipment", "labal-courrier") ?></h1>

    <div class="track-shipment-form-area shadow-sm rounded p-4 mb-5 bg-white">
        <form id="frmTrackingDetails" action="<?php echo esc_attr('wp-admin/admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="get_tracking_details">

            <div class="lc-form-control mb-3">
                <label for=""><?= esc_html_e("Carrier", "labal-courrier") ?></label>
                <div class="lc-form-control-cr lc-form-control-radio lc-grid gap-4 mt-2 pb-2" id="package_type">
                    <label for="carrier_id_dhl" class="lcl-radio is-insurance ">
                        <input required <?= (isset($_GET['carrier']) && $_GET['carrier'] == 'dhl') ? 'checked' : '' ?> type="radio" name="carrier_id" class="is-insurance-field me-1" value="dhl" id="carrier_id_dhl">
                        <?= esc_html_e("DHL", "labal-courrier") ?> </label>
                    <label for="carrier_id_ups" class="lcl-radio is-insurance label-ins-disable">
                        <input required <?= (isset($_GET['carrier']) && $_GET['carrier'] == 'ups') ? 'checked' : '' ?> type="radio" name="carrier_id" class="is-insurance-field me-1" value="ups" id="carrier_id_ups">
                        <?= esc_html_e("UPS", "labal-courrier") ?> </label>
                </div>
            </div>

            <div class="lc-form-control" id="insurance_value">
                <label for="id_insurance_value"><?= esc_html_e("Enter the consignment no.", "labal-courrier") ?></label>
                <input required type="text" name="wbn" class="" value="<?= (isset($_GET['num']) && $_GET['num'] != '') ? $_GET['num'] : '' ?>" />
            </div>

            <div class="lc-form-control mt-3 text-end">
                <button type="submit" class="btn-quote btn-quote-full rounded py-2 "><?= esc_html_e("Track", "labal-courrier") ?></button>
            </div>
        </form>
    </div>

    <div id="result_area" class="track-shipment-result shadow-sm rounded p-4 mb-5 bg-white">

    </div>
</div>



<?php
if (isset($_GET['num']) && $_GET['num'] != '') {
?>

    <script>
        jQuery(function($) {
            $('#frmTrackingDetails').submit();
        });
    </script>

<?php
}
?>