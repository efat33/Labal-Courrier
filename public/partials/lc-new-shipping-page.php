<?php

if (!is_user_logged_in()) {
    echo '
        <script>
            window.location.replace("' . esc_url(site_url()) . '");
        </script>
    ';
}
$countries = lc_get_all_countries();

$user = wp_get_current_user();
$user_id = $user->ID;

$errors = [];
$form_updated = 0;

if (
    isset($_GET['request_status']) && $_GET['request_status'] == 'error' &&
    isset($_GET['request_id']) && !empty($_GET['request_id'])
) {
    $r_data = get_transient($_GET['request_id']);
    $s_data = (object) $r_data['old_values'];
    $errors = array_diff_key($r_data, array_flip(["old_values"]));
} else if (isset($_GET['request_status']) && $_GET['request_status'] == 'success') {
    $form_updated = 1;
}

// get the existing data in case of editing 
if (isset($_GET['id'])) {
    global $wpdb;
    $table = $wpdb->prefix . 'lc_shipping_addresses';
    $address = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $_GET['id']));

    $s_data = $address;

    $post_arr = explode('--', $s_data->col_postcode_or_city);

    $s_data->sender_postcode = $post_arr[0];
    $s_data->sender_suburb = $post_arr[1];
    $s_data->sender_city = $post_arr[2];
}

?>

<script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>

