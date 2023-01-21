<?php
// prevent cache so when browser back button is pressed, previous data can be displayed
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0 "); // Proxies.

$current_language = get_locale();
$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

$old_values = [];

$error = $_GET['e'];
if (isset($_GET['shipment_id'])) {

    $countries = lc_get_all_countries();

    $carriers = [
        'CARRIER_DHL' => 'DHL Express',
        'CARRIER_UPS' => 'UPS',
    ];
    $shipment_id = $_GET['shipment_id'];
    global $wpdb, $table_prefix;
    $lc_shipment = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '$shipment_id'", ARRAY_A);
    // $sender_trade_type = $lc_shipment['sender_trade_type'];
    $get_quote_results = unserialize($lc_shipment['get_quote_result']);
    if (is_array($get_quote_results)) {
        $deliveryDate = $get_quote_results['deliveryTime'];
    }

    $user_info = [];
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        // $user_info['first_name'] = get_user_meta($user_id, 'first_name', true);
        // $user_info['last_name'] = get_user_meta($user_id, 'last_name', true);
        // $user_info['phone'] = get_user_meta($user_id, 'phone', true);
        // $user_info['email'] = get_user_meta($user_id, 'billing_email', true);
        // $user_info['company'] = get_user_meta($user_id, 'billing_company', true);
        // $user_info['address'] = get_user_meta($user_id, 'billing_address_1', true);
        // $user_info['city'] = get_user_meta($user_id, 'billing_city', true);
        // $user_info['country'] = get_user_meta($user_id, 'billing_country', true);

        $default_billing_id = get_user_meta($user_id, 'lc_billing_address_default', true);
        if ($default_billing_id != '') {
            $table_billing = $wpdb->prefix . 'lc_billing_addresses';
            $default_billing_address = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_billing WHERE id = %d", $default_billing_id));

            $user_info['email'] = $default_billing_address->email;
            $user_info['company'] = $default_billing_address->company;
            $user_info['address'] = $default_billing_address->address;
        }

        $referral_code_discount = get_option('referral_code_discount', 10) == '' ? 10 : get_option('referral_code_discount', 10);
        $referral_code_limit = get_option('referral_code_limit', 60) == '' ? 60 : get_option('referral_code_limit', 60);

        $user_discount_credit = get_user_meta($user_id, 'mnfr_referral_credit', true);
    }
