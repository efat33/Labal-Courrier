<?php
// prevent cache so when browser back button is pressed, previous data can be displayed
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0 "); // Proxies.

// require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/helpers/countries.php';

$current_language = get_locale();
$countries = lc_get_all_countries();
$error = false;
$messages = [];
$errors = [];
$q_data = '';
$quote_id = '';

if (isset($_GET['quote_id']) && !empty($_GET['quote_id'])) {
    $quote_id = $_GET['quote_id'];
}

if (isset($_GET['request_id']) && !empty($_GET['request_id'])) {
    $error = true;
    $messages = get_transient($_GET['request_id']);
    $q_data = (object) $messages['old_values'];
    $errors = array_diff_key($messages, array_flip(["old_values"]));
} else if ($quote_id != '') {
    $data = get_transient($quote_id);

    if (isset($data['quote_request']) && !empty($data['quote_request'])) {
        $q_data = (object) $data['quote_request'];
    }
} else {
    $lc_quote_id = getQuoteID();

    if ($lc_quote_id != '') {
        $data = get_transient($lc_quote_id);

        if (isset($data['quote_request']) && !empty($data['quote_request'])) {
            $q_data = (object) $data['quote_request'];
        }
    }
}

// echo '<pre>';
// print_r($q_data);
?>

<script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>

<a href="#" class="btn btn-tips d-md-none" data-bs-toggle="modal" data-bs-target="#tipsModal">Tips</a>

