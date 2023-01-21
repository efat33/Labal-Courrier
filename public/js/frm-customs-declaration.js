function initializeSelect2() {
    jQuery('.items_fields .package_item:last').find('.ccd-select-country').select2();
}

jQuery(function ($) {
    if ($('#frm_checkout').length) {
        $('.lcc-select-country .col_country').select2();
    } 
    if ($('#frm_customs_declaration').length) {

        $('body').find('.ccd-select-country').select2();

        let item_index = 1;
        $(document).on('click', '#add_item', function (e) {

            e.preventDefault();

            let countries_html = '';
            for (var country_code in lc_obj.countries) {
                var country_name = lc_obj.countries[country_code];
                countries_html += "<option value='" + country_code + "'>" + country_name + "</option>";
            }

            let newSelect = `<select required class="form-control lc-select-country ccd-select-country" name="item[`+ item_index + `][item_origin]" id="" aria-label="Example select with button addon">
                                <option value="" selected>Pays</option>
                                `+ countries_html + `
                            </select>`;

            const tpl = `
            <div class="package_item">
                <div class="item_number">
                `+ (item_index + 1) + `
                </div>
                <div class="row">
                    <div class="col-7">
                        <label for="item_description">Description du contenu:*</label>
                        <input required type="text" data-id="item_description" name="item[`+ item_index + `][item_description]" class="form-control">
                    </div>
                    <div class="col">
                        <label for="commodity_code">Commodity Code:</label>
                        <input type="text" data-id="commodity_code" name="item[`+ item_index + `][commodity_code]" class="form-control">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        <label for="commodity_code">Quantité:*</label>
                        <input required type="text" data-id="quantity" name="item[`+ item_index + `][quantity]" class="form-control">
                    </div>
                    <div class="col">
                        <label for="units">Unité:*</label>
                        <select required name="item[`+ item_index + `][units]" data-id="units" class="form-control">
                            <option value="PCS">Pieces</option>
                        </select>
                    </div>
                    <div class="col">
                        <label for="item_value">Valeur/unité(€):*</label>
                        <input required type="text" data-id="item_value" name="item[`+ item_index + `][item_value]" class="form-control">
                    </div>
                    <div class="col">
                        <label for="net_weight">Poids Net:</label>
                        <input required type="text" data-id="net_weight" name="item[`+ item_index + `][net_weight]" class="form-control">
                    </div>
                    <div class="col">
                        <label for="gross_weight">Poids Brut:*</label>
                        <input required type="text" data-id="gross_weight" name="item[`+ item_index + `][gross_weight]" class="form-control">
                    </div>
                    <div class="col-3 col-ccd-select-country">
                        <label for="item_origin">Origine:*</label>
                        ` + newSelect + `
                    </div>
                </div>
                <a class="remove_item">
                    X
                </a>
            </div>`;
            item_index++;
            $('#lc_items').append(tpl);

            // initialise the newly added select
            initializeSelect2();
        })

        // $(document).on('click', '.remove_item', function (e) {
        //     e.preventDefault();
        //     $(this).closest('.package_item').remove();
        //     updateTotals();
        // })

        $(document).on('change', 'input[data-id="item_value"]', function (e) {
            e.preventDefault();
            updateTotals();
        })

        $(document).on('change', 'input[data-id="quantity"]', function (e) {
            e.preventDefault();
            updateTotals();
        })

        $(document).on('change', 'input[data-id="net_weight"]', function (e) {
            e.preventDefault();
            updateTotals();
        })

        $(document).on('change', 'input[data-id="gross_weight"]', function (e) {
            e.preventDefault();
            updateTotals();
        })

        function updateTotals() {
            
            let value_fields = $('input[data-id="item_value"]');

            let total_value = 0;
            let total_net = 0;
            let total_gross = 0;
            let total_unit = 0;
            value_fields.each(function (index) {
                let value = $(this).val();
                if (value == '') { value = '0'; }
                value = parseFloat(value.replace(',', '.')); //replace comma with period(.)

                let qty = $(this).closest('.package_item').find('input[data-id="quantity"]').val();
                let net = $(this).closest('.package_item').find('input[data-id="net_weight"]').val();
                let gross = $(this).closest('.package_item').find('input[data-id="gross_weight"]').val();


                let amount = qty * value;

                total_net += qty * net;
                total_gross += qty * gross;
                total_value += amount;
                total_unit += parseInt(qty);
            })

            $('#totalNetWeight').text(total_net.toFixed(2));
            $('#totalGrossWeight').text(total_gross.toFixed(2));

            $('#totalInvoiceVal').val(total_value.toFixed(2));
            $('#totalInvoiceValDisplay').text(total_value.toFixed(2));

            $('#totalUnit').text(total_unit);
        }


        $(document).on('click', '.is-insurance', function () {

            let package_type = $('input[name="package_type"]').val();
            $('.is-insurance').removeClass('active');
            $(this).addClass('active');

            is_insurance = $('input[name="insurance"]:checked').val();

            if (is_insurance === '1') {
                if (package_type == 'Package') {
                    $('#insurance_val_field').show();
                    $('#insurance_val_field input').removeAttr('disabled');
                }
            } else {
                $('#insurance_val_field').hide();
                $('#insurance_val_field input').attr('disabled', 'disabled');
            }
        });
    }
});