?>
    <script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>


    <div x-data="component()" x-init="init()" class="lc-form-container courier-steps-page schedule-pickup-page mt-3 mb-5">
        <!-- include steps bar -->
        <?php include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-courier-steps-bar.php'; ?>

        <h1 class="heading-courier-steps mb-4"><?= __("Confirm Booking", "labal-courrier") ?></h1>

        <div class="lc-confirm-booking-area shadow-sm rounded p-4 mb-5 bg-white">
            <div class="confirm-booking-top-wrapper pb-5 mb-4">
                <h4 class="mb-3"><?= __("Shipment Information", "labal-courrier") ?></h4>
                <div class="confirm-booking-top lc-grid grid-cols-2">
                    <div>
                        <p class="fw-bold"><?= __("Date of Departure", "labal-courrier") ?></p>
                        <p><?= date('d-m-Y', strtotime($lc_shipment['dispatch_date']))  ?></p>
                        <p class="fw-bold mt-2"><?= __("Date of Arrival", "labal-courrier") ?></p>
                        <p><?= date('d-m-Y', strtotime($deliveryDate)) ?></p>
                    </div>
                    <div class="text-end">

                        <?php
                        $rate = unserialize($lc_shipment['get_quote_result']);

                        if ($lc_shipment['vat_amount'] > 0 && $rate['is_vat_applicable_to_carrier'] == 1) {

                            // $rate['carrier_rate'] = $rate['carrier_rate'] - $referral_code_discount;
                            $margin = $rate['labal_margin'];
                            $margin_with_vat = $margin * 1.2;
                            $carrier_total_without_vat = $rate['carrier_rate'] - $rate['carrier_vat'];
                            $carrier_total_with_vat = $rate['carrier_rate'];

                            $total_without_vat = $carrier_total_without_vat +  $margin;
                            $total_with_vat = $carrier_total_with_vat +  $margin_with_vat;
                            $total_vat = number_format((float)($total_with_vat - $total_without_vat), 2, '.', '');

                            // echo '$carrier_total_without_vat = ' . $carrier_total_without_vat;
                            // echo '<br>$carrier_total_with_vat = ' . $carrier_total_with_vat;
                            // echo '<br>-----------';
                            // echo '<br>$margin = ' . $margin;
                            // echo '<br>$margin_with_vat = ' . $margin_with_vat;
                            // echo '<br>-----------';
                            // echo '<br>$total_without_vat = ' . $total_without_vat;
                            // echo '<br>$total_vat = ' . $total_vat;
                            // echo '<br>$total_with_vat = ' . $total_with_vat;
                            // echo '<br>-----------';
                        ?>
                            <p><?= esc_html_e("Vat:", "labal-courrier") ?> €<?= $total_vat ?></p>
                        <?php } else if ($lc_shipment['vat_amount'] > 0) { ?>
                            <p><?= esc_html_e("Vat:", "labal-courrier") ?> €<?= $lc_shipment['vat_amount'] ?></p>
                        <?php } ?>

                        <p class="fw-bold"><?= esc_html_e("Total:", "labal-courrier") ?> €<?= $lc_shipment['total_amount'] ?></p>

                        <?php
                        if (is_user_logged_in() && $lc_shipment['package_type'] == 'Package' && $lc_shipment['total_amount'] > $referral_code_limit && $user_discount_credit >= $referral_code_discount) {
                        ?>
                            <p><?= esc_html_e("Discount:", "labal-courrier") ?> €<?= $referral_code_discount ?></p>
                            <p class="fw-bold"><?= esc_html_e("Final Total:", "labal-courrier") ?> €<?= $lc_shipment['total_amount'] - $referral_code_discount ?></p>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="confirm-booking-bottom-wrapper">
                <h4 class="mb-3"><?= __("Summary", "labal-courrier") ?></h4>

                <div class="confirm-booking-address-details lc-grid grid-cols-1 md-grid-cols-2 gap-4">
                    <div>
                        <p><?= $lc_shipment['sender_first_name'] ?> <?= $lc_shipment['sender_last_name'] ?></p>
                        <p><?= implode(', ', array_filter(unserialize($lc_shipment['sender_address']), fn ($value) => trim($value) != '')) ?></p>
                        <p><?= $lc_shipment['sender_city'] ?> <?= $lc_shipment['sender_postcode'] != '' ? ', ' . $lc_shipment['sender_postcode'] : '' ?> </p>
                        <p><?= lc_get_country_by_code($lc_shipment['sender_country_code']) ?></p>
                    </div>
                    <div>
                        <p><?= $lc_shipment['receiver_first_name'] ?> <?= $lc_shipment['receiver_last_name'] ?></p>
                        <p><?= unserialize($lc_shipment['receiver_address'])[0] ?></p>
                        <p><?= $lc_shipment['receiver_city'] ?> <?= $lc_shipment['receiver_postcode'] != '' ? ', ' . $lc_shipment['receiver_postcode'] : '' ?> </p>
                        <p><?= lc_get_country_by_code($lc_shipment['receiver_country_code']) ?></p>
                    </div>
                </div>

                <p class="mt-3"><?= $lc_shipment['is_pickup_required'] == 1 ? esc_html_e("Pick-up Service", "labal-courrier") : esc_html_e("Drop-off Service", "labal-courrier") ?></p>

                <div class="confirm-booking-bottom lc-grid grid-cols-2 md-grid-cols-3 lg-grid-cols-4 gap-2 mt-3">
                    <div class="single-booking-details">
                        <label for=""><?= esc_html_e("Dimensions", "labal-courrier") ?></label>
                        <?php
                        $packages = unserialize($lc_shipment['packages']);
                        foreach ($packages as $package) { ?>
                            <span><?= $package['length'] ?>cm | <?= $package['width'] ?>cm | <?= $package['height'] ?>cm</span>
                        <?php } ?>
                    </div>
                    <div class="single-booking-details">
                        <label for=""><?= esc_html_e("Weight", "labal-courrier") ?></label>
                        <?php foreach ($packages as $package) { ?>
                            <span><?= $package['weight'] ?>kg</span>
                        <?php } ?>
                    </div>

                    <div class="single-booking-details">
                        <label for=""><?= esc_html_e("Insurance", "labal-courrier") ?></label>
                        <span><?= $lc_shipment['insurance'] == 1 ? esc_html_e("Yes", "labal-courrier") : esc_html_e("No", "labal-courrier") ?></span>
                    </div>
                    <?php
                    if ($lc_shipment['insurance'] == 1 && $lc_shipment['insurance_value'] != '') {
                    ?>
                        <div class="single-booking-details">
                            <label for=""><?= esc_html_e("Item Value", "labal-courrier") ?></label>
                            <span>€<?= $lc_shipment['insurance_value'] ?></span>
                        </div>
                    <?php }
                    ?>
                </div>

                <a href="<?= esc_url(site_url()) ?>/shipping-calculator" class="lc-link text-decoration-underline mt-4 d-block"><i class="fa-solid fa-house"></i> <?= esc_html_e("Edit Address", "labal-courrier") ?></a>
            </div>
        </div>

        <div class="confirm-booking-form-area">
            <form id="frm_checkout" class="wpcsr-quote-book mb-0" action="<?php echo esc_attr('wp-admin/admin-post.php'); ?>" method="post">
                <input type="hidden" name="action" value="lc_do_checkout">
                <input type="hidden" name="shipment_id" id="shipment_id" value="<?= $shipment_id; ?>">

                <div class="confirm-booking-form shadow-sm rounded p-4 mb-5 bg-white">
                    <h4 class="mb-3"><?= __("Billing Information", "labal-courrier") ?></h4>
                    <div class="confirm-booking-form-grid lc-grid grid-cols-1 md-grid-cols-3 gap-3">
                        <!-- <div class="lc-form-control" id="first_name">
                            <label for="id_first_name"><?= esc_html_e("First Name", "labal-courrier") ?></label>
                            <input x-model="formData.first_name" type="text" name="first_name" class="" :class="{ 'is-invalid': !formvalidation.first_name.validated }" />
                            <div x-show="!formvalidation.first_name.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                        <div class="lc-form-control" id="last_name">
                            <label for="id_last_name"><?= esc_html_e("Last Name", "labal-courrier") ?></label>
                            <input x-model="formData.last_name" type="text" name="last_name" class="" :class="{ 'is-invalid': !formvalidation.last_name.validated }" />
                            <div x-show="!formvalidation.last_name.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div> -->
                        <div class="lc-form-control" id="company">
                            <label for="id_company"><?= esc_html_e("Full Name or Company", "labal-courrier") ?></label>
                            <input x-model="formData.company" type="text" name="company" class="" />
                            <div x-show="!formvalidation.company.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                        <div class="lc-form-control" id="address">
                            <label for="id_address"><?= esc_html_e("Address", "labal-courrier") ?></label>
                            <input x-model="formData.address" type="text" name="address" class="" />
                            <div x-show="!formvalidation.address.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                        <div class="lc-form-control" id="email">
                            <label for="id_email"><?= esc_html_e("Email", "labal-courrier") ?></label>
                            <input x-model="formData.email" type="text" name="email" class="" :class="{ 'is-invalid': !formvalidation.email.validated }" />
                            <div x-show="!formvalidation.email.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>

                        <!-- <div class="lc-form-control" id="city">
                            <label for="id_city"><?= esc_html_e("City", "labal-courrier") ?></label>
                            <input x-model="formData.city" type="text" name="city" class="" />
                        </div>
                        <div class="lc-form-control lcc-select-country">
                            <label for="id_country"><?= esc_html_e("Country", "labal-courrier") ?></label>
                            <select x-model="formData.country" class="form-control lc-select-country col_country" name="country" id="country">
                                <option value="" selected><?= esc_html_e("Select the country", "labal-courrier") ?></option>
                                <?php foreach ($countries as $code => $name) : ?>
                                    <option value="<?= $code ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div x-show="!formvalidation.country.validated" class="w-100 text-white validation_message">This cannot be empty</div>
                        </div> -->
                    </div>
                </div>

                <div class="lc-form-control ">
                    <div class="lc-form-control d-flex align-items-center">
                        <input x-model="formData.i_ack_1" type="checkbox" class="form-check-input" name="i_ack_1" id="i_ack_1">
                        <label class="" for="i_ack_1">
                            <?= esc_html_e("Je reconnais avoir lu et accepté les", "labal-courrier") ?> <a class="lc-link" href="<?= site_url() ?>/conditions-generales-de-vente/" target="_blank"><?= esc_html_e("Conditions Générales de Vente", "labal-courrier") ?></a> <?= esc_html_e("de LABAL SAS (Mon Courrier de France)", "labal-courrier") ?>.
                        </label>
                    </div>
                    <div x-show="!formvalidation.i_ack_1.validated" class="w-100 text-white validation_message"><?= esc_html_e("You must agree to this.", "labal-courrier") ?></div>
                </div>

                <div class="lc-form-control ">
                    <div class="lc-form-control d-flex align-items-center mt-3">
                        <input x-model="formData.i_ack_2" type="checkbox" class="form-check-input" name="i_ack_2" id="i_ack_2">
                        <label class="" for="i_ack_2">
                            <?= esc_html_e("Je reconnais avoir pris connaissance des conditions et lois d'importation du pays de destination du colis.", "labal-courrier") ?>
                        </label>
                    </div>
                    <div x-show="!formvalidation.i_ack_2.validated" class="w-100 text-white validation_message"><?= esc_html_e("You must agree to this.", "labal-courrier") ?></div>
                </div>

                <div class="lc-form-control ">
                    <div class="lc-form-control d-flex align-items-center mt-3">
                        <input x-model="formData.i_ack_3" type="checkbox" class="form-check-input" name="i_ack_3" id="i_ack_3">
                        <label class="" for="i_ack_3">
                            <?= esc_html_e("Je reconnais avoir fourni les dimensions et le poids exacts. Si les dimensions et le poids ne correspondent pas, des pénalités/blocages peuvent être appliquées.", "labal-courrier") ?>
                        </label>
                    </div>
                    <div x-show="!formvalidation.i_ack_3.validated" class="w-100 text-white validation_message"><?= esc_html_e("You must agree to this.", "labal-courrier") ?></div>
                </div>

                <div class="lc-steps-btn-area has-border mt-5 text-center text-md-end">
                    <a href="<?= esc_url(site_url()) . '/customs-declaration/?shipment_id=' . $shipment_id ?>" class="btn lc-button lc-btn-back rounded"><?= __("Back", "labal-courrier") ?></a>
                    <button x-on:click.prevent="onSubmit()" type="submit" class="btn lc-button lc-btn-blue rounded ms-2"><?= __("Confirm & Pay", "labal-courrier") ?></button>
                </div>
            </form>
        </div>
    </div>


<?php
}
?>

