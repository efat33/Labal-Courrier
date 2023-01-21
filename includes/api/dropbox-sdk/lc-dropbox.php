<?php
require_once __DIR__ . '/vendor/autoload.php';

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

class LC_DROPBOX
{
    private $dropboxKey;
    private $dropboxSecret;
    private $refresh_token;
    private $access_token;
    private $waybill_number;
    private $shipment_id;
    private $inv_number;
    private $shipment;
    private $dropbox_folder;

    function __construct($shipment_id, $waybill_number)
    {
        global $wpdb, $table_prefix;

        // efat account
        // $this->dropboxKey = '99fe6lan14vpof4';
        // $this->dropboxSecret = '6u5njntqfsyrsdu';
        // $this->refresh_token = 'Vj9zwv1OfosAAAAAAAAAAaF_eMP6O8nrdQmcMERJdTaR7TlIEu3cVLWA8yI0j0hg';

        // $this->dropbox_folder = 'Documents-Test';
        $this->dropbox_folder = 'Documents';
        $this->shipment_id = $shipment_id;
        $this->waybill_number = $waybill_number;
        $this->dropboxKey = '73hv4peghmi05zv';
        $this->dropboxSecret = 's6bimfqafy9lrb7';
        $this->refresh_token = 'TyIXXKKsylsAAAAAAAAAAclsN6tG7gmW3TWab3-vjvexrhXcVofaCLAlbnhlAEcg';

        $this->access_token = $this->get_token()['access_token'];
        // $this->access_token = 'sl.BIU9MTaCC-kS3PAlC2QoksmvcNWZnQepVjDU8HeDmCYDpPS906cksfFa242an3f_HjlMcslSOMGFa08IZPGM3u7G7qfXww0gin3uk82t127TDzhXjpQVQG0IhVefvl11c5jF1II';

        // get invoice row, in order to get invoice number 
        $invoices = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_invoices WHERE shipment_id = '$shipment_id'");
        $this->inv_number = $invoices->inv_number;

        // get shipment details
        $this->shipment = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '$shipment_id'");

        $this->upload_file_to_dropbox();
    }

    public function get_token()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request("POST", "https://{$this->dropboxKey}:{$this->dropboxSecret}@api.dropbox.com/oauth2/token", [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->refresh_token,
                ]
            ]);
            if ($res->getStatusCode() == 200) {
                return json_decode($res->getBody(), TRUE);
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo '<pre>';
            print_r($e);
            exit();
            return false;
        }
    }

    public function upload_file_to_dropbox()
    {
        $only_carrier_dir =  strtolower(str_replace('CARRIER_', '', $this->shipment->selected_carrier_id));

        $dir = LABAL_COURRIER_PLUGIN_PATH . 'docs/' . $only_carrier_dir . '/' . $this->waybill_number;

        $app = new DropboxApp($this->dropboxKey, $this->dropboxSecret, $this->access_token);
        $dropbox = new Dropbox($app);

        if ($this->shipment->selected_carrier_id == 'CARRIER_UPS') {
            $path_documents_expedition = "$dir/Documents-expedition-" . $this->waybill_number . ".pdf";
        } else {
            $path_documents_expedition = "$dir/Documents-expedition-" . $this->waybill_number . ".pdf";
        }

        $file_documents_expedition = new DropboxFile($path_documents_expedition);

        if ($this->shipment->package_type == 'Package') {
            $path_facture_en_douane = "$dir/Facture-en-douane-" . $this->waybill_number . ".pdf";
            $file_facture_en_douane = new DropboxFile($path_facture_en_douane);
        }

        $path_facture_inv_number = "$dir/Facture-" . $this->inv_number . ".pdf";
        $file_facture_inv_number = new DropboxFile($path_facture_inv_number);


        $file = '';

        try {
            if ($this->shipment->selected_carrier_id == 'CARRIER_UPS') {
                $file = $dropbox->upload($file_documents_expedition, "/$this->dropbox_folder/$this->waybill_number/Documents-expedition-" . $this->waybill_number . ".pdf", ['autorename' => true]);
            } else {
                $file = $dropbox->upload($file_documents_expedition, "/$this->dropbox_folder/$this->waybill_number/Documents-expedition-" . $this->waybill_number . ".pdf", ['autorename' => true]);
            }

            if ($this->shipment->package_type == 'Package') {
                $dropbox->upload($file_facture_en_douane, "/$this->dropbox_folder/$this->waybill_number/Facture-en-douane-" . $this->waybill_number . ".pdf", ['autorename' => true]);
            }

            $dropbox->upload($file_facture_inv_number, "/$this->dropbox_folder/$this->waybill_number/Facture-" . $this->inv_number . ".pdf", ['autorename' => true]);
        } catch (\Exception $e) {
            // echo '<pre>';
            // print_r($e);
        }

        //Uploaded File
        // $file_name = $file->getName();
        // echo '<pre>';
        // print_r($file);
        // exit();
    }
}

/**
 * https://gist.github.com/phuze/755dd1f58fba6849fbf7478e77e2896a
 * get refresh_token code and access token code from above URL
 * $curl_response =  exec(' CURL code goes here'); 
 */
