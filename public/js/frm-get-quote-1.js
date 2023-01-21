jQuery(function ($) {

    let col_country = '';
    let col_postcode_or_city_text = '';
    let col_postcode_or_city_value = '';
    let del_country = '';
    let del_postcode_or_city_text = '';
    let del_postcode_or_city_value = '';
    
    if ($('#frm_get_quote').length) {

        //Initial events start

        col_country = $('select[name="col_country"]').val();
        del_country = $('select[name="del_country"]').val();
        $(window).on('popstate', function () {
            col_country = $('select[name="col_country"]').val();
            del_country = $('select[name="del_country"]').val();
        });

        //Initial events end


        $(document).on('change', 'select[name="col_country"], select[name="col_country_full"]', function (e) {
            e.preventDefault();
            col_country = $(this).val();
            
            $('#col_postcode_or_city').val(null).trigger('change');
        })

        $('#col_country_from').select2();
        $('#col_country_to').select2();

        $('#col_country_from_quick').select2();
        $('#col_country_to_quick').select2();
        
        $('#col_postcode_or_city').select2({
            minimumInputLength: 3,
            placeholder: 'Code Postal ou Ville',
            language: {
                inputTooShort: function () { return "Saisissez au moins 3 caractère"; },
                noResults: function () { return "Aucun résultat trouvé"; },
                searching: function(){ return"Recherche en cours…" }
            },
            ajax: {
                delay: 250, // wait 250 milliseconds before triggering the request
                url: lc_obj.ajax_url + '?action=search_by_postcode_or_city',
                data: function (params) {
                    console.log('col_country', col_country);
                    var query = {
                        search: params.term,
                        country: col_country,
                    };
                    // Query parameters will be ?search=[term]&type=public
                    return query;
                },
                dataType: 'json'
            }
        })

        $(document).on('change', '#col_postcode_or_city', function () {
            col_postcode_or_city_value = $(this).val();
            col_postcode_or_city_text = $('#col_postcode_or_city option:selected').text();
        })


        $(document).on('change', 'select[name="del_country"], select[name="del_country_full"]', function (e) {
            e.preventDefault();
            del_country = $(this).val();

            $('#del_postcode_or_city').val(null).trigger('change');
        })


        $('#del_postcode_or_city').select2({
            minimumInputLength: 3,
            placeholder: 'Code Postal ou Ville',
            language: {
                inputTooShort: function () { return "Saisissez au moins 3 caractère"; },
                noResults: function () { return "Aucun résultat trouvé"; },
                searching: function(){ return"Recherche en cours…" }
            },
            ajax: {
                delay: 250,
                url: lc_obj.ajax_url + '?action=search_by_postcode_or_city',
                data: function (params) {
                    var query = {
                        search: params.term,
                        country: del_country,
                    };
                    // Query parameters will be ?search=[term]&type=public
                    return query;
                },
                dataType: 'json'
            }
        })

        $(document).on('change', '#del_postcode_or_city', function () {
            del_postcode_or_city_value = $(this).val();
            del_postcode_or_city_text = $('#del_postcode_or_city option:selected').text();
        })
        
        $(document).on('click', '#exchange_button', function () {
            let cc = col_country;
            let dc = del_country;

            $('#col_country_to').val(cc).trigger('change.select2');
            $('#col_country_from').val(dc).trigger('change.select2');

            col_country = dc;
            del_country = cc;


            // if (col_postcode_or_city_text.length && del_postcode_or_city_text.length) {
            cpt = col_postcode_or_city_text;
            cpv = col_postcode_or_city_value;

            dpt = del_postcode_or_city_text;
            dpv = del_postcode_or_city_value;


            $('#del_postcode_or_city').html('');
            $('#col_postcode_or_city').html('');

            var dpo = new Option(cpt, cpv, true, true);
            var cpo = new Option(dpt, dpv, true, true);

            $('#col_postcode_or_city').append(cpo).trigger('change');
            $('#del_postcode_or_city').append(dpo).trigger('change');

            col_postcode_or_city_text = dpt;
            col_postcode_or_city_value = dpv;

            del_postcode_or_city_text = cpt;
            del_postcode_or_city_value = cpv;
            // }
        })


        /*
                $('#del_suburb').select2({
                    placeholder: 'Suburb',
                    ajax: {
                        url: lc_obj.ajax_url + '?action=search_by_suburb',
                        data: function (params) {
                            var query = {
                                search: params.term,
                                country: del_country,
                                city: del_city,
                            };
                            // Query parameters will be ?search=[term]&type=public
                            return query;
                        },
                        dataType: 'json'
                    }
                })
        
                $('#del_postcode').select2({
                    placeholder: 'Postcode',
                    ajax: {
                        url: lc_obj.ajax_url + '?action=search_by_postcode',
                        data: function (params) {
                            var query = {
                                search: params.term,
                                country: del_country,
                                city: del_city,
                            };
                            // Query parameters will be ?search=[term]&type=public
                            return query;
                        },
                        dataType: 'json'
                    }
                })
        
                $('#del_city').select2({
                    placeholder: 'City',
                    ajax: {
                        url: lc_obj.ajax_url + '?action=search_by_city',
                        data: function (params) {
                            var query = {
                                search: params.term,
                                country: del_country,
                                postcode: del_postcode,
                            };
                            // Query parameters will be ?search=[term]&type=public
                            return query;
                        },
                        dataType: 'json'
                    }
                })
        */

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