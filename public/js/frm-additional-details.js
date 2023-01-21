jQuery(function ($) {

    let no_postalcode_countries = [];
    
    if ($('#frm_addtional_info').length || $('#lc_shipping_address_form').length) {

        $('#col_country').select2();
        $('#del_country').select2();

        // -- SENDER --
        $(document).on('change', '#sender_phone_number', function () {
            $('#sender_full_phone_number').val($('#sender_phone_code').val() + $(this).val());
        })

        // -- RECEIVER --
        $(document).on('change', '#receiver_phone_number', function () {
            $('#receiver_full_phone_number').val($('#receiver_phone_code').val() + $(this).val());
        })


        let is_insurance = '0';
        let package_type = ''

        $(document).on('click', '.package-type-label', function () {
            $('.package-type-label').removeClass('active');
            $(this).addClass('active');
            package_type = $('.package-type:checked').val();

            if (package_type == 'Package' && is_insurance === '1') {
                $('#insurance_val_field').show();
                $('#insurance_val_field input').removeAttr('disabled');
            } else {
                $('#insurance_val_field').hide();
                $('#insurance_val_field input').attr('disabled', 'disabled');
            }
        });

        $(document).on('click', '.is-insurance', function () {

            $('.is-insurance').removeClass('active');
            $(this).addClass('active');

            is_insurance = $('input[name="insurance"]:checked').val();

            // console.log(is_insurance);

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

        $(document).on('change', '.lc-select-country', function (e) {
            let country_field = $(this);
            const selected_country = $(this).val();
            if (lc_obj.id_required_countries.includes(selected_country)) {
                if (country_field.attr('name') == 'col_country') {
                    country_field.closest('.info-section-wrapper').find('.info-form-identity').append(
                        '<div class="lc-form-control id_number_div"><label for="sender_id_number">'+lc_obj.passport_field_label+'</label><input value="" type="text" class="form-control" name="sender_id_number" id="sender_id_number" /></div>'
                    )
                } else {
                    country_field.closest('.info-section-wrapper').find('.info-form-identity').append(
                        '<div class="lc-form-control id_number_div"><label for="receiver_id_number">'+lc_obj.passport_field_label+'</label><input value="" type="text" class="form-control" name="receiver_id_number" id="receiver_id_number" /></div>'
                    )
                }
            } else {
                country_field.closest('.info-section-wrapper').find('.id_number_div').remove()
            }
        });

        $.ajax({
            url: lc_obj.ajax_url,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'get_no_postalcode_countries',
            },
            success: function (d) {
                if (d.status == 'SUCCESS') {
                    no_postalcode_countries = d.no_postalcode_countries;
                }
            }
        });



    }

});