<div x-data="component()" x-init="init()" class="lc-customer-dashboar lc-profile-page lc-form-container my-5">
    <div class="lc-dashboard-wrapper lc-grid grid-cols-1 md-grid-cols-4">
        <div class="lc-dashboard-left">
            <?php
            include LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-dashboard-menu.php';
            ?>
        </div>
        <div class="lc-dashboard-right py-3">
            <h1 class="lr-form-heading mb-4"><?= isset($_GET['id']) ? esc_html_e("Edit Shipping Address", "labal-courrier") : esc_html_e("Create Shipping Address", "labal-courrier") ?></h1>

            <div class="lc-dashboard-right-wrapper p-4 rounded">
                <form id="lc_shipping_address_form" class=" w-100" action="<?php echo esc_url(site_url('wp-admin/admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="lc_shipping_address_action">
                    <?php if (isset($_GET['id'])) { ?>
                        <input type="hidden" name="address_id" value="<?= $_GET['id'] ?>">
                    <?php } ?>
                    <?php wp_nonce_field('lc_shipping_address_nonce', 'lc_nonce'); ?>

                    <!-- show error message -->
                    <div style="<?php echo (count($errors) > 0) ? "" : "display: none;" ?>" class="w-100 common_error text-danger validation_message mb-3">
                        <?php
                        if (count($errors) > 0) {
                            foreach ($errors as $key => $value) {
                        ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= $value ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>

                    <!-- show success message -->
                    <?php if ($form_updated == 1) { ?>
                        <div class="alert alert-success fade show mb-3" role="alert">
                            <?=
                            isset($_GET['edited']) ? esc_html_e('Updated Successfully', 'labal-courrier') : esc_html_e('Created Successfully', 'labal-courrier');
                            ?>
                        </div>
                    <?php } ?>


                    <div class="info-form-wrapper info-form-identity lc-grid grid-cols-1 md-grid-cols-2 gap-3 mb-3">
                        <div class="lc-form-control bg-grey" id="sender_first_name">
                            <label for="sender_first_name"><?= esc_html_e("First Name", "labal-courrier") ?></label>
                            <input x-model="formData.sender_first_name" type="text" name="sender_first_name" placeholder="John" class="" :class="{ 'is-invalid': !formvalidation.sender_first_name.validated }" />
                            <div x-show="!formvalidation.sender_first_name.validated" class="w-100 text-white validation_message"><?= esc_html_e("Firstname cannot be empty", "labal-courrier") ?></div>
                        </div>
                        <div class="lc-form-control bg-grey" id="sender_last_name">
                            <label for="sender_last_name"><?= esc_html_e("Last Name", "labal-courrier") ?></label>
                            <input x-model="formData.sender_last_name" type="text" name="sender_last_name" placeholder="Doe" class="" :class="{ 'is-invalid': !formvalidation.sender_last_name.validated }" />
                            <div x-show="!formvalidation.sender_last_name.validated" class="w-100 text-white validation_message"><?= esc_html_e("Lastname cannot be empty", "labal-courrier") ?></div>
                        </div>
                    </div>

                    <div class="info-form-wrapper lc-grid grid-cols-1 md-grid-cols-2 gap-3 mb-3">
                        <div class="lc-form-control bg-grey lc-form-control-phone">
                            <label for="sender_phone_number"><?= esc_html_e("Phone", "labal-courrier") ?></label>
                            <input type="hidden" required name="sender_full_phone_number" id="sender_full_phone_number">
                            <div class="form-control-phone-grid lc-grid">
                                <div class="" style="position: relative;">
                                    <img id="sender_phone_flag" style="position: absolute;top: 8px;left: 12px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAKJSURBVHja1JbLahRBFIa/7uqZzCVxJlGCGoSoiSAh6FIDigxxkRfwBfIeLnwGt7rwBUQR1J0gIkg2YmZpjBNClATT3dOd6e66uchMbt3OKll4oKCoOnX+c/7q+k871lrO0lzO2M4cwAE8oAKUTjm2BBIPGF1cXNwVQhR6PaM2NMryo6l/7r1ffjruAXXXdWm1WoVONcpDAVozE4XrH9ZWAOoe4CmlSNMU3/exfd72zaKoDKZHNw7Wfndtf2oPOG9Ux9DaAHgegNaaOI6J4ziXicUMrSCWSW6tJEooo6F/wUgpiaKIKIpyzgYFAtzRBm59/z5skqD/bAMeUTqWO+O5Aq3VIYBSim63SxiGBQAJ3uUp9M4OjpjE7MXYXkZp+hpZu02YnsudEa5AmhMAYRgeABylW1y9Se3BfaJXbyjfmIWSR/z2HSO359FBQJh08wCOg1L6OIDv+wRBkOdz5jpyY5Pa0kMwFnHhPCNzc8j1dbypSwTpVv5xOQ7Z0QqklHQ6HXzfz9+i1ojxBtnXVcq35rG9BLWxSXV2Brn+k478lTsSVmKqTvkQIMsylFJorXPO2Wqb0vVpTJoi137gCA93YgLRbND9/AW9kP/KlNVkRh5q0SC4MSY3eh8/gYXqwh1Es4mYaFK9d5fs+zrpygrGmtzQVqO0PE7RIGCOoW7AzuMnjC4tIS5OYvd6pN/aRC9fY9IUUyD3yhgcbQ8BBtkXUQQuemub4PmLQq3UBUkZY5DKHq9gsEHBSxhmxhZUbTRmXyr6FSiNK1zq9XpB/sPVtF6uFMiLPfaSpVQSRzuFAXZJTrSPI0oHZFEytCc4QAO4AjRPueH4wMago40M6DpFU0Dq/Pd/FX8HAMTHY7xw6NPHAAAAAElFTkSuQmCC" alt="">
                                    <input x-model="formData.sender_phone_code" type="text" list="sender_phone_codes" class="form-control info-phone-code" id="sender_phone_code" />
                                    <datalist id="sender_phone_codes">

                                    </datalist>
                                </div>
                                <input x-model="formData.sender_phone_number" value="" maxlength="22" required type="text" name="sender_phone_number" placeholder="Numéro de telephone" class="form-control phone_numeber_field validate_number" id="sender_phone_number" />
                            </div>
                            <div x-show="!formvalidation.sender_phone_code.validated || !formvalidation.sender_phone_number.validated" class="w-100 text-white validation_message"><?= esc_html_e("Please provide a valid phone number", "labal-courrier") ?></div>
                        </div>
                        <div class="lc-form-control bg-grey">
                            <label for="sender_email"><?= esc_html_e("Email", "labal-courrier") ?></label>
                            <input x-model="formData.sender_email" type="text" maxlength="50" class="form-control" placeholder="Email" name="sender_email" id="sender_email" />
                            <div x-show="!formvalidation.sender_email.validated" class="w-100 text-white validation_message"><?= esc_html_e("Please provide a valid email address", "labal-courrier") ?></div>
                        </div>
                    </div>

                    <div class="info-form-wrapper lc-grid grid-cols-1 md-grid-cols-3 gap-3 mb-3">
                        <div class="lc-form-control bg-grey" :class="{ 'is-invalid-select': !formvalidation.col_country.validated }">
                            <label for=""><?= esc_html_e("Country", "labal-courrier") ?></label>
                            <select x-model="formData.col_country" class="form-control lc-select-country col_country" name="col_country" id="col_country" aria-label="Example select with button addon">
                                <option value="" selected><?= esc_html_e("Select the country", "labal-courrier") ?></option>
                                <?php foreach ($countries as $code => $name) : ?>
                                    <option value="<?= $code ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div x-show="!formvalidation.col_country.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                        <div class="lc-form-control bg-grey">
                            <label for=""><?= esc_html_e("Town/City", "labal-courrier") ?></label>
                            <div style="width: 100%; display:block" class="" :class="{ 'is-invalid-select': !formvalidation.col_postcode_or_city.validated }">
                                <select x-model="formData.col_postcode_or_city" style="width: 100%;" class="form-control" name="col_postcode_or_city" id="col_postcode_or_city"></select>
                            </div>
                            <div x-show="!formvalidation.col_postcode_or_city.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                        <div class="lc-form-control bg-grey">
                            <label for="address_sender"><?= esc_html_e("Address Line", "labal-courrier") ?></label>
                            <input x-model="formData.address_sender" type="text" name="sender_address" placeholder="" class="validate_address" id="address_sender" :class="{ 'is-invalid': !formvalidation.address_sender.validated }" />
                            <div x-show="!formvalidation.address_sender.validated" class="w-100 text-white validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                        </div>
                    </div>


                    <div class="text-end mb-3 mb-md-0">
                        <button x-on:click.prevent="onSubmit()" type="submit" class="btn lc-button lc-btn-blue rounded px-4"><?= isset($_GET['id']) ? __("Update", "labal-courrier") : __("Create", "labal-courrier") ?></button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const validateEmail = (email) => {
        return String(email)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    };

    jQuery(function($) {
        jQuery('body').on('input', '.validate_address', function(event) {
            jQuery(this).val(event.target.value.replace(/[^a-zA-Z0-9 _,.#-]/g, ''))[0].dispatchEvent(new Event('input'));
        });

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
            minimumInputLength: 3,
            placeholder: 'Code postal ou Ville',
            language: {
                inputTooShort: function() {
                    return "Saisissez au moins 3 caractère";
                },
                noResults: function() {
                    return "Aucun résultat trouvé";
                },
                searching: function() {
                    return "Recherche en cours…"
                }
            },
            ajax: {
                delay: 250,
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
    });

    jQuery(window).on('load', function() {
        let col_country = '<?= isset($s_data->col_country) ? $s_data->col_country : "" ?>';

        let col_postcode_or_city = '<?= isset($s_data->col_postcode_or_city) ? $s_data->col_postcode_or_city : "" ?>';

        const col_postcode_or_city_arr = [
            '<?= $s_data->sender_postcode ?? '' ?>',
            '<?= $s_data->sender_suburb ?? '' ?>',
            '<?= $s_data->sender_city ?? '' ?>',
        ]

        setTimeout(() => {
            jQuery('#col_country').val(col_country).change();

            // set default sender postcode
            const data_col = {
                id: col_postcode_or_city,
                text: col_postcode_or_city_arr.filter(x => x != '').join(' , ')
            };

            const newOption_col = new Option(data_col.text, data_col.id, false, false);
            jQuery('#col_postcode_or_city').append(newOption_col).trigger('change');
        }, 900);
    });

    function component() {
        return {
            init() {

                // populate all the fields
                setTimeout(() => {

                    const sender_phone_code = jQuery('#sender_phone_code').val();
                    let sender_phone_number = '<?= $s_data->sender_phone_number ?>';
                    sender_phone_number = sender_phone_number.replace(sender_phone_code, '');
                    jQuery('#sender_phone_number').val(sender_phone_number)[0].dispatchEvent(new Event('input'));
                    jQuery('#sender_full_phone_number').val(sender_phone_code + sender_phone_number);

                }, 1100);

            },

            formData: {
                sender_first_name: '<?= isset($s_data->sender_first_name) ? $s_data->sender_first_name : "" ?>',
                sender_last_name: '<?= isset($s_data->sender_last_name) ? $s_data->sender_last_name : "" ?>',
                col_country: '',
                col_postcode_or_city: '',
                address_sender: '<?= isset($s_data->sender_address) ? $s_data->sender_address : "" ?>',
                sender_phone_code: '',
                sender_phone_number: '',
                sender_email: '<?= isset($s_data->sender_email) ? $s_data->sender_email : "" ?>',
            },
            formvalidation: {
                valid: true,
                sender_first_name: {
                    validated: true,
                },
                sender_last_name: {
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
                sender_phone_code: {
                    validated: true,
                },
                sender_phone_number: {
                    validated: true,
                },
                sender_email: {
                    validated: true,
                },
            },

            validationIds: [],

            resetFormError() {
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

                this.formData.col_country = jQuery('#col_country').val();
                this.formData.col_postcode_or_city = jQuery('#col_postcode_or_city').val();
                this.formData.sender_phone_code = jQuery('#sender_phone_code').val();

                for (const [key, item] of Object.entries(this.formData)) {

                    if (item === '') {
                        this.formvalidation.valid = false;
                        this.formvalidation[key]['validated'] = false;
                        this.validationIds.push(key);
                    }
                }

                if (this.formvalidation.valid) {
                    jQuery(".lc-common-loading").fadeIn();

                    // submit the form
                    jQuery('#lc_shipping_address_form').submit();
                } else {
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }
            }
        }
    }
</script>