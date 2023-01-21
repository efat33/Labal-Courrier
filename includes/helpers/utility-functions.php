<?php

require_once LABAL_COURRIER_PLUGIN_PATH . 'includes/helpers/countries.php';

function lc_get_all_countries()
{
    $country = new LC_Countries();
    return $country->get_all();
}

function lc_get_country_by_code($code)
{
    $country = new LC_Countries();
    return $country->get_country_name_by_code($code);
}

function lc_get_postcode_city_by_country($code)
{
    $country = new LC_Countries();
    return $country->get_postcode_city_by_country($code);
}

function lc_get_no_postalcode_countries()
{
    $country = new LC_Countries();
    return $country->get_no_postalcode_countries();
}

function fr_number_format($number)
{
    return number_format($number, 2, ',', ' ');
}

function ISO8601ToMinutes($ISO8601)
{
    $interval = new \DateInterval($ISO8601);

    return ($interval->h * 60) +
        ($interval->i);
}

function customer_dashboard_menus()
{
    $menu = array(
        array(__("My Shipments", "labal-courrier"), "lc-shipment", '<i class="fa-solid fa-box"></i>'),
        array(__("Address Book", "labal-courrier"), "lc-address-book", '<i class="fa-solid fa-book"></i>'),
        array(__("Promo Code", "labal-courrier"), "lc-promo-code", '<i class="fa-solid fa-circle-dollar-to-slot"></i>'),
        array(__("Invite a Friend", "labal-courrier"), "lc-invite-friend", '<i class="fa-solid fa-gift"></i>'),
        array(__("Profile Settings", "labal-courrier"), "lc-profile", '<i class="fa-solid fa-address-card"></i>'),
        array(__("Log Out", "labal-courrier"), "lc-logout?action=lc_logout", '<i class="fa-solid fa-right-from-bracket"></i>')
    );
    return $menu;
}

// Function to get the client IP address
function get_client_ip()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
function getIpAddress()
{
    $ipAddress = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // to get shared ISP IP address
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check for IPs passing through proxy servers
        // check if multiple IP addresses are set and take the first one
        $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ipAddressList as $ip) {
            if (!empty($ip)) {
                // if you prefer, you can check for valid IP address here
                $ipAddress = $ip;
                break;
            }
        }
    } else if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (!empty($_SERVER['HTTP_FORWARDED'])) {
        $ipAddress = $_SERVER['HTTP_FORWARDED'];
    } else if (!empty($_SERVER['REMOTE_ADDR'])) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
    return $ipAddress;
}

function insertQuoteID($quote_id)
{
    global $wpdb;

    deleteQuoteID();

    $insert_data = array(
        'ip' => is_user_logged_in() ? get_current_user_id() : get_client_ip(),
        'quote_id' => $quote_id,
    );
    $table = $wpdb->prefix . 'lc_quote_ids';
    $format = array('%s', '%s');
    $wpdb->insert($table, $insert_data, $format);
}

function deleteQuoteID()
{
    global $wpdb;
    $table = $wpdb->prefix . 'lc_quote_ids';

    $id = is_user_logged_in() ? get_current_user_id() : get_client_ip();

    $wpdb->delete(
        $table,
        array(
            'ip' => $id
        )
    );
}

function getQuoteID()
{
    global $wpdb;
    $table = $wpdb->prefix . 'lc_quote_ids';
    $quote_id = '';

    $id = is_user_logged_in() ? get_current_user_id() : get_client_ip();

    $quote_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE ip = %s ORDER BY id DESC LIMIT 1", $id));

    if (!empty($quote_result)) $quote_id = $quote_result->quote_id;

    return $quote_id;
}
