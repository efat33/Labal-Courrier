<?php
// prevent cache so when browser back button is pressed, previous data can be displayed
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0 "); // Proxies.

global $wpdb, $table_prefix, $post;
$current_language = get_locale();
$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

if (isset($_GET['shipment_id'])) {
    $shipment_id = $_GET['shipment_id'];

    $countries = lc_get_all_countries();

    $quote_id = $_SESSION['quote_id'];

    // if (isset($_GET['state']) && $_GET['state'] == 'login_return_back') {
    //     $old_values = $wpdb->get_results('SELECT * FROM ' . $table_prefix . 'lc_shipments WHERE lc_shipment_ID = "' . $shipment_id . '"');
    // }
    $s_result = $wpdb->get_results('SELECT * FROM ' . $table_prefix . 'lc_shipments WHERE lc_shipment_ID = "' . $shipment_id . '"');
    $s_data = isset($s_result[0]) ? $s_result[0] : '';

    $selected_carrier_id = $s_data->selected_carrier_id;  // CARRIER_UPS

    $errors = [];
    if (isset($_GET['request_status']) && $_GET['request_status'] == 'error' && isset($_GET['request_id']) && !empty($_GET['request_id'])) {
        $errors = get_transient($_GET['request_id']);
    }

    // query to get all the saved shipper and receiver - from wpcargo address book 
    $shippers = [];
    $receivers = [];
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $user_id = $user->ID;
        $table_shipping = $wpdb->prefix . 'lc_shipping_addresses';

        $receivers = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_shipping WHERE `user_id` = %d", $user_id), ARRAY_A);

        // /////////// //////////// /////////////

        $args_r = array(
            "post_type" => 'wpc_address_book',
            "posts_per_page" => -1,
            'author' => get_current_user_id(),
            'meta_key' => 'wpcargo_receiver_name',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
        );
        $args_r['meta_query'] = array(
            array(
                'key' => 'book',
                'value' => 'receiver'
            )
        );

        $query_r = new WP_Query($args_r);
        if ($query_r->have_posts()) {
            while ($query_r->have_posts()) {
                $query_r->the_post();
                $tmp = [];
                $ID = get_the_ID();

                $tmp['receiver_first_name']  = get_post_meta($ID, 'wpcargo_receiver_name', true);
                $tmp['receiver_last_name']  = get_post_meta($ID, 'wpcargo_receiver_last_name', true);
                $tmp['del_country']  = get_post_meta($ID, 'wpcargo_receiver_country', true);
                $tmp['del_postcode_or_city']  = get_post_meta($ID, 'wpcargo_receiver_postcode', true);
                $tmp['address_receiver']  = get_post_meta($ID, 'wpcargo_receiver_address', true);
                $tmp['receiver_phone_number']  = get_post_meta($ID, 'wpcargo_receiver_phone', true);
                $tmp['receiver_email']  = get_post_meta($ID, 'wpcargo_receiver_email', true);

                $receivers[] = $tmp;
            }
        }
        wp_reset_postdata();
    }
