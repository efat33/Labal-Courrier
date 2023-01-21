<?php
$quote_id = $_GET['quote_id'];
$rates = get_transient($quote_id); // TODO: validate quote if quote_id exists

$requirment = $rates['quote_request'];
?>
<div class="lc-wizard-container lc lc-courrier-shipment">
    <div class="lc-wizard-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2>Résumé du devis</h2>
                <div class="row sm-summary mt-3 mb-3 pt-3 pb-3 lc-content">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-12 sm-summary-destination sm-destination-a">
                                        <div class="">
                                            <span class="w-100 d-block mb-1"><b>Depuis:</b></span>
                                            <span class="w-100 d-block"><?php echo $requirment['col_city'] . ', ' . $requirment['col_postcode']; ?></span>
                                            <span class="w-100 d-block"><?php echo lc_get_country_by_code($requirment['col_country']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-12 sm-summary-destination sm-destination-b">
                                        <div class="">
                                            <span class="w-100 d-block mb-1"><b>Vers:</b></span>
                                            <span class="w-100 d-block"><?php echo $requirment['del_city'] . ', ' . $requirment['del_postcode']; ?></span>
                                            <span class="w-100 d-block"><?php echo lc_get_country_by_code($requirment['del_country']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-12 sm-summary-destination sm-parcel">
                                        <div class="">
                                            <span class="w-100 d-block mb-1"><b><?= $requirment['package_type'] == 'Package' ? 'Colis:' : 'Documents' ?></b></span>

                                            <?php foreach ($requirment['package'] as $package) : ?>
                                                <span class="w-100 d-block">
                                                    <?php
                                                    echo sprintf(
                                                        '<span>Unité:</span> %s - %s kg(%s X %s X %s cm)',
                                                        $package['qty'],
                                                        $package['weight'],
                                                        $package['length'],
                                                        $package['width'],
                                                        $package['height'],
                                                    )
                                                    ?>
                                                </span>
                                            <?php endforeach; ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <h2>Nos offres d'expédition</h2>
                <div class="row lc-content">
                    <?php foreach ($rates['quote_result'] as $rate) : ?>
                        <div class="col-lg-6 col-6 p-3">
                            <div class="row p-3">
                                <div class="col-lg-10 col-sm-12 p-0">
                                    <div class="lc-option p-3 w-100" style="float: left;">
                                        <div class="row">
                                            <div class="col-lg-4 col-sm-12" style="position: relative;">
                                                <div class="lc-carrier-branding">
                                                    <img src="<?php echo LABAL_COURRIER_PLUGIN_URL . 'public/img/dhl_logo.png'; ?>" alt="">
                                                </div>
                                            </div>
                                            <div class="col-lg-8 col-sm-12 pt-sm-3">
                                                <div class="lc-carrier-rate-details">
                                                    <p><b>Transporteur: </b><?php echo $rate['carrierName'] ?> </p>
                                                    <p><b>Prix: </b><?php echo $rate['amount'] . ' ' . $rate['currency'] ?> </p>
                                                    <p><b>Livraison pour: </b><?= date('d-m-Y', strtotime($rate['deliveryTime']))  ?> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 p-0">
                                    <form action="<?php echo site_url('wp-admin/admin-post.php'); ?>" method="POST" style="height: 100%;" class="courier-form">
                                        <div class="w-100" style="height:100%; min-height: 60px; float: left;">
                                            <input type="hidden" name="quote_id" value="<?= $_GET['quote_id'] ?>">
                                            <input type="hidden" name="action" value="get_additional_information_form">
                                            <input type="hidden" name="carrier_id" value="CARRIER_DHL">
                                            <button type="submit" class="btn btn-mid btn-shipping-option"><span style="margin-left: -5px;" class="fa fa-chevron-right"></span></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>