<?php
// prevent cache so when browser back button is pressed, previous data can be displayed
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0 "); // Proxies.

$current_language = get_locale();
$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

$eu_countries = [
    'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'FI', 'DE', 'GR',
    'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PF', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'FR'
];
$is_eu_shipment = false;

global $wpdb, $table_prefix;
$old_values = [];
if (isset($_GET['shipment_id'])) {
    $shipment_id = $_GET['shipment_id'];
    $countries = lc_get_all_countries();

    $s_result = $wpdb->get_results('SELECT * FROM ' . $table_prefix . 'lc_shipments WHERE lc_shipment_ID = "' . $shipment_id . '"');
    $s_data = isset($s_result[0]) ? $s_result[0] : '';

    $package_type = $s_data->package_type;
    // $package_type = "Document"; // Document or Package

    $errors = [];
    if (isset($_GET['request_status']) && $_GET['request_status'] == 'error' && isset($_GET['request_id']) && !empty($_GET['request_id'])) {
        if (get_transient($_GET['request_id'])) {
            $errors = get_transient($_GET['request_id']);
            $old_values = (object) $errors['old_values'];
        }
    }

    if (isset($s_data->sender_country_code) && isset($s_data->receiver_country_code) && in_array($s_data->sender_country_code, $eu_countries) && in_array($s_data->receiver_country_code, $eu_countries)) {
        $is_eu_shipment = true;
    }

?>

    <script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>


    <div x-data="component()" x-init="init()" class="lc-form-container courier-steps-page schedule-pickup-page mt-3 mb-5">
        <!-- include steps bar -->
        <?php include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-courier-steps-bar.php'; ?>

        <h1 class="heading-courier-steps mb-4"><?= $package_type == 'Document' ? __("Type of Document", "labal-courrier") : __("Type of Package", "labal-courrier") ?></h1>

        <form id="frm_customs_declaration" class="wpcsr-quote-book mb-0" action="<?php echo esc_url(site_url('wp-admin/admin-post.php')); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="submit_customs_declaration">
            <input type="hidden" name="current_language" value="<?= $current_language ?>">
            <input type="hidden" name="shipment_id" value="<?= $shipment_id ?>">
            <input type="hidden" name="package_type" value="<?= $package_type ?>">
            <input type="hidden" name="is_eu_shipment" value="<?= $is_eu_shipment ?>">

            <?php
            if (count($errors) > 0) {
            ?>
                <div class="row">
                    <div class="col-12">
                        <?php
                        foreach ($errors as $key => $value) {
                            if (!is_array($value)) {
                                echo '<div class="w-100 mb-3 pt-1 pb-1 common_error validation_message" style="color:red;">';
                                echo $value;
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php
            if ($is_eu_shipment) {
            ?>
                <div class="lc-declaration-type-area shadow-sm rounded mb-5 bg-white p-4">
                    <div class="lc-form-control" id="export_reason_type">
                        <label for="id_export_reason_type"><?= esc_html_e("Summarize the contents of your shipment", "labal-courrier") ?></label>
                        <input x-model="formData.export_reason_type" type="text" name="export_reason_type" placeholder="<?= esc_html_e("2 cell phone, 3 women's shorts, 1 boy's jacket_", "labal-courrier") ?>" class="form-control" :class="{ 'is-invalid': !formvalidation.export_reason_type.validated }">
                        <div x-show="!formvalidation.export_reason_type.validated" class="w-100 validation_message"><?= esc_html_e("Please provide a value of maximum 35 characters", "labal-courrier") ?></div>

                    </div>
                </div>

            <?php } else { ?>
                <div class="lc-declaration-type-area shadow-sm rounded mb-5 bg-white">

                    <?php if ($package_type == 'Package') { ?>
                        <div class="div-own-invoice-area p-4">
                            <div class="div-own-invoice">
                                <p class="mb-3"><?= esc_html_e("For all international shipments, it will be necessary to provide a declaration of contents for customs purposes. Without this document printed and attached to the shipping documents, your package will be returned and potentially charged.", "labal-courrier") ?></p>
                                <p><?= esc_html_e("If you choose to provide your own customs invoice, you take the responsibility for providing a compliant document for the clearance of your goods by the customs services.", "labal-courrier") ?></p>
                                <!-- <div class="lc-form-control">
                                <label for="id_quick_weight"><?= esc_html_e("Do you have your own invoice", "labal-courrier") ?><i class="fa-regular fa-circle-question" data-bs-custom-class="lc-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?= esc_attr_e("Invoice", "labal-courrier") ?>"></i></label>
                                <div class="lc-form-control-cr lc-form-control-radio lc-grid gap-4 mt-2 pb-2" id="have_own_invoice">
                                    <label for="own_invoice_yes" class="lcl-radio is-insurance ">
                                        <input x-model="formData.have_own_invoice" type="radio" name="have_own_invoice" class="is-insurance-field me-1" value="1" id="own_invoice_yes">
                                        <?= esc_html_e("Yes", "labal-courrier") ?> </label>
                                    <label for="own_invoice_no" class="lcl-radio is-insurance label-ins-disable">
                                        <input x-model="formData.have_own_invoice" type="radio" name="have_own_invoice" class="is-insurance-field me-1" value="0" id="own_invoice_no">
                                        <?= esc_html_e("No", "labal-courrier") ?> </label>
                                </div>
                                <div x-show="!formvalidation.have_own_invoice.validated" class="w-100 text-danger validation_message">Please choose your preference</div>
                            </div>
                            <div x-show="formData.have_own_invoice == 1" class="lc-form-control text-end" id="upload_invoice">
                                <label for="id_quick_weight" style="opacity: 0;"><?= esc_html_e("Upload Document", "labal-courrier") ?></label>
                                <a style="display: none;" id="trigger_upload_invoice" href="#" class="lc-link text-decoration-underline"><i class="fa-solid fa-plus me-1"></i><?= __("Upload Document", "labal-courrier") ?></a>
                                <input type="file" accept="application/pdf" name="input_upload_invoice" id="input_upload_invoice" />
                                <input x-model="formData.own_invoice_file" type="hidden" name="own_invoice_file" class="" />
                                <div x-show="!formvalidation.own_invoice_file.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                            </div> -->
                            </div>
                        </div>
                    <?php } ?>

                    <div class="lc-declaration-type-grid lc-grid grid-cols-1 md-grid-cols-2 gap-2">
                        <div class="lc-declaration-type p-4">
                            <?php if ($package_type == 'Document') { ?>
                                <div class="lc-form-control select-type-document">
                                    <label for=""><?= esc_html_e("Type of Document", "labal-courrier") ?></label>
                                    <select name="shipment_description" class="form-control lc-select" id="">
                                        <option><?= __("Advertising brochures, pamphlets", "labal-courrier") ?></option>
                                        <option><?= __("Airline tickets - issued/validated", "labal-courrier") ?></option>
                                        <option><?= __("Annual reports", "labal-courrier") ?></option>
                                        <option><?= __("Bill of lading", "labal-courrier") ?></option>
                                        <option><?= __("Blueprints", "labal-courrier") ?></option>
                                        <option><?= __("Booklets, brochures - non-advertising", "labal-courrier") ?></option>
                                        <option><?= __("Business cards", "labal-courrier") ?></option>
                                        <option><?= __("Catalogs", "labal-courrier") ?></option>
                                        <option><?= __("Certificates", "labal-courrier") ?></option>
                                        <option><?= __("Charts, graphs", "labal-courrier") ?></option>
                                        <option><?= __("Checks - cashier", "labal-courrier") ?></option>
                                        <option><?= __("COMPLETED FORMS", "labal-courrier") ?></option>
                                        <option><?= __("Contract", "labal-courrier") ?></option>
                                        <option><?= __("Credit note", "labal-courrier") ?></option>
                                        <option><?= __("Deeds", "labal-courrier") ?></option>
                                        <option><?= __("Diplomatic mail", "labal-courrier") ?></option>
                                        <option><?= __("Diplomatic material", "labal-courrier") ?></option>
                                        <option><?= __("Documents - general business", "labal-courrier") ?></option>
                                        <option><?= __("Educational material - printed", "labal-courrier") ?></option>
                                        <option><?= __("Examination papers", "labal-courrier") ?></option>
                                        <option><?= __("Identity document", "labal-courrier") ?></option>
                                        <option><?= __("Invoices - not blank", "labal-courrier") ?></option>
                                        <option><?= __("Letter, correspondence", "labal-courrier") ?></option>
                                        <option><?= __("Manual - technical", "labal-courrier") ?></option>
                                        <option><?= __("Manuscripts", "labal-courrier") ?></option>
                                        <option><?= __("MEDICAL EXAMINATION RESULT", "labal-courrier") ?></option>
                                        <option><?= __("Music - printed, manuscript", "labal-courrier") ?></option>
                                        <option><?= __("Ngatives - including x-rays, films", "labal-courrier") ?></option>
                                        <option><?= __("Passports", "labal-courrier") ?></option>
                                        <option><?= __("Photographs", "labal-courrier") ?></option>
                                        <option><?= __("Photographs - as part of business reports", "labal-courrier") ?></option>
                                        <option><?= __("Price lists", "labal-courrier") ?></option>
                                        <option><?= __("Printed matter", "labal-courrier") ?></option>
                                        <option><?= __("Ship manifest - computer genrated", "labal-courrier") ?></option>
                                        <option><?= __("Shipping schedules", "labal-courrier") ?></option>
                                        <option><?= __("Slides", "labal-courrier") ?></option>
                                        <option><?= __("Visa applications", "labal-courrier") ?></option>
                                    </select>
                                </div>
                            <?php } else { ?>
                                <div class="lc-form-control select-type-document">
                                    <label for=""><?= esc_html_e("Type of Package", "labal-courrier") ?></label>
                                    <select x-model="formData.export_reason_type" name="export_reason_type" class="form-control lc-select" id="export_reason_type">
                                        <option value="GIFT" label="Gift"><?= __("Gift", "labal-courrier") ?></option>
                                        <option value="COMMERCIAL_PURPOSE_OR_SALE" label="Commercial"><?= __("Commercial", "labal-courrier") ?></option>
                                        <option value="PERSONAL_BELONGINGS_OR_PERSONAL_USE" label="Personal, Not for Resale"><?= __("Personal, Not for Resale", "labal-courrier") ?></option>
                                        <option value="SAMPLE" label="Sample"><?= __("Sample", "labal-courrier") ?></option>
                                        <option value="RETURN" label="Return for Repair"><?= __("Return", "labal-courrier") ?></option>
                                        <option value="RETURN_TO_ORIGIN" label="Return after Repair"><?= __("Return to origin", "labal-courrier") ?></option>
                                        <option value="WARRANTY_REPLACEMENT" label="Warranty Replacement"><?= __("Warranty Replacement", "labal-courrier") ?></option>
                                        <option value="USED_EXHIBITION_GOODS_TO_ORIGIN" label="Warranty Replacement"><?= __("Used Exhibition Goods to Origin", "labal-courrier") ?></option>
                                        <option value="INTERCOMPANY_USE" label="Intercompany use only"><?= __("Intercompany use only", "labal-courrier") ?></option>
                                    </select>
                                </div>
                            <?php } ?>
                            <div class="lc-form-control mt-3 declaration-remarks">
                                <label for=""><?= esc_html_e("Remarks", "labal-courrier") ?></label>
                                <textarea x-model="formData.special_pickup_instructions" class="form-control" rows="3" maxlength="40" name="remarks" id="remarks"></textarea>
                            </div>
                        </div>
                        <?php if ($package_type == 'Package') { ?>
                            <div class="lc-declaration-type-right px-4 px-md-0 py-0 py-md-4">
                                <p x-show="formData.export_reason_type == 'PERSONAL_BELONGINGS_OR_PERSONAL_USE'"><?= __("You are sending used personal effects that are more than six months old. You may be asked by customs in the country of arrival to fill out an affidavit. We recommend that you download this template and fill it out and sign it. It is important to print it and attach it to the rest of your shipping documents that you will give to the carrier", "labal-courrier") ?></p>
                                <p x-show="formData.export_reason_type == 'GIFT'"><?= __("You send a gift. Please note that this does not exempt you from paying any customs duties in the country of arrival. For shipments between individuals to France, customs duties are systematically applied from 45 euros of declared value. ", "labal-courrier") ?></p>

                                <a x-show="formData.export_reason_type == 'PERSONAL_BELONGINGS_OR_PERSONAL_USE'" class="btn btn-lc-download mb-4 mb-md-0 mt-3" id="" href="<?php echo LABAL_COURRIER_PLUGIN_URL . 'docs/declaration_sur_l_honneur.pdf' ?>" download=""><i class="fa-solid fa-download"></i> <?= __("Download", "labal-courrier") ?></a>
                            </div>
                        <?php } ?>
                    </div>

                </div>

                <?php if ($package_type == 'Package') { ?>
                    <div class="lc-declaration-items-area shadow-sm rounded mb-4 bg-white">
                        <template x-for="[id, item] in Object.entries(formData.items)">
                            <div class="single-declaration-item package_item">
                                <div class="single-declaration-item-top p-4">
                                    <div class="lc-form-control" id="insurance_value">
                                        <label for="id_insurance_value"><?= esc_html_e("Description of Item", "labal-courrier") ?> <span x-text="parseInt(id) + 1"></span></label>
                                        <template x-if="id == 0">
                                            <p class="fs-6 mb-2"><?= __("Description of each item in your shipment", "labal-courrier") ?> <a href="" class="lc-link text-decoration-underline"><?= __("Learn More", "labal-courrier") ?></a></p>
                                        </template>
                                        <input x-model="item.item_description" type="text" data-id="item_description" :name="'item['+id+'][item_description]'" :id="'package_description_'+id" class="form-control" :class="{ 'is-invalid': !formvalidation.items[id]?.item_description.validated }">
                                        <div x-show="!formvalidation.items[id]?.item_description.validated" class="w-100 validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>

                                    </div>
                                </div>
                                <div class="single-declaration-item-bottom p-4">
                                    <div class="lc-grid-declaration-item lc-grid grid-cols-1 md-grid-cols-2 gap-2 gap-md-3">
                                        <div class="lc-form-control">
                                            <label for=""><?= esc_html_e("Quantity", "labal-courrier") ?></label>
                                            <input x-model="item.quantity" type="text" data-id="quantity" :name="'item['+id+'][quantity]'" :id="'package_quantity_'+id" class="form-control validate_number_int" :class="{ 'is-invalid': !formvalidation.items[id]?.quantity.validated }">
                                            <div x-show="!formvalidation.items[id]?.quantity.validated" class="w-100 text-white validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                        </div>
                                        <div class="lc-form-control">
                                            <label for=""><?= esc_html_e("Value per unit in euros", "labal-courrier") ?></label>
                                            <input x-model="item.item_value" type="text" data-id="item_value" :name="'item['+id+'][item_value]'" :id="'package_item_value_'+id" class="form-control validate_number" :class="{ 'is-invalid': !formvalidation.items[id]?.item_value.validated }">
                                            <div x-show="!formvalidation.items[id]?.item_value.validated" class="w-100 text-white validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                        </div>
                                        <div class="lc-form-control">
                                            <label for=""><?= esc_html_e("Custom Code (optional)", "labal-courrier") ?></label>
                                            <input x-model="item.commodity_code" type="text" data-id="commodity_code" :name="'item['+id+'][commodity_code]'" :id="'package_commodity_code_'+id" class="form-control">
                                        </div>
                                        <div class="lc-form-control">
                                            <label for=""><?= esc_html_e("Country of Origin", "labal-courrier") ?></label>
                                            <select x-model="item.item_origin" required class="form-control lc-select-country ccd-select-country" :name="'item['+id+'][item_origin]'" :id="'package_item_origin_'+id" aria-label="Example select with button addon">
                                                <option value="" selected><?= esc_html_e("Choix du pays", "labal-courrier") ?></option>
                                                <?php foreach ($countries as $code => $name) : ?>
                                                    <option value="<?= $code ?>"><?= $name ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div x-show="!formvalidation.items[id]?.item_origin.validated" class="w-100 text-white validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                        </div>
                                    </div>

                                    <div class="lc-form-control add-duplicate-package text-end">
                                        <label for="" style="opacity: 0;"><?= esc_html_e("Options", "labal-courrier") ?></label>
                                        <div class="add-duplicate-package-options">
                                            <span><i class="fa-solid fa-plus"></i><a x-on:click.prevent="addPackages()" href="#" class="ms-1"><?= esc_html_e("Add Another Item", "labal-courrier") ?></a></span>
                                            <span><i class="fa-regular fa-trash-can"></i><a x-on:click.prevent="removePackage(id)" href="#" class="ms-1"><?= esc_html_e("Delete Item", "labal-courrier") ?></a></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <input type="hidden" id="totalInvoiceVal" name="total_customs_value">

                    <div class="lc-form-control">
                        <div>
                            <span><?= esc_html_e("Total number of items", "labal-courrier") ?>:</span>
                            <span id="lc_total_items">0</span>
                        </div>
                        <div>
                            <span><?= esc_html_e("Total value", "labal-courrier") ?>:</span>
                            <span id="lc_total_value">0</span>
                        </div>
                    </div>
                <?php } ?>

            <?php } ?>
            <div class="lc-steps-btn-area has-border mt-4 text-center text-md-end">
                <a href="<?= esc_url(site_url()) . '/schedule-pickup/?shipment_id=' . $shipment_id ?>" class="btn lc-button lc-btn-back rounded"><?= __("Back", "labal-courrier") ?></a>
                <button x-on:click.prevent="onSubmit()" type="submit" class="btn lc-button lc-btn-blue rounded ms-2"><?= __("Confirm & Continue", "labal-courrier") ?></button>
            </div>
        </form>
    </div>

<?php
}
?>


<script>
    function initSelect2() {
        jQuery('.lc-declaration-items-area .single-declaration-item:last').find('.ccd-select-country').select2();
    }

    function updateTotal() {

        let value_fields = jQuery('input[data-id="item_value"]');

        let total_value = 0;
        let total_net = 0;
        let total_gross = 0;
        let total_unit = 0;
        value_fields.each(function(index) {
            let value = jQuery(this).val();
            if (value == '') {
                value = '0';
            }
            value = parseFloat(value.replace(',', '.')); //replace comma with period(.)

            let qty = jQuery(this).closest('.package_item').find('input[data-id="quantity"]').val();
            let net = jQuery(this).closest('.package_item').find('input[data-id="net_weight"]').val();
            let gross = jQuery(this).closest('.package_item').find('input[data-id="gross_weight"]').val();


            let amount = qty * value;

            total_net += qty * net;
            total_gross += qty * gross;
            total_value += amount;
            total_unit += parseInt(qty);
        })

        jQuery('#totalNetWeight').text(total_net.toFixed(2));
        jQuery('#totalGrossWeight').text(total_gross.toFixed(2));

        jQuery('#totalInvoiceVal').val(total_value.toFixed(2));
        jQuery('#totalInvoiceValDisplay').text(total_value.toFixed(2));

        jQuery('#totalUnit').text(total_unit);
    }

    jQuery(document).ready(function() {
        jQuery('body').on('input', '.validate_number', function(event) {
            let valueTyped = event.target.value;
            if (valueTyped.length == 1 && valueTyped == 0) valueTyped = '';
            jQuery(this).val(valueTyped.replace(',', '.').replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1'))[0].dispatchEvent(new Event('input'));
        });
        jQuery('body').on('input', '.validate_number_int', function(event) {
            let valueTyped = event.target.value;
            if (valueTyped.length == 1 && valueTyped == 0) valueTyped = '';
            jQuery(this).val(valueTyped.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1'))[0].dispatchEvent(new Event('input'));
        });

        jQuery('#trigger_upload_invoice').click(function(e) {
            e.preventDefault();
            jQuery('#input_upload_invoice').trigger("click");
        });
    });

    function component() {
        return {
            init() {
                this.$watch('formData.items', (value, oldValue) => {
                    this.onChangeItems();
                });

                setTimeout(() => {
                    <?php
                    if (isset($errors) && count($errors) > 0) {
                    ?>
                        let insurance = '<?= isset($old_values->insurance) ? $old_values->insurance : "" ?>';
                        let insurance_value = '<?= isset($old_values->insurance_value) ? $old_values->insurance_value : "" ?>';
                        let remarks = '<?= isset($old_values->remarks) ? $old_values->remarks : "" ?>';
                        let export_reason_type = '<?= isset($old_values->export_reason_type) ? $old_values->export_reason_type : "" ?>';
                        let items = '<?= isset($old_values->item) ? json_encode($old_values->item) : "" ?>';

                    <?php
                    } else {
                    ?>
                        let insurance = '<?= isset($s_data->insurance) ? $s_data->insurance : "" ?>';
                        let insurance_value = '<?= isset($s_data->insurance_value) ? $s_data->insurance_value : "" ?>';
                        let remarks = '<?= isset($s_data->remarks) ? $s_data->remarks : "" ?>';
                        let export_reason_type = '<?= isset($s_data->export_reason_type) ? $s_data->export_reason_type : "" ?>';
                        let items = '<?= isset($s_data->items) ? json_encode(unserialize($s_data->items)) : "" ?>';
                    <?php
                    }
                    ?>

                    if (items != '' && JSON.parse(items).length > 0) {
                        this.formData.items = JSON.parse(items);

                        let p_validation = [];
                        for (let index = 0; index < items.length; index++) {
                            const element = items[index];
                            const objV = {
                                item_description: {
                                    validated: true
                                },
                                quantity: {
                                    validated: true
                                },
                                item_value: {
                                    validated: true
                                },
                                net_weight: {
                                    validated: true
                                },
                                gross_weight: {
                                    validated: true
                                },
                                item_origin: {
                                    validated: true
                                },
                            }
                            p_validation.push(objV);

                            // trigger the value changes 
                            jQuery('#package_net_weight_' + index).trigger('change');
                            jQuery('#package_item_origin_' + index).trigger('change');
                        }

                        this.formvalidation.items = p_validation;
                    }

                    if (insurance != '') {
                        jQuery("input[name=insurance][value='" + insurance + "']").prop("checked", true).closest('label').addClass('active');
                        this.formData.insurance = insurance;

                        if (insurance == 0) {
                            jQuery('.col-insurance-val').hide();
                        }
                    }
                    jQuery('#insurance_value').val(insurance_value);
                    this.formData.insurance_value = insurance_value;

                    jQuery('#remarks').val(remarks);
                    if (export_reason_type != '') {
                        jQuery('#export_reason_type').val(export_reason_type).trigger('change');

                        this.formData.export_reason_type = export_reason_type;
                    }

                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });

                    updateTotal();

                }, 600);
            },

            package_type: '<?= $package_type ?>',

            submitted: false,
            showError: false,
            errorMessage: [],
            formData: {
                export_reason_type: '<?= $is_eu_shipment ? '' : 'GIFT' ?>',
                remarks: '',
                insurance: '',
                insurance_value: '',
                items: [{
                    item_description: '',
                    commodity_code: '',
                    quantity: '',
                    item_value: '',
                    net_weight: 0.1,
                    gross_weight: 0.1,
                    item_origin: '',
                }]
            },
            formvalidation: {
                valid: true,
                totalInvoiceVal: true,
                export_reason_type: {
                    validated: true
                },
                remarks: {
                    validated: true
                },
                insurance: {
                    validated: true
                },
                insurance_value: {
                    validated: true
                },
                items: [{
                    item_description: {
                        validated: true
                    },
                    quantity: {
                        validated: true
                    },
                    item_value: {
                        validated: true
                    },
                    net_weight: {
                        validated: true
                    },
                    gross_weight: {
                        validated: true
                    },
                    item_origin: {
                        validated: true
                    },
                }],
            },

            validationIds: [],

            onChangeItems() {
                let totalItems = 0;
                let totalValue = 0;

                for (let i = 0; i < this.formData.items.length; i++) {
                    const element = this.formData.items[i];

                    if (element.quantity) {
                        totalItems = totalItems + parseInt(element.quantity);
                    }
                    if (element.quantity && element.item_value) {
                        totalValue = totalValue + parseInt(element.quantity) * parseInt(element.item_value);
                    }
                }
                jQuery("#lc_total_items").text(totalItems);
                if (totalValue > 0) {
                    jQuery("#lc_total_value").text(totalValue + "€");
                } else {
                    jQuery("#lc_total_value").text(totalValue);
                }
            },

            onChangePoids(event) {
                const val = jQuery(event.target).val();
                jQuery(event.target).closest('.col').next('.col').find('input').val(val);
            },

            addPackages() {

                let obj = {
                    item_description: '',
                    commodity_code: '',
                    quantity: '',
                    item_value: '',
                    net_weight: 0.1,
                    gross_weight: 0.1,
                    item_origin: '',
                }

                this.formData.items.push(obj);

                const objV = {
                    item_description: {
                        validated: true
                    },
                    quantity: {
                        validated: true
                    },
                    item_value: {
                        validated: true
                    },
                    net_weight: {
                        validated: true
                    },
                    gross_weight: {
                        validated: true
                    },
                    item_origin: {
                        validated: true
                    },
                }
                this.formvalidation.items.push(objV);

                setTimeout(() => {
                    initSelect2();

                    // enable the tooltips 
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })

                }, 200);

            },

            removePackage(index = '') {

                if (index == '') return;

                // remove form data
                this.formData.items.splice(index, 1);

                // remove validation
                this.formvalidation.items.splice(index, 1);

                setTimeout(() => {
                    updateTotal();
                }, 200);

            },

            resetFormError() {
                this.showError = false;
                this.errorMessage = [];
                this.validationIds = [];

                Object.entries(this.formvalidation).forEach(([key, item]) => {
                    if (key == 'valid') {
                        this.formvalidation[key] = true;
                    } else if (key == 'items') {
                        if (this.formvalidation.items.length > 0) {
                            for (const iterator of this.formvalidation.items) {
                                iterator.item_description.validated = true;
                                iterator.quantity.validated = true;
                                iterator.item_value.validated = true;
                                iterator.net_weight.validated = true;
                                iterator.gross_weight.validated = true;
                                iterator.item_origin.validated = true;
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
                const totalInvoiceVal = jQuery('#totalInvoiceVal').val();

                <?php
                if ($is_eu_shipment) {
                ?>

                    if (this.formData.export_reason_type == '' || this.formData.export_reason_type.length > 35) {
                        this.formvalidation.valid = false;
                        this.formvalidation.export_reason_type.validated = false;
                        this.validationIds.push('export_reason_type');
                    }

                <?php } else { ?>
                    // do packages validation 
                    if (this.formData.items.length > 0 && this.package_type == 'Package') {
                        for (const key in this.formData.items) {
                            const package = this.formData.items[key];
                            const vPackage = this.formvalidation.items[key];

                            if (package.item_description == '') {
                                this.formvalidation.valid = false;
                                vPackage.item_description.validated = false;
                                this.validationIds.push('package_description_' + key);
                            }
                            if (package.quantity == '') {
                                this.formvalidation.valid = false;
                                vPackage.quantity.validated = false;
                                this.validationIds.push('package_quantity_' + key);
                            }
                            if (package.item_value == '') {
                                this.formvalidation.valid = false;
                                vPackage.item_value.validated = false;
                                this.validationIds.push('package_item_value_' + key);
                            }
                            // if (package.net_weight == '' || package.net_weight < .1) {
                            //     this.formvalidation.valid = false;
                            //     vPackage.net_weight.validated = false;
                            //     this.validationIds.push('id="package_net_weight_' + key);
                            // }
                            const origin = jQuery('#package_item_origin_' + key).val();
                            if (origin == '') {
                                this.formvalidation.valid = false;
                                vPackage.item_origin.validated = false;
                                this.validationIds.push('package_item_origin_' + key);
                            }
                        }
                    }
                <?php } ?>

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