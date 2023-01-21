<?php
// require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/helpers/countries.php';
session_start();
$countries = lc_get_all_countries();
$error = false;
$messages = [];
if (isset($_GET['request_id']) && !empty($_GET['request_id'])) {
    $error = true;
    $messages = get_transient($_GET['request_id']);
    // echo '--------------';
    // print_r(get_transient($_GET['request_id']));
}
$q_data = new stdClass();

// if (isset($_SESSION['quote_id']) && $_SESSION['quote_id'] != '') {
//     $data = get_transient($_SESSION['quote_id']);

//     if (isset($data['quote_request']) && !empty($data['quote_request'])) {
//         $q_data = (object) $data['quote_request'];
//     }
// }
?>

<script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>

<div x-data="component()" x-init="init()" class="lc-wizard-container lc">
    <div class="lc-wizard-wrapper">
        <form id="frm_get_quote" class="lc-form wpcsr-quote-book mb-0" action="<?php echo esc_attr('wp-admin/admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="get_quote">
            <input type="hidden" name="is_wpcargo" value="1">
            <?php wp_nonce_field('get_quote', 'lc_nonce'); ?>
            <div class="row lc-content">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-12">
                            <div style="<?php echo ($error && (isset($messages['common_error']) || isset($messages['carrier_error']))) ? "" : "display: none;" ?>" class="w-100 mb-3 pt-1 pb-1 text-white common_error validation_message">
                                <?php
                                if ($error && isset($messages['common_error'])) {
                                    echo $messages['common_error'];
                                }
                                if ($error && isset($messages['carrier_error'])) {
                                    echo $messages['carrier_error'];
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-5 col-5 input_collection_from input_group col_location frm-location-from" style="position: relative;">
                            <label class="text-warning">Ville d'origine:<span class="monde-req">*</span></label>

                            <img class="lc-loading" style="width: 20px; display: none;" src="<?php echo LABAL_COURRIER_PLUGIN_URL . '/public/img/loading.gif' ?>" />

                            <div class="input-group" style="position: relative;" :class="{ 'is-invalid-select': !formvalidation.col_country_from.validated }">
                                <select x-model="formData.col_country_from" class="form-control lc-select-country col_country" name="col_country" id="col_country_from" aria-label="Example select with button addon">
                                    <option value="" selected>Pays</option>
                                    <?php foreach ($countries as $code => $name) : ?>
                                        <?php if (isset($messages['old_values']['col_country']) && trim($messages['old_values']['col_country']) == $code) : ?>
                                            <option selected value="<?= $code ?>"><?= $name ?></option>
                                        <?php else : ?>
                                            <option value="<?= $code ?>"><?= $name ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <div x-show="!formvalidation.col_country_from.validated" class="w-100 text-white validation_message">This field cannot be empty</div>

                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="ui-widget">
                                        <div style="width: 100%; display:block" class="" :class="{ 'is-invalid-select': !formvalidation.col_postcode_or_city.validated }">
                                            <select x-model="formData.col_postcode_or_city" style="width: 100%;" class="form-control" name="col_postcode_or_city" id="col_postcode_or_city"></select>
                                        </div>
                                        <!-- <div style="width: 100%; display:block" class="col_postcode_container"><select style="width: 100%;" class="form-control" name="col_postcode" id="col_postcode"></select></div> -->
                                        <!-- <div style="width: 100%; display:none" class="col_suburb_container"><select style="width: 100%;" class="form-control" name="col_suburb" id="col_suburb"></select></div> -->
                                        <div x-show="!formvalidation.col_postcode_or_city.validated" class="w-100 text-white validation_message">This field cannot be empty</div>
                                    </div>
                                </div>
                                <!-- <div class="col-6 pl-0 pr-0">
                                    <div class="ui-widget">
                                        <select class="form-control" name="col_city" style="width: 100%;" id="col_city"></select>
                                    </div>
                                </div> -->
                            </div>
                            <div style="<?php echo ($error && isset($messages['col_location'])) ? "" : "display: none;" ?>" class="w-100 text-white validation_message">
                                <?php
                                if ($error && isset($messages['col_location'])) {
                                    foreach ($messages['col_location'] as $message) {
                                        echo $message . "<br>";
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <div class="col-lg-2  col-2 text-center frm-location-exchange" style="position: relative;">
                            <a id="exchange_button" class="c-d-arrow"><i class="fa fa-exchange"></i></a>
                        </div>

                        <div class="col-lg-5 col-5 input_collection_from input_group frm-location-to">
                            <label class="text-warning">Ville de Destination:<span class="monde-req">*</span></label>

                            <img class="lc-loading" style="width: 20px; display: none;" src="<?php echo LABAL_COURRIER_PLUGIN_URL . '/public/img/loading.gif' ?>" />

                            <div class="input-group" :class="{ 'is-invalid-select': !formvalidation.col_country_to.validated }">
                                <select x-model="formData.col_country_to" class="form-control lc-select-country del_country" name="del_country" id="col_country_to" aria-label="Example select with button addon">
                                    <option value="" selected>Pays</option>

                                    <?php foreach ($countries as $code => $name) : ?>
                                        <?php if (isset($messages['old_values']['del_country']) && trim($messages['old_values']['del_country']) == $code) : ?>
                                            <option selected value="<?= $code ?>"><?= $name ?></option>
                                        <?php else : ?>
                                            <option value="<?= $code ?>"><?= $name ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                </select>
                                <div x-show="!formvalidation.col_country_to.validated" class="w-100 text-white validation_message">This field cannot be empty</div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="ui-widget">
                                        <div style="width: 100%; display:block" class="" :class="{ 'is-invalid-select': !formvalidation.del_postcode_or_city.validated }">
                                            <select x-model="formData.del_postcode_or_city" style="width: 100%;" class="form-control" name="del_postcode_or_city" id="del_postcode_or_city"></select>
                                        </div>
                                        <div x-show="!formvalidation.del_postcode_or_city.validated" class="w-100 text-white validation_message">This field cannot be empty</div>
                                        <!-- <div style="width: 100%; display:block" class="del_postcode_container"><select style="width: 100%;" class="form-control" name="del_postcode" id="del_postcode"></select></div> -->
                                        <!-- <div style="width: 100%; display:none" class="del_suburb_container"><select style="width: 100%;" class="form-control" name="del_suburb" id="del_suburb"></select></div> -->
                                    </div>
                                </div>
                                <!-- <div class="col-6 pl-0 pr-0">
                                    <div class="ui-widget">
                                        <select class="form-control" name="del_city" style="width: 100%;" id="del_city"></select>
                                    </div>
                                </div> -->
                            </div>
                            <div style="<?php echo ($error && isset($messages['del_location'])) ? "" : "display: none;" ?>" class="w-100 text-white validation_message">
                                <?php
                                if ($error && isset($messages['del_location'])) {
                                    foreach ($messages['del_location'] as $message) {
                                        echo $message . "<br>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-5">
                            <label class="text-warning">Date d’envoi:<span class="monde-req">*</span></label>

                            <?php if (isset($messages['old_values']['del_city'])) : ?>
                                <!-- <div class="lc_date_field_wrapper"> -->
                                <input type="hidden" class="gq-dispatch-date-field" name="dispatch_date" value="<?php echo $messages['old_values']['dispatch_date']; ?>" />
                                <input type="text" class="form-control lc-input gq-dispatch-date" />
                                <!-- </div> -->
                            <?php else : ?>
                                <!-- <div class="lc_date_field_wrapper"> -->
                                <input type="hidden" class="gq-dispatch-date-field" name="dispatch_date" value="<?php echo date('Y-m-d', strtotime('+1 Weekday')); ?>" />
                                <input type="text" class="form-control lc-input gq-dispatch-date" />
                                <!-- </div> -->
                            <?php endif; ?>

                        </div>
                    </div>
                    <div class="seperator date-separator"></div>
                    <div class="row row-docs-type mt-3">
                        <div class="col-lg-6" id="package_type">
                            <label class="text-warning w-100">Type d’envoi:<span class="monde-req">*</span></label>
                            <label for="package-type-Package" class="lc-radio package-type-label ">
                                <input x-model="formData.package_type" type="radio" name="package_type" class="package-type" value="Package" id="package-type-Package">
                                Colis </label>
                            <label for="package-type-Documents" class="lc-radio package-type-label ">
                                <input x-model="formData.package_type" type="radio" name="package_type" class="package-type" value="Document" id="package-type-Documents">
                                Documents </label>
                            <div style="<?php echo ($error && isset($messages['package_type'])) ? "" : "display: none;" ?>" class="w-100 text-white validation_message">
                                <?php
                                if ($error && isset($messages['package_type'])) {
                                    echo $messages['package_type'];
                                }
                                ?>
                            </div>
                            <div x-show="!formvalidation.package_type.validated" class="w-100 text-white validation_message">Please choose a package type</div>
                        </div>
                    </div>
                    <div class="row mt-3" id="table_lc_packages">
                        <div class="col-lg-12">
                            <label class="w-100 text-warning">Dimensions et Poids de votre expédition:<span class="monde-req">*</span></label>
                            <div class="table-responsive">
                                <table class="table lc-packages">
                                    <thead>
                                        <tr>
                                            <th>Quantité</th>
                                            <th>Poids</th>
                                            <th>Longueur</th>
                                            <th>Largeur</th>
                                            <th>Hauteur</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lc_packages">
                                        <template x-for="[id, item] in Object.entries(formData.packages)">
                                            <tr>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" :name="'package['+id+'][qty]'" :id="'package_qty_'+id" min="1" value="1" class="form-control">
                                                        <span class="input-group-text">pcs</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input x-model="item.weight" type="text" :name="'package['+id+'][weight]'" :id="'package_weight_'+id" class="form-control validate_number" :class="{ 'is-invalid': !formvalidation.packages[id]?.weight.validated }">
                                                        <span class="input-group-text">kg</span>
                                                    </div>
                                                    <div x-show="!formvalidation.packages[id]?.weight.validated" class="w-100 text-white validation_message">This field cannot be empty</div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input x-model="item.length" type="text" :name="'package['+id+'][length]'" :id="'package_length_'+id" class="form-control validate_number" :class="{ 'is-invalid': !formvalidation.packages[id]?.length.validated }">
                                                        <span class="input-group-text">cm</span>
                                                    </div>
                                                    <div x-show="!formvalidation.packages[id]?.length.validated" class="w-100 text-white validation_message">This field cannot be empty</div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input x-model="item.width" type="text" :name="'package['+id+'][width]'" :id="'package_width_'+id" class="form-control validate_number" :class="{ 'is-invalid': !formvalidation.packages[id]?.width.validated }">
                                                        <span class="input-group-text">cm</span>
                                                    </div>
                                                    <div x-show="!formvalidation.packages[id]?.width.validated" class="w-100 text-white validation_message">This field cannot be empty</div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input x-model="item.height" type="text" :name="'package['+id+'][height]'" :id="'package_height_'+id" class="form-control validate_number" :class="{ 'is-invalid': !formvalidation.packages[id]?.height.validated }">
                                                        <span class="input-group-text">cm</span>
                                                    </div>
                                                    <div x-show="!formvalidation.packages[id]?.height.validated" class="w-100 text-white validation_message">This field cannot be empty</div>
                                                </td>
                                                <td>
                                                    <button x-on:click.prevent="removePackage(id)" type="button" class="btn btn-remove-package">
                                                        <span class="fa fa-trash"></span>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6">
                                                <button x-on:click.prevent="addPackages()" id="" type="button" class="repeater-add btn"><span style="line-height: unset;" class="dashicons dashicons-plus repeater-add" alt="Add"></span> Ajouter colis</button>
                                                <button x-on:click.prevent="copyPackage()" id="" type="button" class="repeater-add btn"><span style="line-height: unset;" class="dashicons dashicons-plus repeater-add" alt="Copy"></span> COPIER</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-6 col-6 col-assurance" id="insurance">
                            <label class="text-warning w-100">Assurance:<span class="monde-req">*</span></label>
                            <label for="ins-enable" class="lc-radio is-insurance ">
                                <input x-model="formData.insurance" type="radio" name="insurance" class="is-insurance-field" value="1" id="ins-enable">
                                Oui </label>
                            <label for="ins-disable" class="lc-radio is-insurance label-ins-disable">
                                <input x-model="formData.insurance" type="radio" name="insurance" class="is-insurance-field" value="0" id="ins-disable">
                                Non </label>

                            <div x-show="!formvalidation.insurance.validated" class="w-100 text-white validation_message">Please choose your preference</div>
                        </div>
                        <div x-show="formData.package_type == 'Package' && formData.insurance == 1" class="col-lg-6 col-6 col-insurance-val" id="insurance_value">
                            <label class="text-warning w-100">Valeur à assurer en €:<span class="monde-req">*</span></label>
                            <input type="text" name="insurance_value" placeholder="0.00" class="form-control validate_number" :class="{ 'is-invalid': !formvalidation.insurance_value.validated }" />
                            <div x-show="!formvalidation.insurance_value.validated" class="w-100 text-white validation_message">This field cannot be empty</div>
                        </div>
                    </div>

                    <div class="seperator"></div>

                    <div class="row mt-3">
                        <div class="col-lg-12 col-quote-book">
                            <!--<div class="form-group form-check text-warning">-->
                            <!--    <input type="checkbox" class="form-check-input text-warning" name="debug" id="debug">-->
                            <!--    <label class="form-check-label" for="debug">Calculation Debug</label>-->
                            <!--</div>-->

                            <div class="quote_book_wrap text-center">
                                <input x-on:click.prevent="onSubmit()" type="submit" class="btn btn-primary get_quote" id="btn_get_quote" value="Devis et Expédition">
                            </div>
                            <!-- <div style="overflow: hidden;" id="rqf_validation_msg" class="text-white validation_message">Veuillez remplir tous les champs</div> -->
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    jQuery(function($) {
        jQuery('body').on('input', '.validate_number', function(event) {
            jQuery(this).val(event.target.value.replace(',', '.').replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1'))[0].dispatchEvent(new Event('input'));
        });

        let col_country = '<?= isset($q_data->col_country) ? $q_data->col_country : "" ?>';
        let del_country = '<?= isset($q_data->del_country) ? $q_data->del_country : "" ?>';

        let col_postcode_or_city = '<?= isset($q_data->col_postcode) ? $q_data->col_postcode . "--" . $q_data->col_suburb . "--" . $q_data->col_city : "" ?>';
        let del_postcode_or_city = '<?= isset($q_data->del_postcode) ? $q_data->del_postcode . "--" . $q_data->del_suburb . "--" . $q_data->del_city : "" ?>';

        const col_postcode_or_city_arr = [
            '<?= $q_data->col_postcode ?? '' ?>',
            '<?= $q_data->col_suburb ?? '' ?>',
            '<?= $q_data->col_city ?? '' ?>',
        ]

        const del_postcode_or_city_arr = [
            '<?= $q_data->del_postcode ?? '' ?>',
            '<?= $q_data->del_suburb ?? '' ?>',
            '<?= $q_data->del_city ?? '' ?>',
        ]

        setTimeout(() => {

            jQuery('#col_country_from').val(col_country).change();
            jQuery('#col_country_to').val(del_country).change();

            // set default sender postcode
            const data_col = {
                id: col_postcode_or_city,
                text: col_postcode_or_city_arr.filter(x => x != '').join(' , ')
            };

            const newOption_col = new Option(data_col.text, data_col.id, false, false);
            $('#col_postcode_or_city').append(newOption_col).trigger('change');

            // set default receiver postcode
            const data_del = {
                id: del_postcode_or_city,
                text: del_postcode_or_city_arr.filter(x => x != '').join(' , ')
            };

            const newOption_del = new Option(data_del.text, data_del.id, false, false);
            $('#del_postcode_or_city').append(newOption_del).trigger('change');

        }, 900);


    });

    function component() {
        return {
            init() {
                setTimeout(() => {
                    let package_type = '<?= isset($q_data->package_type) ? $q_data->package_type : "" ?>';
                    let insurance = '<?= isset($q_data->insurance) ? $q_data->insurance : "" ?>';
                    let insurance_value = '<?= isset($q_data->insurance_value) ? $q_data->insurance_value : "" ?>';
                    let package = '<?= isset($q_data->package) ? json_encode($q_data->package) : "" ?>';

                    if (package != '' && JSON.parse(package).length > 0) {
                        this.formData.packages = JSON.parse(package);

                        let p_validation = [];
                        for (const iterator of JSON.parse(package)) {
                            const objV = {
                                weight: {
                                    validated: true,
                                    message: ''
                                },
                                length: {
                                    validated: true,
                                    message: ''
                                },
                                width: {
                                    validated: true,
                                    message: ''
                                },
                                height: {
                                    validated: true,
                                    message: ''
                                },
                            }

                            p_validation.push(objV);
                        }
                        this.formvalidation.packages = p_validation;
                    }
                    if (package_type != '') {
                        jQuery("input[name=package_type][value='" + package_type + "']").prop("checked", true).closest('label').addClass('active');
                        this.formData.package_type = package_type;
                    }
                    if (insurance != '') {
                        jQuery("input[name=insurance][value='" + insurance + "']").prop("checked", true).closest('label').addClass('active');
                        this.formData.insurance = insurance;
                    }
                    jQuery('#insurance_value input').val(insurance_value);
                }, 1000);
            },

            submitted: false,
            showError: false,
            errorMessage: [],
            formData: {
                col_country_from: '',
                col_country_to: '',
                col_postcode_or_city: '',
                del_postcode_or_city: '',
                package_type: '',
                insurance: '',
                insurance_value: '',
                packages: [{
                    weight: '',
                    length: '',
                    width: '',
                    height: '',
                }],
            },
            formvalidation: {
                valid: true,
                col_country_from: {
                    validated: true,
                    message: ''
                },
                col_country_to: {
                    validated: true,
                    message: ''
                },
                col_postcode_or_city: {
                    validated: true,
                    message: ''
                },
                del_postcode_or_city: {
                    validated: true,
                    message: ''
                },
                package_type: {
                    validated: true,
                    message: ''
                },
                insurance: {
                    validated: true,
                    message: ''
                },
                insurance_value: {
                    validated: true,
                    message: ''
                },
                packages: [{
                    weight: {
                        validated: true,
                        message: ''
                    },
                    length: {
                        validated: true,
                        message: ''
                    },
                    width: {
                        validated: true,
                        message: ''
                    },
                    height: {
                        validated: true,
                        message: ''
                    },
                }],
            },

            validationIds: [],

            addPackages(object = null) {
                let obj = {};
                if (object) {
                    obj = object;
                } else {
                    obj = {
                        weight: '',
                        length: '',
                        width: '',
                        height: '',
                    }
                }

                this.formData.packages.push(obj);

                const objV = {
                    weight: {
                        validated: true,
                        message: ''
                    },
                    length: {
                        validated: true,
                        message: ''
                    },
                    width: {
                        validated: true,
                        message: ''
                    },
                    height: {
                        validated: true,
                        message: ''
                    },
                }
                this.formvalidation.packages.push(objV);
            },

            removePackage(index = '') {

                if (index == '') return;

                // remove form data
                this.formData.packages.splice(index, 1);

                // remove validation
                this.formvalidation.packages.splice(index, 1);
            },

            copyPackage() {
                if (this.formData.packages.length > 0) {
                    const obj = JSON.parse(JSON.stringify(this.formData.packages[this.formData.packages.length - 1]));
                    this.addPackages(obj);
                }
            },

            resetFormError() {
                this.showError = false;
                this.errorMessage = [];
                this.validationIds = [];

                Object.entries(this.formvalidation).forEach(([key, item]) => {
                    if (key == 'valid') {
                        this.formvalidation[key] = true;
                    } else if (key == 'packages') {
                        if (this.formvalidation.packages.length > 0) {
                            for (const iterator of this.formvalidation.packages) {
                                iterator.weight.validated = true;
                                iterator.length.validated = true;
                                iterator.width.validated = true;
                                iterator.height.validated = true;
                            }
                        }
                    } else {
                        item.validated = true;
                        item.message = '';
                    }
                });
            },

            onSubmit() {
                this.resetFormError();
                const offsetMinus = 200;

                // get all the values 
                const col_country_from = jQuery('#col_country_from').val();
                const col_postcode_or_city = jQuery('#col_postcode_or_city').val();
                const col_country_to = jQuery('#col_country_to').val();
                const del_postcode_or_city = jQuery('#del_postcode_or_city').val();
                const package_type = this.formData.package_type = jQuery("input[name='package_type']:checked").val();
                const insurance = this.formData.insurance;
                const insurance_value = jQuery('#insurance_value input').val();

                if (col_country_from == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.col_country_from.validated = false;
                    this.validationIds.push('col_country_from');
                }
                if (col_country_to == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.col_country_to.validated = false;
                    this.validationIds.push('col_country_to');
                }
                if (col_postcode_or_city == null) {
                    this.formvalidation.valid = false;
                    this.formvalidation.col_postcode_or_city.validated = false;
                    this.validationIds.push('col_postcode_or_city');
                }
                if (del_postcode_or_city == null) {
                    this.formvalidation.valid = false;
                    this.formvalidation.del_postcode_or_city.validated = false;
                    this.validationIds.push('del_postcode_or_city');
                }
                if (package_type == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.package_type.validated = false;
                    this.validationIds.push('package_type');
                }

                // do packages validation 
                if (this.formData.packages.length > 0) {
                    for (const key in this.formData.packages) {
                        const package = this.formData.packages[key];
                        const vPackage = this.formvalidation.packages[key];

                        if (package.weight == '') {
                            this.formvalidation.valid = false;
                            vPackage.weight.validated = false;
                            this.validationIds.push('package_weight_' + key);
                        }
                        if (package.length == '') {
                            this.formvalidation.valid = false;
                            vPackage.length.validated = false;
                            this.validationIds.push('package_length_' + key);
                        }
                        if (package.width == '') {
                            this.formvalidation.valid = false;
                            vPackage.width.validated = false;
                            this.validationIds.push('package_width_' + key);
                        }
                        if (package.height == '') {
                            this.formvalidation.valid = false;
                            vPackage.height.validated = false;
                            this.validationIds.push('package_height_' + key);
                        }
                    }
                }

                if (insurance == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.insurance.validated = false;
                    this.validationIds.push('insurance');
                }
                if (package_type == 'Package' && insurance == 1 && insurance_value == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.insurance_value.validated = false;
                    this.validationIds.push('insurance_value');
                }

                if (this.formvalidation.valid) {
                    // submit the form
                    jQuery('#frm_get_quote').submit();
                    jQuery(".lc-loading-screen").fadeIn();
                } else {
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#" + this.validationIds[0]).offset().top - offsetMinus
                    }, 300);
                }

            }
        }
    }
</script>