<div x-data="component()" x-init="init()" class="lc-shipping-calculator lc-form-container">
    <div class="shipping-calculator-wrapper lc-grid py-4 mb-5 lg-gap-10 md-gap-5">
        <div class="lc-home-step-grid-content shadow-sm rounded grid-align-self-end">
            <div class="quote-form-area">
                <form id="frm_get_quote" class="lc-form wpcsr-quote-book mb-0" action="<?php echo esc_url(site_url('wp-admin/admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="get_quote">
                    <input type="hidden" name="current_language" value="<?= $current_language ?>">
                    <input type="hidden" name="shipping_calculator" value="1">
                    <input type="hidden" name="quote_type" value="full">
                    <input type="hidden" name="quote_id" value="<?= $quote_id ?>">
                    <?php wp_nonce_field('get_quote', 'lc_nonce'); ?>

                    <div style="<?php echo ($error && (count($errors) > 0 || isset($messages['common_error']) || isset($messages['carrier_error']))) ? "" : "display: none;" ?>" class="w-100 p-4 pb-0 common_error text-danger validation_message">
                        <?php
                        if ($error && isset($messages['common_error'])) {
                            echo $messages['common_error'];
                        }
                        if ($error && isset($messages['carrier_error'])) {
                            echo $messages['carrier_error'];
                        }
                        if (count($errors) > 0) {
                            foreach ($errors as $key => $value) {
                                echo $value;
                            }
                        }
                        ?>
                    </div>

                    <div x-show="formData.quote_type == 'full'" class="quote-form-full bg-white">
                        <div class="packages-wrapper p-4">
                            <div class="lc-form-control mb-3">
                                <label for=""><?= esc_html_e("Type of Shipment", "labal-courrier") ?></label>
                                <div class="lc-form-control-cr lc-form-control-radio lc-grid gap-4 mt-2 pb-2" id="package_type">
                                    <label for="package-type-Package" class="lcl-radio is-insurance ">
                                        <input x-model="formData.package_type" type="radio" name="package_type" class="is-insurance-field me-1" value="Package" id="package-type-Package">
                                        <?= esc_html_e("Package", "labal-courrier") ?> </label>
                                    <label for="package-type-Documents" class="lcl-radio is-insurance label-ins-disable">
                                        <input x-model="formData.package_type" type="radio" name="package_type" class="is-insurance-field me-1" value="Document" id="package-type-Documents">
                                        <?= esc_html_e("Document", "labal-courrier") ?> </label>
                                </div>
                                <div x-show="!formvalidation.package_type.validated" class="w-100 text-danger validation_message"><?= esc_html_e("Please choose your preference", "labal-courrier") ?></div>
                            </div>
                            <template x-for="[id, item] in Object.entries(formData.packages)">
                                <div class="single-package lc-grid grid-cols-1 md-grid-cols-3 gap-2 mb-4">
                                    <div class="lc-form-control">
                                        <div style="display: none;" class="input-group">
                                            <input type="text" :name="'package['+id+'][qty]'" :id="'package_qty_'+id" min="1" value="1" class="form-control">
                                        </div>
                                        <label for=""><?= esc_html_e("Dimensions", "labal-courrier") ?><i class="fa-regular fa-circle-question" data-bs-delay='{"show":0,"hide":500}' data-bs-custom-class="lc-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?= esc_attr_e("L'une des dimensions ne peut être supérieure à 180 cm. Si l'une des dimensions est supérieure à 100 cm, il y aura un petit supplément.", "labal-courrier") ?>"></i></label>
                                        <span class="measurement-instruction"><input x-model="item.length" @keyup="checkVolumeWeight(id)" type="text" :name="'package['+id+'][length]'" :id="'package_length_'+id" class="validate_number" :class="{ 'is-invalid': !formvalidation.packages[id]?.length.validated }" placeholder="<?= __("Length", "labal-courrier") ?>"><span><?= esc_html_e("cm", "labal-courrier") ?></span></span>
                                        <div x-show="!formvalidation.packages[id]?.length.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                    </div>
                                    <div class="lc-form-control">
                                        <label for="" style="opacity: 0;"><?= esc_html_e("Dimensions", "labal-courrier") ?></label>
                                        <span class="measurement-instruction"><input x-model="item.width" @keyup="checkVolumeWeight(id)" type="text" :name="'package['+id+'][width]'" :id="'package_width_'+id" class="validate_number" :class="{ 'is-invalid': !formvalidation.packages[id]?.width.validated }" placeholder="<?= __("Width", "labal-courrier") ?>"><span><?= esc_html_e("cm", "labal-courrier") ?></span></span>
                                        <div x-show="!formvalidation.packages[id]?.width.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                    </div>
                                    <div class="lc-form-control">
                                        <label for="" style="opacity: 0;"><?= esc_html_e("Dimensions", "labal-courrier") ?></label>
                                        <span class="measurement-instruction"><input x-model="item.height" @keyup="checkVolumeWeight(id)" type="text" :name="'package['+id+'][height]'" :id="'package_height_'+id" class="validate_number" :class="{ 'is-invalid': !formvalidation.packages[id]?.height.validated }" placeholder="<?= __("Height", "labal-courrier") ?>"><span><?= esc_html_e("cm", "labal-courrier") ?></span></span>

                                        <div x-show="!formvalidation.packages[id]?.height.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                    </div>
                                    <div class="lc-form-control">
                                        <label for=""><?= esc_html_e("Weight", "labal-courrier") ?></label>
                                        <span class="measurement-instruction"><input x-model="item.weight" @keyup="checkVolumeWeight(id)" type="text" :name="'package['+id+'][weight]'" :id="'package_weight_'+id" class="validate_number" :class="{ 'is-invalid': !formvalidation.packages[id]?.weight.validated }"><span><?= esc_html_e("kg", "labal-courrier") ?></span></span>
                                        <div x-show="!formvalidation.packages[id]?.weight.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                    </div>
                                    <div x-show="item.has_volume_weight" class="lc-form-control">
                                        <label for=""><?= esc_html_e("DIM Weight", "labal-courrier") ?><i class="fa-regular fa-circle-question" data-bs-delay='{"show":0,"hide":500}' data-bs-custom-class="lc-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?= esc_attr_e("Le poids volumétrique est supérieur au poids de votre colis. Le poids d'un colis est défini par la valeur la plus élevée entre le poids réel et le poids volumétrique. Le poids volumétrique se calcule comme suit (hauteur x longueur x largeur) /5000. Pour plus d'informations, consultez notre article sur le poids volumétrique.", "labal-courrier") ?>"></i></label>
                                        <span class="measurement-instruction"><input x-model="item.volume_weight" readonly type="text" class="form-control validate_number is-invalid"><span><?= esc_html_e("kg", "labal-courrier") ?></span></span>
                                    </div>
                                    <div class="lc-form-control add-duplicate-package text-end">
                                        <label for="" style="opacity: 0;"><?= esc_html_e("Options", "labal-courrier") ?></label>
                                        <div class="add-duplicate-package-options">
                                            <span><i class="fa-solid fa-plus"></i><a x-on:click.prevent="addPackages()" href="#" class="ms-1"><?= esc_html_e("Add Another Parcel", "labal-courrier") ?></a></span>
                                            <span><i class="fa-regular fa-copy"></i><a x-on:click.prevent="copyPackage()" href="#" class="ms-1"><?= esc_html_e("Duplicate Parcel", "labal-courrier") ?></a></span>
                                            <span><i class="fa-regular fa-trash-can"></i><a x-on:click.prevent="removePackage(id)" href="#" class="ms-1"><?= esc_html_e("Delete Parcel", "labal-courrier") ?></a></span>
                                        </div>
                                    </div>
                                    <!-- <button x-on:click.prevent="removePackage(id)" type="button" class="btn btn-remove-package"><span class="fa fa-trash"></span></button> -->
                                </div>
                            </template>
                        </div>
                        <div class="full-quote-bottom p-4">
                            <div class="full-quote-from-to lc-grid grid-cols-1 md-grid-cols-2 md-gap-5 lg-gap-10">
                                <div class="full-quote-from">
                                    <div class="lc-form-control bg-grey" :class="{ 'is-invalid-select': !formvalidation.col_country_from.validated }">
                                        <label for=""><?= esc_html_e("From", "labal-courrier") ?></label>
                                        <select x-model="formData.col_country_from" class="form-control lc-select-country col_country" name="col_country" id="col_country_from" aria-label="Example select with button addon">
                                            <option value="" selected><?= esc_html_e("Choix du pays", "labal-courrier") ?></option>
                                            <?php foreach ($countries as $code => $name) : ?>
                                                <?php if (isset($messages['old_values']['col_country']) && trim($messages['old_values']['col_country']) == $code) : ?>
                                                    <option selected value="<?= $code ?>"><?= $name ?></option>
                                                <?php else : ?>
                                                    <option value="<?= $code ?>"><?= $name ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <div x-show="!formvalidation.col_country_from.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                    </div>
                                    <div class="lc-form-control bg-grey mt-2">
                                        <div style="width: 100%; display:block" class="" :class="{ 'is-invalid-select': !formvalidation.col_postcode_or_city.validated }">
                                            <select x-model="formData.col_postcode_or_city" style="width: 100%;" class="form-control" name="col_postcode_or_city" id="col_postcode_or_city"></select>
                                        </div>
                                        <div x-show="!formvalidation.col_postcode_or_city.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                    </div>
                                </div>
                                <div class="full-quote-to mt-2 mt-md-0">
                                    <div class="lc-form-control bg-grey" :class="{ 'is-invalid-select': !formvalidation.col_country_to.validated }">
                                        <label for=""><?= esc_html_e("To", "labal-courrier") ?></label>
                                        <select x-model="formData.col_country_to" class="form-control lc-select-country del_country" name="del_country" id="col_country_to" aria-label="Example select with button addon">
                                            <option value="" selected><?= esc_html_e("Choix du pays", "labal-courrier") ?></option>

                                            <?php foreach ($countries as $code => $name) : ?>
                                                <?php if (isset($messages['old_values']['del_country']) && trim($messages['old_values']['del_country']) == $code) : ?>
                                                    <option selected value="<?= $code ?>"><?= $name ?></option>
                                                <?php else : ?>
                                                    <option value="<?= $code ?>"><?= $name ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <div x-show="!formvalidation.col_country_to.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                    </div>
                                    <div class="lc-form-control bg-grey mt-2">
                                        <div style="width: 100%; display:block" class="" :class="{ 'is-invalid-select': !formvalidation.del_postcode_or_city.validated }">
                                            <select x-model="formData.del_postcode_or_city" style="width: 100%;" class="form-control" name="del_postcode_or_city" id="del_postcode_or_city"></select>
                                        </div>
                                        <div x-show="!formvalidation.del_postcode_or_city.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="full-quote-insurance lc-grid grid-cols-1 md-grid-cols-2 md-gap-5 lg-gap-10 mt-3 ">
                                <div class="lc-form-control">
                                    <label for="id_quick_weight"><?= esc_html_e("Insurance", "labal-courrier") ?><i class="fa-regular fa-circle-question" data-bs-delay='{"show":0,"hide":500}' data-bs-custom-class="lc-tooltip" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-title="<?= __("Vous avez choisi de ne pas assurer votre envoi! En cas de perte ou de dommage, vous n'aurez droit qu'à une indemnité contractuelle de la part du transporteur (maximum 25 dollars/kg si l'envoi est livré par avion ou 12 dollars/kg s'il est transporté par route).  Vous avez choisi d'assurer votre envoi! Remboursement de 100% de la valeur déclarée. Couverture en cas de perte ou de dommage. Procédure de remboursement simplifiée", "labal-courrier") ?>"></i></label>
                                    <div class="lc-form-control-cr lc-form-control-radio lc-grid gap-4 mt-2 pb-2" id="insurance">
                                        <label for="ins-enable" class="lcl-radio is-insurance ">
                                            <input x-model="formData.insurance" type="radio" name="insurance" class="is-insurance-field me-1" value="1" id="ins-enable">
                                            <?= esc_html_e("Yes", "labal-courrier") ?> </label>
                                        <label for="ins-disable" class="lcl-radio is-insurance label-ins-disable">
                                            <input x-model="formData.insurance" type="radio" name="insurance" class="is-insurance-field me-1" value="0" id="ins-disable">
                                            <?= esc_html_e("No", "labal-courrier") ?> </label>
                                    </div>
                                    <div x-show="!formvalidation.insurance.validated" class="w-100 text-danger validation_message"><?= esc_html_e("Please choose your preference", "labal-courrier") ?></div>
                                </div>
                                <div x-show="formData.insurance == 1" class="lc-form-control" id="insurance_value">
                                    <label for="id_insurance_value"><?= esc_html_e("Item Value", "labal-courrier") ?></label>
                                    <input x-model="formData.insurance_value" type="text" name="insurance_value" placeholder="0.00" class="validate_number" :class="{ 'is-invalid': !formvalidation.insurance_value.validated }" />
                                    <div x-show="!formvalidation.insurance_value.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                </div>
                            </div>

                            <div class="full-quote-pickup-type lc-grid grid-cols-1 md-grid-cols-2 md-gap-5 align-items-end lg-gap-10 mt-3 ">
                                <div class="lc-form-control-group">
                                    <div class="lc-form-control">
                                        <label for="id_quick_weight"><?= esc_html_e("Service Type", "labal-courrier") ?></label>
                                        <div class="lc-form-control-cr lc-form-control-radio lc-grid gap-4 mt-2" id="pickup_type">
                                            <label for="pickup-enable" class="lcl-radio is-pickup ">
                                                <input x-model="formData.is_pickup_required" type="radio" name="is_pickup_required" class="is-insurance-field me-1" value="1" id="pickup-enable">
                                                <?= esc_html_e("Pick up", "labal-courrier") ?> </label>
                                            <label for="pickup-disable" class="lcl-radio is-insurance label-ins-disable">
                                                <input x-model="formData.is_pickup_required" type="radio" name="is_pickup_required" class="is-insurance-field me-1" value="0" id="pickup-disable">
                                                <?= esc_html_e("Drop-off", "labal-courrier") ?> </label>
                                        </div>
                                        <div x-show="!formvalidation.is_pickup_required.validated" class="w-100 text-danger mt-2 validation_message"><?= esc_html_e("Please choose your preference", "labal-courrier") ?></div>
                                    </div>
                                    <div x-show="formData.is_pickup_required == 1" class="lc-form-control mt-3" id="dispatch_date">
                                        <label for=""><?= esc_html_e("Pick Up Date", "labal-courrier") ?></label>
                                        <input type="hidden" id="id_gq_dispatch_date" class="gq-dispatch-date-field" name="dispatch_date" value="<?= isset($q_data->dispatch_date) ? $q_data->dispatch_date : ''; ?>" />
                                        <input :disabled="!showCalendar" type="text" id="id_calendar_trigger" class=" gq-dispatch-date" />
                                        <span x-show="!showCalendar" class="lc-text-note"><?= esc_html_e("Note: Need to choose sender country to enable date field", "labal-courrier") ?></span>
                                        <div x-show="!formvalidation.dispatch_date.validated" class="w-100 text-danger validation_message"><?= esc_html_e("This field cannot be empty", "labal-courrier") ?></div>
                                    </div>
                                </div>
                                <div class="lc-form-control mt-5 mt-md-0 md-grid-justify-self-end">
                                    <button x-on:click.prevent="onSubmit()" type="button" class="btn-quote btn-quote-full rounded py-2 w-100"><?= esc_html_e("Quote", "labal-courrier") ?></button>
                                </div>
                            </div>
                            <div style="overflow: hidden;" id="rqf_validation_msg" class="text-danger validation_message">Veuillez remplir tous les champs</div>
                        </div>
                    </div>
                </form>
            </div>
            <img class="quote-form-img quote-form-img-bottom" src="<?= LABAL_COURRIER_PLUGIN_URL . '/public/img/cloud.svg' ?>" alt="<?= esc_attr_e("Cloud", "labal-courrier") ?>">
        </div>
        <div class="lc-shipping-tips-area rounded d-none d-md-block">
            <img class="tips-sidebar-img" src="<?= LABAL_COURRIER_PLUGIN_URL . '/public/img/cloud.svg' ?>" alt="<?= esc_attr_e("Cloud", "labal-courrier") ?>">
            <?php
            include LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-shipping-tips.php';
            ?>
        </div>


    </div>
