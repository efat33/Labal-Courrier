<?php
// prevent cache so when browser back button is pressed, previous data can be displayed
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0 "); // Proxies.

$current_language = get_locale();
$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

global $wpdb, $table_prefix;
$pickup_details = [];
if (isset($_GET['shipment_id'])) {
    $shipment_id = $_GET['shipment_id'];
    $shipment_details = $wpdb->get_results('SELECT * FROM ' . $table_prefix . 'lc_shipments WHERE lc_shipment_ID = "' . $shipment_id . '"');

    $s_data = isset($shipment_details[0]) ? $shipment_details[0] : '';

    $selected_carrier_id = $s_data->selected_carrier_id;  // CARRIER_UPS

    $pickup_details = unserialize($shipment_details[0]->get_quote_result);
    // $pickup_cutoff_time = ISO8601ToMinutes($pickup_details['cutoffTimeOffset']);

    $pickup_cutoff_time = 120;

    if ($s_data->sender_country_code == 'MX' || $s_data->sender_country_code == 'PE') {
        $pickup_cutoff_time = 180;
    } else {
        $pickup_cutoff_time = 120;
    }

    if ($selected_carrier_id == 'CARRIER_UPS') {
        $pickup_details['pickupWindowEarliestTime'] = '9:00:00';
        $pickup_details['pickupWindowLatestTime'] = '17:00:00';
    }


    /**
     * check the difference between earliest_pickup_time and latest_pickup_time
     * if it's less than $pickup_cutoff_time, then add x hours to latest_pickup_time
     * so users can choose the time slots and time difference would be minimum 120 minutes
     */
    $time1 = strtotime($pickup_details['pickupWindowEarliestTime']);
    $time2 = strtotime($pickup_details['pickupWindowLatestTime']);
    $difference = round(abs($time2 - $time1) / 3600, 2);
    $difference_min = round(abs($time2 - $time1) / 60, 2);

    // if the difference between earliest and latest pickup time is less than 120, then set pickup_cutoff_time to the difference between earliest and latest pickup time 
    if ($difference_min < $pickup_cutoff_time) $pickup_cutoff_time = $difference_min;
    $required_hour_diffn = round($pickup_cutoff_time / 60, 2);

    if ($difference <= $required_hour_diffn) {
        $hours_tobe_increased = round($required_hour_diffn - $difference + 1);
        $pickup_details['pickupWindowLatestTime'] = date('H:i:s', strtotime($pickup_details['pickupWindowLatestTime'] . " +" . $hours_tobe_increased . " hours"));
    }


    $errors = [];
    if (isset($_GET['request_status']) && $_GET['request_status'] == 'error' && isset($_GET['request_id']) && !empty($_GET['request_id'])) {
        $errors = get_transient($_GET['request_id']);
    }
    // $selected_carrier_id = 'CARRIER_DHL';
    // $s_data->is_pickup_required = 0;

    if ($s_data->is_pickup_required == 0) {
        if ($selected_carrier_id == 'CARRIER_UPS') {
            require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/api/ups/UPS.php';
            $ups = new UPS();
            $locations = $ups->getDropoffLocations($s_data);
        } else {
            require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/api/dhl-curl/DHL.php';
            $dhl = new DHL('labalFR', 'Q^2oU$8lI#1a', '950455439');
            $locations = $dhl->getDropoffLocations($s_data);
        }
    }

?>
    <script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>
    <script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/jquery.nicescroll.min.js"></script>

    <div x-data="component()" x-init="init()" class="lc-form-container courier-steps-page schedule-pickup-page mt-3 mb-5">
        <!-- include steps bar -->
        <?php include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-courier-steps-bar.php'; ?>

        <h1 class="heading-courier-steps mb-4"><?= __("Pick-Up Details", "labal-courrier") ?></h1>

        <div class="lc-shipping-details-area shadow-sm rounded p-4 mb-5 bg-white">
            <div class="lc-shipping-details-wrapper lc-grid gap-3 md-gap-4">
                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("Summary", "labal-courrier") ?></label>
                    <span><?= implode(', ', array_filter(unserialize($s_data->sender_address), fn ($value) => trim($value) != '')) ?></span>
                    <span><?= $s_data->sender_city ?><?= $s_data->sender_postcode != '' ? ', ' . $s_data->sender_postcode : '' ?></span>
                    <span><?= lc_get_country_by_code($s_data->sender_country_code) ?></span>
                </div>
                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("Dimensions", "labal-courrier") ?></label>
                    <?php
                    $packages = unserialize($s_data->packages);
                    foreach ($packages as $package) { ?>
                        <span><?= $package['length'] ?><?= esc_html_e("cm", "labal-courrier") ?> | <?= $package['width'] ?><?= esc_html_e("cm", "labal-courrier") ?> | <?= $package['height'] ?><?= esc_html_e("cm", "labal-courrier") ?></span>
                    <?php } ?>
                </div>
                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("Weight", "labal-courrier") ?></label>
                    <?php foreach ($packages as $package) { ?>
                        <span><?= $package['weight'] ?><?= esc_html_e("kg", "labal-courrier") ?></span>
                    <?php } ?>
                </div>

                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("Insurance", "labal-courrier") ?></label>
                    <span><?= $s_data->insurance == 1 ? esc_html_e("Yes", "labal-courrier") : esc_html_e("No", "labal-courrier") ?></span>
                </div>
                <?php
                if ($s_data->insurance == 1 && $s_data->insurance_value != '') {
                ?>
                    <div class="single-shipping-details">
                        <label for=""><?= esc_html_e("Item Value", "labal-courrier") ?></label>
                        <span>€<?= $s_data->insurance_value ?></span>
                    </div>
                <?php } ?>

                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("Service Type", "labal-courrier") ?></label>
                    <span><?= $s_data->is_pickup_required == 1 ? esc_html_e("Pick-up Service", "labal-courrier") : esc_html_e("Drop-off Service", "labal-courrier") ?></span>
                </div>
            </div>
        </div>

        <form id="frm_customs_declaration" class="wpcsr-quote-book mb-0" action="<?php echo esc_url(site_url('wp-admin/admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="submit_pickup_details">
            <input type="hidden" name="current_language" value="<?= $current_language ?>">
            <input type="hidden" name="shipment_id" value="<?= $shipment_id ?>">

            <?php
            if (count($errors) > 0) {
            ?>
                <div class="row">
                    <div class="col-12">
                        <?php
                        foreach ($errors as $key => $value) {
                            if (!is_array($value)) {
                                echo '<div class="w-100 mb-3 pt-1 pb-1 text-white common_error validation_message">';
                                echo $value;
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php if ($s_data->is_pickup_required == 1) { ?>

                <div class="lc-pickup-location-area shadow-sm rounded p-4 mb-5 bg-white">
                    <div class="lc-form-control mb-3">
                        <label for=""><?= esc_html_e("Select your desired pick up location", "labal-courrier") ?></label>
                        <div class="lc-form-control-cr lc-form-control-radio mt-2 pb-2" id="pickup_location">
                            <label for="pickup-location-reception" class="lcl-radio mb-3">
                                <input x-model="formData.pickup_location" type="radio" name="pickup_location" class="me-1" value="Reception" id="pickup-location-reception">
                                <?= esc_html_e("Reception", "labal-courrier") ?> </label>
                            <label for="pickup-location-other" class="lcl-radio">
                                <input x-model="formData.pickup_location" type="radio" name="pickup_location" class="me-1" value="Other" id="pickup-location-other">
                                <?= esc_html_e("Other", "labal-courrier") ?> </label>
                        </div>
                        <div x-show="!formvalidation.pickup_location.validated" class="w-100 text-danger validation_message"><?= esc_html_e("Please choose your preference", "labal-courrier") ?></div>
                    </div>
                    <div class="lc-form-control" id="">
                        <label for="sender_last_name"><?= esc_html_e("Details", "labal-courrier") ?></label>
                        <textarea x-model="formData.special_pickup_instructions" class="form-control" rows="3" maxlength="40" name="special_pickup_instructions" id="special_pickup_instructions"></textarea>
                    </div>
                    <!-- <a class="lc-link text-decoration-underline mt-4 d-block text-end fs-6" href="#"><i class="fa-solid fa-truck-ramp-box me-1"></i>Carrier Instructions</a> -->
                </div>

                <div class="lc-pickup-time-area shadow-sm rounded p-4 mb-5 bg-white">
                    <p><?= __("Shipment Pick-Up", "labal-courrier") ?></p>
                    <p class="lc-text-note-pickup"><?= esc_html_e("Veuillez choisir l'heure de collecte entre", "labal-courrier") ?> <span id="earliest_time"><?= substr($pickup_details['pickupWindowEarliestTime'], 0, strlen($pickup_details['pickupWindowEarliestTime']) - 3) ?></span><?= esc_html_e("h et", "labal-courrier") ?> <span id="latest_time"><?= substr($pickup_details['pickupWindowLatestTime'], 0, strlen($pickup_details['pickupWindowLatestTime']) - 3) ?></span><?= esc_html_e("h", "labal-courrier") ?></p>
                    <p class="lc-text-note-pickup"><?= esc_html_e("Veuillez prévoir plus de", "labal-courrier") ?> <span id="pickup_cutoff_time"><?= $pickup_cutoff_time ?></span> <?= esc_html_e("minutes pour votre fenêtre de collecte", "labal-courrier") ?>.</p>

                    <div class="lc-pickup-time-grid lc-grid grid-cols-2 gap-3 mt-3" id="time_difference">
                        <div class="lc-form-control">
                            <label for=""><?= esc_html_e("From", "labal-courrier") ?></label>
                            <select x-model="formData.e_pickup_time" class="form-control" name="e_pickup_time" id="e_pickup_time"></select>
                            <div x-show="!formvalidation.e_pickup_time.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                        <div class="lc-form-control">
                            <label for=""><?= esc_html_e("To", "labal-courrier") ?></label>
                            <select x-model="formData.l_pickup_time" class="form-control" name="l_pickup_time" id="l_pickup_time"></select>
                            <div x-show="!formvalidation.l_pickup_time.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                    </div>
                    <div class="lc-form-control">
                        <div x-show="!formvalidation.time_difference.validated" class="w-100 validation_message"><?= esc_html_e("Veuillez prévoir plus de", "labal-courrier") ?> <span id="pickup_cutoff_time"><?= $pickup_cutoff_time ?></span> <?= esc_html_e("minutes pour votre fenêtre de collecte", "labal-courrier") ?>.</div>
                    </div>
                </div>

            <?php } else { ?>
                <div class="pickup-map-area lc-grid grid-cols-1 md-grid-cols-3 gap-2">
                    <div class="pickup-map-left">
                        <?php
                        $dropofff_location = ($selected_carrier_id == 'CARRIER_UPS') ? 'https://www.ups.com/dropoff/' : "https://locator.dhl.com/";
                        if (isset($locations) && count($locations) > 0) {
                            foreach ($locations as $key => $item) { ?>
                                <div class="single-pickup-location-details shadow-sm bg-white">

                                    <div class="p-3">
                                        <p class="fw-bold"><?= $item['company_name'] ?></p>
                                        <p><?= $item['address'] ?></p>
                                        <p><?= $item['city'] ?>, <?= $item['postcode'] ?></p>
                                    </div>
                                    <div class="opening-hours-area p-3">
                                        <p class="fw-bold"><?= __("Opening Hours", "labal-courrier") ?></p>

                                        <div class="opening-hours-grid-wrapper">
                                            <?php foreach ($item['openingHours'] as $i => $hour) { ?>
                                                <div class="lc-grid grid-cols-2 gap-2">
                                                    <p><?= $i ?></p>
                                                    <p>
                                                        <?php foreach ($hour as $value) { ?>
                                                            <span><?= $value[0] ?> - <?= $value[1] ?></span>
                                                        <?php } ?>
                                                    </p>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="opening-hours-expand text-center"><i class="fa-solid fa-angles-down"></i></div>
                                </div>

                        <?php
                            }
                        } else {
                            echo '<div class="no-dropoff text-center px-2">
                                    <p>' . __("Don't see an office? No worries.", "labal-courrier") . '</p>
                                    <p>' . sprintf(__("Log in <a href='%s' target='_blank' class='lc-link text-decoration-underline'>here</a> and find a drop-off location.", "labal-courrier"), esc_url($dropofff_location)) . '</p>
                                  </div>';
                        }
                        ?>
                    </div>
                    <div class="pickup-map-right">
                        <div id="map_lc_dropoff"></div>
                        <a target="_blank" class="lc-link text-decoration-underline mt-2 d-block text-end fs-6" href="<?= $dropofff_location ?>"><i class="fa-solid fa-location-crosshairs me-1"></i><?= esc_html_e("Location Finder", "labal-courrier") ?></a>
                    </div>
                </div>
            <?php } ?>

            <div class="lc-steps-btn-area has-border mt-5 text-center text-md-end">
                <a href="<?= esc_url(site_url()) . '/labal-courrier-additional-information/?shipment_id=' . $shipment_id ?>" class="btn lc-button lc-btn-back rounded"><?= __("Back", "labal-courrier") ?></a>
                <button x-on:click.prevent="onSubmit()" type="submit" class="btn lc-button lc-btn-blue rounded ms-2"><?= __("Confirm & Continue", "labal-courrier") ?></button>
            </div>
        </form>

    </div>

<?php
}
?>

<?php if ($s_data->is_pickup_required == 0) { ?>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD8JpwCNg8LKKop2qPQt9x_sc_2MxyYkRM&callback=initMap" defer></script>

    <script>
        jQuery(document).ready(function() {
            jQuery('.opening-hours-expand').click(function() {
                jQuery(this).prev('.opening-hours-area').find('.opening-hours-grid-wrapper').slideToggle(300);
            });
        });

        const locations = <?= json_encode($locations); ?>;

        // const beaches = [
        //     ["Bondi Beach", -33.890542, 151.274856, 4],
        //     ["Coogee Beach", -33.923036, 151.259052, 5],
        //     ["Cronulla Beach", -34.028249, 151.157507, 3],
        //     ["Manly Beach", -33.80010128657071, 151.28747820854187, 2],
        //     ["Maroubra Beach", -33.950198, 151.259302, 1],
        // ];

        function initMap() {
            const bounds = new google.maps.LatLngBounds();

            const map = new google.maps.Map(document.getElementById("map_lc_dropoff"), {
                zoom: 10,
                // center: {
                //     lat: -33.9,
                //     lng: 151.2
                // },
            });

            setMarkers(map, bounds);

            map.fitBounds(bounds);
        }

        function setMarkers(map, bounds) {

            for (let i = 0; i < locations.length; i++) {
                const location = locations[i];

                const marker = new google.maps.Marker({
                    position: {
                        lat: Number(location.latitude),
                        lng: Number(location.longitude)
                    },
                    map,
                    title: location.company_name,
                });

                bounds.extend(marker.position);
            }
        }

        window.initMap = initMap;

        // activate nice scroll 
        jQuery(document).ready(function() {
            jQuery(".pickup-map-left").niceScroll({
                cursoropacitymin: 1,
                cursorcolor: "#112A46",
            });
        });
    </script>

<?php } ?>

<script>
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    jQuery(function($) {



        <?php if ($selected_carrier_id == 'CARRIER_UPS') { ?>
            let times_morning = returnTimesInBetween('<?= $pickup_details['pickupWindowEarliestTime'] ?>', '13:00:00');
            let times_afternoon = returnTimesInBetween('13:00:00', '<?= $pickup_details['pickupWindowLatestTime'] ?>');

            let options_str_morning = '';
            let options_str_afternoon = '';

            times_morning.forEach(function(value, index) {
                options_str_morning += '<option value="' + value + '" >' + value + '</option>';
            })
            times_afternoon.forEach(function(value, index) {
                options_str_afternoon += '<option value="' + value + '" >' + value + '</option>';
            })

            $('#e_pickup_time').html(options_str_morning)
            $('#l_pickup_time').html(options_str_afternoon)

        <?php } else { ?>

            let times = returnTimesInBetween('<?= $pickup_details['pickupWindowEarliestTime'] ?>', '<?= $pickup_details['pickupWindowLatestTime'] ?>');
            let options_str = '';
            times.forEach(function(value, index) {
                options_str += '<option value="' + value + '" >' + value + '</option>';
            })

            $('#e_pickup_time').html(options_str)
            $('#l_pickup_time').html(options_str)

        <?php } ?>

        function returnTimesInBetween(start, end) {
            var timesInBetween = [];
            var startH = parseInt(start.split(":")[0]);
            var startM = parseInt(start.split(":")[1]);
            var endH = parseInt(end.split(":")[0]);
            var endM = parseInt(end.split(":")[1]);

            if (startM == 30) {
                timesInBetween.push(startH < 10 ? "0" + startH + ":30" : startH + ":30");
                startH++;
            }

            for (var i = startH; i < endH; i++) {
                timesInBetween.push(i < 10 ? "0" + i + ":00" : i + ":00");
                timesInBetween.push(i < 10 ? "0" + i + ":30" : i + ":30");
            }
            timesInBetween.push(endH + ":00");
            if (endM == 30)
                timesInBetween.push(endH + ":30")

            return timesInBetween;
        }
    });

    function component() {
        return {
            init() {
                setTimeout(() => {
                    const shedule_pickup = '<?= $s_data->is_pickup_required ?>';
                    const e_pickup_time = '<?= $s_data->earliest_pickup_time ?>';
                    const l_pickup_time = '<?= $s_data->latest_pickup_time ?>';

                    jQuery('#e_pickup_time').val(e_pickup_time).trigger('change');
                    jQuery('#l_pickup_time').val(l_pickup_time).trigger('change');

                }, 500);
            },

            cutoff_time: <?= $pickup_cutoff_time ?>,

            submitted: false,
            showError: false,
            errorMessage: [],
            formData: {
                shedule_pickup: '<?= isset($s_data->is_pickup_required) ? $s_data->is_pickup_required : "" ?>',
                pickup_location: '<?= isset($s_data->pickup_location) ? $s_data->pickup_location : "" ?>',
                special_pickup_instructions: '<?= isset($s_data->special_pickup_instructions) ? $s_data->special_pickup_instructions : "" ?>',
                e_pickup_time: '<?= isset($s_data->earliest_pickup_time) ? $s_data->earliest_pickup_time : "" ?>',
                l_pickup_time: '<?= isset($s_data->latest_pickup_time) ? $s_data->latest_pickup_time : "" ?>',
            },
            formvalidation: {
                valid: true,
                shedule_pickup: {
                    validated: true
                },
                pickup_location: {
                    validated: true
                },
                e_pickup_time: {
                    validated: true
                },
                l_pickup_time: {
                    validated: true
                },
                time_difference: {
                    validated: true
                },
            },

            validationIds: [],

            resetFormError() {
                this.showError = false;
                this.errorMessage = [];
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

            calculateTime() {
                let valuestart = jQuery("#e_pickup_time").val();
                let valuestop = jQuery("#l_pickup_time").val();
                let timeStart = new Date("01/01/2007 " + valuestart);
                let timeEnd = new Date("01/01/2007 " + valuestop);
                let difference = timeEnd - timeStart;
                difference = difference / 60 / 1000;
                return parseInt(difference);
            },

            onSubmit() {
                this.resetFormError();
                const offsetMinus = 200;

                this.formData.e_pickup_time = jQuery('#e_pickup_time').val();
                this.formData.l_pickup_time = jQuery('#l_pickup_time').val();


                if (this.formData.shedule_pickup == 1) {
                    if (this.formData.pickup_location == '') {
                        this.formvalidation.valid = false;
                        this.formvalidation.pickup_location.validated = false;
                        this.validationIds.push('pickup_location');
                    }

                    if (!this.formData.e_pickup_time) {
                        this.formvalidation.valid = false;
                        this.formvalidation.e_pickup_time.validated = false;
                        this.validationIds.push('e_pickup_time');
                    }
                    if (!this.formData.l_pickup_time) {
                        this.formvalidation.valid = false;
                        this.formvalidation.l_pickup_time.validated = false;
                        this.validationIds.push('l_pickup_time');
                    }

                    if (this.formData.shedule_pickup == 1 && this.calculateTime() < parseInt(this.cutoff_time)) {
                        this.formvalidation.valid = false;
                        this.formvalidation.time_difference.validated = false;
                        this.validationIds.push('time_difference');
                    }
                }

                if (this.formvalidation.valid) {
                    // submit the form
                    jQuery('#frm_customs_declaration').submit();
                    jQuery(".lc-loading-modal").fadeIn();
                } else {
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }

            }
        }
    }
</script>