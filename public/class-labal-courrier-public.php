<?php
class Labal_Courrier_Public
{
	private $plugin_name;

	private $version;

	private $dhl;
	private $ups;

	private $eu_countries;

	private $stripePublicKey;
	private $stripeSecretKey;

	private $referral_code_discount;
	private $referral_code_limit;

	public function __construct($plugin_name, $version)
	{
		add_action("init", array($this, 'lc_start_session'));
		add_action("after_setup_theme", array($this, 'lc_remove_admin_bar'));

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->init_carriers();

		$this->eu_countries = [
			'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'FI', 'DE', 'GR',
			'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PF', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'FR'
		];

		$this->referral_code_discount = get_option('referral_code_discount', 10) == '' ? 10 : get_option('referral_code_discount', 10);
		$this->referral_code_limit = get_option('referral_code_limit', 60) == '' ? 60 : get_option('referral_code_limit', 60);

		$this->stripePublicKey = 'pk_test_cdDNEk2qgWrCh7ENx0JxE90Y';
		$this->stripeSecretKey = 'sk_test_o9FZmhS5bbfFMqxNEuWjL4im';
		// $this->stripePublicKey = 'pk_live_51JNFRJCmTAWyJf9RJQndzijEHnEVDNHjxb2KVYW3zMKAbJJO8r11mm2t0iY9hTReWtzjEJIUcmilM414vJrg9UbS00tZ0yN6Mh';
		// $this->stripeSecretKey = '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';
	}

	public function lc_remove_admin_bar()
	{
		if (!current_user_can('administrator') && !is_admin()) {
			show_admin_bar(false);
		}
	}