?>
    <script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>

    <div x-data="component()" x-init="init()" class="lc-form-container courier-steps-page additional-info-page mt-3">
        <!-- include steps bar -->
        <?php include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-courier-steps-bar.php'; ?>

        <h1 class="heading-courier-steps mb-4"><?= __("Delivery Details", "labal-courrier") ?></h1>

        <div class="lc-shipping-details-area shadow-sm rounded p-4 mb-5 bg-white">
            <div class="lc-shipping-details-wrapper lc-grid gap-3 md-gap-4">
                <div class="single-shipping-details">
                    <label for=""><?= esc_html_e("Summary", "labal-courrier") ?></label>
                    <span><?= lc_get_country_by_code($s_data->sender_country_code) ?> - <?= lc_get_country_by_code($s_data->receiver_country_code) ?></span>
                    <span><?= $s_data->sender_city ?>, <?= $s_data->sender_postcode ?> - <?= $s_data->receiver_city ?>, <?= $s_data->receiver_postcode ?></span>
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

        <div class="info-section-area mb-5">
            <form id="frm_addtional_info" class="wpcsr-quote-book mb-0" action="<?php echo esc_url(site_url('wp-admin/admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="submit_shipment_details">
                <input type="hidden" name="current_language" value="<?= $current_language ?>">
                <input type="hidden" name="shipment_id" value="<?= $shipment_id ?>">
                <input type="hidden" name="package_type" value="<?= $s_data->package_type ?>">
                <input type="hidden" name="selected_carrier_id" value="<?= $s_data->selected_carrier_id ?>">
                <?php wp_nonce_field('get_quote', 'lc_nonce'); ?>

                <?php if (count($errors) > 0) { ?>
                    <div class="row">
                        <div class="col-12">
                            <?php
                            foreach ($errors as $key => $value) {
                                if (!is_array($value)) {
                                    echo '<div class="w-100 mb-3 pt-1 pb-1 common_error validation_message" style="color: red;">';
                                    echo $value;
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="info-sub-heading-area lc-grid">
                    <h1 class="heading-courier-steps mb-4"><?= __("Sender Details", "labal-courrier") ?></h1>
                    <span class="text-end"><i class="fa-regular fa-circle-down"></i></span>
                </div>
                <div class="info-section-wrapper-outer">
                    <div class="info-section-wrapper info-sender-wrapper" style="display: block;">
                        <div class="info-form-top-wrapper mb-4 lc-grid grid-cols-1 md-grid-cols-3 gap-3">
                            <?php if (is_user_logged_in()) { ?>
                                <div class="lc-form-control">
                                    <label for="" class="fw-bold"><?= esc_html_e("Address Book", "labal-courrier") ?></label>
                                    <select class="form-control lc-select-  search_sender_name" name="search_sender_name" id="search_sender_name">
                                        <option value="" selected><?= esc_html_e("Search Sender Name", "labal-courrier") ?></option>

                                        <?php foreach ($receivers as $item) { ?>
                                            <option value="<?= $item['sender_email'] ?>"><?= $item['sender_first_name'] ?> <?= $item['sender_last_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>

                            <div class="lc-form-control">
                                <label for="" class="fw-bold"><?= esc_html_e("Status", "labal-courrier") ?></label>
                                <select x-model="formData.sender_trade_type" id="sender_trade_type" class="form-control" name="sender_trade_type">
                                    <option value="PR" selected><?= esc_html_e("Je suis un particulier", "labal-courrier") ?></option>
                                    <option value="BU"><?= esc_html_e("Je suis un professionnel", "labal-courrier") ?></option>
                                </select>
                            </div>
                        </div>

                        <p class="info-fields-heading mb-2"><?= __("Enter New Address", "labal-courrier") ?></p>
                        <div class="info-form-wrapper info-form-identity lc-grid grid-cols-1 md-grid-cols-3 gap-3 mb-3">
                            <div class="lc-form-control">
                                <label for="sender_first_name"><?= esc_html_e("First Name", "labal-courrier") ?></label>
                                <input x-model="formData.sender_first_name" id="sender_first_name" type="text" name="sender_first_name" placeholder="John" class="" :class="{ 'is-invalid': !formvalidation.sender_first_name.validated }" />
                                <div x-show="!formvalidation.sender_first_name.validated" class="w-100 text-white validation_message"><?= esc_html_e("Firstname cannot be empty", "labal-courrier") ?></div>
                            </div>
                            <div class="lc-form-control">
                                <label for="sender_last_name"><?= esc_html_e("Last Name", "labal-courrier") ?></label>
                                <input x-model="formData.sender_last_name" id="sender_last_name" type="text" name="sender_last_name" placeholder="Doe" class="" :class="{ 'is-invalid': !formvalidation.sender_last_name.validated }" />
                                <div x-show="!formvalidation.sender_last_name.validated" class="w-100 text-white validation_message"><?= esc_html_e("Lastname cannot be empty", "labal-courrier") ?></div>
                            </div>
                            <div x-show="formData.sender_trade_type == 'BU'" class="lc-form-control">
                                <label for="sender_company"><?= esc_html_e("Company", "labal-courrier") ?></label>
                                <input x-model="formData.sender_company" type="text" id="sender_company" name="sender_company" placeholder="" class="" :class="{ 'is-invalid': !formvalidation.sender_company.validated && formData.sender_trade_type == 'BU' }" />
                                <div x-show="!formvalidation.sender_company.validated && formData.sender_trade_type == 'BU'" class="w-100 text-white validation_message"><?= esc_html_e("Please provide a company name with characters less than 25", "labal-courrier") ?></div>
                            </div>
                            <div x-show="formData.sender_trade_type == 'BU'" class="lc-form-control">
                                <label for="sender_tva_number"><?= esc_html_e("VAT No.", "labal-courrier") ?></label>
                                <input x-model="formData.sender_tva_number" value="<?= $old_values[0]->sender_tva_number ?? '' ?>" type="text" class="form-control" placeholder="Numéro de TVA" name="sender_tva_number" id="sender_tva_number" />
                            </div>
                            <div x-show="formData.sender_trade_type == 'BU'" class="lc-form-control">
                                <label for="sender_eori_number"><?= esc_html_e("EOI No.", "labal-courrier") ?></label>
                                <input x-model="formData.sender_eori_number" value="<?= $old_values[0]->sender_eori_number ?? '' ?>" type="text" class="form-control" placeholder="Numéro EORI" name="sender_eori_number" id="sender_eori_number" />
                            </div>
                        </div>

                        <div class="info-form-wrapper lc-grid grid-cols-1 md-grid-cols-3 gap-3 mb-3">
                            <div class="lc-form-control lc-form-control-phone">
                                <label for="sender_phone_number"><?= esc_html_e("Phone", "labal-courrier") ?></label>
                                <input type="hidden" required name="sender_full_phone_number" id="sender_full_phone_number">
                                <div class="form-control-phone-grid lc-grid">
                                    <div class="" style="position: relative;">
                                        <img id="sender_phone_flag" style="position: absolute;top: 8px;left: 12px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAKJSURBVHja1JbLahRBFIa/7uqZzCVxJlGCGoSoiSAh6FIDigxxkRfwBfIeLnwGt7rwBUQR1J0gIkg2YmZpjBNClATT3dOd6e66uchMbt3OKll4oKCoOnX+c/7q+k871lrO0lzO2M4cwAE8oAKUTjm2BBIPGF1cXNwVQhR6PaM2NMryo6l/7r1ffjruAXXXdWm1WoVONcpDAVozE4XrH9ZWAOoe4CmlSNMU3/exfd72zaKoDKZHNw7Wfndtf2oPOG9Ux9DaAHgegNaaOI6J4ziXicUMrSCWSW6tJEooo6F/wUgpiaKIKIpyzgYFAtzRBm59/z5skqD/bAMeUTqWO+O5Aq3VIYBSim63SxiGBQAJ3uUp9M4OjpjE7MXYXkZp+hpZu02YnsudEa5AmhMAYRgeABylW1y9Se3BfaJXbyjfmIWSR/z2HSO359FBQJh08wCOg1L6OIDv+wRBkOdz5jpyY5Pa0kMwFnHhPCNzc8j1dbypSwTpVv5xOQ7Z0QqklHQ6HXzfz9+i1ojxBtnXVcq35rG9BLWxSXV2Brn+k478lTsSVmKqTvkQIMsylFJorXPO2Wqb0vVpTJoi137gCA93YgLRbND9/AW9kP/KlNVkRh5q0SC4MSY3eh8/gYXqwh1Es4mYaFK9d5fs+zrpygrGmtzQVqO0PE7RIGCOoW7AzuMnjC4tIS5OYvd6pN/aRC9fY9IUUyD3yhgcbQ8BBtkXUQQuemub4PmLQq3UBUkZY5DKHq9gsEHBSxhmxhZUbTRmXyr6FSiNK1zq9XpB/sPVtF6uFMiLPfaSpVQSRzuFAXZJTrSPI0oHZFEytCc4QAO4AjRPueH4wMago40M6DpFU0Dq/Pd/FX8HAMTHY7xw6NPHAAAAAElFTkSuQmCC" alt="">
                                        <input x-model="formData.sender_phone_code" type="text" list="sender_phone_codes" class="form-control info-phone-code" id="sender_phone_code" />
                                        <datalist id="sender_phone_codes">

                                        </datalist>
                                    </div>
                                    <input x-model="formData.sender_phone_number" value="" maxlength="22" required type="text" placeholder="Numéro de telephone" class="form-control phone_numeber_field validate_number" id="sender_phone_number" />
                                </div>
                                <div x-show="!formvalidation.sender_phone_code.validated || !formvalidation.sender_phone_number.validated" class="w-100 text-white validation_message"><?= esc_html_e("Please provide a valid phone number", "labal-courrier") ?></div>
                            </div>
                            <div class="lc-form-control">
                                <label for="sender_email"><?= esc_html_e("Email", "labal-courrier") ?></label>
                                <input x-model="formData.sender_email" type="text" maxlength="50" class="form-control" placeholder="Email" name="sender_email" id="sender_email" />
                                <div x-show="!formvalidation.sender_email.validated" class="w-100 text-white validation_message"><?= esc_html_e("Please provide a valid email address", "labal-courrier") ?></div>
                            </div>
                        </div>

                        <div class="info-form-wrapper lc-grid grid-cols-1 md-grid-cols-3 gap-3 mb-3">
                            <div class="lc-form-control">
                                <label for="address_sender"><?= esc_html_e("Address Line 1", "labal-courrier") ?></label>
                                <input x-model="formData.address_sender" type="text" name="sender_address[]" placeholder="" class="validate_address" id="address_sender" :class="{ 'is-invalid': !formvalidation.address_sender.validated }" />
                                <div x-show="!formvalidation.address_sender.validated" class="w-100 text-white validation_message"><?= esc_html_e("Please provide an address of less than 35 characters", "labal-courrier") ?></div>
                            </div>
                            <div class="lc-form-control">
                                <label for="address_sender1"><?= esc_html_e("Address Line 2", "labal-courrier") ?></label>
                                <input type="text" name="sender_address[]" placeholder="" class="validate_address" id="address_sender1" :class="{ 'is-invalid': !formvalidation.address_sender1.validated }" />
                                <div x-show="!formvalidation.address_sender1.validated" class="w-100 text-white validation_message"><?= esc_html_e("Please provide an address of less than 35 characters", "labal-courrier") ?></div>
                            </div>
                        </div>

                        <div class="info-form-wrapper lc-grid grid-cols-1 md-grid-cols-3 gap-3 mb-3">
                            <div class="lc-form-control not-edditable-fields" :class="{ 'is-invalid-select': !formvalidation.col_country.validated }">
                                <label for=""><?= esc_html_e("Country", "labal-courrier") ?></label>
                                <select x-model="formData.col_country" class="form-control lc-select-country col_country" name="col_country" id="col_country" aria-label="Example select with button addon">
                                    <option value="" selected><?= esc_html_e("Select the country", "labal-courrier") ?></option>
                                    <?php foreach ($countries as $code => $name) : ?>
                                        <option value="<?= $code ?>"><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div x-show="!formvalidation.col_country.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                            </div>
                            <div class="lc-form-control not-edditable-fields">
                                <label for=""><?= esc_html_e("Town/City", "labal-courrier") ?></label>
                                <div style="width: 100%; display:block" class="" :class="{ 'is-invalid-select': !formvalidation.col_postcode_or_city.validated }">
                                    <select x-model="formData.col_postcode_or_city" style="width: 100%;" class="form-control" name="col_postcode_or_city" id="col_postcode_or_city"></select>
                                </div>
                                <div x-show="!formvalidation.col_postcode_or_city.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                            </div>

                            <input type="hidden" name="col_state" placeholder="" class="" value="<?= $s_data->sender_state ?>" />

                            <div class="lc-form-control save-to-address-book d-flex align-items-end justify-content-end">
                                <a @click.prevent="saveSenderAddress($event)" href="#" id="save_sender_address" class="lc-link text-end text-decoration-underline"><i class="fa-regular fa-id-badge"></i> <?= __("Save to Address Book", "labal-courrier") ?></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-sub-heading-area lc-grid mt-4">
                    <h1 class="heading-courier-steps mb-4"><?= __("Destination Details", "labal-courrier") ?></h1>
                    <span class="text-end"><i class="fa-regular fa-circle-down"></i></span>
                </div>
                <div class="info-section-wrapper-outer">
                    <div class="info-section-wrapper info-receiver-wrapper">
                        <div class="info-form-top-wrapper mb-4 lc-grid grid-cols-1 md-grid-cols-3 gap-3">
                            <?php if (is_user_logged_in()) { ?>
                                <div class="lc-form-control">
                                    <label for="" class="fw-bold"><?= __("Address Book", "labal-courrier") ?></label>
                                    <select class="form-control lc-select-  search_receiver_name" name="search_receiver_name" id="search_receiver_name">
                                        <option value="" selected>Search Receiver Name</option>

                                        <?php foreach ($receivers as $item) { ?>
                                            <option value="<?= $item['sender_email'] ?>"><?= $item['sender_first_name'] ?> <?= $item['sender_last_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>
                            <div class="lc-form-control">
                                <label for="" class="fw-bold"><?= esc_html_e("Status", "labal-courrier") ?></label>
                                <select x-model="formData.receiver_trade_type" id="receiver_trade_type" class="form-control" name="receiver_trade_type">
                                    <option value="PR" selected>Je suis un particulier</option>
                                    <option value="BU">Je suis un professionnel</option>
                                </select>
                            </div>
                        </div>

                        <p class="info-fields-heading mb-2"><?= __("Enter New Address", "labal-courrier") ?></p>
                        <div class="info-form-wrapper info-form-identity lc-grid grid-cols-1 md-grid-cols-3 gap-3 mb-3">
                            <div class="lc-form-control">
                                <label for="receiver_first_name"><?= esc_html_e("First Name", "labal-courrier") ?></label>
                                <input x-model="formData.receiver_first_name" id="receiver_first_name" type="text" name="receiver_first_name" placeholder="John" class="" :class="{ 'is-invalid': !formvalidation.receiver_first_name.validated }" />
                                <div x-show="!formvalidation.receiver_first_name.validated" class="w-100 text-white validation_message">Firstname cannot be empty</div>
                            </div>
                            <div class="lc-form-control">
                                <label for="receiver_last_name"><?= esc_html_e("Last Name", "labal-courrier") ?></label>
                                <input x-model="formData.receiver_last_name" id="receiver_last_name" type="text" name="receiver_last_name" placeholder="Doe" class="" :class="{ 'is-invalid': !formvalidation.receiver_last_name.validated }" />
                                <div x-show="!formvalidation.receiver_last_name.validated" class="w-100 text-white validation_message">Lastname cannot be empty</div>
                            </div>

                            <div x-show="formData.receiver_trade_type == 'BU'" class="lc-form-control">
                                <label for="receiver_company"><?= esc_html_e("Company", "labal-courrier") ?></label>
                                <input x-model="formData.receiver_company" type="text" id="receiver_company" name="receiver_company" placeholder="" class="" :class="{ 'is-invalid': !formvalidation.receiver_company.validated && formData.receiver_trade_type == 'BU' }" />
                                <div x-show="!formvalidation.receiver_company.validated && formData.receiver_trade_type == 'BU'" class="w-100 text-white validation_message"><?= esc_html_e("Please provide a company name with characters less than 25", "labal-courrier") ?></div>
                            </div>
                            <div x-show="formData.receiver_trade_type == 'BU'" class="lc-form-control">
                                <label for="receiver_tva_number"><?= esc_html_e("VAT No.", "labal-courrier") ?></label>
                                <input x-model="formData.receiver_tva_number" value="<?= $old_values[0]->receiver_tva_number ?? '' ?>" type="text" class="form-control" placeholder="Numéro de TVA" name="receiver_tva_number" id="receiver_tva_number" />
                            </div>
                            <div x-show="formData.receiver_trade_type == 'BU'" class="lc-form-control">
                                <label for="receiver_eori_number"><?= esc_html_e("EOI No.", "labal-courrier") ?></label>
                                <input x-model="formData.receiver_eori_number" value="<?= $old_values[0]->receiver_eori_number ?? '' ?>" type="text" class="form-control" placeholder="Numéro EORI" name="receiver_eori_number" id="receiver_eori_number" />
                            </div>
                        </div>

                        <div class="info-form-wrapper lc-grid grid-cols-1 md-grid-cols-3 gap-3 mb-3">
                            <div class="lc-form-control lc-form-control-phone">
                                <label for="receiver_phone_number"><?= esc_html_e("Phone", "labal-courrier") ?></label>
                                <input type="hidden" required name="receiver_full_phone_number" id="receiver_full_phone_number">
                                <div class="form-control-phone-grid lc-grid">
                                    <div class="" style="position: relative;">
                                        <img id="receiver_phone_flag" style="position: absolute;top: 8px;left: 12px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAKJSURBVHja1JbLahRBFIa/7uqZzCVxJlGCGoSoiSAh6FIDigxxkRfwBfIeLnwGt7rwBUQR1J0gIkg2YmZpjBNClATT3dOd6e66uchMbt3OKll4oKCoOnX+c/7q+k871lrO0lzO2M4cwAE8oAKUTjm2BBIPGF1cXNwVQhR6PaM2NMryo6l/7r1ffjruAXXXdWm1WoVONcpDAVozE4XrH9ZWAOoe4CmlSNMU3/exfd72zaKoDKZHNw7Wfndtf2oPOG9Ux9DaAHgegNaaOI6J4ziXicUMrSCWSW6tJEooo6F/wUgpiaKIKIpyzgYFAtzRBm59/z5skqD/bAMeUTqWO+O5Aq3VIYBSim63SxiGBQAJ3uUp9M4OjpjE7MXYXkZp+hpZu02YnsudEa5AmhMAYRgeABylW1y9Se3BfaJXbyjfmIWSR/z2HSO359FBQJh08wCOg1L6OIDv+wRBkOdz5jpyY5Pa0kMwFnHhPCNzc8j1dbypSwTpVv5xOQ7Z0QqklHQ6HXzfz9+i1ojxBtnXVcq35rG9BLWxSXV2Brn+k478lTsSVmKqTvkQIMsylFJorXPO2Wqb0vVpTJoi137gCA93YgLRbND9/AW9kP/KlNVkRh5q0SC4MSY3eh8/gYXqwh1Es4mYaFK9d5fs+zrpygrGmtzQVqO0PE7RIGCOoW7AzuMnjC4tIS5OYvd6pN/aRC9fY9IUUyD3yhgcbQ8BBtkXUQQuemub4PmLQq3UBUkZY5DKHq9gsEHBSxhmxhZUbTRmXyr6FSiNK1zq9XpB/sPVtF6uFMiLPfaSpVQSRzuFAXZJTrSPI0oHZFEytCc4QAO4AjRPueH4wMago40M6DpFU0Dq/Pd/FX8HAMTHY7xw6NPHAAAAAElFTkSuQmCC" alt="">
                                        <input x-model="formData.receiver_phone_code" type="text" list="receiver_phone_codes" class="form-control info-phone-code" id="receiver_phone_code" />
                                        <datalist id="receiver_phone_code">

                                        </datalist>
                                    </div>
                                    <input x-model="formData.receiver_phone_number" :class="{ 'is-invalid': !formvalidation.receiver_phone_number.validated }" value="" maxlength="22" required type="text" placeholder="Numéro de telephone" class="phone_numeber_field validate_number" id="receiver_phone_number" />
                                </div>
                                <div x-show="!formvalidation.receiver_phone_code.validated || !formvalidation.receiver_phone_number.validated" class="w-100 text-white validation_message">Please provide a valid phone number</div>
                            </div>
                            <div class="lc-form-control">
                                <label for="receiver_email"><?= esc_html_e("Email", "labal-courrier") ?></label>
                                <input x-model="formData.receiver_email" :class="{ 'is-invalid': !formvalidation.receiver_email.validated }" type="text" maxlength="50" class="" placeholder="Email" name="receiver_email" id="receiver_email" />
                                <div x-show="!formvalidation.receiver_email.validated" class="w-100 text-white validation_message">Please provide a valid email address</div>
                            </div>
                        </div>

                        <div class="info-form-wrapper lc-grid grid-cols-1 md-grid-cols-3 gap-3 mb-3">
                            <div class="lc-form-control">
                                <label for="address_receiver"><?= esc_html_e("Address Line 1", "labal-courrier") ?></label>
                                <input x-model="formData.address_receiver" type="text" name="receiver_address[]" placeholder="" class="validate_address" id="address_receiver" :class="{ 'is-invalid': !formvalidation.address_receiver.validated }" />
                                <div x-show="!formvalidation.address_receiver.validated" class="w-100 text-white validation_message"><?= esc_html_e("Please provide an address of less than 35 characters", "labal-courrier") ?></div>
                            </div>
                            <div class="lc-form-control">
                                <label for="address_receiver1"><?= esc_html_e("Address Line 2", "labal-courrier") ?></label>
                                <input type="text" name="receiver_address[]" placeholder="" class="validate_address" id="address_receiver1" :class="{ 'is-invalid': !formvalidation.address_receiver1.validated }" />
                                <div x-show="!formvalidation.address_receiver1.validated" class="w-100 text-white validation_message"><?= esc_html_e("Please provide an address of less than 35 characters", "labal-courrier") ?></div>
                            </div>
                        </div>

                        <div class="info-form-wrapper lc-grid grid-cols-1 md-grid-cols-3 gap-3 mb-3">
                            <div class="lc-form-control not-edditable-fields" :class="{ 'is-invalid-select': !formvalidation.del_country.validated }">
                                <label for="del_country"><?= esc_html_e("Country", "labal-courrier") ?></label>
                                <select x-model="formData.del_country" class="form-control lc-select-country del_country" name="del_country" id="del_country" aria-label="Example select with button addon">
                                    <option value="" selected><?= esc_html_e("Select the country", "labal-courrier") ?></option>
                                    <?php foreach ($countries as $code => $name) : ?>
                                        <option value="<?= $code ?>"><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div x-show="!formvalidation.del_country.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                            </div>
                            <div class="lc-form-control not-edditable-fields">
                                <label for="del_postcode_or_city"><?= esc_html_e("Town/City", "labal-courrier") ?></label>
                                <div style="width: 100%; display:block" class="" :class="{ 'is-invalid-select': !formvalidation.del_postcode_or_city.validated }">
                                    <select x-model="formData.del_postcode_or_city" style="width: 100%;" class="form-control" name="del_postcode_or_city" id="del_postcode_or_city"></select>
                                </div>
                                <div x-show="!formvalidation.del_postcode_or_city.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                            </div>

                            <input type="hidden" name="del_state" placeholder="" class="" value="<?= $s_data->receiver_state ?>" />

                            <div class="lc-form-control save-to-address-book d-flex align-items-end justify-content-end">
                                <a @click.prevent="saveReceiverAddress()" href="#" id="save_receiver_address" class="lc-link text-end text-decoration-underline"><i class="fa-regular fa-id-badge"></i> <?= __("Save to Address Book", "labal-courrier") ?></a>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="lc-steps-btn-area mt-5 text-center text-md-end">
                    <a href="<?= esc_url(site_url()) . '/labal-courrier-shipment/?quote_id=' . $quote_id ?>" class="btn lc-button lc-btn-back rounded"><?= __("Back", "labal-courrier") ?></a>
                    <button x-on:click.prevent="onSubmit()" type="submit" class="btn lc-button lc-btn-blue rounded ms-2"><?= __("Confirm & Continue", "labal-courrier") ?></button>
                </div>
            </form>
        </div>
    </div>

<?php

}
// print_r($_SESSION); die;
// echo $shipment_id; die;

?>

<script>
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });



    jQuery(document).ready(function() {
        jQuery('.info-sub-heading-area span').click(function() {
            jQuery(this).closest('.info-sub-heading-area').next('.info-section-wrapper-outer').find('.info-section-wrapper').slideToggle(300);
        });
    });

    const receivers = <?= json_encode($receivers); ?>;

    jQuery(function($) {

        $('#search_sender_name').select2();
        $('#search_receiver_name').select2();

        $(document).on('change', 'select[name="search_sender_name"]', function(e) {
            e.preventDefault();
            let search_sender_email = $(this).val();

            const sender = receivers.find(element => element.sender_email == search_sender_email);

            if (typeof sender === 'object' && Object.keys(sender).length > 0) {

                jQuery('#sender_first_name').val(sender.sender_first_name)[0].dispatchEvent(new Event('input'));
                jQuery('#sender_last_name').val(sender.sender_last_name)[0].dispatchEvent(new Event('input'));
                jQuery('#address_sender').val(sender.sender_address)[0].dispatchEvent(new Event('input'));
                jQuery('#sender_phone_number').val(sender.sender_phone_number)[0].dispatchEvent(new Event('input'));
                jQuery('#sender_email').val(sender.sender_email)[0].dispatchEvent(new Event('input'));
                jQuery('#col_country').val(sender.col_country).change();

                // set default sender postcode
                const data_col = {
                    id: sender.col_postcode_or_city,
                    text: sender.col_postcode_or_city.split('--').filter(x => x != '').join(' , ')
                };

                const newOption_col = new Option(data_col.text, data_col.id, false, false);
                jQuery('#col_postcode_or_city').html('').append(newOption_col).trigger('change');
            }
        });

        $(document).on('change', 'select[name="search_receiver_name"]', function(e) {
            e.preventDefault();
            let search_sender_email = $(this).val();

            const receiver = receivers.find(element => element.sender_email == search_sender_email);
            if (typeof receiver === 'object' && Object.keys(receiver).length > 0) {
                jQuery('#receiver_first_name').val(receiver.sender_first_name)[0].dispatchEvent(new Event('input'));
                jQuery('#receiver_last_name').val(receiver.sender_last_name)[0].dispatchEvent(new Event('input'));
                jQuery('#address_receiver').val(receiver.sender_address)[0].dispatchEvent(new Event('input'));
                jQuery('#receiver_phone_number').val(receiver.sender_phone_number)[0].dispatchEvent(new Event('input'));
                jQuery('#receiver_email').val(receiver.sender_email)[0].dispatchEvent(new Event('input'));
                jQuery('#del_country').val(receiver.col_country).change();

                // set default sender postcode
                const data_col = {
                    id: receiver.col_postcode_or_city,
                    text: receiver.col_postcode_or_city.split('--').filter(x => x != '').join(' , ')
                };

                const newOption_col = new Option(data_col.text, data_col.id, false, false);
                jQuery('#del_postcode_or_city').html('').append(newOption_col).trigger('change');
            }
        });

        jQuery('body').on('input', '.validate_address', function(event) {
            jQuery(this).val(event.target.value.replace(/[^a-zA-Z0-9 _,.#-]/g, ''))[0].dispatchEvent(new Event('input'));
        });

        jQuery('body').on('input', '.validate_number', function(event) {
            jQuery(this).val(event.target.value.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1'))[0].dispatchEvent(new Event('input'));
        });
        // jQuery('body').on('input', '.id_number', function(event) {
        //     jQuery(this).val(event.target.value.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1'))[0].dispatchEvent(new Event('input'));
        // });

        let phone_code_details = [];
        let dial_codes = [];

        //Init starts
        $.getJSON(lc_obj.plugin_url + "/js/countries/countries1.json", function(data) {
            phone_code_details = data;
            // console.log('phone code returned');
            Object.keys(phone_code_details).forEach(key => {
                dial_codes.push(phone_code_details[key].dial_code);
            });

            let html_string = '';
            dial_codes.forEach(function(code) {
                html_string += '<option value="' + code + '">'
            });
            $('#sender_phone_codes').html(html_string);
            $('#receiver_phone_codes').html(html_string);

            updatePhoneCode();
        });
        //Init ends

        function updatePhoneCode() {
            let col_country = $('select[name="col_country"]').val();
            if (col_country != '' && phone_code_details[col_country]) {
                let phone_data = phone_code_details[col_country];

                $('#sender_phone_code').val(phone_data.dial_code);
                $('#sender_phone_flag').attr('src', phone_data.flag);

                // console.log(phone_data.dial_code + $('sender_phone_number').val())
                $('#sender_full_phone_number').val(phone_data.dial_code + $('#sender_phone_number').val());
            }

            let del_country = $('select[name="del_country"]').val();
            if (del_country != '' && phone_code_details[del_country]) {
                let phone_data = phone_code_details[del_country];

                $('#receiver_phone_code').val(phone_data.dial_code);
                $('#receiver_phone_flag').attr('src', phone_data.flag);

                $('#receiver_full_phone_number').val(phone_data.dial_code + $('#receiver_phone_number').val());
            }
        }

        // -- SENDER --
        $(document).on('change', 'select[name="col_country"]', function(e) {
            e.preventDefault();
            col_country = $(this).val();
            // console.log('col_country changed');
            if (col_country != '' && phone_code_details[col_country]) {
                let phone_data = phone_code_details[col_country];

                $('#sender_phone_code').val(phone_data.dial_code);
                $('#sender_phone_flag').attr('src', phone_data.flag);

                // console.log(phone_data.dial_code + $('sender_phone_number').val())
                $('#sender_full_phone_number').val(phone_data.dial_code + $('#sender_phone_number').val());
            } else {
                $('#sender_phone_code').val('');
                $('#sender_phone_flag').attr('src', '');

                // console.log(phone_data.dial_code + $('sender_phone_number').val())
                $('#sender_full_phone_number').val('');
            }

        })


        $('#col_postcode_or_city').select2({
            placeholder: 'Code postal ou Ville',
            ajax: {
                url: lc_obj.ajax_url + '?action=search_by_postcode_or_city',
                data: function(params) {
                    var query = {
                        search: params.term,
                        country: col_country,
                    };
                    // Query parameters will be ?search=[term]&type=public
                    return query;
                },
                dataType: 'json'
            }
        });

        $(document).on('change', '#sender_phone_code', function() {
            let sender_phone_code = $(this).val();
            if (!dial_codes.includes(sender_phone_code)) {
                alert("Invalid dial code!");
                $(this).val('').focus();
            } else {
                Object.keys(phone_code_details).forEach(key => {
                    if (phone_code_details[key].dial_code == sender_phone_code) {
                        // console.log(phone_code_details[key].dial_code, sender_phone_code)
                        $('#sender_phone_flag').attr('src', phone_code_details[key].flag);
                        $('#sender_full_phone_number').val(sender_phone_code + $('#sender_phone_number').val());
                    }
                });
            }
        })

        // -- RECEIVER --
        $(document).on('change', 'select[name="del_country"]', function(e) {
            e.preventDefault();
            del_country = $(this).val();

            $('#del_postcode').val(null).trigger('change');
            $('#del_city').val(null).trigger('change');

            if (del_country != '' && phone_code_details[del_country]) {
                let phone_data = phone_code_details[del_country];

                $('#receiver_phone_code').val(phone_data.dial_code);
                $('#receiver_phone_flag').attr('src', phone_data.flag);

                $('#receiver_full_phone_number').val(phone_data.dial_code + $('#receiver_phone_number').val());
            } else {
                $('#receiver_phone_code').val('');
                $('#receiver_phone_flag').attr('src', '');

                $('#receiver_full_phone_number').val('');
            }

        })

        $('#del_postcode_or_city').select2({
            placeholder: 'Code postal ou ville',
            ajax: {
                url: lc_obj.ajax_url + '?action=search_by_postcode_or_city',
                data: function(params) {
                    var query = {
                        search: params.term,
                        country: del_country,
                    };
                    // Query parameters will be ?search=[term]&type=public
                    return query;
                },
                dataType: 'json'
            }
        });

        $(document).on('change', '#receiver_phone_code', function() {
            let receiver_phone_code = $(this).val();
            if (!dial_codes.includes(receiver_phone_code)) {
                alert("Invalid dial code!");
                $(this).val('').focus();
            } else {
                Object.keys(phone_code_details).forEach(key => {
                    if (phone_code_details[key].dial_code == receiver_phone_code) {
                        // console.log(phone_code_details[key].dial_code, receiver_phone_code)
                        $('#receiver_phone_flag').attr('src', phone_code_details[key].flag);
                        $('#receiver_full_phone_number').val(receiver_phone_code + $('#receiver_phone_number').val())
                    }
                });
            }
        })

    });

    jQuery(window).on('load', function() {
        let col_country = '<?= isset($s_data->sender_country_code) ? $s_data->sender_country_code : "" ?>';
        let del_country = '<?= isset($s_data->receiver_country_code) ? $s_data->receiver_country_code : "" ?>';

        let col_postcode_or_city = '<?= isset($s_data->sender_postcode) ? $s_data->sender_postcode . "--" . $s_data->sender_suburb . "--" . $s_data->sender_city : "" ?>';
        let del_postcode_or_city = '<?= isset($s_data->receiver_postcode) ? $s_data->receiver_postcode . "--" . $s_data->receiver_suburb . "--" . $s_data->receiver_city : "" ?>';

        const col_postcode_or_city_arr = [
            '<?= $s_data->sender_postcode ?? '' ?>',
            '<?= $s_data->sender_suburb ?? '' ?>',
            '<?= $s_data->sender_city ?? '' ?>',
        ]

        const del_postcode_or_city_arr = [
            '<?= $s_data->receiver_postcode ?? '' ?>',
            '<?= $s_data->receiver_suburb ?? '' ?>',
            '<?= $s_data->receiver_city ?? '' ?>',
        ]

        setTimeout(() => {
            jQuery('#col_country').val(col_country).change();
            jQuery('#del_country').val(del_country).change();

            // set default sender postcode
            const data_col = {
                id: col_postcode_or_city,
                text: col_postcode_or_city_arr.filter(x => x != '').join(' , ')
            };

            const newOption_col = new Option(data_col.text, data_col.id, false, false);
            jQuery('#col_postcode_or_city').append(newOption_col).trigger('change');

            // set default receiver postcode
            const data_del = {
                id: del_postcode_or_city,
                text: del_postcode_or_city_arr.filter(x => x != '').join(' , ')
            };

            const newOption_del = new Option(data_del.text, data_del.id, false, false);
            jQuery('#del_postcode_or_city').append(newOption_del).trigger('change');

            // populate sender phone number
            const sender_phone_code = jQuery('#sender_phone_code').val();
            let sender_phone_number = '<?= $s_data->sender_phone_number ?>';
            sender_phone_number = sender_phone_number.replace(sender_phone_code, '');
            jQuery('#sender_phone_number').val(sender_phone_number)[0].dispatchEvent(new Event('input'));
            jQuery('#sender_full_phone_number').val(sender_phone_code + sender_phone_number)[0].dispatchEvent(new Event('input'));

            // populate receiver phone number
            const receiver_phone_code = jQuery('#receiver_phone_code').val();
            let receiver_phone_number = '<?= $s_data->receiver_phone_number ?>';
            receiver_phone_number = receiver_phone_number.replace(receiver_phone_code, '');
            jQuery('#receiver_phone_number').val(receiver_phone_number)[0].dispatchEvent(new Event('input'));
            jQuery('#receiver_full_phone_number').val(receiver_phone_code + receiver_phone_number);

        }, 900);
    });

    const validateEmail = (email) => {
        return String(email)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    };

    function component() {
        return {
            init() {

                // populate all the fields
                setTimeout(() => {
                    const sender_trade_type = '<?= $s_data->sender_trade_type ?>';
                    if (sender_trade_type) {
                        jQuery('#sender_trade_type').val('<?= $s_data->sender_trade_type ?>').trigger('change');
                        this.formData.sender_trade_type = '<?= $s_data->sender_trade_type ?>';
                    }

                    const receiver_trade_type = '<?= $s_data->receiver_trade_type ?>';
                    if (receiver_trade_type) {
                        jQuery('#receiver_trade_type').val('<?= $s_data->receiver_trade_type ?>').trigger('change');
                        this.formData.receiver_trade_type = '<?= $s_data->receiver_trade_type ?>';
                    }


                    const sender_address = <?= json_encode(unserialize($s_data->sender_address)) ?>;
                    const receiver_address = <?= json_encode(unserialize($s_data->receiver_address)) ?>;

                    jQuery('#address_sender').val(sender_address[0])[0].dispatchEvent(new Event('input'));
                    jQuery('#address_sender1').val(sender_address[1])[0].dispatchEvent(new Event('input'));
                    // jQuery('#address_sender2').val(sender_address[2])[0].dispatchEvent(new Event('input'));

                    jQuery('#address_receiver').val(receiver_address[0])[0].dispatchEvent(new Event('input'));
                    jQuery('#address_receiver1').val(receiver_address[1])[0].dispatchEvent(new Event('input'));
                    // jQuery('#address_receiver2').val(receiver_address[2])[0].dispatchEvent(new Event('input'));

                }, 1100);

            },

            submitted: false,
            showError: false,
            errorMessage: [],
            formData: {
                sender_first_name: '<?= isset($s_data->sender_first_name) ? $s_data->sender_first_name : "" ?>',
                sender_last_name: '<?= isset($s_data->sender_last_name) ? $s_data->sender_last_name : "" ?>',
                sender_company: '<?= isset($s_data->sender_company_name) ? $s_data->sender_company_name : "" ?>',
                sender_trade_type: '',
                col_country: '',
                col_postcode_or_city: '',
                address_sender: '',
                sender_phone_code: '',
                sender_phone_number: '',
                sender_email: '<?= isset($s_data->sender_email) ? $s_data->sender_email : "" ?>',
                sender_id_number: '<?= isset($s_data->sender_id_number) ? $s_data->sender_id_number : "" ?>',
                sender_tva_number: '<?= isset($s_data->sender_tva_number) ? $s_data->sender_tva_number : "" ?>',
                sender_eori_number: '<?= isset($s_data->sender_eori_number) ? $s_data->sender_eori_number : "" ?>',

                receiver_first_name: '<?= isset($s_data->receiver_first_name) ? $s_data->receiver_first_name : "" ?>',
                receiver_last_name: '<?= isset($s_data->receiver_last_name) ? $s_data->receiver_last_name : "" ?>',
                receiver_company: '<?= isset($s_data->receiver_company_name) ? $s_data->receiver_company_name : "" ?>',
                receiver_trade_type: '',
                del_country: '',
                del_postcode_or_city: '',
                address_receiver: '',
                receiver_phone_code: '',
                receiver_phone_number: '',
                receiver_email: '<?= isset($s_data->receiver_email) ? $s_data->receiver_email : "" ?>',
                receiver_id_number: '<?= isset($s_data->receiver_id_number) ? $s_data->receiver_id_number : "" ?>',
                receiver_tva_number: '<?= isset($s_data->receiver_tva_number) ? $s_data->receiver_tva_number : "" ?>',
                receiver_eori_number: '<?= isset($s_data->receiver_eori_number) ? $s_data->receiver_eori_number : "" ?>',
            },
            formvalidation: {
                valid: true,
                sender_first_name: {
                    validated: true,
                },
                sender_last_name: {
                    validated: true,
                },
                sender_company: {
                    validated: true,
                },
                sender_trade_type: {
                    validated: true,
                },
                col_country: {
                    validated: true,
                },
                col_postcode_or_city: {
                    validated: true,
                },
                address_sender: {
                    validated: true,
                },
                address_sender1: {
                    validated: true,
                },
                sender_phone_code: {
                    validated: true,
                },
                sender_phone_number: {
                    validated: true,
                },
                sender_email: {
                    validated: true,
                },
                // -------------
                receiver_first_name: {
                    validated: true,
                },
                receiver_last_name: {
                    validated: true,
                },
                receiver_company: {
                    validated: true,
                },
                receiver_trade_type: {
                    validated: true,
                },
                del_country: {
                    validated: true,
                },
                del_postcode_or_city: {
                    validated: true,
                },
                address_receiver: {
                    validated: true,
                },
                address_receiver1: {
                    validated: true,
                },
                receiver_phone_code: {
                    validated: true,
                },
                receiver_phone_number: {
                    validated: true,
                },
                receiver_email: {
                    validated: true,
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
                    }
                });

                // remove validation from id_number 
                jQuery('.id_number').removeClass('is-invalid');
                jQuery('.id_number_validation_m').remove();
            },

            saveReceiverAddress() {
                this.resetFormError();
                const offsetMinus = 250;

                // remove alert messages
                jQuery('.alert').remove();

                // get all the values 
                this.formData.del_country = jQuery('#del_country').val();
                this.formData.del_postcode_or_city = jQuery('#del_postcode_or_city').val();
                // this.formData.receiver_phone_number = jQuery('#receiver_phone_number').val();

                if (this.formData.receiver_first_name == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['receiver_first_name']['validated'] = false;
                    this.validationIds.push('receiver_first_name');
                }
                if (this.formData.receiver_last_name == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['receiver_last_name']['validated'] = false;
                    this.validationIds.push('receiver_last_name');
                }
                if (this.formData.address_receiver == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['address_receiver']['validated'] = false;
                    this.validationIds.push('address_receiver');
                }
                if (this.formData.receiver_phone_number == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['receiver_phone_number']['validated'] = false;
                    this.validationIds.push('receiver_phone_number');
                }

                if (this.formData.del_country == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['del_country']['validated'] = false;
                    this.validationIds.push('del_country');
                }
                if (this.formData.del_postcode_or_city == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['del_postcode_or_city']['validated'] = false;
                    this.validationIds.push('del_postcode_or_city');
                }


                // phone validation 
                if (this.formData.receiver_phone_number.length < 5) {
                    this.formvalidation.valid = false;
                    this.formvalidation['receiver_phone_number']['validated'] = false;
                    this.validationIds.push('receiver_phone_number');
                }

                // email validation
                if (!validateEmail(this.formData.receiver_email)) {
                    this.formvalidation.valid = false;
                    this.formvalidation['receiver_email']['validated'] = false;
                    this.validationIds.push('receiver_email');
                }

                if (this.formvalidation.valid) {
                    // show loading icon
                    jQuery(".lc-common-loading").fadeIn();

                    jQuery.ajax({
                        url: lc_obj.ajax_url,
                        method: 'post',
                        data: {
                            action: 'save_receiver_address',
                            data: this.formData
                        },
                        dataType: 'json',
                        success: (resData) => {
                            // console.log(resData);
                            jQuery(".lc-common-loading").hide();

                            if (resData.status && resData.status == 'success') {
                                let status_msg = '<div class="alert alert-dismissible alert-success fade show" role="alert">\
                                            ' + resData.message + '\
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>\
                                        </div>';

                                jQuery("#save_receiver_address").closest('.info-section-wrapper').append(status_msg);
                            } else {
                                let status_msg = '<div class="alert alert-dismissible alert-danger fade show" role="alert">\
                                            ' + resData.message + '\
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>\
                                        </div>';

                                jQuery("#save_receiver_address").closest('.info-section-wrapper').append(status_msg);
                            }
                        }
                    });

                } else {
                    jQuery('.info-section-wrapper.info-receiver-wrapper').show();
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }

            },

            saveSenderAddress(event) {
                this.resetFormError();
                const offsetMinus = 250;

                // remove alert messages
                jQuery('.alert').remove();

                // get all the values 
                this.formData.col_country = jQuery('#col_country').val();
                this.formData.col_postcode_or_city = jQuery('#col_postcode_or_city').val();

                if (this.formData.sender_first_name == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['sender_first_name']['validated'] = false;
                    this.validationIds.push('sender_first_name');
                }
                if (this.formData.sender_last_name == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['sender_last_name']['validated'] = false;
                    this.validationIds.push('sender_last_name');
                }
                if (this.formData.address_sender == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['address_sender']['validated'] = false;
                    this.validationIds.push('address_sender');
                }
                if (this.formData.sender_phone_number == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['sender_phone_number']['validated'] = false;
                    this.validationIds.push('sender_phone_number');
                }

                if (this.formData.col_country == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['col_country']['validated'] = false;
                    this.validationIds.push('col_country');
                }
                if (this.formData.col_postcode_or_city == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation['col_postcode_or_city']['validated'] = false;
                    this.validationIds.push('col_postcode_or_city');
                }


                // phone validation 
                if (this.formData.sender_phone_number.length < 5) {
                    this.formvalidation.valid = false;
                    this.formvalidation['sender_phone_number']['validated'] = false;
                    this.validationIds.push('sender_phone_number');
                }

                // email validation
                if (!validateEmail(this.formData.sender_email)) {
                    this.formvalidation.valid = false;
                    this.formvalidation['sender_email']['validated'] = false;
                    this.validationIds.push('sender_email');
                }

                if (this.formvalidation.valid) {
                    // show loading icon
                    jQuery(".lc-common-loading").fadeIn();

                    jQuery.ajax({
                        url: lc_obj.ajax_url,
                        method: 'post',
                        data: {
                            action: 'save_sender_address',
                            data: this.formData
                        },
                        dataType: 'json',
                        success: (resData) => {
                            // console.log(resData);
                            jQuery(".lc-common-loading").hide();

                            if (resData.status && resData.status == 'success') {
                                let status_msg = '<div class="alert alert-dismissible alert-success fade show" role="alert">\
                                            ' + resData.message + '\
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>\
                                        </div>';

                                jQuery("#save_sender_address").closest('.info-section-wrapper').append(status_msg);
                            } else {
                                let status_msg = '<div class="alert alert-dismissible alert-danger fade show" role="alert">\
                                            ' + resData.message + '\
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>\
                                        </div>';

                                jQuery("#save_sender_address").closest('.info-section-wrapper').append(status_msg);
                            }
                        }
                    });

                } else {
                    jQuery('.info-section-wrapper.info-sender-wrapper').show();
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }

            },

            onSubmit() {
                this.resetFormError();
                const offsetMinus = 250;

                // get all the values 
                this.formData.col_country = jQuery('#col_country').val();
                this.formData.col_postcode_or_city = jQuery('#col_postcode_or_city').val();
                this.formData.sender_trade_type = jQuery('#sender_trade_type').val();
                this.formData.del_country = jQuery('#del_country').val();
                this.formData.del_postcode_or_city = jQuery('#del_postcode_or_city').val();
                this.formData.receiver_trade_type = jQuery('#receiver_trade_type').val();
                this.formData.sender_phone_code = jQuery('#sender_phone_code').val();
                this.formData.receiver_phone_code = jQuery('#receiver_phone_code').val();
                this.formData.sender_id_number = jQuery('#sender_id_number').val();
                this.formData.receiver_id_number = jQuery('#receiver_id_number').val();

                this.formData.sender_tva_number = jQuery('#sender_tva_number').val();
                this.formData.sender_eori_number = jQuery('#sender_eori_number').val();
                this.formData.receiver_tva_number = jQuery('#receiver_tva_number').val();
                this.formData.receiver_eori_number = jQuery('#receiver_eori_number').val();

                const address_sender1 = jQuery('#address_sender1').val();
                const address_receiver1 = jQuery('#address_receiver1').val();

                for (const [key, item] of Object.entries(this.formData)) {

                    if (key == 'sender_company' || key == 'receiver_company' || key == 'sender_id_number' || key == 'receiver_id_number' ||
                        key == 'sender_tva_number' || key == 'sender_eori_number' || key == 'receiver_tva_number' || key == 'receiver_eori_number') {
                        continue;
                    }

                    if (item === '') {
                        this.formvalidation.valid = false;
                        this.formvalidation[key]['validated'] = false;
                        this.validationIds.push(key);
                    }
                }

                if (this.formData.sender_trade_type === 'BU' && this.formData.sender_company === '' || this.formData.sender_company.length >= 25) {
                    this.formvalidation.valid = false;
                    this.formvalidation['sender_company']['validated'] = false;
                    this.validationIds.push('sender_company');
                }

                if (this.formData.receiver_trade_type === 'BU' && (this.formData.receiver_company === '' || this.formData.receiver_company.length >= 25)) {
                    this.formvalidation.valid = false;
                    this.formvalidation['receiver_company']['validated'] = false;
                    this.validationIds.push('receiver_company');
                }

                // phone validation 
                if (this.formData.sender_phone_number.length < 5) {
                    this.formvalidation.valid = false;
                    this.formvalidation['sender_phone_number']['validated'] = false;
                    this.validationIds.push('sender_phone_number');
                }
                if (this.formData.receiver_phone_number.length < 5) {
                    this.formvalidation.valid = false;
                    this.formvalidation['receiver_phone_number']['validated'] = false;
                    this.validationIds.push('receiver_phone_number');
                }

                // email validation
                if (!validateEmail(this.formData.sender_email)) {
                    this.formvalidation.valid = false;
                    this.formvalidation['sender_email']['validated'] = false;
                    this.validationIds.push('sender_email');
                }
                if (!validateEmail(this.formData.receiver_email)) {
                    this.formvalidation.valid = false;
                    this.formvalidation['receiver_email']['validated'] = false;
                    this.validationIds.push('receiver_email');
                }

                if (jQuery('.id_number').length > 0) {
                    jQuery('.id_number').each((index, element) => {
                        const val_id = jQuery(element).val();
                        if (val_id == '') {
                            this.formvalidation.valid = false;
                            jQuery(element).addClass('is-invalid');
                            jQuery(element).after('<div class="w-100 text-white validation_message id_number_validation_m"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>');
                        }
                    });
                }

                // if trade type is private, then company will be firstname + lastname 
                if (this.formData.sender_trade_type === 'PR') {
                    jQuery('#sender_company').val(this.formData.sender_first_name + ' ' + this.formData.sender_last_name);
                }
                if (this.formData.receiver_trade_type === 'PR') {
                    jQuery('#receiver_company').val(this.formData.receiver_first_name + ' ' + this.formData.receiver_last_name);
                }

                // address validation 
                if (this.formData.address_sender.length >= 35) {
                    this.formvalidation.valid = false;
                    this.formvalidation['address_sender']['validated'] = false;
                    this.validationIds.push('address_sender');
                }
                if (address_sender1.length >= 35) {
                    this.formvalidation.valid = false;
                    this.formvalidation['address_sender1']['validated'] = false;
                    this.validationIds.push('address_sender1');
                }

                if (this.formData.address_receiver.length >= 35) {
                    this.formvalidation.valid = false;
                    this.formvalidation['address_receiver']['validated'] = false;
                    this.validationIds.push('address_receiver');
                }
                if (address_receiver1.length >= 35) {
                    this.formvalidation.valid = false;
                    this.formvalidation['address_receiver1']['validated'] = false;
                    this.validationIds.push('address_receiver1');
                }


                if (this.formvalidation.valid) {
                    // submit the form
                    jQuery('#frm_addtional_info').submit();
                    jQuery(".lc-loading-modal").fadeIn();
                } else {
                    jQuery('.info-section-wrapper').show();
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }

            }

        }
    }
</script>