<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dhl\Express\RequestBuilder\RateRequestBuilder;
use Dhl\Express\Webservice\SoapServiceFactory;
use Dhl\Express\Model\Request\Rate\ShipmentDetails;
use Dhl\Express\RequestBuilder\ShipmentRequestBuilder;

class LC_DHL
{

    private $logger;
    private $serviceFactory;
    private $service;
    private $username;
    private $password;
    private $carrierName;
    private $accountNumber;
    public $carrierId;

    function __construct()
    {
        $this->carrierName = 'DHL Express';
        $this->carrierId = 'DHL';
        $this->logger = new \Psr\Log\NullLogger();
        $this->serviceFactory = new SoapServiceFactory();
        $this->username = 'labalFR';
        $this->password = 'Q^2oU$8lI#1a';
        $this->accountNumber = '950455439';
    }

    public function get_quote($data, $get_lowest = true)
    {
        $this->service = $this->serviceFactory->createRateService($this->username, $this->password, $this->logger, true);
        $package_type = false;
        if (isset($data['package_type'])) :
            $package_type = ($data['package_type'] == 'Package') ? ShipmentDetails::CONTENT_TYPE_NON_DOCUMENTS : ShipmentDetails::CONTENT_TYPE_DOCUMENTS;
        endif;
        $requestBuilder = new RateRequestBuilder();
        $requestBuilder->setIsUnscheduledPickup(true);
        $requestBuilder->setShipperAccountNumber($this->accountNumber);
        if (isset($data['col_address'])) {
            $requestBuilder->setShipperAddress($data['col_country'], $data['col_postcode'], $data['col_city']);
        } else {
            $requestBuilder->setShipperAddress($data['col_country'], $data['col_postcode'], $data['col_city']);
        }

        if (isset($data['del_address'])) {
            $requestBuilder->setRecipientAddress($data['del_country'], $data['del_postcode'], $data['del_city'], $data['del_address']);
        } else {
            $requestBuilder->setRecipientAddress($data['del_country'], $data['del_postcode'], $data['del_city'], ["XYZ"]);
        }
        // $requestBuilder->setShipperAddress('DE', '97799', 'Zeitlofs');
        // $requestBuilder->setRecipientAddress("FR", "75015", "Paris", ["32 Rue Georges Pitard"]);
        $requestBuilder->setIsValueAddedServicesRequested(false);
        $requestBuilder->setNextBusinessDayIndicator(false);
        $requestBuilder->setTermsOfTrade(ShipmentDetails::PAYMENT_TYPE_DDU);
        if ($package_type) {
            $requestBuilder->setContentType($package_type);
        }
        $requestBuilder->setReadyAtTimestamp(new \DateTime($data['dispatch_date']));

        $sq_no = 1;
        foreach ($data['package'] as $key => $package) {
            for ($x = 1; $x <= intval($package['qty']); $x++) {
                $requestBuilder->addPackage($sq_no, $package['weight'], 'kg', $package['length'], $package['width'], $package['height'], 'Cm');
                $sq_no++;
            }
        }

        if (isset($data['insurance']) && $data['insurance'] == '1' && isset($data['insurance_value'])) {
            $requestBuilder->setInsurance(number_format($data['insurance_value'], 2, '.', ''), 'EUR');
        }

        $request = $requestBuilder->build();
        // print_r($request);
        // die;
        $response = false;
        try {
            $response = $this->service->collectRates($request);
            // die('aab');
            // print_r($response);
            // die;
        } catch (Exception $e) {
            // print_r($e); die;
            return "Sorry, Something went wrong!";
            // return $e->getMessage();
            // print_r($e); die;
        }

        // print_r($response); die;
        $rates = [];
        if (!$response) {
            return false;
        }
        foreach ($response->getRates() as $rate) {
            $carrierName = $this->carrierName;
            $serviceCode = $rate->getServiceCode();
            $label = $rate->getLabel();
            $amount = $rate->getAmount();
            $insurance = $rate->getInsurance();
            $currency = $rate->getCurrencyCode();
            $deliveryTime = $rate->getDeliveryTime()->format('Y-m-d');

            $rate = compact('carrierName', 'serviceCode', 'label', 'amount', 'insurance', 'currency', 'deliveryTime');

            array_push($rates, $rate);
        }

        $result = $rates;

        // $rates = [
        //     ['serviceCode' => 'P', 'label' => 'EXPRESS WORLDWIDE', 'amount' => '88.9', 'currency' => 'EUR'],
        //     ['serviceCode' => 'P', 'label' => 'MEDICAL EXPRESS', 'amount' => '40.9', 'currency' => 'EUR'],
        //     ['serviceCode' => 'P', 'label' => 'EXPRESS WORLDWIDE', 'amount' => '100.9', 'currency' => 'EUR'],
        // ];

        // Keep lowest charge and remove other options
        if ($get_lowest) {
            $fee = floatval($rates[0]['amount']);
            $index = 0;
            foreach ($rates as $key => $rate) {
                if ($fee > floatval($rate['amount'])) {
                    $index = $key;
                    $fee = floatval($rate['amount']);
                }
            }
            $result = $rates[$index];
        }
        return $result;
    }