</div>

<div class="bike-grass-roads-area">
    <div class="bike-grass-roads">
        <img src="<?php echo LABAL_COURRIER_PLUGIN_URL . '/public/img/order-bike.svg' ?>" alt="">
        <img src="<?php echo LABAL_COURRIER_PLUGIN_URL . '/public/img/grass-road.png' ?>" alt="">
    </div>
</div>

<!-- Tips Modal -->
<div class="modal fade" id="tipsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <?php
                include LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-shipping-tips.php';
                ?>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Modal -->
<?php
include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-faq.php';
?>

<script>
    // const exampleEl = document.querySelector('.lc-form-control label i')
    // const tooltip = new bootstrap.Tooltip(exampleEl)
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })


    jQuery(function($) {

        jQuery('body').on('input', '.validate_number', function(event) {
            jQuery(this).val(event.target.value.replace(',', '.').replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1'))[0].dispatchEvent(new Event('input'));
        });
    });

    function component() {
        return {
            showCalendar: false,
            calendarStartDay: false,
            holidays: [],
            init() {

                jQuery(document).on('change', 'select[name="col_country"]', (e) => {
                    const col_country = jQuery('#col_country_from').val();
                    if (col_country) this.initiateOrUpdateCalendar();
                });

                let col_country = this.formData.col_country_from = '<?= isset($q_data->col_country) ? $q_data->col_country : "" ?>';
                let del_country = '<?= isset($q_data->del_country) ? $q_data->del_country : "" ?>';

                let col_postcode_or_city = '<?= isset($q_data->col_postcode) ? $q_data->col_postcode . "--" . $q_data->col_suburb . "--" . $q_data->col_city . "--" . $q_data->col_state : "" ?>';
                let del_postcode_or_city = '<?= isset($q_data->del_postcode) ? $q_data->del_postcode . "--" . $q_data->del_suburb . "--" . $q_data->del_city . "--" . $q_data->del_state : "" ?>';

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
                    jQuery('#col_postcode_or_city').append(newOption_col).trigger('change');

                    // set default receiver postcode
                    const data_del = {
                        id: del_postcode_or_city,
                        text: del_postcode_or_city_arr.filter(x => x != '').join(' , ')
                    };

                    const newOption_del = new Option(data_del.text, data_del.id, false, false);
                    jQuery('#del_postcode_or_city').append(newOption_del).trigger('change');

                    <?php
                    if (isset($q_data->dispatch_date)) {
                    ?>
                        // jQuery('.gq-dispatch-date').data('daterangepicker').setStartDate('<?= date("d-m-Y", strtotime($q_data->dispatch_date)); ?>');
                    <?php } ?>

                }, 600);

                setTimeout(() => {
                    let is_pickup_required = '<?= isset($q_data->is_pickup_required) ? $q_data->is_pickup_required : "" ?>';
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

                        // check the volume weight 
                        for (const key in this.formData.packages) {
                            this.checkVolumeWeight(key);
                        }
                    }
                    if (package_type != '') {
                        jQuery("input[name=package_type][value='" + package_type + "']").prop("checked", true).closest('label').addClass('active');
                        this.formData.package_type = package_type;
                    }
                    if (insurance != '') {
                        jQuery("input[name=insurance][value='" + insurance + "']").prop("checked", true).closest('label').addClass('active');
                        this.formData.insurance = insurance;
                    }
                    if (is_pickup_required != '') {
                        jQuery("input[name=is_pickup_required][value='" + is_pickup_required + "']").prop("checked", true);
                        this.formData.is_pickup_required = is_pickup_required;
                    }
                    jQuery('#insurance_value input').val(insurance_value);

                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })

                }, 900);

            },

            getNextWeekDay($format = "DD-MMMM-YYYY") {

                const initM = moment();
                let mToday = moment();
                let businessDay = mToday.format($format);

                while (
                    this.holidays.includes(moment(businessDay, "DD-MM-YYYY").format("YYYY-MM-DD")) || mToday.day() === 5 || mToday.day() === 6 ||
                    mToday.isSame(initM, 'day')
                ) {

                    if (mToday.day() === 5) { // friday, show monday
                        // set to monday
                        businessDay = mToday.weekday(8).format($format);
                    } else if (mToday.day() === 6) { // saturday, show monday
                        // set to monday
                        businessDay = mToday.weekday(8).format($format);
                    } else { // other days, show next day
                        businessDay = mToday.add('days', 1).format($format);
                    }

                    mToday = moment(businessDay, "DD-MM-YYYY");
                }

                this.calendarStartDay = businessDay;
                return businessDay;
            },

            getMaxDay($format = "DD/MM/YYYY") {
                let businessDay = new Date();
                businessDay = moment(this.calendarStartDay, 'DD-MM-YYYY').add('days', 7).format($format);

                // populate date field with the initial date
                <?php
                if (isset($q_data->dispatch_date)) {
                ?>
                    jQuery('.gq-dispatch-date-field').val('<?= $q_data->dispatch_date ?>');
                <?php } else { ?>
                    jQuery('.gq-dispatch-date-field').val(moment(this.calendarStartDay, 'DD-MM-YYYY').format('YYYY-MM-DD'));
                <?php } ?>

                return businessDay;
            },

            async getHolidays() {
                const holidayAPIKey = '280bd1b9-85ef-46cf-8fa7-2d76110b7901';
                const year = moment().year();
                let col_country_from = jQuery('#col_country_from').val();

                try {
                    const res = await fetch(
                        `https://holidayapi.com/v1/holidays?public=true&country=${col_country_from}&year=${year}&key=${holidayAPIKey}`
                    );
                    const resData = await res.json();
                    if (resData.status && resData.status == 200) {
                        const holidays = resData.holidays.map((item) => item.date);
                        return holidays;
                    }
                } catch (error) {
                    console.log(error);
                }
            },

            async initiateOrUpdateCalendar() {

                this.showCalendar = true;
                this.holidays = await this.getHolidays();
                // this.holidays = [
                //     '2022-03-17',
                //     '2022-04-14',
                //     '2022-05-01',
                //     '2022-11-14',
                //     '2022-11-15',
                // ];

                <?php if (isset($q_data->dispatch_date) && $q_data->dispatch_date != '') { ?>
                    jQuery('.gq-dispatch-date').daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        startDate: '<?= date("d-m-Y", strtotime($q_data->dispatch_date)); ?>',
                        locale: {
                            format: 'DD-MM-YYYY'
                        },
                        minDate: this.getNextWeekDay('DD-MM-YYYY'),
                        maxDate: this.getMaxDay('DD-MM-YYYY'),
                        isInvalidDate: (date) => {
                            if (this.holidays && this.holidays.includes(date.format("YYYY-MM-DD"))) return true;

                            if (date.day() == 0 || date.day() == 6)
                                return true;
                            return false;
                        }
                    }, (start, end, label) => {
                        jQuery('.gq-dispatch-date-field').val(moment(start).format('YYYY-MM-DD'));
                    });

                <?php } else { ?>
                    jQuery('.gq-dispatch-date').daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        startDate: this.getNextWeekDay('DD-MM-YYYY'),
                        locale: {
                            format: 'DD-MM-YYYY'
                        },
                        minDate: this.getNextWeekDay('DD-MM-YYYY'),
                        maxDate: this.getMaxDay('DD-MM-YYYY'),
                        isInvalidDate: (date) => {
                            if (this.holidays && this.holidays.includes(date.format("YYYY-MM-DD"))) return true;

                            if (date.day() == 0 || date.day() == 6)
                                return true;
                            return false;
                        }
                    }, (start, end, label) => {
                        jQuery('.gq-dispatch-date-field').val(moment(start).format('YYYY-MM-DD'));
                    });
                <?php } ?>
            },

            submitted: false,
            showError: false,
            errorMessage: [],
            formData: {
                quote_type: 'full',
                col_country_from: '',
                col_country_to: '',
                col_postcode_or_city: '',
                del_postcode_or_city: '',
                package_type: '',
                insurance: '',
                insurance_value: '',
                is_pickup_required: '',
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
                is_pickup_required: {
                    validated: true,
                    message: ''
                },
                dispatch_date: {
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

            checkVolumeWeight(id) {
                const package = this.formData.packages[id];

                if (package.length && package.width && package.height && package.weight) {
                    const VW = (parseInt(package.length) * parseInt(package.width) * parseInt(package.height)) / 5000;

                    if (VW > package.weight) {
                        this.formData.packages[id]['has_volume_weight'] = true;
                    } else {
                        this.formData.packages[id]['has_volume_weight'] = false;
                    }
                    this.formData.packages[id]['volume_weight'] = VW;
                }

            },

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

                setTimeout(() => {
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                }, 100);

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
                const col_country_from = this.formData.col_country_from = jQuery('#col_country_from').val();
                const col_postcode_or_city = this.formData.col_postcode_or_city = jQuery('#col_postcode_or_city').val();
                const col_country_to = this.formData.col_country_to = jQuery('#col_country_to').val();
                const del_postcode_or_city = this.formData.del_postcode_or_city = jQuery('#del_postcode_or_city').val();
                const dispatch_date = jQuery('.gq-dispatch-date-field').val();
                const package_type = this.formData.package_type;
                const is_pickup_required = this.formData.is_pickup_required;
                const insurance = this.formData.insurance;
                const insurance_value = this.formData.insurance_value

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
                if (col_postcode_or_city == null || col_postcode_or_city == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.col_postcode_or_city.validated = false;
                    this.validationIds.push('col_postcode_or_city');
                }
                if (del_postcode_or_city == null || del_postcode_or_city == '') {
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

                if (is_pickup_required == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.is_pickup_required.validated = false;
                    this.validationIds.push('pickup_type');
                }

                if (insurance == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.insurance.validated = false;
                    this.validationIds.push('insurance');
                }
                if (insurance == 1 && insurance_value == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.insurance_value.validated = false;
                    this.validationIds.push('insurance_value');
                }
                if (is_pickup_required == 1 && dispatch_date == '') {
                    this.formvalidation.valid = false;
                    this.formvalidation.dispatch_date.validated = false;
                    this.validationIds.push('dispatch_date');
                }

                if (this.formvalidation.valid) {
                    // submit the form
                    jQuery('#frm_get_quote').submit();
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