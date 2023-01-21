<?php
// prevent cache so when browser back button is pressed, previous data can be displayed
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0 "); // Proxies.

$order_id = $_GET['order_id'];
$status = isset($_GET['s']) ? $_GET['s'] : '';
$message = isset($_GET['m']) ? $_GET['m'] : '';

global $wpdb, $table_prefix;
$lc_shipment = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '$order_id'", ARRAY_A);

// $dir = LABAL_COURRIER_PLUGIN_PATH . 'docs/ups/1z1661964290';
// $b64Doc = chunk_split(base64_encode(file_get_contents($dir . '/Facture-en-douane-1z1661964290.pdf')));

?>
<input type="hidden" id="order_id" value="<?= $order_id ?>">
<input type="hidden" id="status" value="<?= $status ?>">
<input type="hidden" id="msg" value="<?= $message ?>">

<div x-data="component()" x-init="init()" class="lc-form-container courier-steps-page schedule-pickup-page mt-3 mb-5">
    <!-- include steps bar -->
    <?php include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-courier-steps-bar.php'; ?>

    <h1 class="heading-courier-steps mb-4"><?= __("Download Documents", "labal-courrier") ?></h1>

    <div class="lc-download-documents-area mb-5">
        <div class="col-lg-12 hidable" id="loading">
            <p style="font-size: 1.5em; font-weight: 500;"><?= esc_html_e("Please wait", "labal-courrier") ?><span id="wait">.</span></p>
        </div>
        <div class="col-lg-12 hidable" id="dl_btns" style="display: none;">
            <!-- <h2>Shipment Created Successfully</h2> -->
            <p style="margin: 25px 0 10px 0;"><?= esc_html_e("Dear Madam, dear Sir", "labal-courrier") ?></p>

            <p style="margin: 10px 0 10px 0;"><?= esc_html_e("Thank you for using the Mon Courrier de France service for your shipment.", "labal-courrier") ?></p>

            <p style="margin: 10px 0 10px 0;"><?= esc_html_e("You will find enclosed all the documents to be printed for your shipment.", "labal-courrier") ?></p>

            <p style="margin: 10px 0 10px 0;"><?= esc_html_e("You will find the shipment number of your shipment on the shipping labels. This is the Waybill number. You can follow the progress of your shipment from the following link: ", "labal-courrier") ?> : <a href="<?= esc_url(site_url() . '/tracking/') ?>" class="lc-link"><?= esc_url(site_url() . '/tracking/') ?></a></p>

            <p style="margin: 10px 0 15px 0;"><?= esc_html_e("See you soon for your next shipment.", "labal-courrier") ?></p>

            <div class="mb-3">
                <p class="fw-bold mb-2"><?= __("Your Documents To Print", "labal-courrier") ?></p>
                <a class="btn btn-lc-download mb-2 mb-sm-0" id="label_btn" href="<?php echo LABAL_COURRIER_PLUGIN_URL . '/docs/dhl/' . $quote_id . '/label.pdf' ?>" download=""><i class="fa-solid fa-download"></i> <?= __("Shipping Documents", "labal-courrier") ?></a>
                <a class="btn btn-lc-download mb-2 mb-sm-0" id="invoice_btn" href="<?php echo LABAL_COURRIER_PLUGIN_URL . '/docs/dhl/' . $quote_id . '/invoice.pdf' ?>" download=""><i class="fa-solid fa-download"></i> <?= __("Custom Invoice", "labal-courrier") ?></a>
            </div>

            <p class="fw-bold mb-2"><?= __("Your Invoice", "labal-courrier") ?></p>
            <a class="btn btn-lc-download" id="lc_invoice_btn" href="<?php echo LABAL_COURRIER_PLUGIN_URL . '/docs/dhl/' . $quote_id . '/Facture-' . $quote_id . '.pdf' ?>" download=""><i class="fa-solid fa-download"></i> <?= __('<span class="translation-block" data-context="Download Invoice">Invoice</span>', "labal-courrier") ?></a>

            <?php if (!is_user_logged_in()) { ?>
                <div class="lc-steps-btn-area has-border mt-5 text-center text-md-end">
                    <a href="#" type="submit" class="btn lc-button lc-btn-blue rounded ms-2"><?= esc_html_e("Finish", "labal-courrier") ?></a>
                </div>
            <?php } ?>
        </div>
        <div class="col-lg-12 hidable" id="msgbox" style="display: none;">
            <p style="font-size: 1.5em; font-weight: 500; color: #fff;"> <?= esc_html_e("Sorry, something went wrong...", "labal-courrier") ?></p>
        </div>
    </div>


</div>

<script>
    jQuery(function($) {
        var doc_url = lc_obj.plugin_url_root + "docs/";
        if ($('#status').val() == 'f') {
            $('.hidable').hide();
            $('#msgbox').show();
        } else {
            $.ajax({
                url: lc_obj.ajax_url,
                method: 'post',
                data: {
                    action: 'create_shipment_order',
                    order_id: $('#order_id').val()
                },
                dataType: 'json',
                success: function(response) {
                    $('.hidable').hide();
                    console.log(response);
                    if (response.status == 'success') {
                        $('#dl_btns').show();

                        if (response.carrier_dir == 'ups') {
                            $('#label_btn').attr('href', doc_url + response.carrier_dir + "/" + response.id + "/Documents-expedition-" + response.id + ".pdf")
                        } else {
                            $('#label_btn').attr('href', doc_url + response.carrier_dir + "/" + response.id + "/Documents-expedition-" + response.id + ".pdf")
                        }

                        if (response.package_type == 'Package')
                            $('#invoice_btn').attr('href', doc_url + response.carrier_dir + "/" + response.id + "/Facture-en-douane-" + response.id + ".pdf")
                        else
                            $('#invoice_btn').hide();

                        $('#lc_invoice_btn').attr('href', doc_url + response.carrier_dir + "/" + response.id + "/Facture-" + response.invoice_no + ".pdf")
                    } else if (response.status == 'fail') {
                        window.location.href = "<?= site_url() . '/error-page/' ?>";
                    }
                }
            });
        }

        var dots = window.setInterval(function() {
            var wait = $("#wait");
            if (wait.html().length > 3)
                wait.html("");
            else
                wait.append(".");
        }, 100);
    });
</script>