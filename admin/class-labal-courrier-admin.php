<?php
class Labal_Courrier_Admin
{

	private $plugin_name;

	private $version;

	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, LABAL_COURRIER_PLUGIN_URL . 'admin/css/labal-courrier-admin.css', array(), $this->version, 'all');
		wp_enqueue_style('lc-bootstrap', LABAL_COURRIER_PLUGIN_URL . 'admin/css/lc-bootstrap.css', array(), $this->version, 'all');
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, LABAL_COURRIER_PLUGIN_URL . 'admin/js/labal-courrier-admin.js', array('jquery'), $this->version, false);
		wp_enqueue_script('lc-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
		wp_localize_script(
			$this->plugin_name,
			'lc_obj',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ajax-nonce')
			)
		);
	}

	public function register_actions()
	{
		$this->addCustomRole();
		$this->register_request_handlers();

		add_action('edit_user_profile', [$this, 'lc_professional_user_profile']);
	}

	public function lc_professional_user_profile()
	{
		$user = get_user_by('id', $_GET['user_id']);
		$roles = $user->roles;
		if (in_array('professional', $roles)) {
?>
			<table class="form-table">
				<tr>
					<th>
						<h3>Labal Courrier</h3>
					</th>
					<td>
						<a href="<?= esc_url(admin_url() . 'admin.php?page=lc_dhl_user_coefficient&user_id=' . $_GET['user_id']) ?>" class="button button-secondary"><?= __("DHL", "labal-courrier") ?></a>
						<a href="<?= esc_url(admin_url() . 'admin.php?page=lc_user_general_ups_saver&user_id=' . $_GET['user_id']) ?>" class="button button-secondary"><?= __("UPS Saver", "labal-courrier") ?></a>
						<a href="<?= esc_url(admin_url() . 'admin.php?page=lc_user_general_ups_standard&user_id=' . $_GET['user_id']) ?>" class="button button-secondary"><?= __("UPS Standard", "labal-courrier") ?></a>
						<a href="<?= esc_url(admin_url() . 'admin.php?page=lc_user_general_ups_expedited&user_id=' . $_GET['user_id']) ?>" class="button button-secondary"><?= __("UPS Expedited", "labal-courrier") ?></a>
					</td>
				</tr>
			</table>
<?php
		}
	}

	public function addCustomRole()
	{
		global $wp_roles;
		if (!isset($wp_roles))
			$wp_roles = new WP_Roles();

		$subs = $wp_roles->get_role('subscriber');

		// Adding a 'professional' with all subscriber caps
		$wp_roles->add_role('professional', 'Professional', $subs->capabilities);
	}

	public function labal_courrier_menu()
	{
		add_menu_page("Labal Courrier", "Labal Courrier", "manage_options", "lc_settings", [$this, 'lc_settings_page'], plugin_dir_url(__FILE__) . 'img/icon.png', 10);
		add_submenu_page("lc_settings", "UPS Saver", "UPS Saver", "manage_options", "lc_general_ups_saver", [$this, 'lc_general_ups_saver_page'], plugin_dir_url(__FILE__) . '', 10);
		add_submenu_page("lc_settings", "UPS Standard", "UPS Standard", "manage_options", "lc_general_ups_standard", [$this, 'lc_general_ups_standard_page'], plugin_dir_url(__FILE__) . '', 10);
		add_submenu_page("lc_settings", "UPS Expedited", "UPS Expedited", "manage_options", "lc_general_ups_expedited", [$this, 'lc_general_ups_expedited_page'], plugin_dir_url(__FILE__) . '', 10);

		add_submenu_page("", "User DHL Coefficient", "User DHL Coefficient", "manage_options", "lc_dhl_user_coefficient", [$this, 'lc_dhl_user_coefficient_page'], plugin_dir_url(__FILE__) . '', 10);
		add_submenu_page("", "User UPS Saver", "User UPS Saver", "manage_options", "lc_user_general_ups_saver", [$this, 'lc_user_general_ups_saver_page'], plugin_dir_url(__FILE__) . '', 10);
		add_submenu_page("", "User UPS Standard", "User UPS Standard", "manage_options", "lc_user_general_ups_standard", [$this, 'lc_user_general_ups_standard_page'], plugin_dir_url(__FILE__) . '', 10);
		add_submenu_page("", "User UPS Expedited", "User UPS Expedited", "manage_options", "lc_user_general_ups_expedited", [$this, 'lc_user_general_ups_expedited_page'], plugin_dir_url(__FILE__) . '', 10);
	}

	public function lc_general_ups_expedited_page()
	{
		include_once LABAL_COURRIER_PLUGIN_PATH . 'admin/partials/labal-courrier-admin-ups-expedited-zones-setting.php';
	}

	public function lc_general_ups_standard_page()
	{
		include_once LABAL_COURRIER_PLUGIN_PATH . 'admin/partials/labal-courrier-admin-ups-standard-zones-setting.php';
	}

	public function lc_general_ups_saver_page()
	{
		include_once LABAL_COURRIER_PLUGIN_PATH . 'admin/partials/labal-courrier-admin-ups-saver-zones-setting.php';
	}

	public function lc_settings_page()
	{
		include_once LABAL_COURRIER_PLUGIN_PATH . 'admin/partials/labal-courrier-admin-zones-setting.php';
	}

	public function lc_dhl_user_coefficient_page()
	{
		if (!isset($_GET['user_id']) || $_GET['user_id'] == '') {
			echo '<div class="update-nag notice notice-error inline">' . __("User ID is not provided", "labal-courrier") . '</div>';
			wp_die();
		}

		$user_coeffient_page = 1;
		include_once LABAL_COURRIER_PLUGIN_PATH . 'admin/partials/labal-courrier-admin-zones-setting.php';
	}

	public function lc_user_general_ups_expedited_page()
	{
		if (!isset($_GET['user_id']) || $_GET['user_id'] == '') {
			echo '<div class="update-nag notice notice-error inline">' . __("User ID is not provided", "labal-courrier") . '</div>';
			wp_die();
		}

		$user_coeffient_page = 1;
		include_once LABAL_COURRIER_PLUGIN_PATH . 'admin/partials/labal-courrier-admin-ups-expedited-zones-setting.php';
	}

	public function lc_user_general_ups_standard_page()
	{
		if (!isset($_GET['user_id']) || $_GET['user_id'] == '') {
			echo '<div class="update-nag notice notice-error inline">' . __("User ID is not provided", "labal-courrier") . '</div>';
			wp_die();
		}

		$user_coeffient_page = 1;
		include_once LABAL_COURRIER_PLUGIN_PATH . 'admin/partials/labal-courrier-admin-ups-standard-zones-setting.php';
	}

	public function lc_user_general_ups_saver_page()
	{
		if (!isset($_GET['user_id']) || $_GET['user_id'] == '') {
			echo '<div class="update-nag notice notice-error inline">' . __("User ID is not provided", "labal-courrier") . '</div>';
			wp_die();
		}

		$user_coeffient_page = 1;
		include_once LABAL_COURRIER_PLUGIN_PATH . 'admin/partials/labal-courrier-admin-ups-saver-zones-setting.php';
	}

	public function register_request_handlers()
	{
		$admin_post_functions = array(
			'update_ups_ie_lc_rate' => 'update_ups_ie_lc_rate',
			'update_impoty_export_lc_rate' => 'update_impoty_export_lc_rate',
			'update_normal_lc_rate' => 'update_normal_lc_rate',
		);

		foreach ($admin_post_functions as $name => $function) {
			add_action("admin_post_$name", array($this, $function));
		}
	}

	public function update_ups_ie_lc_rate()
	{
		$data = $_POST;
		$service_code = $data['service_code'];
		$shipment_type = $data['shipment_type'];

		if (current_user_can('editor') || current_user_can('administrator')) {
			unset($data['action']);
			unset($data['service_code']);
			unset($data['shipment_type']);

			if (isset($data['user_coeffient_page']) && $data['user_coeffient_page'] == 1) {
				update_user_meta($data['user_id'], 'ups_' . $service_code . '_' . $shipment_type . '_rate_' . $data['zone_id'], $data);
				wp_redirect(admin_url() . 'admin.php?page=lc_user_general_ups_' . $service_code . '&updated_zone=' . $data['zone_id'] . '&type=' . $shipment_type . '&user_id=' . $data['user_id']);
			} else {
				update_option('ups_' . $service_code . '_' . $shipment_type . '_rate_' . $data['zone_id'], $data);
				wp_redirect(admin_url() . 'admin.php?page=lc_general_ups_' . $service_code . '&updated_zone=' . $data['zone_id'] . '&type=' . $shipment_type);
			}
		} else {
			echo 'Access denied!';
		}
	}

	public function update_impoty_export_lc_rate()
	{
		$data = $_POST;
		// print_r($data); die;
		if (current_user_can('editor') || current_user_can('administrator')) {
			unset($data['action']);

			if (isset($data['user_coeffient_page']) && $data['user_coeffient_page'] == 1) {
				update_user_meta($data['user_id'], 'lc_import_export_rate_' . $data['zone_id'], $data);
				wp_redirect(admin_url() . 'admin.php?page=lc_dhl_user_coefficient&user_id=' . $data['user_id'] . '&updated_zone=' . $data['zone_id']);
			} else {
				update_option('lc_import_export_rate_' . $data['zone_id'], $data);
				wp_redirect(admin_url() . 'admin.php?page=lc_settings&updated_zone=' . $data['zone_id']);
			}
		} else {
			echo 'Access denied!';
		}
	}

	public function update_normal_lc_rate()
	{
		$data = $_POST;

		if (current_user_can('editor') || current_user_can('administrator')) {
			if (isset($data['user_coeffient_page']) && $data['user_coeffient_page'] == 1) {
				update_user_meta($data['user_id'], 'lc_normal_rate', $data);
				wp_redirect(admin_url() . 'admin.php?page=lc_dhl_user_coefficient&user_id=' . $data['user_id'] . '&updated_normal_rate=1');
			} else {
				update_option('lc_normal_rate', $data);
				wp_redirect(admin_url() . 'admin.php?page=lc_settings&updated_normal_rate=1');
			}
		} else {
			echo 'Access denied!';
		}
	}
}