	public function lc_start_session()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}

	private function init_carriers()
	{
		require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/api/dhl-curl/DHL.php';
		$this->dhl = new DHL('labalFR', 'Q^2oU$8lI#1a', '950455439');

		require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/api/ups/UPS.php';
		$this->ups = new UPS();
	}

	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/labal-courrier-public.css', array(), time(), 'all');
		wp_enqueue_style('lc-bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css', array(), $this->version, 'all');
		wp_enqueue_style('jquery-ui-theme', 'http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), $this->version, 'all');
		wp_enqueue_style('lc-select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], $this->version, false);
		wp_enqueue_style('intl-tel-input-master-css', plugin_dir_url(__FILE__) . 'plugins/intl-tel-input-master/build/css/intlTelInput.css', [], $this->version, false);
		wp_enqueue_style('lc-fontawesome', plugin_dir_url(__FILE__) . 'plugins/fontawesome-6/css/all.css', [], $this->version, false);
		wp_enqueue_style('lc-daterangepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', [], $this->version, false);
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script('lc-popper', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script('lc-bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
		// wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.11.4/jquery-ui.js', array('jquery'), $this->version, false);

		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-autocomplete', '', array('jquery-ui-widget', 'jquery-ui-position'), '1.8.6');
		wp_enqueue_script('jquery-validate', 'https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js', array('jquery'));

		wp_enqueue_script('lc-select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'));

		wp_enqueue_script('intl-tel-input-master-js', plugin_dir_url(__FILE__) . 'plugins/intl-tel-input-master/build/js/intlTelInput.min.js', array('jquery'), $this->version, false);

		wp_enqueue_script('lc-moment', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script('lc-daterangepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array('jquery'), $this->version, false);

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/labal-courrier-public.js', array('jquery', 'jquery-ui-autocomplete'), $this->version, false);

		wp_localize_script(
			$this->plugin_name,
			'lc_obj',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'plugin_url' => plugin_dir_url(__FILE__),
				'countries' => lc_get_all_countries(),
				'id_required_countries' => $this->get_id_required_countries(),
				'nonce' => wp_create_nonce('ajax-nonce'),
				'plugin_url_root' => LABAL_COURRIER_PLUGIN_URL,
			)
		);

		wp_enqueue_script($this->plugin_name . '_frm_get_quote_1', plugin_dir_url(__FILE__) . 'js/frm-get-quote-1.js', array('jquery', 'jquery-ui-autocomplete'));
		wp_localize_script(
			$this->plugin_name . '_frm_get_quote_1',
			'lc_obj',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'plugin_url' => plugin_dir_url(__FILE__),
				'countries' => lc_get_all_countries(),
				'id_required_countries' => $this->get_id_required_countries(),
				'nonce' => wp_create_nonce('ajax-nonce'),
				'plugin_url_root' => LABAL_COURRIER_PLUGIN_URL,
			)
		);

		wp_localize_script(
			$this->plugin_name . '_frm_addtional_info',
			'lc_obj',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'plugin_url' => plugin_dir_url(__FILE__),
				'countries' => lc_get_all_countries(),
				'id_required_countries' => $this->get_id_required_countries(),
				'nonce' => wp_create_nonce('ajax-nonce'),
				'plugin_url_root' => LABAL_COURRIER_PLUGIN_URL,
			)
		);
		wp_enqueue_script($this->plugin_name . '_frm_addtional_info', plugin_dir_url(__FILE__) . 'js/frm-additional-details.js', array('jquery', 'jquery-ui-autocomplete'), $this->version, false);


		wp_enqueue_script($this->plugin_name . '_frm_customs_declaration', plugin_dir_url(__FILE__) . 'js/frm-customs-declaration.js', array('jquery', 'jquery-ui-autocomplete'), $this->version, false);
		wp_localize_script(
			$this->plugin_name . '_frm_customs_declaration',
			'lc_obj',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'plugin_url' => plugin_dir_url(__FILE__),
				'countries' => lc_get_all_countries(),
				'id_required_countries' => $this->get_id_required_countries(),
				'nonce' => wp_create_nonce('ajax-nonce'),
				'plugin_url_root' => LABAL_COURRIER_PLUGIN_URL,
				'passport_field_label' => __("Passport No.", "labal-courrier"),
			)
		);
	}

	public function register_actions()
	{
		$this->template_functions();
		$this->register_shortcodes();
		$this->register_request_handlers();
		$this->register_filters();
	}

	public function register_filters()
	{
		add_filter('wp_mail_from_name', array($this, 'lc_wpmail_from_name'));
		add_filter('page_template', array($this, 'lc_wizard_page'));
		// add_filter('registration_redirect', array($this, 'lc_registration_redirect'));
		// add_filter('login_redirect', array($this, 'lc_login_redirect'));
	}

	public function lc_wpmail_from_name()
	{
		return 'Mon Courrier De France';
	}

	public function lc_registration_redirect()
	{
		$shipment_id = get_transient('shipment_id');
		if (isset($_SESSION['lc_redirect_back_to']) && $_SESSION['lc_redirect_back_to'] == 'additional-detail-form') {
			if ($_SESSION['lc_redirect_back_to'] == 'additional-detail-form') {
				return site_url() . '/labal-courrier-additional-information/?shipment_id=' . $shipment_id . '&state=login_return_back';
			}
		}
	}

	public function lc_login_redirect()
	{
		// $lc_redirect_to = get_transient('lc_redirect_back_to');
		$shipment_id = get_transient('shipment_id');
		if (isset($_SESSION['lc_redirect_back_to']) && $_SESSION['lc_redirect_back_to'] == 'additional-detail-form') {
			if ($_SESSION['lc_redirect_back_to'] == 'additional-detail-form') {
				return site_url() . '/labal-courrier-additional-information/?shipment_id=' . $shipment_id . '&state=login_return_back';
			}
		}
	}

	public function lc_wizard_page($page_template)
	{
		if (get_post_field('post_name', get_post()) == 'labal-courrier-shipment') {
			$page_template = LABAL_COURRIER_PLUGIN_PATH . 'public/page-templates/labal-courrier-wizard-page.php';
		}
		return $page_template;
	}

	public function register_shortcodes()
	{
		add_shortcode('lc_login_page', [$this, 'lc_login_page']);
		add_shortcode('lc_forgot_password_page', [$this, 'lc_forgot_password_page']);
		add_shortcode('lc_reset_password_page', [$this, 'lc_reset_password_page']);
		add_shortcode('lc_register_page', [$this, 'lc_register_page']);

		add_shortcode('lc_profile_page', [$this, 'lc_profile_page']);
		add_shortcode('lc_promo_code_page', [$this, 'lc_promo_code_page']);
		add_shortcode('lc_invite_friend_page', [$this, 'lc_invite_friend_page']);
		add_shortcode('lc_my_shipment_page', [$this, 'lc_my_shipment_page']);
		add_shortcode('lc_address_book_page', [$this, 'lc_address_book_page']);
		add_shortcode('lc_new_billing_page', [$this, 'lc_new_billing_page']);
		add_shortcode('lc_new_shipping_page', [$this, 'lc_new_shipping_page']);

		add_shortcode('parcel_lc_shipping_order', [$this, 'parcel_lc_shipping_order']);
		add_shortcode('lc_shipping_calculator', [$this, 'lc_shipping_calculator']);
		add_shortcode('lc_shipping_order', [$this, 'lc_shipping_order']);
		add_shortcode('lc_shipping_order_country', [$this, 'lc_shipping_order_country']);
		add_shortcode('lc_wizard_wpcargo', [$this, 'lc_wizard_wpcargo']);
		add_shortcode('lc_wizard', [$this, 'lc_wizard']);
		add_shortcode('lc_additional_information', [$this, 'lc_additional_information']);
		add_shortcode('lc_customs_declaration_form', [$this, 'lc_customs_declaration_form']);
		add_shortcode('lc_shedule_pickup', [$this, 'lc_shedule_pickup']);
		add_shortcode('lc_shipment_created_successfully', [$this, 'lc_shipment_created_successfully']);
		add_shortcode('lc_checkout', [$this, 'lc_checkout']);

		add_shortcode('lc_tracking_form', [$this, 'lc_tracking_form']);
	}

	public function lc_new_shipping_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-new-shipping-page.php';
		return ob_get_clean();
	}

	public function lc_new_billing_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-new-billing-page.php';
		return ob_get_clean();
	}

	public function lc_address_book_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-address-book-page.php';
		return ob_get_clean();
	}

	public function lc_my_shipment_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-my-shipments-page.php';
		return ob_get_clean();
	}

	public function lc_invite_friend_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-invite-friend-page.php';
		return ob_get_clean();
	}

	public function lc_promo_code_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-promo-code-page.php';
		return ob_get_clean();
	}

	public function lc_profile_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-profile-page.php';
		return ob_get_clean();
	}

	public function lc_register_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-register-page.php';
		return ob_get_clean();
	}

	public function lc_login_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-login-page.php';
		return ob_get_clean();
	}

	public function lc_forgot_password_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-forgot-password-page.php';
		return ob_get_clean();
	}

	public function lc_reset_password_page()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-reset-password-page.php';
		return ob_get_clean();
	}

	public function parcel_lc_shipping_order()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/parcel-request-quote-form.php';
		return ob_get_clean();
	}

	public function lc_shipping_calculator()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/shipping-calculator.php';
		return ob_get_clean();
	}

	public function lc_tracking_form()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/labal-courrier-tracking-form.php';
		return ob_get_clean();
	}

	public function lc_checkout()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/labal-courrier-checkout.php';
		return ob_get_clean();
	}

	public function lc_shipping_order()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/request-quote-form.php';
		return ob_get_clean();
	}

	public function lc_shipping_order_country($attributes)
	{
		$s_pairs = array(
			'sender' => 'FR',
			'receiver' => 'US',
		);
		$atts = shortcode_atts($s_pairs, $attributes);

		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/request-quote-form.php';

		return ob_get_clean();
	}

	public function lc_wizard()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/labal-courrier-wizard.php';
		return ob_get_clean();
	}

	public function lc_wizard_wpcargo()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/labal-courrier-wizard-wpcargo.php';
		return ob_get_clean();
	}

	public function lc_additional_information()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/labal-courrier-additional-info.php';
		return ob_get_clean();
	}

	public function lc_shipment_created_successfully()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/labal-courrier-shipment-created.php';
		return ob_get_clean();
	}

	public function lc_customs_declaration_form()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/labal-courrier-customs-declaration.php';
		return ob_get_clean();
	}

	public function lc_shedule_pickup()
	{
		ob_start();
		include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/labal-courrier-schedule-pickup.php';
		return ob_get_clean();
	}

	public function register_ajax_request_handler()
	{
		// $ajax_functions = array('get_city_list' => 'get_city_list');
		// $ajax_nopriv_functions = array('get_city_list' => 'get_city_list');

		// foreach ($ajax_functions as $name => $function) {
		// 	add_action("wp_ajax_$name", array($this, $function));
		// }

		// foreach ($ajax_nopriv_functions as $name => $function) {
		// 	add_action("wp_ajax_nopriv_$name", array($this, $function));
		// }
	}

	public function get_city_list()
	{
		// print_r($_POST);
		$country = $_POST['country'];
		$postal_code = $_POST['postal_code'];
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://app.zipcodebase.com/api/v1/search?codes=$postal_code&country=$country",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'apikey: fe179ea0-d599-11eb-b19e-97501bc0f792'
			),
		));

		$response = curl_exec($curl);
		// print_r($response); die;

		curl_close($curl);
		$response = json_decode($response);


		$cities = [];
		foreach ($response->results as $code) {
			foreach ($code as $info) {
				array_push($cities, $info->city);
			}
		}

		// print_r($cities);
		echo json_encode($cities);
		wp_die();
	}

	public function get_postcode_list()
	{
		// print_r($_POST);
		$country = $_POST['country'];
		$city = $_POST['city'];
		$curl = curl_init();

		$postal_codes = [];

		if (!empty($country)  && !empty($city)) {

			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://app.zipcodebase.com/api/v1/code/city?city=$city&country=$country",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_HTTPHEADER => array(
					'apikey: fe179ea0-d599-11eb-b19e-97501bc0f792'
					// 'apikey: d48dcf50-da34-11eb-848f-9583c87d2097'
				),
			));

			$response = curl_exec($curl);

			// print_r($response); die;
			curl_close($curl);
			$response = json_decode($response);

			foreach ($response->results as $code) {
				array_push($postal_codes, $code);
			}
		}

		// print_r($cities);
		echo json_encode($postal_codes);
		wp_die();
	}

	public function register_request_handlers()
	{
		// $admin_post_functions = array('get_quote' => 'lc_get_quote');
		// $admin_post_nopriv_functions = array('get_quote' => 'lc_get_quote');

		// foreach ($admin_post_functions as $name => $function) {
		// 	add_action("admin_post_$name", array($this, $function));
		// }

		// foreach ($admin_post_nopriv_functions as $name => $function) {
		// 	add_action("admin_post_nopriv_$name", array($this, $function));
		// }

		if (!empty($_POST['action'])) {
			if ($_POST['action'] === 'get_quote') {
				$this->lc_get_quote();
			}
			if ($_POST['action'] === 'get_city_list') {
				$this->get_city_list();
			}
			if ($_POST['action'] === 'get_postcode_list') {
				$this->get_postcode_list();
			}
			if ($_POST['action'] === 'get_additional_information_form') {
				$this->get_additional_information_form();
			}
			if ($_POST['action'] === 'submit_shipment_details') {
				$this->submit_shipment_details();
			}
			if ($_POST['action'] === 'submit_pickup_details') {
				$this->submit_pickup_details();
			}
			if ($_POST['action'] === 'submit_customs_declaration') {
				$this->submit_customs_declaration();
			}
			if ($_POST['action'] === 'create_shipment_order') {
				$this->create_shipment_order();
			}
			if ($_POST['action'] === 'lc_do_checkout') {
				$this->lc_do_checkout();
			}
			if ($_POST['action'] === 'get_no_postalcode_countries') {
				$this->get_no_postalcode_countries();
			}
			if ($_POST['action'] === 'update_pickup') {
				$this->update_pickup();
			}
			if ($_POST['action'] === 'get_location_format') {
				$this->get_location_format();
			}
			if ($_POST['action'] === 'get_tracking_details') {
				$this->get_tracking_details();
			}

			if ($_POST['action'] === 'create_stripe_session') {
				$this->create_stripe_session();
			}
			if ($_POST['action'] === 'lc_login_action') {
				$this->lc_login_action();
			}
			if ($_POST['action'] === 'lc_forgot_password_action') {
				$this->lc_forgot_password_action();
			}
			if ($_POST['action'] === 'lc_reset_password_action') {
				$this->lc_reset_password_action();
			}
			if ($_POST['action'] === 'lc_register_action') {
				$this->lc_register_action();
			}
			if ($_POST['action'] === 'lc_profile_setting_action') {
				$this->lc_profile_setting_action();
			}

			if ($_POST['action'] === 'lc_billing_address_action') {
				$this->lc_billing_address_action();
			}
			if ($_POST['action'] === 'lc_shipping_address_action') {
				$this->lc_shipping_address_action();
			}

			if ($_POST['action'] === 'save_sender_address') {
				$this->save_sender_address();
			}
			if ($_POST['action'] === 'save_receiver_address') {
				$this->save_receiver_address();
			}
		}

		if (!empty($_GET['action'])) {
			if ($_GET['action'] === 'search_by_postcode') {
				$this->search_by_postcode();
			}
			if ($_GET['action'] === 'search_by_city') {
				$this->search_by_city();
			}
			if ($_GET['action'] === 'search_by_state') {
				$this->search_by_state();
			}
			if ($_GET['action'] === 'search_by_suburb') {
				$this->search_by_suburb();
			}
			if ($_GET['action'] === 'search_by_postcode_or_city') {
				$this->search_by_postcode_or_city();
			}
			if ($_GET['action'] === 'stripe_return') {
				$this->stripe_return_handle();
			}
			if ($_GET['action'] === 'lc_logout') {
				$this->lc_user_logout();
			}
			if ($_GET['action'] === 'lc_default_billing_address') {
				$this->lc_default_billing_address();
			}
			if ($_GET['action'] === 'lc_delete_billing_address') {
				$this->lc_delete_billing_address();
			}
			if ($_GET['action'] === 'lc_delete_shipping_address') {
				$this->lc_delete_shipping_address();
			}
		}
	}

	public function lc_delete_shipping_address()
	{
		global $wpdb;
		$id = $_GET['id'];

		$user = wp_get_current_user();
		$user_id = $user->ID;

		$table = $wpdb->prefix . 'lc_shipping_addresses';
		$existing_address = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));

		if ($existing_address->user_id != $user_id) {
			wp_die(__("You are not allowed", "labal-courrier"));
		}

		$wpdb->delete(
			$table,
			array(
				'id' => $id // value in column to target for deletion
			),
			array(
				'%d' // format of value being targeted for deletion
			)
		);

		wp_safe_redirect(site_url() . '/lc-address-book');
		exit;
	}

	public function lc_delete_billing_address()
	{
		global $wpdb;
		$id = $_GET['id'];

		$user = wp_get_current_user();
		$user_id = $user->ID;

		$table = $wpdb->prefix . 'lc_billing_addresses';
		$existing_address = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));

		if ($existing_address->user_id != $user_id) {
			wp_die(__("You are not allowed", "labal-courrier"));
		}

		$wpdb->delete(
			$table,
			array(
				'id' => $id // value in column to target for deletion
			),
			array(
				'%d' // format of value being targeted for deletion
			)
		);

		wp_safe_redirect(site_url() . '/lc-address-book');
		exit;
	}

	public function lc_default_billing_address()
	{
		global $wpdb;
		$id = $_GET['id'];

		$user = wp_get_current_user();
		$user_id = $user->ID;

		$table = $wpdb->prefix . 'lc_billing_addresses';
		$existing_address = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));

		if ($existing_address->user_id != $user_id) {
			wp_die(__("You are not allowed", "labal-courrier"));
		}

		update_user_meta($user_id, 'lc_billing_address_default', $id);

		wp_safe_redirect(site_url() . '/lc-address-book');
		exit;
	}

	public function lc_user_logout()
	{
		wp_logout();

		// after logout redirect to home page
		wp_safe_redirect(site_url());
		exit;
	}
	public function lc_profile_setting_action()
	{
		global $wpdb;

		$errors = [];
		$nonce = $_POST['lc_nonce'];

		if (!wp_verify_nonce($nonce, 'lc_profile_setting_nonce')) {
			wp_die(__("Invalid Nonce", "labal-courrier"));
		}

		$change_password = false;

		if ($_POST['password'] != '' && $_POST['confirm_password'] != '') {

			$change_password = true;

			// Check password is valid  
			if (0 === preg_match("/.{6,}/", $_POST['password'])) {
				$errors[] = __("Password must be at least six characters", "labal-courrier");
			}

			// Check password confirmation_matches  
			if (0 !== strcmp($_POST['password'], $_POST['confirm_password'])) {
				$errors[] = __("Passwords do not match", "labal-courrier");
			}
		}

		// if validation fails, then redirect to register page with errors
		if (count($errors)) {
			$errors['old_values'] = $_POST;
			set_transient("request_$nonce", $errors, (5000 * 120));
			wp_safe_redirect(add_query_arg(array(
				'request_status' => "error",
				'request_id' => "request_$nonce",
			), site_url() . '/lc-profile'));
			exit;
		}

		$user_id = get_current_user_id();

		if ($change_password) {
			wp_update_user(array('ID' => $user_id, 'user_pass' => $_POST['password']));
		}

		// set firstname and lastname
		update_user_meta($user_id, "first_name",  $_POST['first_name']);
		update_user_meta($user_id, "last_name",  $_POST['last_name']);

		wp_safe_redirect(add_query_arg(array(
			'request_status' => "success"
		), site_url() . '/lc-profile'));
		exit;
	}


	public function lc_shipping_address_action()
	{
		global $wpdb;

		$errors = [];
		$nonce = $_POST['lc_nonce'];

		if (!wp_verify_nonce($nonce, 'lc_shipping_address_nonce')) {
			wp_die(__("Invalid Nonce", "labal-courrier"));
		}

		$user = wp_get_current_user();
		$user_id = $user->ID;

		if (isset($_REQUEST['address_id'])) {
			$table = $wpdb->prefix . 'lc_shipping_addresses';
			$existing_address = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $_REQUEST['address_id']));

			if ($existing_address->user_id != $user_id) {
				wp_die(__("You are not allowed", "labal-courrier"));
			}
		}

		$sender_first_name = trim($_REQUEST['sender_first_name']);
		$sender_last_name = trim($_REQUEST['sender_last_name']);
		$sender_trade_type = trim($_REQUEST['sender_trade_type']);
		$sender_company = trim($_REQUEST['sender_company']);
		$sender_tva_number = trim($_REQUEST['sender_tva_number']);
		$sender_eori_number = trim($_REQUEST['sender_eori_number']);
		$sender_phone_number = trim($_REQUEST['sender_phone_number']);
		$sender_email = trim($_REQUEST['sender_email']);
		$col_country = trim($_REQUEST['col_country']);
		$col_postcode_or_city = trim($_REQUEST['col_postcode_or_city']);
		$sender_address = trim($_REQUEST['sender_address']);
		$sender_address_1 = trim($_REQUEST['sender_address_1']);

		// Check email address is present and valid  
		if (!is_email($sender_email)) {
			$errors[] = __("Please enter a valid email", "labal-courrier");
		}

		// check for unique email address 
		$table = $wpdb->prefix . 'lc_shipping_addresses';
		$address = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE sender_email = '%s'", $sender_email));

		if ($address) {
			$errors[] = __("The email already exists", "labal-courrier");
		}

		// if validation fails, then redirect to register page with errors
		if (count($errors)) {
			$errors['old_values'] = $_POST;
			set_transient("request_$nonce", $errors, (5000 * 120));
			wp_safe_redirect(add_query_arg(array(
				'request_status' => "error",
				'request_id' => "request_$nonce",
			), site_url() . '/lc-new-shipping'));
			exit;
		}

		$insert_data = array(
			'sender_first_name' => $sender_first_name,
			'sender_last_name' => $sender_last_name,
			'sender_trade_type' => $sender_trade_type,
			'sender_company' => $sender_company,
			'sender_tva_number' => $sender_tva_number,
			'sender_eori_number' => $sender_eori_number,
			'sender_phone_number' => $sender_phone_number,
			'sender_email' => $sender_email,
			'col_country' => $col_country,
			'col_postcode_or_city' => $col_postcode_or_city,
			'sender_address' => $sender_address,
			'sender_address_1' => $sender_address_1,
		);

		if (isset($_REQUEST['address_id'])) {
			$data = $insert_data;
			$format = array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s');
			$where = ['id' => $_REQUEST['address_id']];
			$where_format = ['%d'];
			$wpdb->update($wpdb->prefix . 'lc_shipping_addresses', $data, $where, $format, $where_format);
		} else {
			// insert data into table
			$table = $wpdb->prefix . 'lc_shipping_addresses';

			$insert_data['user_id'] = $user_id;
			$data = $insert_data;
			$format = array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s');
			$wpdb->insert($table, $data, $format);
			$insert_id = $wpdb->insert_id;
		}

		$query_arg = array(
			'request_status' => "success"
		);

		if (isset($_REQUEST['address_id'])) {
			$query_arg['id'] = $_REQUEST['address_id'];
			$query_arg['edited'] = 1;
		}

		wp_safe_redirect(add_query_arg($query_arg, site_url() . '/lc-new-shipping'));
		exit;
	}

	public function lc_billing_address_action()
	{
		global $wpdb;

		$errors = [];
		$nonce = $_POST['lc_nonce'];

		if (!wp_verify_nonce($nonce, 'lc_billing_address_nonce')) {
			wp_die(__("Invalid Nonce", "labal-courrier"));
		}

		$user = wp_get_current_user();
		$user_id = $user->ID;

		if (isset($_REQUEST['address_id'])) {
			$table = $wpdb->prefix . 'lc_billing_addresses';
			$existing_address = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $_REQUEST['address_id']));

			if ($existing_address->user_id != $user_id) {
				wp_die(__("You are not allowed", "labal-courrier"));
			}
		}


		$company = trim($_REQUEST['company']);
		$address = trim($_REQUEST['address']);

		// Check email address is present and valid  
		$email = trim($_REQUEST['email']);
		if (!is_email($email)) {
			$errors[] = __("Please enter a valid email", "labal-courrier");
		}

		// if validation fails, then redirect to register page with errors
		if (count($errors)) {
			$errors['old_values'] = $_POST;
			set_transient("request_$nonce", $errors, (5000 * 120));
			wp_safe_redirect(add_query_arg(array(
				'request_status' => "error",
				'request_id' => "request_$nonce",
			), site_url() . '/lc-new-billing'));
			exit;
		}


		if (isset($_REQUEST['address_id'])) {
			$data = array('company' => $company, 'address' => $address, 'email' => $email);
			$format = array('%s', '%s', '%s');
			$where = ['id' => $_REQUEST['address_id']];
			$where_format = ['%d'];
			$wpdb->update($wpdb->prefix . 'lc_billing_addresses', $data, $where, $format, $where_format);
		} else {
			// insert data into table
			$table = $wpdb->prefix . 'lc_billing_addresses';
			$data = array('user_id' => $user_id, 'company' => $company, 'address' => $address, 'email' => $email);
			$format = array('%d', '%s', '%s', '%s');
			$wpdb->insert($table, $data, $format);
			$insert_id = $wpdb->insert_id;

			// check if any there is any default billing address
			$default_billing = get_user_meta($user_id, 'lc_billing_address_default', true);

			if (empty($default_billing)) update_user_meta($user_id, 'lc_billing_address_default', $insert_id);
		}

		$query_arg = array(
			'request_status' => "success"
		);

		if (isset($_REQUEST['address_id'])) {
			$query_arg['id'] = $_REQUEST['address_id'];
			$query_arg['edited'] = 1;
		}

		wp_safe_redirect(add_query_arg($query_arg, site_url() . '/lc-new-billing'));
		exit;
	}

	public function lc_register_action()
	{
		global $wpdb;

		$errors = [];
		$nonce = $_POST['lc_nonce'];

		if (!wp_verify_nonce($nonce, 'lc_register_nonce')) {
			wp_die(__("Invalid Nonce", "labal-courrier"));
		}

		// Check username is present and not already in use  
		$username = trim(esc_sql($_REQUEST['username']));
		if (strpos($username, ' ') !== false) {
			$errors[] = __("Sorry, no spaces allowed in usernames", "labal-courrier");
		}

		// Check username is present and valid  
		if (empty($username)) {
			$errors[] = __("Please enter a username", "labal-courrier");
		} else if (username_exists($username)) {
			$errors[] = __("Username already exists, please try another", "labal-courrier");
		}

		// Check email address is present and valid  
		$email = trim(esc_sql($_REQUEST['email']));
		if (!is_email($email)) {
			$errors[] = __("Please enter a valid email", "labal-courrier");
		} elseif (email_exists($email)) {
			$errors[] = __("This email address is already in use", "labal-courrier");
		}

		// Check password is valid  
		if (0 === preg_match("/.{6,}/", $_POST['password'])) {
			$errors[] = __("Password must be at least six characters", "labal-courrier");
		}

		// Check password confirmation_matches  
		if (0 !== strcmp($_POST['password'], $_POST['confirm_password'])) {
			$errors[] = __("Passwords do not match", "labal-courrier");
		}

		$res_arr = array(
			'request_status' => "error",
			'request_id' => "request_$nonce",
		);
		if (isset($_POST['referred_by']) && $_POST['referred_by'] != '') $res_arr['r'] = $_POST['referred_by'];

		// if validation fails, then redirect to register page with errors
		if (count($errors)) {
			$errors['old_values'] = $_POST;
			set_transient("request_$nonce", $errors, (5000 * 120));
			wp_safe_redirect(add_query_arg($res_arr, site_url() . '/register'));
			exit;
		}

		// validation passed, create the users 
		$password = $_POST['password'];
		$result = wp_create_user($username, $password, $email);

		if (is_wp_error($result)) {
			$errors[] = $result->get_error_message();
			$errors['old_values'] = $_POST;
			set_transient("request_$nonce", $errors, (5000 * 120));
			wp_safe_redirect(add_query_arg($res_arr, site_url() . '/register'));
			exit;
		}

		// set firstname and lastname
		$new_user_id = $result;
		update_user_meta($new_user_id, "first_name",  $_POST['first_name']);
		update_user_meta($new_user_id, "last_name",  $_POST['last_name']);

		// update referral credit 
		if (isset($_POST['referred_by']) && $_POST['referred_by'] != '') {
			$refn_code = $_POST['referred_by'];

			$referred_by = get_user_by('login', sanitize_text_field($refn_code));

			// proceed only if the referrance code is valid
			if (!is_wp_error($referred_by)) {
				$referral_code_discount = get_option('referral_code_discount', 10) == '' ? 10 : get_option('referral_code_discount', 10);

				update_user_meta($new_user_id, 'mnfr_referred_by', $referred_by->ID);
				update_user_meta($new_user_id, 'shipping_sixty_done', 0);
				update_user_meta($new_user_id, 'mnfr_referral_credit', $referral_code_discount);
			}
		}

		wp_safe_redirect(add_query_arg(array(
			'request_status' => "success"
		), site_url() . '/register'));
		exit;
	}

	public function lc_login_action()
	{
		global $wpdb;

		$errors = [];
		$nonce = $_POST['lc_nonce'];

		if (!wp_verify_nonce($nonce, 'lc_login_nonce')) {
			wp_die(__("Invalid Nonce", "labal-courrier"));
		}

		//We shall SQL escape all inputs  
		$email = esc_sql($_REQUEST['email']);
		$password = esc_sql($_REQUEST['password']);

		if (isset($_REQUEST['remember_me'])) {
			$remember = "true";
		} else {
			$remember = "false";
		}


		$login_data = array();
		$login_data['user_login'] = $email;
		$login_data['user_password'] = $password;
		$login_data['remember'] = $remember;

		$user_verify = wp_signon($login_data, false);

		if (is_wp_error($user_verify)) {
			$errors[] = __("Invalid login details", "labal-courrier");
		}

		if (count($errors)) {
			// $errors['old_values'] = $_POST;
			set_transient("request_$nonce", $errors, (5000 * 120));
			wp_safe_redirect(add_query_arg(array(
				'request_status' => "error",
				'request_id' => "request_$nonce",
			), site_url() . '/login'));
			exit;
		}

		// login successful, redirect to home page 
		wp_safe_redirect(site_url());
		exit;
	}

	public function lc_forgot_password_action()
	{
		global $wpdb;

		$errors = [];
		$nonce = $_POST['lc_nonce'];

		if (!wp_verify_nonce($nonce, 'lc_forgot_password_nonce')) {
			wp_die(__("Invalid Nonce", "labal-courrier"));
		}

		//We shall SQL escape all inputs  
		$email = esc_sql($_REQUEST['email']);

		$user = get_user_by('email', $email);
		if (!$user) {
			$errors[] = __("There is no account associated with this email", "labal-courrier");
		}

		$key = get_password_reset_key($user);

		if ($user && is_wp_error($key)) {
			$errors[] = __("Reset key is not generated. Please try again.", "labal-courrier");
		}

		if (count($errors)) {
			// $errors['old_values'] = $_POST;
			set_transient("request_$nonce", $errors, (5000 * 120));
			wp_safe_redirect(add_query_arg(array(
				'request_status' => "error",
				'request_id' => "request_$nonce",
			), site_url() . '/forgot-password'));
			exit;
		}

		// send email with reset password link
		$to = $email;
		$subject = 'Reset Password';
		$headers = array('Content-Type: text/html; charset=UTF-8');

		$msg  = __('Hello!', 'labal-courrier') . "\r\n\r\n";
		$msg .= sprintf(__('You asked us to reset your password for your account using the email address %s.', 'labal-courrier'), $email) . "\r\n\r\n";
		$msg .= __("If this was a mistake, or you didn't ask for a password reset, just ignore this email and nothing will happen.", 'labal-courrier') . "\r\n\r\n";
		$msg .= __('To reset your password, visit the following address:', 'labal-courrier') . "\r\n\r\n";
		$msg .= site_url("reset-password?key=$key&login=" . rawurlencode($user->user_login)) . "\r\n\r\n";
		$msg .= __('Thanks!', 'labal-courrier') . "\r\n";

		add_filter('wp_mail_from_name', function ($name) {
			return LABAL_COURRIER_EMAIL_FROM_NAME;
		});
		wp_mail($to, $subject, $msg, $headers);
		remove_filter('wp_mail_from_name', function () {
		});

		// verification is successful 
		wp_safe_redirect(add_query_arg(array(
			'request_status' => "success",
		), site_url() . '/forgot-password'));
		exit;
	}

	public function lc_reset_password_action()
	{
		global $wpdb;

		$errors = [];
		$nonce = $_POST['lc_nonce'];

		if (!wp_verify_nonce($nonce, 'lc_reset_password_nonce')) {
			wp_die(__("Invalid Nonce", "labal-courrier"));
		}

		//We shall SQL escape all inputs  
		$token_key = esc_sql($_REQUEST['token_key']);
		$user_login = esc_sql($_REQUEST['user_login']);
		$new_password = esc_sql($_REQUEST['new_password']);
		$confirm_password = esc_sql($_REQUEST['confirm_password']);

		$user = check_password_reset_key($token_key, $user_login);

		if (!$user || is_wp_error($user)) {
			if ($user && $user->get_error_code() === 'expired_key') {
				$errors[] = __("Token has expired.", "labal-courrier");
			} else {
				$errors[] = __("Token is not valid.", "labal-courrier");
			}
		}

		if ($new_password != $confirm_password) {
			$errors[] = __("New password and confirm password do not match", "labal-courrier");
		}

		if (count($errors)) {
			// $errors['old_values'] = $_POST;
			set_transient("request_$nonce", $errors, (5000 * 120));
			wp_safe_redirect(add_query_arg(array(
				'request_status' => "error",
				'key' => $token_key,
				'login' => $user_login,
				'request_id' => "request_$nonce",
			), site_url() . '/reset-password'));
			exit;
		}

		// Parameter checks OK, reset password
		reset_password($user, $new_password);

		wp_safe_redirect(add_query_arg(array(
			'request_status' => "success",
		), site_url() . '/reset-password'));
		exit;
	}

	public function get_tracking_details()
	{

		$request_data = $_REQUEST;
		$html = '';

		$tracking_number = $request_data['wbn'];

		if ($request_data['carrier_id'] == 'dhl') {
			require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/api/dhl-curl/DHL.php';
			$dhl = new DHL('labalFR', 'Q^2oU$8lI#1a', '950455439');
			$result = $dhl->getTrackingDetails($request_data['wbn']);

			if (isset($result['error']) && $result['error'] == 1) {
				echo $result['message'];
				exit();
			}

			$html = $this->generateHtml($result);
		} else if ($request_data['carrier_id'] == 'ups') {
			$result = $this->ups->getTrackingDetails($request_data['wbn']);

			if (isset($result['error']) && $result['error'] == 1) {
				echo $result['message'];
				exit();
			}

			global $wpdb, $table_prefix;
			$shipment = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE tracking_number = '$tracking_number'");
			$html = $this->generateUPSHtml($result, $shipment);
		}

		echo $html;
		die;
	}

	private function generateUPSHtml($data, $shipment)
	{
?>
		<div class="row mb-3">
			<div class="col-6">
				<h4><?= esc_html_e("Origin", "labal-courrier") ?></h4>
				<p><?= $shipment->sender_city ?><br>
					<?= $shipment->sender_postcode ?><br>
					<?= lc_get_country_by_code($shipment->sender_country_code) ?></p>
			</div>
			<div class="col-6">
				<h4><?= esc_html_e("Destination", "labal-courrier") ?></h4>
				<p><?= $shipment->receiver_city ?><br>
					<?= $shipment->receiver_postcode ?><br>
					<?= lc_get_country_by_code($shipment->receiver_country_code) ?></p>
			</div>
		</div>
		<table>
			<tr>
				<th><?= esc_html_e("Date and Time", "labal-courrier") ?></th>
				<th><?= esc_html_e("Status", "labal-courrier") ?></th>
			</tr>
			<?php
			if (isset($data['shipment_events']->Activity) && is_array($data['shipment_events']->Activity)) {
				foreach ($data['shipment_events']->Activity as $event) {
			?>
					<tr>
						<td><?= date('Y-m-d', strtotime($event->Date)) . ' ' . date('H:i:s', strtotime($event->Time)) ?></td>
						<td><?= $event->Status->Description ?></td>
					</tr>
			<?php
				}
			}
			?>
		</table>
	<?php
	}

	private function generateHtml($data)
	{
	?>
		<div class="row mb-3">
			<div class="col-6">
				<h4><?= esc_html_e("Origin", "labal-courrier") ?></h4>
				<p><?= $data['shipper']['City'] ?? '' ?><br>
					<?= $data['shipper']['PostalCode'] ?? '' ?><br>
					<?= lc_get_country_by_code($data['shipper']['CountryCode']) ?? '' ?></p>
			</div>
			<div class="col-6">
				<h4><?= esc_html_e("Destination", "labal-courrier") ?></h4>
				<p><?= $data['consignee']['City'] ?? '' ?><br>
					<?= $data['consignee']['PostalCode'] ?? '' ?><br>
					<?= lc_get_country_by_code($data['consignee']['CountryCode']) ?? '' ?></p>
			</div>
		</div>
		<table>
			<tr>
				<th><?= esc_html_e("Date and Time", "labal-courrier") ?></th>
				<th><?= esc_html_e("Status", "labal-courrier") ?></th>
			</tr>
			<?php foreach ($data['shipment_events'] as $event) : ?>
				<tr>
					<td><?= $event['Date'] . ' ' . $event['Time'] ?></td>
					<td><?= $event['ServiceEvent']['Description'] ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php
	}

	public function get_location_format()
	{
		global $wpdb, $table_prefix;
		$request_data = $_REQUEST;

		$q = "SELECT * FROM " . $table_prefix . "postal_location ";
		$q .= "WHERE country_code = '" . $request_data['country'] . "' ";
		$q .= "limit 1";

		$results = $wpdb->get_results($q);
		var_dump($results);
		die;
	}

	public function search_by_postcode_or_city()
	{
		global $wpdb, $table_prefix;
		$request_data = $_REQUEST;

		$q = "SELECT `postcode`, `suburb`, `city`, `state_code` FROM " . $table_prefix . "postal_location ";
		$q .= "WHERE country_code = '" . $request_data['country'] . "' ";
		$q .= "AND (postcode like '%" . $request_data['search'] . "%' ";
		$q .= "OR suburb like '%" . $request_data['search'] . "%' ";
		$q .= "OR city like '%" . $request_data['search'] . "%' )";
		$q .= " limit 30";
		// $q .= "ORDER BY city ASC limit 12";

		$results = $wpdb->get_results($q);

		$r = [];
		foreach ($results as $key => $record) {
			$r[$key]['id'] = $record->postcode . '--' .  $record->suburb . '--' . $record->city . '--' . $record->state_code;
			$r[$key]['text'] = $record->postcode . ' ' .  $record->suburb . ', ' . $record->city;
		}
		echo json_encode(['results' => $r]);
		die;
	}

	public function search_by_postcode()
	{
		global $wpdb, $table_prefix;
		$request_data = $_REQUEST;

		$q = "SELECT DISTINCT `postcode` FROM " . $table_prefix . "postal_location ";
		$q .= "WHERE country_code = '" . $request_data['country'] . "' ";
		if ($request_data['city'] != '')
			$q .= "AND city like '%" . $request_data['city'] . "%' ";
		$q .= "AND (postcode like '" . $request_data['search'] . "%' OR postcode_non_format like '" . $request_data['search'] . "%')";
		// $q .= "limit 10";

		$results = $wpdb->get_results($q);
		// var_dump($results); die;
		$r = [];
		foreach ($results as $key => $record) {
			$r[$key]['id'] = $record->postcode;
			$r[$key]['text'] = $record->postcode;
		}
		echo json_encode(['results' => $r]);
		die;
	}

	public function search_by_suburb()
	{
		global $wpdb, $table_prefix;
		$request_data = $_REQUEST;

		$q = "SELECT DISTINCT `suburb` FROM " . $table_prefix . "postal_location ";
		$q .= "WHERE country_code = '" . $request_data['country'] . "' ";
		if ($request_data['city'] != '')
			$q .= "AND city like '%" . $request_data['city'] . "%' ";
		$q .= "AND suburb like '" . $request_data['search'] . "%'";
		// $q .= "limit 10";

		$results = $wpdb->get_results($q);
		// var_dump($results); die;
		$r = [];
		foreach ($results as $key => $record) {
			$r[$key]['id'] = $record->suburb;
			$r[$key]['text'] = $record->suburb;
		}
		echo json_encode(['results' => $r]);
		die;
	}

	public function search_by_city()
	{
		global $wpdb, $table_prefix;
		$request_data = $_REQUEST;

		$q = "SELECT DISTINCT `city` FROM " . $table_prefix . "postal_location";
		$q .= " WHERE country_code = '" . $request_data['country'] . "'";
		if ($request_data['postcode'] != '')
			$q .= " AND postcode like '" . $request_data['postcode'] . "%'";
		$q .= " AND city like '%" . $request_data['search'] . "%'";
		// $q .= "limit 10";

		$results = $wpdb->get_results($q);
		// var_dump($results); die;
		$r = [];
		foreach ($results as $key => $record) {
			$r[$key]['id'] = $record->city;
			$r[$key]['text'] = $record->city;
		}
		echo json_encode(['results' => $r]);
		die;
	}

	public function search_by_state()
	{
		global $wpdb, $table_prefix;
		$request_data = $_REQUEST;

		$q = "SELECT DISTINCT `state` FROM " . $table_prefix . "postal_location";
		$q .= " WHERE country_code = '" . $request_data['country'] . "'";
		if ($request_data['postcode'] != '')
			$q .= " AND postcode like '" . $request_data['postcode'] . "%'";
		if ($request_data['city'] != '')
			$q .= " AND city like '%" . $request_data['city'] . "%'";
		$q .= " AND `state` like '%" . $request_data['search'] . "%'";
		// $q .= "limit 10";

		$results = $wpdb->get_results($q);
		// var_dump($results); die;
		$r = [];
		foreach ($results as $key => $record) {
			$r[$key]['id'] = $record->state_code;
			$r[$key]['text'] = $record->state;
		}
		echo json_encode(['results' => $r]);
		die;
	}

	public function update_pickup()
	{
		global $wpdb, $table_prefix;
		$request_data = $_POST;
		$data = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '" . $request_data['shipment_id'] . "'");
		// print_r();
		// die;
		$dispatch_number = unserialize($data->shipment_created_response)['dispatch_confirmation_nummber'];
		$shipment = [
			'sender_first_name' => $data->sender_first_name,
			'sender_last_name' => $data->sender_last_name,
			'sender_company_name' => $data->sender_company_name,
			'sender_address' => unserialize($data->sender_address),
			'sender_country_code' => $data->sender_country_code,
			'sender_postcode' => $data->sender_postcode,
			'sender_city' => $data->sender_city,
			'sender_phone_number' => $data->sender_phone_number,
			'sender_email' => $data->sender_email,
			'sender_email' => $data->sender_email,
			'sender_trade_type' => $data->sender_trade_type,
			'sender_id_number' => $data->sender_id_number,

			'receiver_first_name' => $data->receiver_first_name,
			'receiver_last_name' => $data->receiver_last_name,
			'receiver_company_name' => $data->receiver_company_name,
			'receiver_address' => unserialize($data->receiver_address),
			'receiver_country_code' => $data->receiver_country_code,
			'receiver_postcode' => $data->receiver_postcode,
			'receiver_city' => $data->receiver_city,
			'receiver_phone_number' => $data->receiver_phone_number,
			'receiver_email' => $data->receiver_email,
			'receiver_trade_type' => $data->receiver_trade_type,
			'receiver_id_number' => $data->receiver_id_number,

			'dispatch_date' => $data->dispatch_date,
			'package_type' => ($data->package_type == 'Package') ? 'NON_DOCUMENTS' : 'DOCUMENTS',
			'packages' => unserialize($data->packages),
			'shipment_description' => $data->shipment_description,
			'insurance' => $data->insurance,
			'insurance_value' => $data->insurance_value,
			'items' => unserialize($data->items),
			'total_customs_value' => $data->total_customs_value,
			'export_reason_type' => $data->export_reason_type,
			'is_pickup_required' => $data->is_pickup_required,
			'pickup_date' => $data->pickup_date,
			'shipment_creator_fname' => $data->shipment_creator_fname,
			'shipment_creator_lname' => $data->shipment_creator_lname,
			'shipment_creator_email' => $data->shipment_creator_email,
			'selected_carrier_id' => $data->selected_carrier_id,
			'is_shipment_created' => $data->is_shipment_created,
			'total_amount' => $data->total_amount,
			'shipment_type' => $data->shipment_type,
			'carrier_rate' => $data->carrier_rate,
			'vat_amount' => $data->vat_amount,

			'get_quote_result' => $data->get_quote_result,

			'dispatch_number' => $dispatch_number,
			'e_pickup_time' => $request_data['e_pickup_time'],
			'l_pickup_time' => $request_data['l_pickup_time'],
		];
		if ($this->dhl->updatePickup($shipment)) {
			echo json_encode(['status' => 'success', 'message' => 'Pickup successfully updated']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Pickup not updated']);
		}
		wp_die();
	}

	public function lc_do_checkout()
	{
		$request_data = $_POST;
		$shipment_id = $request_data['shipment_id'];

		// if (
		// 	!isset($request_data['i_ack_1'])
		// 	|| !isset($request_data['i_ack_2'])
		// 	|| !$request_data['i_ack_3']
		// ) {
		// 	wp_safe_redirect(add_query_arg(array(
		// 		'shipment_id' => $shipment_id,
		// 	), site_url() . '/labal-courrier-checkout'));
		// 	exit;
		// }

		if ($this->store_billing_details($request_data)) {
			wp_safe_redirect(add_query_arg(array(
				'pg' => 'pg_imp_init',
				'order_id' => $shipment_id,
			), site_url()));
			exit;
		}
	}

	private function store_billing_details($data)
	{
		global $wpdb, $table_prefix;
		$insert_b_d = $wpdb->insert($table_prefix . 'paygreen_billing_details', array(
			'shipment_id' => $data['shipment_id'],
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'company' => $data['company'] ?? '',
			'email' => $data['email'],
			'address' => $data['address'],
			'city' => $data['city'],
			'country_code' => $data['country'],
		));
		return $insert_b_d ? true : false;
	}

	private function validate_customer_declaration_details($d)
	{
		global $wpdb, $table_prefix;

		$errors = [];
		$output = ['request_data' => $d, 'errors' => $errors];

		if (empty($d) || !isset($d['shipment_id'])) {
			$output['errors'][] = 'Please provide all the data';
			return $output;
		}

		// get shipping details 
		$shipments = $wpdb->get_row($wpdb->prepare("select * from {$table_prefix}lc_shipments where lc_shipment_ID = %s", [$d['shipment_id']]));

		if (empty($shipments)) return $output;

		// if ($d['is_eu_shipment'] && ($d['export_reason_type'] == '' || strlen($d['export_reason_type']) > 35)) {
		// 	$output['errors'][] = __("Please provide shipment summery within 35 characters", "labal-courrier");
		// 	return $output;
		// }

		if ($shipments->package_type == 'Package' && !$d['is_eu_shipment'] && !isset($d['item'])) {
			$output['errors'][] = 'Le contenu du package ne peut pas être vide';
			return $output;
		}

		// do validation for package_type = Package
		if ($shipments->package_type == 'Package' && !$d['is_eu_shipment']) {
			// $packages = unserialize($shipments->packages);

			$items = $d['item'];

			foreach ($d['item'] as $key => $item) {

				// replace comma with point 
				$d['item'][$key]['quantity'] = str_replace(',', '.', $item['quantity']);
				$d['item'][$key]['item_value'] = str_replace(',', '.', $item['item_value']);

				// validate if input is number
				if (
					!is_numeric($d['item'][$key]['quantity']) || $d['item'][$key]['quantity'] < 0 ||
					!is_numeric($d['item'][$key]['item_value']) || $d['item'][$key]['item_value'] < 0
				) {
					$output['errors'][] = 'Veuillez fournir un nombre valide pour Quantité, Valeur/unité, Poids Net et Poids Brut';
				}
			}

			// validate insurance value 
			$total_value  = array_reduce(
				$items,
				fn ($previous, $current) => $previous + $current['item_value'] * $current['quantity']
			);

			if ($d['insurance'] == 1 &&  $d['insurance_value'] > $total_value) {
				$output['errors'][] = 'Le prix de votre contenu est inférieur au prix de votre valeur assuré';
			}
		}


		return $output;
	}

	public function submit_customs_declaration()
	{
		$request_data = $_POST;
		$current_language = $request_data['current_language'];
		$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

		// if (!function_exists('wp_handle_upload')) require_once(ABSPATH . 'wp-admin/includes/file.php');

		// $destination = LABAL_COURRIER_PLUGIN_PATH . 'docs/tmp-invoice/Facture-en-douane-' . $request_data['shipment_id'] . '.pdf';

		// $source = $_FILES['input_upload_invoice']['tmp_name'];
		// $movefile = move_uploaded_file($source, $destination);

		list(
			'request_data' => $request_data,
			'errors' => $errors
		) = $this->validate_customer_declaration_details($request_data);

		if (count($errors) > 0) {
			$nonce = 'cdd_nonce';

			$errors['old_values'] = $request_data;

			set_transient("request_$nonce", $errors, (5000 * 120));

			wp_safe_redirect(add_query_arg(array(
				'shipment_id' => $request_data['shipment_id'],
				'pt' => $request_data['package_type'],
				'request_status' => "error",
				'request_id' => "request_$nonce",
			), $lc_site_url  . '/customs-declaration'));
			exit;
		}

		$lc_shipment = $this->get_lc_shipment_by_id($request_data['shipment_id']);

		$packages = unserialize($lc_shipment['packages']);

		$this->store_customer_declaration_details($request_data, $packages);

		$lc_shipment['col_country'] 	= $lc_shipment['sender_country_code'];
		$lc_shipment['col_postcode'] 	= $lc_shipment['sender_postcode'];
		$lc_shipment['col_city'] 		= $lc_shipment['sender_city'];
		$lc_shipment['col_address'] 	= unserialize($lc_shipment['sender_address']);
		$lc_shipment['del_country'] 	= $lc_shipment['receiver_country_code'];
		$lc_shipment['del_postcode'] 	= $lc_shipment['receiver_postcode'];
		$lc_shipment['del_city'] 		= $lc_shipment['receiver_city'];
		$lc_shipment['del_address'] 	= unserialize($lc_shipment['receiver_address']);
		$lc_shipment['package'] 		= unserialize($lc_shipment['packages']);
		// die('adsafas');
		$rates = $this->get_carrier_rate($lc_shipment, $lc_shipment['selected_carrier_id']);

		if ($lc_shipment['selected_carrier_id'] == 'CARRIER_DHL') {
			$lc_rate = $this->apply_labal_courrier_margin([$rates], $lc_shipment, true);
		} else if ($lc_shipment['selected_carrier_id'] == 'CARRIER_UPS') {
			$lc_rate = array();
			$lc_rate[0] = $rates;

			if ($lc_shipment['col_country'] == LC_ORG_COUNTRY && $lc_shipment['del_country'] == LC_ORG_COUNTRY) {
				$shipment_type = LC_Rate::LC_NOR;
			} else {
				$shipment_type = '';
				if ($lc_shipment['del_country'] == LC_ORG_COUNTRY) {
					$shipment_type = LC_Rate::LC_IMP;
				} elseif ($lc_shipment['col_country'] == LC_ORG_COUNTRY) {
					$shipment_type = LC_Rate::LC_EXP;
				} else {
					$shipment_type = LC_Rate::LC_NOR;
				}
			}
			$lc_rate['shipment_type'] = $shipment_type;
		}

		$error = (isset($lc_rate[0]['error']) && $lc_rate[0]['error'] == 1) ? 1 : 0;

		$rate_updated = $this->update_carrier_result($rates, $lc_rate, $request_data['shipment_id']);
		// var_dump($rate_updated); die;

		// check if dispatch date and order date are same. if same, then redirect to check out page, and set is_pickup_required to 0
		$dispatch_date = $lc_shipment['dispatch_date'];
		$today = date("Y-m-d");
		// if ($dispatch_date == $today) {
		// 	$pData['is_pickup_required'] = 0;
		// 	$pData['pickup_location'] = 'Reception';
		// 	$pData['special_pickup_instructions'] = '';
		// 	$pData['earliest_pickup_time'] = '10:00';
		// 	$pData['latest_pickup_time'] = '10:00';
		// 	$pData['shipment_id'] = $request_data['shipment_id'];

		// 	$this->store_pickup_details($pData);

		// 	wp_safe_redirect(add_query_arg(array(
		// 		'shipment_id' => $request_data['shipment_id'],
		// 	), site_url() . '/labal-courrier-checkout'));
		// 	exit;
		// }

		// delete transient so when goes back to this page after success, no error shows up
		delete_transient("request_cdd_nonce");

		// wp_safe_redirect(add_query_arg(array(
		// 	'shipment_id' => $request_data['shipment_id'],
		// 	'e' => $error
		// ), site_url() . '/schedule-pickup'));
		wp_safe_redirect(add_query_arg(array(
			'shipment_id' => $request_data['shipment_id'],
		), $lc_site_url  . '/labal-courrier-checkout'));
		exit;
	}

	private function update_carrier_result($result, $lc_rate, $shipment_id)
	{
		$amount_with_commission = $lc_rate[0]['amount'];
		$vat_amount = isset($lc_rate[0]['vat_amount']) ? $lc_rate[0]['vat_amount'] : '';
		$carrier_rate = $lc_rate[0]['carrier_rate'];
		$shipment_type = $lc_rate['shipment_type'];

		global $wpdb, $table_prefix;
		return $wpdb->update(
			$table_prefix . 'lc_shipments',
			array(
				// 'package_type' => $data['package_type'],
				'get_quote_result' => serialize($lc_rate[0]),
				'total_amount' => $amount_with_commission,
				'vat_amount' => $vat_amount,
				'carrier_rate' => $carrier_rate,
				'shipment_type' => $shipment_type,
			),
			array('lc_shipment_id' => $shipment_id)
		);
	}

	private function get_lc_shipment_by_id($shipment_id)
	{
		global $wpdb, $table_prefix;
		$lc_shipment = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '$shipment_id'", ARRAY_A);
		return $lc_shipment;
	}

	private function store_customer_declaration_details($data, $packages)
	{
		global $wpdb, $table_prefix;

		// calculate the package weight and distribute to the items equally 
		$total_weight  = array_reduce(
			$packages,
			fn ($previous, $current) => $previous + $current['weight']
		);

		if (isset($data['item'])) {

			$total_line_item  = array_reduce(
				$data['item'],
				fn ($previous, $current) => $previous + $current['quantity']
			);

			$per_item_weight = round($total_weight / $total_line_item, 2);

			// populate items with $per_item_weight
			foreach ($data['item'] as $key => $item) {
				$data['item'][$key]['net_weight'] = round($per_item_weight * $item['quantity'], 2);
				$data['item'][$key]['gross_weight'] = round($per_item_weight * $item['quantity'], 2);
			}
		}

		return $wpdb->update(
			$table_prefix . 'lc_shipments',
			array(
				// 'package_type' => $data['package_type'],
				'items' => (isset($data['item'])) ? serialize($data['item']) : '',
				'total_customs_value' => $data['total_customs_value'],
				// 'have_own_invoice' => $data['have_own_invoice'],
				// 'insurance_value' => $data['insurance_value'],
				'export_reason_type' => isset($data['export_reason_type']) ? $data['export_reason_type'] : '',
				'shipment_description' => $data['shipment_description'],
				'remarks' => ($data['remarks']),
			),
			array('lc_shipment_ID' => $data['shipment_id'])
		);
	}

	public function stripe_return_handle()
	{
		global $wpdb, $table_prefix;

		require_once LABAL_COURRIER_PLUGIN_PATH . 'includes/stripe/init.php';

		$stripe = new \Stripe\StripeClient(
			$this->stripeSecretKey
		);

		$session_id = $_GET['session_id'];
		$order_id = $_GET['order_id'];

		$current_language = get_locale();
		$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

		$restriveSession = $stripe->checkout->sessions->retrieve(
			$session_id,
			[]
		);

		if (isset($restriveSession->customer) && !empty($restriveSession->customer) && isset($restriveSession->payment_status) && $restriveSession->payment_status == 'paid') {
			$stripeId = $restriveSession->customer;

			$payments_table = $wpdb->prefix . 'paygreen_payments';
			$insert_imprint = $wpdb->insert($payments_table, array(
				'payment_id' => $restriveSession->payment_intent,
				'customer_id' => $restriveSession->customer_email,
				'payment_amount' => floatval(intval($restriveSession->amount_total) / 100),
				'payment_currency' => 'EUR',
				'payment_type' => 'one_time',
				'payment_state' => $restriveSession->payment_status,
				'note' => 'note',
				'transaction_id' => $order_id,
				'is_test' => 'live'
			));

			// update user available credit in case discount is implemented
			$shipment_details = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '$order_id'", ARRAY_A);

			if (is_user_logged_in() && $shipment_details['refn_discount'] > 0) {
				$user_id = get_current_user_id();
				$current_credit = get_user_meta($user_id, 'mnfr_referral_credit', true);

				if ((int)$current_credit >= $shipment_details['refn_discount']) {
					$updated_credit = (int)$current_credit - $shipment_details['refn_discount'];
				} else {
					$updated_credit = 0;
				}

				// update credit of the referrer user 
				update_user_meta($user_id, 'mnfr_referral_credit', $updated_credit);
			}

			wp_safe_redirect(add_query_arg(array(
				'order_id' => $order_id,
				's' => 's', //status = success
			), $lc_site_url . '/finalize-shipment'));
			exit();
		} else {
			wp_safe_redirect(add_query_arg(array(
				'order_id' => $order_id,
				's' => 'f', //status = fail
			), $lc_site_url . '/finalize-shipment'));
			exit();
		}
	}

	public function save_receiver_address()
	{
		global $wpdb, $table_prefix;

		$res = [];
		$res['status'] = 'error';
		$res['message'] = __("Something went wrong. Please try again", "labal-courrier");

		$user = wp_get_current_user();
		$user_id = $user->ID;

		$data = $_POST['data'];

		$sender_first_name = trim($data['receiver_first_name']);
		$sender_last_name = trim($data['receiver_last_name']);
		$sender_phone_number = trim($data['receiver_phone_number']);
		$sender_email = trim($data['receiver_email']);
		$col_country = trim($data['del_country']);
		$col_postcode_or_city = trim($data['del_postcode_or_city']);
		$sender_address = trim($data['address_receiver']);

		$insert_data = array(
			'sender_first_name' => $sender_first_name,
			'sender_last_name' => $sender_last_name,
			'sender_phone_number' => $sender_phone_number,
			'sender_email' => $sender_email,
			'col_country' => $col_country,
			'col_postcode_or_city' => $col_postcode_or_city,
			'sender_address' => $sender_address
		);

		// insert data into table
		$table = $wpdb->prefix . 'lc_shipping_addresses';

		$insert_data['user_id'] = $user_id;
		$data = $insert_data;
		$format = array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d');
		$wpdb->insert($table, $data, $format);
		$insert_id = $wpdb->insert_id;

		if (!is_wp_error($insert_id)) {
			$res['status'] = 'success';
			$res['message'] = __("Address has been saved succesfully", "labal-courrier");
		}

		echo json_encode($res);
		wp_die();
	}

	public function save_sender_address()
	{
		global $wpdb, $table_prefix;

		$res = [];
		$res['status'] = 'error';
		$res['message'] = __("Something went wrong. Please try again", "labal-courrier");

		$user = wp_get_current_user();
		$user_id = $user->ID;

		$data = $_POST['data'];

		$sender_first_name = trim($data['sender_first_name']);
		$sender_last_name = trim($data['sender_last_name']);
		$sender_phone_number = trim($data['sender_phone_number']);
		$sender_email = trim($data['sender_email']);
		$col_country = trim($data['col_country']);
		$col_postcode_or_city = trim($data['col_postcode_or_city']);
		$sender_address = trim($data['address_sender']);

		$insert_data = array(
			'sender_first_name' => $sender_first_name,
			'sender_last_name' => $sender_last_name,
			'sender_phone_number' => $sender_phone_number,
			'sender_email' => $sender_email,
			'col_country' => $col_country,
			'col_postcode_or_city' => $col_postcode_or_city,
			'sender_address' => $sender_address
		);

		// insert data into table
		$table = $wpdb->prefix . 'lc_shipping_addresses';

		$insert_data['user_id'] = $user_id;
		$data = $insert_data;
		$format = array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d');
		$wpdb->insert($table, $data, $format);
		$insert_id = $wpdb->insert_id;

		if (!is_wp_error($insert_id)) {
			$res['status'] = 'success';
			$res['message'] = __("Address has been saved succesfully", "labal-courrier");
		}

		echo json_encode($res);
		wp_die();
	}

	public function create_stripe_session()
	{
		global $wpdb, $table_prefix;


		require_once LABAL_COURRIER_PLUGIN_PATH . 'includes/stripe/init.php';

		$stripe = new \Stripe\StripeClient(
			$this->stripeSecretKey
		);

		$res = [];
		$res['status'] = 'error';

		$data = $_POST['data'];
		$shipment_id = $data['shipment_id'];

		$current_language = $data['current_language'];
		$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

		$shipment_details = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '$shipment_id'", ARRAY_A);
		$total_amount = floatval($shipment_details['total_amount']);

		// if referral credit is available and shipment amount is more than $this->referral_code_limit, then apply discount 
		if ($shipment_details['package_type'] == 'Package' &&  $total_amount > $this->referral_code_limit && is_user_logged_in()) {
			$user_id = get_current_user_id();
			$available_credit = get_user_meta($user_id, 'mnfr_referral_credit', true);

			if ($available_credit && $available_credit >= $this->referral_code_discount) {
				$total_amount = $total_amount - $this->referral_code_discount;

				// update database - to keep record that the shipment has discount
				$wpdb->update(
					$table_prefix . 'lc_shipments',
					array(
						'refn_discount' => $this->referral_code_discount,
					),
					array('lc_shipment_ID' => $shipment_id),
				);
			}
		}


		$amount = $total_amount * 100;

		$session = $stripe->checkout->sessions->create([
			'success_url' => $lc_site_url . '/finalize-shipment?action=stripe_return&order_id=' . $shipment_id . '&session_id={CHECKOUT_SESSION_ID}',
			'cancel_url' => $lc_site_url . '/finalize-shipment?order_id=' . $shipment_id . '&s=f',
			// 'customer_email' => $data['email'],
			'line_items' => [[
				'price_data' => [
					'currency' => 'EUR',
					'unit_amount' => $amount,
					'product_data' => [
						'name' => 'Frais d’expédition - Mon Courrier de France'
					],
				],
				'quantity' => 1,
			]],
			'payment_intent_data' => [
				'setup_future_usage' => 'off_session'
			],
			'mode' => 'payment',
		]);

		if (isset($session['id']) && !empty($session['id'])) {

			$wpdb->insert($table_prefix . 'paygreen_billing_details', array(
				'shipment_id' => $data['shipment_id'],
				'first_name' => $data['first_name'] ?? '',
				'last_name' => $data['last_name'] ?? '',
				'company' => $data['company'] ?? '',
				'email' => $data['email'],
				'address' => $data['address'],
				'city' => $data['city'] ?? '',
				'country_code' => $data['country'] ?? '',
			));

			$stripeSessionId = $session['id'];
			$res['status'] = 'success';
			$res['stripeSessionId'] = $stripeSessionId;
		} else {
			$res['msg'] = 'Failed to create stripe session!';
		}

		echo json_encode($res);
		wp_die();
	}

	public function update_credit_on_reference()
	{
		if (is_user_logged_in()) {
			$current_user_id = get_current_user_id();

			$referred_by_id = get_user_meta($current_user_id, 'mnfr_referred_by', true);
			$shipping_sixty_done = get_user_meta($current_user_id, 'shipping_sixty_done', true);

			if ($referred_by_id && $shipping_sixty_done != 1) {
				$current_credit = get_user_meta($referred_by_id, 'mnfr_referral_credit', true);

				$updated_credit = $this->referral_code_discount;
				if ($current_credit) $updated_credit = (int)$current_credit + $this->referral_code_discount;

				// update credit of the referrer user 
				update_user_meta($referred_by_id, 'mnfr_referral_credit', $updated_credit);

				// set shipping_sixty_done to 1 so as to prevent add credit later 
				update_user_meta($current_user_id, 'shipping_sixty_done', 1);

				// send email to referer user 
				$sponsor_info = get_userdata($referred_by_id);

				$to = $sponsor_info->user_email;
				$subject = "Félicitations, vous avez reçu " . $this->referral_code_discount . " EUR à dépenser avec Mon courrier de France";

				ob_start();
				include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/email-template-referral.php';
				$message = ob_get_clean();

				$content_type = function () {
					return 'text/html';
				};
				add_filter('wp_mail_content_type', $content_type);
				wp_mail($to, $subject, $message);
				remove_filter('wp_mail_content_type', $content_type);
			}
		}
	}

	public function create_shipment_order()
	{
		global $wpdb, $table_prefix;
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		unset($_SESSION['quote_id']);
		deleteQuoteID();

		$order_id = $_POST['order_id'];
		$payment = $wpdb->get_row("SELECT * FROM " . $table_prefix . "paygreen_payments WHERE transaction_id = '$order_id'");

		// if (!is_null($payment) && $payment->payment_state == "SUCCESSED") {
		if (!is_null($payment) && $payment->payment_state == "paid") {
			// die('aaa');
			// if ($payment->payment_state == "REFUSED") {
			$shipment_id = $payment->transaction_id;
			// $shipment_id = $order_id; // bypass payment
			// var_dump ($shipment_id); die;
			// echo "SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '$shipment_id'"; die;
			$shipment = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '$order_id'");
			// $shipment = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '$shipment_id'");

			if ($shipment->is_shipment_created != 1) {
				$result = $this->create_shipment_on_carrier($shipment);

				// do calculation for the reference Code
				if (isset($result['label']) && $shipment->total_amount > $this->referral_code_limit) {
					$this->update_credit_on_reference();
				}
			}

			if (isset($result['label']) || $shipment->is_shipment_created) {
				if (isset($result['label'])) {
					if (!$shipment->is_shipment_created) {
						$update_response = $wpdb->update(
							$table_prefix . 'lc_shipments',
							array(
								'user_id' => is_user_logged_in() ? get_current_user_id() : null,
								'is_shipment_created' => 1,
								'tracking_number' => $result['dhl_shipment_id'],
								'shipment_created_response' => serialize([
									'package_result' => $result['package_result'],
									'dhl_shipment_id' => $result['dhl_shipment_id'],
									'pickup_details' => $result['pickup_details'],
									'dispatch_confirmation_nummber' => $result['dispatch_confirmation_nummber'],
								]),
							),
							array('lc_shipment_ID' => $shipment_id),
						);

						$this->save_and_send_documents($result, $shipment_id, $shipment, $result['waybill_number']);
					}

					// $pickup_cutoff_time = new DateInterval($result['pickup_details']['CutoffTimeOffset']);
					// $pickup_cutoff_time = ISO8601ToMinutes($result['pickup_details']['CutoffTimeOffset']);

					// get invoice row, in order to get invoice number 
					$invoices = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_invoices WHERE shipment_id = '$order_id'");

					echo json_encode(
						[
							"status" => 'success',
							"id" => $result['dhl_shipment_id'],
							"invoice_no" => $invoices->inv_number,
							"lc_shipment_ID" => $shipment->lc_shipment_ID,
							"package_type" => $shipment->package_type,
							"carrier_dir" => strtolower(str_replace('CARRIER_', '', $shipment->selected_carrier_id)),
						]
					);
				} else {
					if ($shipment->is_shipment_created) {
						// get invoice row, in order to get invoice number 
						$invoices = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_invoices WHERE shipment_id = '$order_id'");

						echo json_encode(
							[
								"status" => 'success',
								"id" => $shipment->tracking_number,
								"invoice_no" => $invoices->inv_number,
								"lc_shipment_ID" => $shipment->lc_shipment_ID,
								"package_type" => $shipment->package_type,
								"carrier_dir" => strtolower(str_replace('CARRIER_', '', $shipment->selected_carrier_id)),
							]
						);
					} else {
						echo json_encode(
							[
								"status" => 'fail',
							]
						);
					}
				}
				die;
			} else {
				echo json_encode(
					[
						"status" => 'fail',
					]
				);
				die;
			}
		}
	}

	private function generate_custom_invoice($dir, $shipment_data, $waybill_number, $api_response)
	{
		global $wpdb, $table_prefix;

		$sender_country_name =  lc_get_country_by_code($shipment_data->sender_country_code);
		$receiver_country_name =  lc_get_country_by_code($shipment_data->receiver_country_code);
		$sender_trade_type =  $shipment_data->sender_trade_type == "PR" ? "Private" : "Professional";
		$receiver_trade_type =  $shipment_data->receiver_trade_type == "PR" ? "Private" : "Professional";
		$sender_vat =  $shipment_data->sender_tva_number != "" ? $shipment_data->sender_tva_number : "";
		$sender_eori =  $shipment_data->sender_eori_number != "" ? $shipment_data->sender_eori_number : "";
		$receiver_vat =  $shipment_data->receiver_tva_number != "" ? $shipment_data->receiver_tva_number : "";
		$receiver_eori =  $shipment_data->receiver_eori_number != "" ? $shipment_data->receiver_eori_number : "";
		$seletected_carrier = str_replace('CARRIER_', '', $shipment_data->selected_carrier_id);

		$mpdf = get_mpdf();

		$html = '<!DOCTYPE html>
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
			
                <style>
                    body {background: #fff;font-family: \'Open Sans\', sans-serif;}
                    page[size="A4"] {background: white;width: 21cm;height: 29.7cm;display: block;margin: 0 auto;margin-bottom: 0.5cm;					}
                    @media print {body,page[size="A4"] {margin: 0;box-shadow: 0;}}
                    .rounded {height: 120mm;}
                    img {width: auto;height: 35mm;object-fit: contain;}
            
                    .para {
                        text-align: right;
            
                    }
            
                    .para2 {
                        text-align: left;
            
                    }
            
                    .p1 {
                        font-weight: 900;
                        font-size: 18px;
                        line-height: 1;
                    }
            
                    .p2 {
                        font-size: 12px;
						line-height: .5;
                    }
            
                    .p3 {
                        font-size: 12px;
                        font-weight: 900;
						line-height: 1;
                    }
            
                    .footer {
                        text-align: center;
                        margin-top: 241px;
            
                    }

                    .flex-container {
                        display: flex;
                        justify-content: space-between;
                    }

                    table, th, td {
                        border: 1px solid #333;
                        border-collapse: collapse;
                      }
                </style>
            </head>
            
            <body>
                <page size="A4" layout="portrait">';

		$html .= '<p class="p1">Customs Invoice</p>
        <div style="width: 100%; line-height: 1;">
            <div style="width: 40%; float: left;">
                <p class="p2">AWB No : ' . $waybill_number . '</p>
            </div>
            <div style="width: 40%; float: right;">
                <p class="p2">Invoice Date : ' . $shipment_data->dispatch_date . ' </p>
            </div>
        </div>';

		$html .= '
        <div style="width: 100%; line-height: 1;">
            <div style="width: 40%; float: left;">
                <p class="p3"><strong>Ship From :<strong/></p>
                <p class="p2">' . $shipment_data->sender_first_name . ' ' . $shipment_data->sender_last_name . '</p>
                <p class="p2">' . $shipment_data->sender_company_name . '</p>
                <p class="p2">' . unserialize($shipment_data->sender_address)[0]  . '</p>
                <p class="p2">' . $shipment_data->sender_postcode . ', ' . $shipment_data->sender_city . '</p>
                <p class="p2">' . $sender_country_name . '</p>
                <p class="p2">' . $shipment_data->sender_phone_number . '</p>
                <p class="p2">' . $shipment_data->sender_email . '</p>
            </div>
            <div style="width: 40%; float: right;">
				<p class="p3"><strong>Ship To:<strong/></p>
				<p class="p2">' . $shipment_data->receiver_first_name . ' ' . $shipment_data->receiver_last_name . '</p>
				<p class="p2">' . $shipment_data->receiver_company_name . '</p>
				<p class="p2">' . unserialize($shipment_data->receiver_address)[0]  . '</p>
				<p class="p2">' . $shipment_data->receiver_postcode . ', ' . $shipment_data->receiver_city . '</p>
				<p class="p2">' . $receiver_country_name . '</p>
				<p class="p2">' . $shipment_data->receiver_phone_number . '</p>
				<p class="p2">' . $shipment_data->receiver_email . '</p>
            </div>
        </div>
        ';

		$html .= '
        <div style="width: 100%; line-height: 1;">
            <div style="width: 40%; float: left;">
                <p class="p2">Trader type: ' . $sender_trade_type  . '</p>
                <p class="p2">VAT No: ' . $sender_vat . '</p>
                <p class="p2">EORI: ' . $sender_eori . '</p>
            </div>
            <div style="width: 40%; float: right;">
                <p class="p2">Trader type: ' . $receiver_trade_type . '</p>
                <p class="p2">VAT No: ' . $receiver_vat . '</p>
                <p class="p2">EORI: ' . $receiver_eori . '</p>
            </div>
        </div>
        ';

		if ($shipment_data->remarks != '') {
			$html .= '
			<div style="width: 100%; line-height: 1;">
				<p class="p2">Remarks: ' . $shipment_data->remarks . '</p>
			</div>
			';
		}


		$html .= '
        <div style="width: 100%;">     
            <table style="width: 100%;" cellspacing="0">

                <thead>
                    <tr style="background-color: #ccc;">
                        <th scope="col">Item</th>
                        <th scope="col">Description</th>
                        <th scope="col">Commodity<br>Code</th>
                        <th scope="col">Country<br>of Origin</th>
                        <th scope="col">Qty</th>
                        <th scope="col">Unit Value</th>
                        <th scope="col">Subtotal Value</th>
                    </tr>
                </thead>
                <tbody>';

		// line items 
		$total_value = 0;
		$total_weight = 0;
		$total_line_item = sizeof(unserialize($shipment_data->items));
		$total_unit = 0;
		foreach (unserialize($shipment_data->items) as $key => $item) {
			$html .= '<tr>
                        <td style="text-align: center;">' . ($key + 1) . '</td>
                        <td style="text-align: center;">' . $item['item_description'] . '</td>
                        <td style="text-align: center;">' . $item['commodity_code'] . '</td>
                        <td style="text-align: center;">' . lc_get_country_by_code($item['item_origin']) . '</td>
                        <td style="text-align: center;">' . $item['quantity'] . '</td>
                        <td style="text-align: center;">' . $item['item_value'] . ' €</td>
                        <td style="text-align: center;">' . $item['quantity'] * $item['item_value'] . ' €</td>
                    </tr>';

			$total_value +=  $item['quantity'] * $item['item_value'];
			$total_weight +=  $item['quantity'] * $item['gross_weight'];
			$total_unit += $item['quantity'];
		}

		$html .= '</tbody>
            </table>
        </div>
        ';

		$html .= '
        <div style="width: 100%; line-height: 1; padding-top: 20px;">
            <div style="width: 30%; float: left;">
                <p class="p2"><strong>Total goods value: <strong/></p>
                <p class="p2"><strong>Total invoice amount: <strong/></p>
                <p class="p2"><strong>Currency code: <strong/></p>
                <p class="p2"><strong>Terms of trade: <strong/></p>
                <p class="p2"><strong>Reason for export: <strong/></p>
            </div>
            <div style="width: 40%; float: left;">
                <p class="p2">' . $total_value . ' EUR</p>
                <p class="p2">' . $total_value . ' EUR</p>
                <p class="p2">EUR</p>
                <p class="p2">Delivered at place</p>
                <p class="p2">' . $shipment_data->export_reason_type . '</p>
            </div>
        </div>
        ';

		$html .= '
        <div style="width: 100%; line-height: 1;">
            <div style="width: 30%; float: left;">
                <p class="p2"><strong>Total line items: <strong/></p>
                <p class="p2"><strong>Total unit: <strong/></p>
            </div>
            <div style="width: 40%; float: left;">
                <p class="p2">' . $total_line_item . '</p>
                <p class="p2">' . $total_unit . '</p>
            </div>
        </div>
        ';

		$html .= '
        <div style="width: 100%; line-height: 1;">
            <div style="width: 30%; float: left;">
                <p class="p2"><strong>Duty/Taxes: <strong/></p>
                <p class="p2"><strong>Carrier: <strong/></p>
            </div>
            <div style="width: 40%; float: left;">
                <p class="p2">Receiver will pay</p>
                <p class="p2">' . $seletected_carrier . '</p>
            </div>
        </div>
        ';

		$html .= '</page>
        </body>
    </html>';

		// echo '<pre>';
		// print_r($html);
		// exit();

		$mpdf->WriteHTML($html);
		$mpdf->Output($dir . '/Facture-en-douane-' . $waybill_number . '.pdf', 'F');
	}
	private function generate_lc_invoice($dir, $shipment_data, $waybill_number, $api_response)
	{
		global $wpdb, $table_prefix;
		$billing_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_prefix}paygreen_billing_details WHERE shipment_id = %s", $shipment_data->lc_shipment_ID));
		// $billing_details = unserialize($shipment_data->shipment_created_response);
		// var_dump($tracking_number); die;

		// print_r($billing_details);
		// die;

		$last_invoice_number = $wpdb->get_row($wpdb->prepare("SELECT `inv_number` FROM {$table_prefix}lc_invoices ORDER BY ID DESC LIMIT 1"));

		if (!$last_invoice_number) {
			$current_inv_number = sprintf('%06d', 1);
		} else {
			$current_inv_number = sprintf('%06d', intval($last_invoice_number->inv_number) + 1);
		}

		$insert_invoice = $wpdb->insert(
			$table_prefix . 'lc_invoices',
			[
				'shipment_id' => $shipment_data->lc_shipment_ID,
				'inv_number' => $current_inv_number
			]
		);


		$total_amount = floatval($shipment_data->total_amount);
		$carrier_rate = floatval($shipment_data->carrier_rate);
		$refn_discount = floatval($shipment_data->refn_discount);
		$lc_commission = floatval($total_amount - $carrier_rate);

		$quote_result = unserialize($shipment_data->get_quote_result);
		$margin = $quote_result['labal_margin'];

		if ($refn_discount > 0) {
			// $margin = $quote_result['labal_margin'] - $refn_discount;
			$total_amount = $total_amount - $refn_discount;
			$quote_result['carrier_rate'] = $quote_result['carrier_rate'] - $refn_discount;
			$carrier_rate = $carrier_rate - $refn_discount;
		}

		$sender_country_name =  lc_get_country_by_code($shipment_data->sender_country_code);
		$receiver_country_name =  lc_get_country_by_code($shipment_data->receiver_country_code);
		$package_type =  strtolower($shipment_data->package_type);
		$dispatch_date =  $shipment_data->dispatch_date;
		$seletected_carrier = str_replace('CARRIER_', '', $shipment_data->selected_carrier_id);

		if ($shipment_data->vat_amount > 0 && $quote_result['is_vat_applicable_to_carrier'] == 1) {
			$margin_with_vat = $margin * 1.2;
			$carrier_total_without_vat = $quote_result['carrier_rate'] - $quote_result['carrier_vat'];
			$carrier_total_with_vat = $quote_result['carrier_rate'];

			$total_without_vat = $carrier_total_without_vat +  $margin;
			$total_with_vat = $carrier_total_with_vat +  $margin_with_vat;
			$total_vat = $total_with_vat - $total_without_vat;
		} else if ($shipment_data->vat_amount > 0) {
			$margin_with_vat = $margin * 1.2;
			$carrier_total_without_vat = $quote_result['carrier_rate'];
			$carrier_total_with_vat = $quote_result['carrier_rate'];

			$total_without_vat = $carrier_total_without_vat +  $margin;
			$total_with_vat = $carrier_total_with_vat +  $margin_with_vat;
			$total_vat = $total_with_vat - $total_without_vat;
		}


		$vat_amount = '';
		$date_today = date('d/m/Y');

		$mpdf = get_mpdf();

		$html = '<!DOCTYPE html>
			<html xmlns="http://www.w3.org/1999/xhtml">
			
			<head>

			<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
			
				<style>
					body {background: #fff;font-family: \'Open Sans\', sans-serif;}
					page[size="A4"] {background: white;width: 21cm;height: 29.7cm;display: block;margin: 0 auto;margin-bottom: 0.5cm;					}
					@media print {body,page[size="A4"] {margin: 0;box-shadow: 0;}}
					.rounded {height: 120mm;}
					img {width: auto;height: 35mm;object-fit: contain;}
			
					.para {
						text-align: right;
			
					}
			
					.para2 {
						text-align: left;
			
					}
			
					.p1 {
						font-weight: 800;
						font-size: 24px;
					}
			
					.p2 {
						font-size: 12px;
					}
			
					.p3 {
						font-size: 18px;
						font-weight: 800;
					}
			
					.footer {
						text-align: center;
						margin-top: 241px;
			
					}
				</style>
			</head>
			
			<body>
				<page size="A4" layout="portrait">
					<div style="width: 100%; padding: 20px 0;">
						<div style="width: 33%; float: left;">
							<img src="' . LABAL_COURRIER_PLUGIN_PATH . 'public/img/logo-square.png">
						</div>
						<div style="width: 33%; float: right;">
							<p class="p1">Facture - ' . $current_inv_number . '</p>
							<p class="p2">Date de facturation: ' . $date_today . '</p>
							<p class="p2">Échéance: ' . $date_today . '</p>
						</div>
					</div>
					<div style="width: 100%; padding: 20px 0; clear: both;">
						<div style="width: 33%; float: left;">
							<p class="p3">SAS LABAL</p>
							<p class="p2">149 Avenue du Maine</p>
							<p class="p2">75014 Paris</p>
							<p class="p2">www.moncourrierdefrance.com</p>
						</div>
						<div style="width: 33%; float: right;">';
		// <p class="p3">' . $billing_details->first_name . ' ' . $billing_details->last_name . '</p>';
		$html .= ($billing_details->company != '') ? '<p class="p3">' . $billing_details->company . '</p>' : '';
		$html .= '<p class="p2">' . $billing_details->email . '</p>';
		$html .= '<p class="p2">' . $billing_details->address . '</p>';
		$html .= '</div>
					</div>
					<div style="width: 100%; clear: both; padding: 20px 0;">
						<p style="">Envoi de ' . $package_type . ' via ' . $seletected_carrier . ' - ' . $sender_country_name . '/' . $receiver_country_name . ' - ' . $dispatch_date . '</p>

						<p>';

		foreach (unserialize($shipment_data->packages) as $package) :
			$html .= sprintf("Units %s - %skg - (%s x %s x %s cm) <br>", $package['qty'], $package['weight'], $package['length'], $package['width'], $package['height']);
		endforeach;

		$html .= '</p>
		<p>Waybill no: ' . $waybill_number . '</p>';

		if (isset($api_response['dispatch_confirmation_nummber']) && $api_response['dispatch_confirmation_nummber'] != '') {
			$html .= '<p>Pick-up number: ' . $api_response['dispatch_confirmation_nummber'] . '</p>';
		}

		$html .= '</div>
					<div style="width: 100%;">
			
						<table style="width: 100%; border-bottom: 1px solid #333" cellspacing="0">
			
							<thead>
								<tr style="background-color: #ccc;">
									<th scope="col">Description</th>
									<th scope="col">Date</th>
									<th scope="col">Qté</th>
									<th scope="col">Unité</th>
									<th scope="col">Prix unitaire</th>
									<th scope="col">TVA</th>
									<th scope="col">Montant</th>
								</tr>
							</thead>
							<tbody style="border-width: 0 0 1px 0; border-style: solid; border-color: #333;">
								<tr>
									<th scope="row">Prestation ' . $seletected_carrier . '</th>
									<td>' . $date_today . '</td>
									<td>1,00</td>
									<td>pce</td>';

		if ($shipment_data->vat_amount > 0 && $quote_result['is_vat_applicable_to_carrier'] == 1) {
			$html .= '<td>' . fr_number_format($carrier_total_without_vat) . ' €</td>
						<td>20,0 %</td>
						<td style="text-align: right;">' . fr_number_format($carrier_rate) . ' €</td>';
		} else {
			$html .= '<td>' . fr_number_format($carrier_rate) . ' €</td>
						<td>0,0 %</td>
						<td style="text-align: right;">' . fr_number_format($carrier_rate) . ' €</td>';
		}


		$html .= '</tr>
								<tr>
									<th scope="row-dark">Prestation Mon Courrier de France</th>
									<td>' . $date_today . '</td>
									<td>1,00</td>
									<td>pce</td>';
		if ($shipment_data->vat_amount > 0) {
			// $html .= '<td>' . fr_number_format($margin_without_tax) . ' €</td>';
			$html .= '<td>' . fr_number_format($margin) . ' €</td>';
			$html .= '<td>20,0 %</td>';
			$html .= '<td style="text-align: right;">' . fr_number_format($margin_with_vat) . ' €</td>';
		} else {
			$html .= '<td>' . fr_number_format($margin) . ' €</td>';
			$html .= '<td>0,0 %</td>';
			$html .= '<td style="text-align: right;">' . fr_number_format($margin) . ' €</td>';
		}

		// $html .= '<td style="text-align: right;">' . fr_number_format($margin) . ' €</td>';


		$html .= '	</tr>
			
							</tbody>
						</table>
					</div>
					<div style="margin-top: 20px;">
						<table align="right" cellspacing="0">';
		if ($shipment_data->vat_amount > 0) {
			$html .= '<tr>
							<td scope="col" style="text-align: left;font-weight: 600;padding-right: 20px;"><b>Total HT</b></td>
							<td scope="col" style="text-align: right;"><b>' . fr_number_format($total_without_vat) . ' €</b></td>
						</tr>
						<tr>
							<td scope="col" style="text-align: left;font-weight: 600;padding-right: 20px;"><b>TVA 20,0 %</b></td>
							<td scope="col" style="text-align: right;"><b>' . fr_number_format($total_vat) . ' €</b></td>
						</tr>';
		} else {
			$html .= '<tr>
							<td scope="col" style="text-align: left;font-weight: 600;padding-right: 20px;"><b>Total HT</b></td>
							<td scope="col" style="text-align: right;"><b>' . fr_number_format($total_amount) . ' €</b></td>
						</tr>
						<tr>
							<td scope="col" style="text-align: left;font-weight: 600;padding-right: 20px;"><b>TVA 0,0 %</b></td>
							<td scope="col" style="text-align: right;"><b>0,00 €</b></td>
						</tr>';
		}
		// if ($shipment_data->vat_amount > 0) {
		// 	if ($refn_discount > 0) {
		// 		$html .= '<tr>
		// 					<td scope="col" style="text-align: left;font-weight: 600;padding-right: 20px;"><b>TVA 20,0 %</b></td>
		// 					<td scope="col" style="text-align: right;"><b>' . fr_number_format($total_amount - $refn_discount - $carrier_rate - ($margin * 0.83333333333)) . ' €</b></td>
		// 				</tr>';
		// 	} else {
		// 		$html .= '<tr>
		// 					<td scope="col" style="text-align: left;font-weight: 600;padding-right: 20px;"><b>TVA 20,0 %</b></td>
		// 					<td scope="col" style="text-align: right;"><b>' . $shipment_data->vat_amount . ' €</b></td>
		// 				</tr>';
		// 	}
		// }
		$html .= '<tr style="border-top: 1px solid #333">
						<td scope="col" style="text-align: left;font-weight: 600;border-top: 1px solid #333;padding-right: 20px;"><b>Total TTC</b></td>
						<td scope="col" style="text-align: right;border-top: 1px solid #333"><b>' . fr_number_format($total_amount)  . ' €</b></td>
					</tr>';

		$html .= '	</table>
					</div>
					<div style="width: 100%">
					<b>Conditions de paiement: </b>30 jours
					</div>
				</page>
			</body>
			
			</html>';
		// print_r($dir); die;

		$footer_content = '<p style="font-weight: 100"><i>Exoneration de TVA - Art. 262 du CGI</i></p>';
		$footer_content .= '<p style="font-size: 16px; font-weight: bold;">SAS LABAL - SAS</p>';
		$footer_content .= '<p style="font-weight: 100"><i>149 Avenue du Maine 75014 Paris</i></p>';
		$footer_content .= '<p style="font-weight: 100"><i>Numéro de SIRET: 89350229400018 - Numéro de TVA: FR67893502294</i></p>';

		$mpdf->SetFooter(array(
			'odd' => array(
				'L' => array(
					'content' => '',
					'font-size' => 10,
					'font-style' => 'B',
					'font-family' => 'serif',
					'color' => '#000000'
				),
				'C' => array(
					'content' => $footer_content,
					'font-size' => 10,
					'color' => '#000000'
				),
				'R' => array(
					'content' => '',
					'font-size' => 10,
					'font-style' => 'B',
					'font-family' => 'serif',
					'color' => '#000000'
				),
				'line' => 0,
			),
			'even' => array()
		));
		$mpdf->WriteHTML($html);
		// $mpdf->Output();
		$mpdf->Output($dir . "/Facture-$current_inv_number.pdf", 'F');
		// }
	}

	private function validate_pickup_details($d)
	{
		global $wpdb, $table_prefix;

		$errors = [];

		if (empty($d) || !isset($d['shipment_id'])) return $errors;

		if (empty($shipments)) return $errors;

		// do validation for package_type = Package
		// if ($tw_gross > $tw_init) {
		// 	$errors[] = 'Le poids total ne peut pas être supérieur au poids du colis';
		// }
		$errors[] = 'Le poids total ne peut pas être supérieur au poids du colis';

		return $errors;
	}

	public function submit_pickup_details()
	{
		$request_data = $_POST;

		$current_language = $request_data['current_language'];
		$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

		// $errors = $this->validate_pickup_details($request_data);
		// if (count($errors) > 0) {
		// 	$nonce = 'cpd_nonce';

		// 	$errors['old_values'] = $request_data;

		// 	set_transient("request_$nonce", $errors, (5000 * 120));

		// 	wp_safe_redirect(add_query_arg(array(
		// 		'shipment_id' => $request_data['shipment_id'],
		// 		'request_status' => "error",
		// 		'request_id' => "request_$nonce",
		// 	), site_url() . '/schedule-pickup'));
		// 	exit;
		// }

		$update_data = $this->store_pickup_details($request_data);

		// wp_safe_redirect(add_query_arg(array(
		// 	'shipment_id' => $request_data['shipment_id'],
		// ), site_url() . '/labal-courrier-checkout'));
		wp_safe_redirect(add_query_arg(array(
			'shipment_id' => $request_data['shipment_id'],
			'pt' => $request_data['package_type'],
		), $lc_site_url . '/customs-declaration'));
		exit;
	}


	private function store_pickup_details($data)
	{
		// print_r($data); die;
		global $wpdb, $table_prefix;
		return $wpdb->update(
			$table_prefix . 'lc_shipments',
			array(
				// 'is_pickup_required' => ($data['shedule_pickup']),
				'pickup_location' => ($data['pickup_location']),
				'special_pickup_instructions' => ($data['special_pickup_instructions']),

				'earliest_pickup_time' => ($data['e_pickup_time']),
				'latest_pickup_time' => ($data['l_pickup_time']),
			),
			array('lc_shipment_id' => $data['shipment_id'])
		);
	}

	private function validate_shipment_details($d)
	{
		global $wpdb, $table_prefix;

		$errors = [];

		if (empty($d) || !isset($d['shipment_id'])) return $errors;

		// do validation for number fields
		foreach ($d['package'] as $key => $item) {
			// replace comma with point 
			$d['package'][$key]['qty'] = str_replace(',', '.', $item['qty']);
			$d['package'][$key]['weight'] = str_replace(',', '.', $item['weight']);
			$d['package'][$key]['length'] = str_replace(',', '.', $item['length']);
			$d['package'][$key]['width'] = str_replace(',', '.', $item['width']);
			$d['package'][$key]['height'] = str_replace(',', '.', $item['height']);

			// validate if input is number
			if (
				!is_numeric($d['package'][$key]['qty']) || $d['package'][$key]['qty'] < 0 ||
				!is_numeric($d['package'][$key]['weight']) || $d['package'][$key]['weight'] < 0 ||
				!is_numeric($d['package'][$key]['length']) || $d['package'][$key]['length'] < 0 ||
				!is_numeric($d['package'][$key]['width']) || $d['package'][$key]['width'] < 0 ||
				!is_numeric($d['package'][$key]['height']) || $d['package'][$key]['height'] < 0
			) {
				$errors[] = 'Veuillez fournir un nombre valide pour le poids, la longueur, la largeur et la hauteur';
			}
		}

		if (strlen($d['sender_full_phone_number']) < 5) {
			$errors[] = 'Please provide a valid sender phone number';
		}
		if (strlen($d['receiver_full_phone_number']) < 5) {
			$errors[] = 'Please provide a valid receiver phone number';
		}

		$output = ['request_data' => $d, 'errors' => $errors];

		return $output;
	}

	public function submit_shipment_details()
	{
		$request_data = $_POST;
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$current_language = $request_data['current_language'];
		$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

		$col = explode('--', $request_data['col_postcode_or_city']);

		$request_data['col_postcode'] = $col[0];
		$request_data['col_suburb'] = $col[1];
		$request_data['col_city'] = $col[2];

		$del = explode('--', $request_data['del_postcode_or_city']);

		$request_data['del_postcode'] = $del[0];
		$request_data['del_suburb'] = $del[1];
		$request_data['del_city'] = $del[2];

		list(
			'request_data' => $request_data,
			'errors' => $errors
		) = $this->validate_shipment_details($request_data);


		if (count($errors) > 0) {
			$nonce = 'vsd_nonce';

			$errors['old_values'] = $request_data;

			set_transient("request_$nonce", $errors, (5000 * 120));

			wp_safe_redirect(add_query_arg(array(
				'shipment_id' => $request_data['shipment_id'],
				'request_status' => "error",
				'request_id' => "request_$nonce",
			), $lc_site_url . '/labal-courrier-additional-information'));
			exit;
		}

		// UPS address validation
		// if ($request_data['selected_carrier_id'] == 'CARRIER_UPS') {
		// 	$avRes = $this->ups->addressValidation($request_data);

		// 	if (!$avRes['validated']) {
		// 		$nonce = 'vsd_nonce';

		// 		$errors = array();
		// 		$errors[] = $avRes['message'];
		// 		$errors['old_values'] = $request_data;

		// 		set_transient("request_$nonce", $errors, (5000 * 120));

		// 		wp_safe_redirect(add_query_arg(array(
		// 			'shipment_id' => $request_data['shipment_id'],
		// 			'request_status' => "error",
		// 			'request_id' => "request_$nonce",
		// 		), $lc_site_url . '/labal-courrier-additional-information'));
		// 		exit;
		// 	}
		// }


		$update_data = $this->store_shipment_details($request_data);
		// wp_safe_redirect(add_query_arg(array(
		// 	'shipment_id' => $request_data['shipment_id'],
		// 	'pt' => $request_data['package_type'],
		// ), site_url() . '/customs-declaration'));
		wp_safe_redirect(add_query_arg(array(
			'shipment_id' => $request_data['shipment_id']
		), $lc_site_url . '/schedule-pickup'));
		exit;
		unset($_SESSION['lc_redirect_back_to']);

		wp_safe_redirect(add_query_arg(array(
			'order_id' => $request_data['shipment_id'],
			'first_name' => $request_data['shipment_creator_fname'],
			'last_name' => $request_data['shipment_creator_lname'],
			'email' => $request_data['shipment_creator_email'],
			'country' => $request_data['shipment_creator_country'],
		), $lc_site_url));
		exit();
	}

	private function store_shipment_details($data)
	{
		// print_r($data); die;
		global $wpdb, $table_prefix;
		// $wpdb->update($table_prefix . 'lc_shipments', array(
		// 	'lc_shipment_ID' => $shipment_id,
		// 	'sender_country_code' => $data['quote_request']['col_country'],
		// 	'sender_postcode' => $data['quote_request']['col_postcode'],
		// 	'sender_city' => $data['quote_request']['col_city'],
		// 	'receiver_country_code' => $data['quote_request']['del_country'],
		// 	'receiver_postcode' => $data['quote_request']['del_postcode'],
		// 	'receiver_city' => $data['quote_request']['del_city'],
		// ));
		return $wpdb->update(
			$table_prefix . 'lc_shipments',
			array(
				'sender_first_name' => $data['sender_first_name'],
				'sender_last_name' => $data['sender_last_name'],
				'sender_company_name' => $data['sender_company'],
				'sender_country_code' => $data['col_country'],
				'sender_postcode' => $data['col_postcode'],
				'sender_suburb' => $data['col_suburb'],
				// 'sender_state' => $data['col_state'],
				'sender_city' => $data['col_city'],
				'sender_address' => serialize($data['sender_address']),
				'sender_phone_number' => $data['sender_full_phone_number'],
				'sender_email' => $data['sender_email'],
				'sender_trade_type' => $data['sender_trade_type'],
				'sender_id_number' => isset($data['sender_id_number']) ? $data['sender_id_number'] : '',
				'sender_tva_number' => $data['sender_tva_number'],
				'sender_eori_number' => $data['sender_eori_number'],

				'receiver_first_name' => $data['receiver_first_name'],
				'receiver_last_name' => $data['receiver_last_name'],
				'receiver_company_name' => $data['receiver_company'],
				'receiver_country_code' => $data['del_country'],
				'receiver_postcode' => $data['del_postcode'],
				'receiver_suburb' => $data['del_suburb'],
				// 'receiver_state' => $data['del_state'],
				'receiver_city' => $data['del_city'],
				'receiver_address' => serialize($data['receiver_address']),
				'receiver_phone_number' => $data['receiver_full_phone_number'],
				'receiver_email' => $data['receiver_email'],
				'receiver_trade_type' => $data['receiver_trade_type'],
				'receiver_id_number' => isset($data['receiver_id_number']) ? $data['receiver_id_number'] : '',
				'receiver_tva_number' => $data['receiver_tva_number'],
				'receiver_eori_number' => $data['receiver_eori_number'],

				// 'items' => serialize($data['item']),

				// 'package_type' => $data['package_type'],
				// 'packages' => serialize($data['package']),

				// 'is_pickup_required' => ($data['shedule_pickup']),
				// 'dispatch_date' => ($data['dispatch_date']),

				// 'pickup_location' => ($data['pickup_location']),
				// 'special_pickup_instructions' => ($data['special_pickup_instructions']),
			),
			array('lc_shipment_id' => $data['shipment_id'])
		);
	}

	private function save_and_send_documents($api_response, $shipment_id, $shipment, $waybill_number)
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}


		global $wpdb, $table_prefix;

		$only_carrier_dir =  strtolower(str_replace('CARRIER_', '', $shipment->selected_carrier_id));

		$dir = LABAL_COURRIER_PLUGIN_PATH . 'docs/' . $only_carrier_dir . '/' . $waybill_number;
		if (!is_dir($dir)) {
			mkdir($dir);
		}

		$is_eu_shipment = false;
		if (in_array($shipment->sender_country_code, $this->eu_countries) && in_array($shipment->receiver_country_code, $this->eu_countries)) {
			$is_eu_shipment = true;
		}

		if ($shipment->selected_carrier_id == 'CARRIER_UPS') {
			foreach ($api_response['label'] as $key => $item) {
				file_put_contents($dir . '/rotated-Documents-expedition-' . $waybill_number . '-' . $key . '.png', base64_decode($item));

				// rotate the file, so it appears right 
				$fileName = $dir . '/rotated-Documents-expedition-' . $waybill_number . '-' . $key . '.png';
				$degrees = 270;
				$source = imagecreatefrompng($fileName);
				$rotate = imagerotate($source, $degrees, 0);
				imagepng($rotate, $dir . '/Documents-expedition-' . $waybill_number . '-' . $key . '.png');

				// delete the original file 
				unlink($fileName);
			}

			// convert all the labels png to 1 pdf file 
			$html = '';
			$mpdf = get_mpdf();
			foreach ($api_response['label'] as $key => $item) {
				$inputPath = $dir . '/Documents-expedition-' . $waybill_number . '-' . $key . '.png';
				$html .= '<img src="' . $inputPath . '"/>';
				// $html .= '<br>';
			}

			$mpdf->WriteHTML($html);
			$mpdf->Output($dir . '/Documents-expedition-' . $waybill_number . '.pdf', 'F');
		} else {
			file_put_contents($dir . '/Documents-expedition-' . $waybill_number . '.pdf', base64_decode($api_response['label']));
		}
		// $attachments = array($dir . '/label.pdf', $dir . '/lc_invoice.pdf');

		if ($shipment->package_type == 'Package' && !$is_eu_shipment) {
			// if ($shipment->selected_carrier_id == 'CARRIER_UPS') {
			// 	$this->generate_custom_invoice($dir, $shipment, $waybill_number, $api_response);
			// } else {
			// 	file_put_contents($dir . '/Facture-en-douane-' . $waybill_number . '.pdf', base64_decode($api_response['invoice']));
			// }
			$this->generate_custom_invoice($dir, $shipment, $waybill_number, $api_response);
			// array_push($attachments, $dir . '/invoice.pdf');
		}

		// if ($shipment->package_type == 'Package' && $shipment->have_own_invoice == 1) {
		// 	// rename user provided invoice, and move from tmp-invoice folder to docs folder 
		// 	$source_file = LABAL_COURRIER_PLUGIN_PATH . 'docs/tmp-invoice/Facture-en-douane-' . $shipment_id . '.pdf';
		// 	rename($source_file, $dir . '/Facture-en-douane-' . $waybill_number . '.pdf');
		// }

		$this->generate_lc_invoice($dir, $shipment, $waybill_number, $api_response);

		// save documents in dropbox 
		require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/api/dropbox-sdk/lc-dropbox.php';
		$lc_dropbox = new LC_DROPBOX($shipment_id, $waybill_number);

		// add_action('phpmailer_init', [$this, 'lc_shipment_mail_config']);

		$customer = $wpdb->get_row($wpdb->prepare("select * from {$table_prefix}paygreen_billing_details where shipment_id = %s", [$shipment_id]));
		$to = array(
			$customer->email,
			$shipment->sender_email
		);
		$subject = "Document d’expédition ($waybill_number) – Mon Courrier de France";

		$_SESSION['shipment_id_customer_email'] = $shipment_id;

		ob_start();
		if ($shipment->is_pickup_required == 1) {
			include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/email-template-with-pickup.php';
		} else {
			include_once LABAL_COURRIER_PLUGIN_PATH . 'public/partials/email-template-without-pickup.php';
		}

		$message = ob_get_clean();

		$content_type = function () {
			return 'text/html';
		};
		add_filter('wp_mail_content_type', $content_type);
		add_filter('wp_mail_from_name', function ($name) {
			return LABAL_COURRIER_EMAIL_FROM_NAME;
		});
		wp_mail($to, $subject, $message);
		remove_filter('wp_mail_content_type', $content_type);

		unset($_SESSION['shipment_id_customer_email']);
	}

	public function lc_shipment_mail_config($phpmailer)
	{
		$phpmailer->isSMTP();
		$phpmailer->Host       = 'sxb1plzcpnl434630.prod.sxb1.secureserver.net';
		$phpmailer->Port       = '465';
		$phpmailer->SMTPSecure = 'tls';
		// $phpmailer->SMTPAuth   = true;
		$phpmailer->Username   = 'shipment@moncourrierfrance.com';
		$phpmailer->Password   = 'Delmas2020!';
		$phpmailer->From       = 'shipment@moncourrierfrance.com';
		$phpmailer->FromName   = 'Labal Courrier';
		// $phpmailer->addReplyTo('info@example.com', 'Information');
	}
	public function set_lc_mail_content_type()
	{
		return "text/html";
	}
	public function get_additional_information_form()
	{
		$data = $_POST;
		$quote_request_data = '';
		$quote_request_data = get_transient($data['quote_id']);

		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$current_language = $data['current_language'];
		$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

		unset($_SESSION['shipment_id']);
		if (isset($_SESSION['shipment_id']) && !empty($_SESSION['shipment_id'])) {
			$shipment_id = $_SESSION['shipment_id'];
		} else {
			$shipment_id = $this->store_quote_data($quote_request_data, $data);
			$_SESSION['shipment_id'] = $shipment_id;
		}
		// var_dump($_SESSION); die;
		// die(site_url() . '/labal-courrier-additional-information');

		wp_safe_redirect(add_query_arg(array(
			'shipment_id' => $shipment_id,
		), $lc_site_url  . '/labal-courrier-additional-information'));
		exit;
	}

	private function store_quote_data($request_data, $post)
	{
		global $wpdb, $table_prefix;
		$id = $wpdb->get_var('SELECT id FROM ' . $wpdb->prefix . 'lc_shipments' . ' ORDER BY id DESC LIMIT 1');
		$shipment_id = sprintf('%04d', intval($id) + 1);


		$data = $request_data['quote_request'];
		$selected_rate = $request_data['quote_result'][$post['carrier_arr_id']];

		$inserted = $wpdb->insert($table_prefix . 'lc_shipments', array(
			'lc_shipment_ID' => $shipment_id,
			'selected_carrier_id' => $post['carrier_id'],

			'sender_country_code' => $data['col_country'],
			'sender_postcode' => $data['col_postcode'],
			'sender_suburb' => $data['col_suburb'],

			'sender_city' => $data['col_city'],
			'sender_state' => $data['col_state'],

			'receiver_country_code' => $data['del_country'],
			'receiver_postcode' => $data['del_postcode'],
			'receiver_suburb' => $data['del_suburb'],

			'receiver_city' => $data['del_city'],
			'receiver_state' => $data['del_state'],

			'package_type' => $data['package_type'],
			'packages' => serialize($data['package']),

			'dispatch_date' => ($data['dispatch_date']),

			'is_pickup_required' => ($data['is_pickup_required']),

			'insurance' => ($data['insurance']),
			'insurance_value' => ($data['insurance_value']),

			'get_quote_result' => serialize($selected_rate)
		));
		return $inserted ? $shipment_id : false;
	}

	private function get_carriers()
	{
		require_once LABAL_COURRIER_PLUGIN_PATH . 'includes/api/dhl-sdk-api-express/lc-dhl.php';

		return array(
			LC_DHL::class
			// All the carriers come here
		);
	}

	private function get_carrier_rate($data, $carrier)
	{
		$rates = null;

		$ar = [
			'sender_country_code' => $data['col_country'],
			'sender_postcode' => $data['col_postcode'],
			'sender_city' => $data['col_city'],

			'receiver_country_code' => $data['del_country'],
			'receiver_postcode' => $data['del_postcode'],
			'receiver_city' => $data['del_city'],

			'dispatch_date' => $data['dispatch_date'],

			'package_type' => ($data['package_type'] == 'Package') ? 'NON_DOCUMENTS' : 'DOCUMENTS',
			'packages' => $data['package'],

			'insurance' => $data['insurance'],
			'insurance_value' => $data['insurance_value'],
		];

		if ($carrier == 'CARRIER_DHL') {
			$rates = $this->dhl->getQuote($ar);
		} else if ($carrier == 'CARRIER_UPS') {
			$rates = unserialize($data['get_quote_result']);
		}
		return $rates;
	}

	public function get_carrier_rates($data)
	{
		$ar = [
			'sender_country_code' => $data['col_country'],
			'sender_postcode' => $data['col_postcode'],
			'sender_city' => $data['col_city'],

			'receiver_country_code' => $data['del_country'],
			'receiver_postcode' => $data['del_postcode'],
			'receiver_city' => $data['del_city'],

			'dispatch_date' => $data['dispatch_date'],

			'package_type' => ($data['package_type'] == 'Package') ? 'NON_DOCUMENTS' : 'DOCUMENTS',
			'packages' => $data['package'],

			'is_pickup_required' => $data['is_pickup_required'],
			'insurance' => $data['insurance'],
			'insurance_value' => $data['insurance_value'],

			'weight' => $data['quick_weight'],
			'quote_type' => $data['quote_type'],
		];

		if ($data['col_postcode'] != "") {
			$ar['sender_postcode'] = $data['col_postcode'];
		} elseif ($data['col_suburb'] != "") {
			$ar['sender_suburb'] = $data['col_suburb'];
		}
		if ($data['col_state'] != "") {
			$ar['sender_state'] = $data['col_state'];
		}

		if ($data['del_postcode'] != "") {
			$ar['receiver_postcode'] = $data['del_postcode'];
		} elseif ($data['del_suburb'] != "") {
			$ar['receiver_suburb'] = $data['del_suburb'];
		}
		if ($data['del_state'] != "") {
			$ar['receiver_state'] = $data['del_state'];
		}


		$dhl_rate = $this->dhl->getQuote($ar);

		$ups_rate = $this->ups->getQuote($ar);

		$rates = array($dhl_rate);
		// $rates = array();

		if (is_array($ups_rate) && sizeof($ups_rate) > 0) $rates = array_merge($rates, $ups_rate);

		// sort the rates in ascending order of the amount 
		usort($rates, fn ($a, $b) => $a['amount'] - $b['amount']);

		return $rates;
	}

	public function create_shipment_on_carrier($data)
	{
		// $apiResponse = null;

		// $carriers = $this->get_carriers();

		// $carrier_rates = [];
		// foreach ($carriers as $carrier) {
		// 	$carrier_instant = new $carrier;
		// 	if ($apiResponse = $carrier_instant->create_shipment($data)) {
		// 		// if (is_string($rates)) {
		// 		// 	$this->error_redirect(['common_error' => $rates]);
		// 		// }
		// 		// array_push($carrier_rates, $rates);
		// 	}
		// }
		// return $apiResponse;
		// print_r($data);
		// die;

		$is_eu_shipment = false;
		if (in_array($data->sender_country_code, $this->eu_countries) && in_array($data->receiver_country_code, $this->eu_countries)) {
			$is_eu_shipment = true;
		}

		$ar = [
			'lc_shipment_ID' => $data->lc_shipment_ID,

			'sender_first_name' => $data->sender_first_name,
			'sender_last_name' => $data->sender_last_name,
			'sender_company_name' => $data->sender_company_name,
			'sender_address' => unserialize($data->sender_address),
			'sender_country_code' => $data->sender_country_code,
			'sender_postcode' => $data->sender_postcode,
			'sender_suburb' => $data->sender_suburb,
			'sender_state' => $data->sender_state,
			'sender_city' => $data->sender_city,
			'sender_phone_number' => $data->sender_phone_number,
			'sender_email' => $data->sender_email,
			'sender_trade_type' => $data->sender_trade_type,
			'sender_id_number' => $data->sender_id_number,
			'sender_tva_number' => $data->sender_tva_number,
			'sender_eori_number' => $data->sender_eori_number,

			'receiver_first_name' => $data->receiver_first_name,
			'receiver_last_name' => $data->receiver_last_name,
			'receiver_company_name' => $data->receiver_company_name,
			'receiver_address' => unserialize($data->receiver_address),
			'receiver_country_code' => $data->receiver_country_code,
			'receiver_postcode' => $data->receiver_postcode,
			'receiver_suburb' => $data->receiver_suburb,
			'receiver_state' => $data->receiver_state,
			'receiver_city' => $data->receiver_city,
			'receiver_phone_number' => $data->receiver_phone_number,
			'receiver_email' => $data->receiver_email,
			'receiver_trade_type' => $data->receiver_trade_type,
			'receiver_id_number' => $data->receiver_id_number,
			'receiver_tva_number' => $data->receiver_tva_number,
			'receiver_eori_number' => $data->receiver_eori_number,

			'dispatch_date' => $data->dispatch_date,
			'package_type' => ($data->package_type == 'Package') ? 'NON_DOCUMENTS' : 'DOCUMENTS',
			'packages' => unserialize($data->packages),
			'shipment_description' => $data->shipment_description,
			'insurance' => $data->insurance,
			'insurance_value' => $data->insurance_value,
			'items' => unserialize($data->items),
			'total_customs_value' => $data->total_customs_value,
			'export_reason_type' => $data->export_reason_type,
			'is_pickup_required' => $data->is_pickup_required,
			'pickup_date' => $data->pickup_date,
			'pickup_location' => $data->pickup_location,
			'special_pickup_instructions' => $data->special_pickup_instructions,
			'earliest_pickup_time' => $data->earliest_pickup_time,
			'latest_pickup_time' => $data->latest_pickup_time,
			'shipment_creator_fname' => $data->shipment_creator_fname,
			'shipment_creator_lname' => $data->shipment_creator_lname,
			'shipment_creator_email' => $data->shipment_creator_email,
			'selected_carrier_id' => $data->selected_carrier_id,
			'remarks' => $data->remarks,
			'total_amount' => $data->total_amount,
			'shipment_type' => $data->shipment_type,
			'carrier_rate' => $data->carrier_rate,
			'vat_amount' => $data->vat_amount,

			'get_quote_result' => $data->get_quote_result,

			'is_eu_shipment' => $is_eu_shipment,
		];

		if ($data->selected_carrier_id == 'CARRIER_DHL') {
			require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/api/dhl-curl/DHL.php';
			$dhl = new DHL('labalFR', 'Q^2oU$8lI#1a', '950455439');
			return $dhl->createShipment($ar);
		} else if ($data->selected_carrier_id == 'CARRIER_UPS') {
			return $this->ups->createShipment($ar);
		}


		// print_r ($dhl->createShipment($ar));
		// die;

	}

	private function error_redirect($errors, $lc_site_url)
	{
		$nonce = $_POST['lc_nonce'];
		if (count($errors)) {
			$errors['old_values'] = $_POST;
			set_transient("request_$nonce", $errors, (5000 * 120));
			wp_safe_redirect(add_query_arg(array(
				'request_status' => "error",
				'request_id' => "request_$nonce",
			), site_url() . '/'));
			exit;
		}
	}

	public function lc_get_quote()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		global $wpdb;

		unset($_SESSION['shipment_id']);

		$request_data = $_POST;

		if (isset($request_data['shipping_calculator']) && $request_data['quote_id'] != '') {
			$quote_id = $request_data['quote_id'];
		} else {
			$quote_id = time();
		}

		$current_language = $request_data['current_language'];
		$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

		/**
		 * if quote_type == quick, then adjust some data 
		 * set col_country to col_country_quick, del_country to del_country_quick
		 * set packages length, width, height - so DHL Rate API works properly
		 */
		if ($request_data['quote_type'] == 'quick') {
			$request_data['package_type'] = 'Package';
			$request_data['insurance'] = 0;
			$request_data['is_pickup_required'] = 0;

			$request_data['col_country'] = $request_data['col_country_quick'];
			$request_data['del_country'] = $request_data['del_country_quick'];
			$request_data['dispatch_date'] = $request_data['dispatch_date_quick'];

			$request_data['package'][0]['weight'] = $request_data['quick_weight'];
			$request_data['package'][0]['length'] = $request_data['quick_weight'];
			$request_data['package'][0]['width'] = $request_data['quick_weight'];
			$request_data['package'][0]['height'] = $request_data['quick_weight'];

			$col_p_arr = lc_get_postcode_city_by_country($request_data['col_country']);
			$del_p_arr = lc_get_postcode_city_by_country($request_data['del_country']);

			if (isset($col_p_arr['suburb'])) {
				$request_data['col_postcode_or_city'] = $col_p_arr['postcode'] . "--" . $col_p_arr['suburb'] . "--" . $col_p_arr['capital'] . "--" . $col_p_arr['state'];
			} else {
				$request_data['col_postcode_or_city'] = $col_p_arr['postcode'] . "--" . '' . "--" . $col_p_arr['capital'] . "--" . $col_p_arr['state'];
			}
			if (isset($del_p_arr['suburb'])) {
				$request_data['del_postcode_or_city'] = $del_p_arr['postcode'] . "--" . $del_p_arr['suburb'] . "--" . $del_p_arr['capital'] . "--" . $del_p_arr['state'];
			} else {
				$request_data['del_postcode_or_city'] = $del_p_arr['postcode'] . "--" . '' . "--" . $del_p_arr['capital'] . "--" . $del_p_arr['state'];
			}
		} else if ($request_data['quote_type'] == 'full' && !isset($request_data['shipping_calculator'])) {
			$request_data['col_country'] = $request_data['col_country_full'];
			$request_data['del_country'] = $request_data['del_country_full'];
			$request_data['dispatch_date'] = $request_data['dispatch_date_full'];
		} else if ($request_data['quote_type'] == 'full' && isset($request_data['shipping_calculator'])) {
			$request_data['col_country_full'] = $request_data['col_country'];
			$request_data['del_country_full'] = $request_data['del_country'];
			$request_data['dispatch_date_full'] = $request_data['dispatch_date'];
		}


		// clear any previous errors 
		$nonce = $_POST['lc_nonce'];
		set_transient("request_$nonce", '', (5000 * 120));

		// If comma used instead of period(.) replace them with period
		foreach ($request_data['package'] as $key => $package) {
			$qty['package'][$key]['qty'] = str_replace(',', '.', $package['qty']);
			$request_data['package'][$key]['weight'] = str_replace(',', '.', $package['weight']);
			$request_data['package'][$key]['length'] = str_replace(',', '.', $package['length']);
			$request_data['package'][$key]['width'] = str_replace(',', '.', $package['width']);
			$request_data['package'][$key]['height'] = str_replace(',', '.', $package['height']);
		}

		$col = explode('--', $request_data['col_postcode_or_city']);

		$request_data['col_postcode'] = $col[0];
		$request_data['col_suburb'] = $col[1];
		$request_data['col_city'] = $col[2];
		$request_data['col_state'] = $col[3];

		$del = explode('--', $request_data['del_postcode_or_city']);

		$request_data['del_postcode'] = $del[0];
		$request_data['del_suburb'] = $del[1];
		$request_data['del_city'] = $del[2];
		$request_data['del_state'] = $del[3];



		// check for any error 
		$errors = $this->validate_get_quote_form_request($request_data);

		if (count($errors) > 0) {
			/**
			 * reset the request data before saving to session. 
			 */
			if ($request_data['quote_type'] == 'quick') {
				$request_data['package_type'] = '';
				$request_data['insurance'] = '';
				$request_data['is_pickup_required'] = '';

				$request_data['package'][0]['weight'] = $request_data['quick_weight'];
				$request_data['package'][0]['length'] = '';
				$request_data['package'][0]['width'] = '';
				$request_data['package'][0]['height'] = '';
			}

			/**
			 * set post data to session so data can be populated in case of any error 
			 */
			$quote_data = [
				"quote_result" => '',
				"quote_request" => $request_data,
			];

			set_transient($quote_id, $quote_data, 5000);
			$_SESSION['quote_id'] = $quote_id;

			if ($request_data['quote_type'] == 'quick' && !isset($request_data['is_country_page'])) {
				// save session quote_id to database table 
				insertQuoteID($quote_id);
			}

			$this->error_redirect($errors, $lc_site_url);
		}
		// echo '<pre>';
		// print_r($request_data);
		// exit();
		$carrier_rates = $this->get_carrier_rates($request_data);

		$error_message = '';
		foreach ($carrier_rates as $key => $rate) {
			if (isset($rate['error']) && $rate['error'] == 1) {
				$error_message = $rate['error_message'] ?? '';

				/**
				 * reset the request data before saving to session. 
				 */
				if ($request_data['quote_type'] == 'quick') {
					$request_data['package_type'] = '';
					$request_data['insurance'] = '';
					$request_data['is_pickup_required'] = '';

					$request_data['package'][0]['weight'] = $request_data['quick_weight'];
					$request_data['package'][0]['length'] = '';
					$request_data['package'][0]['width'] = '';
					$request_data['package'][0]['height'] = '';
				}

				/**
				 * set post data to session so data can be populated in case of any error 
				 */
				$quote_data = [
					"quote_result" => '',
					"quote_request" => $request_data,
				];

				set_transient($quote_id, $quote_data, 5000);
				$_SESSION['quote_id'] = $quote_id;

				// save session quote_id to database table 
				if ($request_data['quote_type'] == 'quick' && !isset($request_data['is_country_page'])) {
					insertQuoteID($quote_id);
				}

				if ($error_message != '') {
					$this->error_redirect(['carrier_error' => $error_message], $lc_site_url);
				} else {
					$this->error_redirect(['carrier_error' => 'Couldn\'t find a match'], $lc_site_url);
				}
			}
		}

		// if (empty($carrier_rates)) {
		// 	if ($error_message != '') {
		// 		$this->error_redirect(['carrier_error' => $error_message]);
		// 	} else {
		// 		$this->error_redirect(['carrier_error' => 'Couldn\'t find a match']);
		// 	}
		// }

		$lc_rates = $this->apply_labal_courrier_margin($carrier_rates, $request_data);

		/**
		 * reset the request data before saving to session. 
		 */
		if ($request_data['quote_type'] == 'quick') {
			$request_data['package_type'] = '';
			$request_data['insurance'] = '';
			$request_data['is_pickup_required'] = '';

			$request_data['package'][0]['weight'] = $request_data['quick_weight'];
			$request_data['package'][0]['length'] = '';
			$request_data['package'][0]['width'] = '';
			$request_data['package'][0]['height'] = '';
		}

		$quote_data = [
			"quote_result" => $lc_rates,
			"quote_request" => $request_data,
		];

		set_transient($quote_id, $quote_data, 5000);
		$_SESSION['quote_id'] = $quote_id;

		// save session quote_id to database table 
		if ($request_data['quote_type'] == 'quick' && !isset($request_data['is_country_page'])) {
			insertQuoteID($quote_id);
		}

		// echo '<pre>';
		// print_r($_SESSION);
		// exit();

		if (isset($request_data['is_wpcargo'])) {
			wp_safe_redirect(add_query_arg(array(
				'quote_id' => $quote_id,
			), $lc_site_url . '/wpcargo-labal-courrier-shipment'));
			exit;
		}

		// if it's full quote submission, then delete all the previous quick quote data
		if ($request_data['quote_type'] == 'full') {
			deleteQuoteID();
		}

		wp_safe_redirect(add_query_arg(array(
			'quote_id' => $quote_id,
		), $lc_site_url . '/labal-courrier-shipment'));
		exit;
	}

	private function validate_get_quote_form_request($d)
	{
		$errors = [];

		//collection location
		if (isset($d['col_country']) && (empty($d['col_country']) || $d['col_country'] == "0")) {
			$errors['col_location'][] = 'Please choose the collection country';
			if (!in_array($d['col_country'], lc_get_no_postalcode_countries())) {
				if (isset($d['col_postcode']) && (empty($d['col_postcode']) || $d['col_postcode'] == "")) {
					$errors['col_location'][] = 'Please enter a valid postcode';
				}
			}
		}

		if (!isset($d['col_postcode_or_city'])) {
			$errors['col_location'][] = 'This field cannot be empty';
		}

		//delivery location
		if (isset($d['del_country']) && (empty($d['del_country']) || $d['del_country'] == "0")) {
			$errors['del_location'][] = 'Please choose the delivery country';
			if (!in_array($d['del_country'], lc_get_no_postalcode_countries())) {
				if (isset($d['del_postcode']) && (empty($d['del_postcode']) || $d['del_postcode'] == "")) {
					$errors['del_location'][] = 'Please enter a valid postcode';
				}
			}
		}
		if (!isset($d['del_postcode_or_city'])) {
			$errors['del_location'][] = 'This field cannot be empty';
		}
		//delivery location
		// if (!isset($d['package_type'])) {
		// 	$errors['package_type'] = 'Please choose a package type';
		// }

		return $errors;
	}

	private function apply_labal_courrier_margin($carrier_rates, $data, $final_quote = false)
	{
		$lc_rate = [];
		$rate = new LC_Rate;
		$shipment_type = '';
		$vat_available = false;

		$current_language = $data['current_language'];
		$lc_site_url = $current_language == 'en_US' ? site_url('en') : site_url();

		// $zone = new LC_Zone;
		if ($data['col_country'] == LC_ORG_COUNTRY && $data['del_country'] == LC_ORG_COUNTRY) {
			$shipment_type = LC_Rate::LC_NOR;
		} else {
			// $col_zone = ($data['col_country'] != LC_ORG_COUNTRY) ? $zone->get_zone_by_country($data['col_country']) : false;
			// $del_zone = ($data['del_country'] != LC_ORG_COUNTRY) ? $zone->get_zone_by_country($data['del_country']) : false;
			$coefficient = -1;
			$shipment_type = '';
			if ($data['del_country'] == LC_ORG_COUNTRY) {
				$shipment_type = LC_Rate::LC_IMP;
			} elseif ($data['col_country'] == LC_ORG_COUNTRY) {
				$shipment_type = LC_Rate::LC_EXP;
			} else {
				$shipment_type = LC_Rate::LC_NOR;
			}
		}

		// try {
		// 	$coefficient = $rate->get_coefficient($shipment_type, $data['package_type'], $data['package'], $col_zone, $del_zone);
		// } catch (Exception $e) {
		// 	$this->error_redirect(['common_error' => $e->getMessage()]);
		// }

		$is_insurance = false;
		if (floatval($_POST['insurance_value'] > 1)) {
			$is_insurance = true;
		}

		// if (isset($_POST['debug'])) {
		// 	$lc_rates = $rate->calculate_rate_debug($shipment_type, $coefficient, $carrier_rates, $is_insurance);
		// }

		// check if destination country is in european union
		// if ($shipment_type == LC_Rate::LC_EXP && in_array($data['del_country'], $this->eu_countries)) {
		if (in_array($data['col_country'], $this->eu_countries) && in_array($data['del_country'], $this->eu_countries)) {
			$vat_available = true;
		}

		try {
			$lc_rates = $rate->calculate_rate($shipment_type, $coefficient, $carrier_rates, $is_insurance, $vat_available, $data);
		} catch (Exception $e) {
			$this->error_redirect(['common_error' => $e->getMessage()], $lc_site_url);
		}

		if ($final_quote) {
			$lc_rates['shipment_type'] = $shipment_type;
			$lc_rates['lc_commission'] = $coefficient;
		}

		return $lc_rates;
	}

	private function template_functions()
	{
		add_action('wp_footer', array($this, 'lc_footer'));
		add_action('wp_head', array($this, 'mnfr_add_page_sharing_data'), .1);
	}

	public function mnfr_add_page_sharing_data()
	{
		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		if (strpos($actual_link, 'register/?r=') !== false) {
			$referral_code_discount = get_option('referral_code_discount', 10) == '' ? 10 : get_option('referral_code_discount', 10);
			$referral_code_limit = get_option('referral_code_limit', 60) == '' ? 60 : get_option('referral_code_limit', 60);

			echo '<meta property="og:title" content="Je reçois ' . $referral_code_discount . ' euros pour mon envoi" />';
			echo '<meta property="og:description" content="Recevez une remise de ' . $referral_code_discount . ' euros sur votre premier envoi de colis de plus de ' . $referral_code_limit . ' euros à l\'aide de ce lien" />';
			echo '<meta property="og:image" content="' . site_url() . '/wp-content/uploads/2022/11/logo-1200.png" />';
		}
	}

	public function lc_footer()
	{
	?>
		<div class="lc-loading-screen" style="display: none;">
			<div class="wrapper">
				<div class="content">
					<img src="<?php echo LABAL_COURRIER_PLUGIN_URL . '/public/img/logo.png' ?>" alt="">
					<p>Nous recherchons les meilleures offres</p>
				</div>
			</div>
		</div>
		<div class="lc-loading-modal" style="display: none;">
			<div class="wrapper">
				<div class="content">
					<img src="<?php echo LABAL_COURRIER_PLUGIN_URL . '/public/img/order-track.svg' ?>" alt="">
					<h3><?= esc_html_e("Please wait while we find the best offers...", "labal-courrier") ?></h3>
				</div>
			</div>
		</div>
		<div class="lc-common-loading" style="display: none;">
			<div class="wrapper">
				<div class="content">
					<i class="fa-solid fa-spinner fa-spin"></i>
				</div>
			</div>
		</div>
<?php
	}

	public function get_no_postalcode_countries()
	{
		echo json_encode(['status' => 'SUCCESS', 'no_postalcode_countries' => lc_get_no_postalcode_countries()]);
		wp_die();
	}

	public function get_id_required_countries()
	{
		return [
			'AR', 'ZA', 'ID', 'PE', 'TR', 'UY', 'CL'
		];
	}
}