<script src="https://js.stripe.com/v3/"></script>

<script>
    const user_info = <?= json_encode($user_info); ?>;

    jQuery(function($) {
        setTimeout(() => {
            if (typeof user_info === 'object' && Object.keys(user_info).length > 0) {
                jQuery('input[name="company"]').val(user_info.company)[0].dispatchEvent(new Event('input'));
                jQuery('input[name="email"]').val(user_info.email)[0].dispatchEvent(new Event('input'));
                jQuery('input[name="address"]').val(user_info.address)[0].dispatchEvent(new Event('input'));
            }
        }, 300);

    });


    function component() {
        return {
            stripe: Stripe('pk_test_cdDNEk2qgWrCh7ENx0JxE90Y'),
            // stripe: Stripe('pk_test_51JNFRJCmTAWyJf9RJzNIn3Q5jMXTKA4OINtMyKYgCW2ciy1ZZ7TY82mtox26Xy3oIuntQppSQNmcrqyWym05mmAi0009nbkihb'),
            // stripe: Stripe('pk_live_51JNFRJCmTAWyJf9RJQndzijEHnEVDNHjxb2KVYW3zMKAbJJO8r11mm2t0iY9hTReWtzjEJIUcmilM414vJrg9UbS00tZ0yN6Mh'),

            init() {

            },

            submitted: false,
            showError: false,
            errorMessage: [],
            formData: {
                shipment_id: '',
                current_language: '<?= $current_language ?>',
                first_name: '',
                last_name: '',
                company: '',
                email: '',
                address: '',
                city: '',
                country: '',
                i_ack_1: '',
                i_ack_2: '',
                i_ack_3: '',
            },
            formvalidation: {
                valid: true,
                first_name: {
                    validated: true
                },
                last_name: {
                    validated: true
                },
                company: {
                    validated: true
                },
                email: {
                    validated: true
                },
                address: {
                    validated: true
                },
                city: {
                    validated: true
                },
                country: {
                    validated: true
                },
                i_ack_1: {
                    validated: true
                },
                i_ack_2: {
                    validated: true
                },
                i_ack_3: {
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

            onSubmit() {
                this.resetFormError();
                const offsetMinus = 200;

                this.formData.country = jQuery('#country').val();
                this.formData.shipment_id = jQuery('#shipment_id').val();

                // if (this.formData.first_name == '') {
                //     this.formvalidation.valid = false;
                //     this.formvalidation.first_name.validated = false;
                //     this.validationIds.push('first_name');
                // }
                if (this.formData.company == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.company.validated = false;
                    this.validationIds.push('company');
                }
                if (this.formData.email == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.email.validated = false;
                    this.validationIds.push('email');
                }
                if (this.formData.address == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.address.validated = false;
                    this.validationIds.push('address');
                }

                if (this.formData.i_ack_1 != true) {
                    this.formvalidation.valid = false;
                    this.formvalidation.i_ack_1.validated = false;
                    this.validationIds.push('i_ack_1');
                }
                if (this.formData.i_ack_2 != true) {
                    this.formvalidation.valid = false;
                    this.formvalidation.i_ack_2.validated = false;
                    this.validationIds.push('i_ack_2');
                }
                if (this.formData.i_ack_3 != true) {
                    this.formvalidation.valid = false;
                    this.formvalidation.i_ack_3.validated = false;
                    this.validationIds.push('i_ack_3');
                }

                if (this.formvalidation.valid) {
                    // submit the form
                    // jQuery('#frm_checkout').submit();
                    jQuery(".lc-loading-modal").fadeIn();

                    jQuery.ajax({
                        url: lc_obj.ajax_url,
                        method: 'post',
                        data: {
                            action: 'create_stripe_session',
                            data: this.formData
                        },
                        dataType: 'json',
                        success: (resData) => {
                            // console.log(resData);
                            jQuery(".lc-loading-modal").hide();
                            if (resData.status && resData.status == 'success') {
                                this.stripe.redirectToCheckout({
                                    sessionId: resData.stripeSessionId
                                }).then((getRes) => {
                                    console.log(getRes);
                                });
                            } else {
                                alert(resData.msg);
                            }
                        },
                        error: (xhr, status, error) => {
                            jQuery(".lc-loading-screen").hide();
                            console.log(error);
                        }
                    });


                } else {
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }

            }
        }
    }
</script>