    public function create_shipment($data)
    {
        // echo "test";
        print_r($data);
        die;

        $insurance_value = $data->insurance_value;

        $sender_country = $data->sender_country_code;
        $sender_postcode = $data->sender_postcode;
        $sender_city = $data->sender_city;
        $sender_first_name = $data->sender_first_name;
        $sender_last_name = $data->sender_last_name;
        $sender_company = $data->sender_company_name;
        $sender_address = unserialize($data->sender_address);
        $sender_phone_number = $data->sender_phone_number;
        $sender_email = $data->sender_email;

        $receiver_country = $data->receiver_country_code;
        $receiver_postcode = $data->receiver_postcode;
        $receiver_city = $data->receiver_city;
        $receiver_first_name = $data->receiver_first_name;
        $receiver_last_name = $data->receiver_last_name;
        $receiver_company = $data->receiver_company_name;
        $receiver_address = unserialize($data->receiver_address);
        $receiver_phone_number = $data->receiver_phone_number;
        $receiver_email = $data->receiver_email;

        $dispatch_date = new \DateTime($data->dispatch_date);

        $contentType = ($data->package_type == 'Package') ? ShipmentDetails::CONTENT_TYPE_NON_DOCUMENTS : ShipmentDetails::CONTENT_TYPE_DOCUMENTS;

        $pickup_date = new \DateTime($data->pickup_date);

        $customs_value = $data->total_customs_value;

        $packages = unserialize($data->packages);
        $number_of_pieces = count($packages);

        $service = $this->serviceFactory->createShipmentService($this->username, $this->password, $this->logger, true);
        $requestBuilder = new ShipmentRequestBuilder();
        $requestBuilder->setIsUnscheduledPickup(true);
        $requestBuilder->setRequestPickupDetails("Y");
        $requestBuilder->setTermsOfTrade(ShipmentDetails::PAYMENT_TYPE_DAP);
        $requestBuilder->setContentType($contentType);
        $requestBuilder->setReadyAtTimestamp($pickup_date);
        $requestBuilder->setNumberOfPieces($number_of_pieces);
        $requestBuilder->setCurrency('EUR');
        $requestBuilder->setDescription('Test');
        $requestBuilder->setCustomsValue($customs_value);
        $requestBuilder->setServiceType('P');
        $requestBuilder->setPayerAccountNumber('950455439');
        $requestBuilder->setWaybillDocumentRequested(true);
        $requestBuilder->setDHLCustomsInvoiceRequested(true);
        $requestBuilder->setDHLCustomsInvoiceType('COMMERCIAL_INVOICE');
        if ($data->insurance && $data->insurance != '0') {
            $requestBuilder->setInsurance($insurance_value, 'EUR');
        }
        $requestBuilder->setShipper(
            $sender_country,
            $sender_postcode,
            $sender_city,
            $sender_address,
            $sender_first_name . ' ' . $sender_last_name,
            $sender_company,
            $sender_phone_number,
            $sender_email
        );
        $requestBuilder->setRecipient(
            $receiver_country,
            $receiver_postcode,
            $receiver_city,
            $receiver_address,
            $receiver_first_name . ' ' . $receiver_last_name,
            $receiver_company,
            $receiver_phone_number,
            $receiver_email
        );
        $requestBuilder->setExportDecalaration("2021-08-25", "INV-865014", "Reason Type", "Reason");
        // $requestBuilder->setDryIce($unCode, $weight);

        // $requestBuilder->addPackage($sequenceNumber, $weight, $weightUOM, $length, $width, $height, $dimensionsUOM, 
        foreach ($packages as $key => $package) {
            $requestBuilder->addPackage(
                ($key + 1),
                $package['weight'],
                'kg',
                10.00,
                $package['width'],
                $package['height'],
                'Cm',
                $receiver_first_name
            );
        }
        // print_r($requestBuilder); die;

        $items = unserialize($data->items);
        foreach ($items as $key => $item) {
            $requestBuilder->addExportLineItem(
                ($key + 1),
                $item['commodity_code'],
                $item['quantity'],
                $item['units'],
                $item['item_description'],
                $item['item_value'],
                $item['net_weight'],
                $item['gross_weight'],
                $item['item_origin'],
                'PERMANENT'
            );
        }

        $request = $requestBuilder->build();
        // print_r($request); die;
        $response = $service->createShipment($request);
        // print_r($response); die;
        // header('Content-Type: application/pdf');
        return [
            'documents' => $response->getDocuments()[0],
            'labelData' => $response->getLabelData(),
            'trackingNumbers' => $response->getTrackingNumbers(),
        ];
    }
}







// $requestBuilder = new ShipmentRequestBuilder();
// $requestBuilder->setIsUnscheduledPickup(false);
// $requestBuilder->setTermsOfTrade(ShipmentDetails::PAYMENT_TYPE_CFR);
// $requestBuilder->setContentType(ShipmentDetails::CONTENT_TYPE_NON_DOCUMENTS);
// $requestBuilder->setReadyAtTimestamp(new \DateTime('2021-06-15'));
// $requestBuilder->setNumberOfPieces(1);
// $requestBuilder->setCurrency('EUR');
// $requestBuilder->setDescription('Test');
// $requestBuilder->setCustomsValue('100.0');
// $requestBuilder->setServiceType('P');
// $requestBuilder->setPayerAccountNumber('950455439');
// $requestBuilder->setInsurance('99.00', 'EUR');
// $requestBuilder->setShipper("DE", "97799", "Zeitlofs", ["32 Rue Georges Pitard"], 'Mahran', 'Imara', '0761231234');
// $requestBuilder->setRecipient("FR", "75015", "Paris", ["32 Rue Georges Pitard"], 'Mahran', 'Imara', '0761231234');
// // $requestBuilder->setDryIce($unCode, $weight);

// // $requestBuilder->addPackage($sequenceNumber, $weight, $weightUOM, $length, $width, $height, $dimensionsUOM, 
// $requestBuilder->addPackage(
//     '123',
//     1.123,
//     'kg',
//     31.123,
//     31.123,
//     31.123,
//     'Cm',
//     'abc'
// );

// $request = $requestBuilder->build();
// $response = $service->createShipment($request);
