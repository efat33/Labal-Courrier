jQuery(function ($) {

	// let no_postalcode_countries;

	// if ($('.lc').length) {
	// 	$.ajax({
	// 		url: lc_obj.ajax_url,
	// 		method: 'post',
	// 		dataType: 'json',
	// 		data: {
	// 			action: 'get_no_postalcode_countries',
	// 		},
	// 		success: function (d) {
	// 			if (d.status == 'SUCCESS') {
	// 				no_postalcode_countries = d.no_postalcode_countries;
	// 			}
	// 		}
	// 	});
	// }

	function getNextWeekDay($format = "DD-MMMM-YYYY") {
		
		var businessDay = new Date();
		if (moment().day() === 5) { // friday, show monday
			// set to monday
			businessDay = moment().weekday(8).format($format);
		} else if (moment().day() === 6) { // saturday, show monday
			// set to monday
			businessDay = moment().weekday(8).format($format);
		}
		else { // other days, show next day
			businessDay = moment().add('days', 1).format($format);
		}
		
		return businessDay;
	}

	function getMaxDay($format = "DD/MM/YYYY") {
		var businessDay = new Date();
		businessDay = moment().add('days', 7).format($format);
		
		return businessDay;
	}

	
	$('.cs-dispatch-date').daterangepicker({
		singleDatePicker: true,
		showDropdowns: true,
		startDate: getNextWeekDay('DD-M-YYYY'),
		locale: {
			format: 'DD-M-YYYY'
		},
		minDate: getNextWeekDay('DD/MM/YYYY'),
		maxDate : getMaxDay(),
		isInvalidDate: function(date) {
			if (date.day() == 0 || date.day() == 6)
			  return true;
			return false;
		}
	}, function (start, end, label) {
		$('.cs-pickup-date-field').val(moment(start).format('YYYY-MM-DD'))
		// var years = moment().diff(start, 'years');
		// alert("You are " + years + " years old!");
	});

	let package_index = 1;
	$(document).on('click', '#add_package', function (e) {
		e.preventDefault();
		const tpl = `<tr>
		<td>
			<div class="input-group">
				<input type="text" name="package[`+ package_index + `][qty]" min="1" value="1" class="form-control">
				<span class="input-group-text">pcs</span>
			</div>
		</td>
		<td>
			<div class="input-group">
				<input type="text" name="package[`+ package_index + `][weight]" class="form-control">
				<span class="input-group-text">kg</span>
			</div>
		</td>
		<td>
			<div class="input-group">
				<input type="text" name="package[`+ package_index + `][length]" class="form-control">
				<span class="input-group-text">cm</span>
			</div>
		</td>
		<td>
			<div class="input-group">
				<input type="text" name="package[`+ package_index + `][width]" class="form-control">
				<span class="input-group-text">cm</span>
			</div>
		</td>
		<td>
			<div class="input-group">
				<input type="text" name="package[`+ package_index + `][height]" class="form-control">
				<span class="input-group-text">cm</span>
			</div>
		</td>
		<td>
			<button class="btn btn-remove-package">
				<span class="fa fa-trash"></span>
			</button>
		</td>
	</tr>`;
		// console.log(tpl);
		package_index++;
		$('#lc_packages').append(tpl);
	});


	// $(document).on('click', '.btn-remove-package', function (e) {
	// 	e.preventDefault();
	// 	$(this).closest('tr').remove();
	// });

	$(document).on('change', 'input[name="package_type"]', function (e) {
		// console.log($(this).val());
		if ($(this).val() == 'Package') {

			let countries_html = '';
			for (var country_code in lc_obj.countries) {
				var country_name = lc_obj.countries[country_code];
				countries_html += "<option value='" + country_code + "'>" + country_name + "</option>";
			}
			$('#item_details_container').html(`
			<div class="col-lg-12">
                                <label class="w-100 text-warning">Describe each unique item in your shipment</label>
                                <div class="items_fields" id="lc_items">
                                    <div class="package_item">
                                        <div class="item_number">
                                            1
                                        </div>
                                        <div class="row">
                                            <div class="col-7">
                                                <label for="item_description">Item Description:*</label>
                                                <a href="<?= site_url() ?>/prohibited-goods-and-contents/" style="text-decoration: underline; font-weight: 300" class="text-right text-warning float-right">View Prohibited Items</a>
                                                <input required type="text" data-id="item_description" name="item[0][item_description]" class="form-control">
                                            </div>
                                            <div class="col">
                                                <label for="commodity_code">Commodity Code:</label>
                                                <input type="text" data-id="commodity_code" name="item[0][commodity_code]" class="form-control">
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col">
                                                <label for="commodity_code">Quantity:*</label>
                                                <input required type="text" data-id="quantity" name="item[0][quantity]" class="form-control">
                                            </div>
                                            <div class="col">
                                                <label for="units">Units:*</label>
                                                <select required name="item[0][units]" data-id="units" class="form-control">
                                                    <option value="PCS">Pieces</option>
                                                </select>
                                            </div>
                                            <div class="col">
                                                <label for="item_value">Item Value:*</label>
                                                <input required type="text" data-id="item_value" name="item[0][item_value]" class="form-control">
                                            </div>
                                            <div class="col">
                                                <label for="net_weight">Net Weight:</label>
                                                <input required type="text" data-id="net_weight" name="item[0][net_weight]" class="form-control">
                                            </div>
                                            <div class="col">
                                                <label for="gross_weight">Gross Weight:*</label>
                                                <input required type="text" data-id="gross_weight" name="item[0][gross_weight]" class="form-control">
                                            </div>
                                            <div class="col-3">
                                                <label for="item_origin">Where was the item made?:*</label>
                                                <select required class="form-control lc-select-country" name="item[0][item_origin]" id="" aria-label="Example select with button addon">
                                                    <option value="0" selected>Country</option>
													`+ countries_html + `
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col text-white">Total Units <span>1</span></div>
                                    <div class="col text-white">Total Net Weight <span id="totalNetWeight">--,--<span></div>
                                    <div class="col text-white">Total Gross Weight <span id="totalGrossWeight">--,--<span></div>
                                    <div class="col text-right">
                                        <button id="add_item" class="repeater-add"><span style="line-height: unset;" class="dashicons dashicons-plus repeater-add" alt="Add"></span> Add Another Item</button>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <input type="hidden" id="totalInvoiceVal" name="total_customs_value">
                                    <div class="col text-white">Total Invoice Value (€): <span id="totalInvoiceValDisplay"></span></div>
                                </div>
                            </div>

				<div class="col-lg-6 pt-3">
					<label class="text-warning w-100">Reason for shipment:</label>
					<select name="export_reason_type" class="form-control" style="border-radius: 0;" id="">
						<option value="GIFT">Gift</option>
						<option value="COMMERCIAL_PURPOSE_OR_SALE">Commercial</option>
						<option value="PERSONAL_BELONGINGS_OR_PERSONAL_USE">Personal, Not for sale</option>
						<option value="SAMPLE">Sample</option>
						<option value="RETURN">Return for repair</option>
						<option value="RETURN_TO_ORIGIN">Return after repair</option>
						<option value="PERMANENT">Permanent</option>
						<option value="TEMPORARY">Temporary</option>
						<option value="USED_EXHIBITION_GOODS_TO_ORIGIN">Used exhibition goods</option>
						<option value="INTERCOMPANY_USE">Intercompany use</option>
						<option value="WARRANTY_REPLACEMENT">Warranty replacement</option>
						<option value="DIPLOMATIC_GOODS">Diplomatic goods</option>
						<option value="DEFENCE_MATERIAL">Defence material</option>
					</select>
			</div>`);
			// $('#document_description_container').hide();
		} else {
			$('#item_details_container').html(`<div class="col-lg-6">
			`);
			// $('#document_description_container').show();
		}
	})

	$(document).on('click', '.package-type-label', function () {
		$('.package-type-label').removeClass('active');
		$(this).addClass('active');
	});

	$(document).on('click', '.lc-radio.pickup', function () {
		$('.lc-radio.pickup').removeClass('active');
		$(this).addClass('active');
	});




	// $(document).on('click', '#btn_get_quote', function (e) {
	// 	$(".lc-loading-screen").fadeIn()
	// })

	// $(document).on('submit', "#frm_customer_declaration", function (e) {
	// 	e.preventDefault();
	// 	var form = $('#frm_customer_declaration')[0];
	// 	console.log(form);
	// 	var country_fields = $(this).find('.lc-select-country');
	// 	if (country_fields.length) {
	// 		country_fields.each(function (index) {
	// 			let value = $(this).val();
	// 			if (value == '0') {
	// 				alert('Please fill the requiered fields');
	// 			}
	// 		})
	// 	}else{
	// 		form.submit();
	// 	}
	// })

	function form_get_quote_values() {
		return {
			col_country: $('#frm_get_quote input[name="col_country"]').val(),
			col_city: $('#frm_get_quote input[name="col_city"]').val(),
			col_postcode: $('#frm_get_quote input[name="col_postcode"]').val(),

			del_country: $('#frm_get_quote input[name="del_country"]').val(),
			del_city: $('#frm_get_quote input[name="del_city"]').val(),
			del_postcode: $('#frm_get_quote input[name="del_postcode"]').val(),

			dispatch_date: $('#frm_get_quote input[name="dispatch_date"]').val(),
		}
	}

	// $(document).on('change', '#frm_get_quote', function () {
	// 	$('.common_error').hide(500);
	// })

	function validate_get_quote_form() {
	}

	// $(document).on('change', '.country', function (e) {
	// 	const postal_code = $('.postcode_field').val();
	// 	if(postal_code != ''){
	// 		let country_field = $('.col_country');
	// 	}
	// });





	let get_cities = function get_cities(postal_code_fld) {
		const postal_code = postal_code_fld.val();

		console.log(postal_code);
		location_type = postal_code_fld.data('type');

		let country_field = $('.col_country');
		let city_datalist = $('.col_cities');

		if (location_type == 'destination') {
			country_field = $('.del_country');
			city_datalist = $('.del_cities');
		}

		let country = country_field.val();
		if (country == 'Country')
			country = '';

		console.log(country);

		let request = $.ajax({
			url: lc_obj.ajax_url,
			method: 'post',
			data: {
				action: 'get_city_list',
				country: country,
				postal_code: postal_code
			},
			dataType: 'json',
			success: function (response) {
				if (response.length) {
					postal_code_fld.closest('.input_group').find('.lc_city').removeAttr('disabled');
					if (location_type == 'destination') {
						del_cities = response;
					} else {
						col_cities = response;
					}
				} else {
					validate_location(postal_code_fld);
				}

				let html_string = '';
				response.forEach(function (city, index) {
					if (city === null) {
						city = "Invalid Post Code";
					} else {
						const regex = /\([^)]*\)/;
						const subst = ``;
						city = city.replace(regex, subst); // remove additional info within brackets
						city = city.substring(0, 34);
					}

					html_string += "<option value='" + city + "'>";
				})
				console.log(city_datalist);
				city_datalist.html(html_string);
			}
		}).done(function () {
			console.log('done')
			postal_code_fld.closest('.input_group').find('.lc-loading').hide();
		});

		return request;
	}

	let get_postcodes = function get_postcodes(city_field) {
		const city = city_field.val();

		console.log(city);

		let country_field = $('.col_country');

		location_type = city_field.data('type');
		if (location_type == 'destination') {
			country_field = $('.del_country');
		}

		let country = country_field.val();
		if (country == 'Country')
			country = '';

		console.log(country);

		let request = $.ajax({
			url: lc_obj.ajax_url,
			method: 'post',
			data: {
				action: 'get_postcode_list',
				country: country,
				city: city
			},
			dataType: 'json',
			success: function (response) {
				if (response.length) {
					city_field.closest('.input_group').find('.lc_city').removeAttr('disabled');
					if (location_type == 'destination') {
						del_postcodes = response;
					} else {
						col_postcodes = response;
					}
					// let html_string = '';
					// response.forEach(function (city, index) {
					// 	if (city === null) {
					// 		city = "Invalid Post Code";
					// 	} else {
					// 		const regex = /\([^)]*\)/;
					// 		const subst = ``;
					// 		city = city.replace(regex, subst); // remove additional info within brackets
					// 		city = city.substring(0, 34);
					// 	}

					// 	html_string += "<option value='" + city + "'>";
					// })
				} else {
					// validate_location(city);
				}


			}
		}).done(function () {
			console.log('done')
			city_field.closest('.input_group').find('.lc-loading').hide();
		});

		return request;
	}


	let city_request = false;
	let postcode_request = false;

	$(document).on('change', '.postcode_field', function (e) {
		$(this).closest('.input_group').find('.lc-loading').hide();

		let location_grp = $(this).closest('.input_group');
		let city_val = location_grp.find('.city_field').val();
		let postcode_val = location_grp.find('.postcode_field').val();
		if (city_val == '' || postcode_val == '') {
			trigger_get_cities($(this));
		}
	});

	$(document).on('change', '.city_field', function (e) {
		$(this).closest('.input_group').find('.lc-loading').hide();

		let location_grp = $(this).closest('.input_group');
		let city_val = location_grp.find('.city_field').val();
		let postcode_val = location_grp.find('.postcode_field').val();
		if (city_val == '' || postcode_val == '') {
			trigger_get_postcodes($(this));
		}
	});

	setInterval(function () {
		let country_field = $('.lc-select-country');
		if (country_field.val() != 0 || country_field.val() != '') {
			country_field.find('.city_field').removeAttr('disabled');
			country_field.find('.postcode_field').removeAttr('disabled');
		}
	}, 3000);

	// $(document).on('change', '.lc-select-country', function (e) {
	// 	let country_field = $(this);
	// 	$(this).closest('.input_group').find('.lc-loading').hide();
	// 	const selected_country = $(this).val();
	// 	const parent = $(this).closest('.input_group');
	// 	const city_field = parent.find('.city_field');
	// 	const postcode_field = parent.find('.postcode_field');
	// 	// console.log(parent.find('.city_field'));
	// 	city_field.removeAttr('disabled').val('');
	// 	postcode_field.removeAttr('disabled').val('');
	// 	if (no_postalcode_countries.includes(selected_country)) {
	// 		postcode_field.removeAttr('required');
	// 	} else {
	// 		postcode_field.attr('required', 'required');
	// 	}
	// 	trigger_get_postcodes(city_field);

	// 	console.log(lc_obj.id_required_countries);
	// 	if ($('#frm_addtional_info').length) {
	// 		if (lc_obj.id_required_countries.includes(selected_country)) {
	// 			if (country_field.attr('name') == 'sender_country') {
	// 				country_field.closest('.input_collection_from').append(
	// 					'<input required type="text" placeholder="ID number or Tax ID" class="form-control mt-3 id_number"  name="sender_id_number" id="sender_id_number" />'
	// 				)
	// 			} else {
	// 				country_field.closest('.input_collection_from').append(
	// 					'<input required type="text" placeholder="ID number or Tax ID" class="form-control mt-3 id_number"  name="receiver_id_number" id="receiver_id_number" />'
	// 				)
	// 			}
	// 		} else {
	// 			country_field.closest('.input_collection_from').find('.id_number').remove()
	// 		}
	// 	}
	// });

	// $(document).on('change', '.lc-select-country', function (e) {
	// 	const postcode_field = $(this).closest('.input_group').find('.postcode_field');
	// 	trigger_get_cities(postcode_field);
	// });

	function trigger_get_postcodes(city_field) {
		// const valid = validate_location(postcode_field);

		// if (!valid) return;

		if (postcode_request && postcode_request.readyState !== 4) { // Abort previous request
			postcode_request.abort();
		}
		city_field.closest('.input_group').find('.lc-loading').show();
		// postcode_field.closest('.input_group').find('.lc_city').attr('disabled', 'disabled');
		postcode_request = get_postcodes(city_field);
	}

	function trigger_get_cities(postcode_field) {
		// const valid = validate_location(postcode_field);

		// if (!valid) return;

		if (city_request && city_request.readyState !== 4) { // Abort previous request
			city_request.abort();
		}
		postcode_field.closest('.input_group').find('.lc-loading').show();
		// postcode_field.closest('.input_group').find('.lc_city').attr('disabled', 'disabled');
		city_request = get_cities(postcode_field);
	}

	function validate_location(postcode_field) {
		const str = postcode_field.val();
		const regex = /[a-zA-Z]/g;
		const containsAlphabet = regex.test(str);

		const location_grp = postcode_field.closest('.input_group');

		if (containsAlphabet) {
			location_grp.find('.validation_message')
				.slideDown()
				.text('Please enter a valid post code');
			return false;
		} else {
			location_grp.find('.validation_message')
				.slideDown()
				.text('');
			return true;
		}
	}


	//------------------------------------------------------------------------------------------

	// var input = document.querySelector("#sender_phone_number"),
	// 	output = document.querySelector("#output");
	// console.log(lc_obj.plugin_url + "/public/plugins/intl-tel-input-master/build/js/utils.js");
	// var iti = window.intlTelInput(input, {
	// 	nationalMode: true,
	// 	utilsScript: lc_obj.plugin_url + "/plugins/intl-tel-input-master/build/js/utils.js" // just for formatting/placeholders etc
	// });

	// var handleChange = function () {
	// 	var text = (iti.isValidNumber()) ? "International: " + iti.getNumber() : "Please enter a number below";
	// 	var textNode = document.createTextNode(text);
	// 	output.innerHTML = "";
	// 	output.appendChild(textNode);
	// };

	// // listen to "keyup", but also "change" to update when the user selects a country
	// input.addEventListener('change', handleChange);
	// input.addEventListener('keyup', handleChange);

	$(document).on('change', '#sender_country', function (e) {
		e.preventDefault();
		var country = $(this).val().toLowerCase();
		iti_sender.setCountry(country);
	})

	$(document).on('change', '#sender_country', function (e) {
		var country = $(this).val().toLowerCase();
		var number_without_code = $('#sender_number').val();
		updateFullTelNumber(country, $('#sender_phone_number'), number_without_code, iti_sender, senderInput)
	})
	$(document).on('change', '#receiver_country', function (e) {
		e.preventDefault();
		var country = $(this).val().toLowerCase();
		var number_without_code = $('#receiver_number').val();
		updateFullTelNumber(country, $('#receiver_phone_number'), number_without_code, iti_receiver, receiverInput)
	})
	function updateFullTelNumber(country, full_number_field, number_without_code, iti, input) {
		iti.setCountry(country);
		let code = window.intlTelInputGlobals.getInstance(input).s.dialCode
		input.value = code;
		full_number_field.val(code + number_without_code);
	}

	$(document).on('keyup', "#sender_number", function () {
		var code = $('#sender_phone_code').val();
		$('#sender_phone_number').val(code + $(this).val());
	})

	$(document).on('keyup', "#receiver_number", function () {
		var code = $('#receiver_phone_code').val();
		$('#receiver_phone_number').val(code + $(this).val());
	})
	// $('input[name="sender_country"]').change()

	$(document).on('select2:open', () => {
		document.querySelector('.select2-search__field').focus();
	});

	if ($('#frmTrackingDetails').length) {
		$(document).on('submit', '#frmTrackingDetails', function (e) {
			e.preventDefault();

			jQuery(".lc-loading-modal").fadeIn();
			const data = $(this).serialize();

			$.ajax({
				url: lc_obj.ajax_url,
				method: 'post',
				data: data,
				dataType: 'html',
				success: function (d) {
					jQuery(".lc-loading-modal").hide();
					$('#result_area').html(d).show();
				}
			})
		})
	}
});
