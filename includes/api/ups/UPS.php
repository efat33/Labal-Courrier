<?php

require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/utilities/arrayToXml.php';

class UPS
{
    private $url;
    private $username;
    private $password;
    private $access_key;
    private $shipper_number;
    private $carrierName;
    private $carrierId;
    private $rate_wsdl;
    private $pickup_wsdl;
    private $tracking_wsdl;

    private $ups_services;

    private $pickup_resp;

    function __construct()
    {
        $this->url = 'https://wwwcie.ups.com';   // test 
        // $this->url = 'https://onlinetools.ups.com';   // live 
        $this->username = 'LABALSAS';
        $this->password = 'hQFd:GyV^d-xvy9';
        $this->access_key = '2DB5ADBEDEA87224';
        $this->shipper_number = '313Y42';
        $this->carrierId = 'UPS';
        $this->carrierName = 'UPS';
        $this->rate_wsdl = LABAL_COURRIER_PLUGIN_PATH . '/includes/api/ups/rate-wsdl/RateWS.wsdl';
        $this->pickup_wsdl = LABAL_COURRIER_PLUGIN_PATH . '/includes/api/ups/pickup-wsdl/Pickup.wsdl';
        $this->tracking_wsdl = LABAL_COURRIER_PLUGIN_PATH . '/includes/api/ups/tracking-wsdl/Track.wsdl';

        $this->ups_services = [
            '08' => 'expedited',
            '11' => 'standard',
            '65' => 'saver',
        ];
    }

    public function get_ups_services()
    {
        return $this->ups_services;
    }

    private function get_timezone_offset_by_country($country_code)
    {
        $timezone = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country_code);
        $target_time_zone = new DateTimeZone($timezone[0]);
        $date_time = new DateTime('now ', $target_time_zone);
        return $date_time->format('P');
    }

    private function convertCurrency($amount, $from_currency, $to_currency)
    {
        // $apikey = 'QsSX9n3NscwevNIO9unzJGpK64lGa8Lr';  // Efat grab from https://apilayer.com/ 
        $apikey = '4Luwkjv5GNoC2IlWepZDvG3wGtM5xkx1';  // grab from https://apilayer.com/ 

        $from_Currency = urlencode($from_currency);
        $to_Currency = urlencode($to_currency);

        $conversion = '';

        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                // CURLOPT_URL => "https://api.apilayer.com/exchangerates_data/convert?to=$to_Currency&from=$from_Currency&amount=$amount",
                CURLOPT_URL => "https://api.apilayer.com/currency_data/convert?to=$to_Currency&from=$from_Currency&amount=$amount",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: text/plain",
                    "apikey: $apikey"
                ),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET"
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $conversion = json_decode($response);
        } catch (Exception $ex) {
        }

        return $conversion;
    }

    private function convertMeasurement($data, $conversion)
    {
        $packages = $data;

        switch ($conversion) {
            case "kgs_to_lbs":
                foreach ($packages as $key => $item) {
                    $packages[$key]['weight'] = round($packages[$key]['weight'] * 2.20462, 2);
                    $packages[$key]['length'] = round($packages[$key]['length'] * 0.393701, 2);
                    $packages[$key]['width'] = round($packages[$key]['width'] * 0.393701, 2);
                    $packages[$key]['height'] = round($packages[$key]['height'] * 0.393701, 2);
                }
                break;
            default:
        }

        return $packages;
    }

    private function mungXML($xml)
    {
        $obj = SimpleXML_Load_String($xml);
        if ($obj === FALSE) return $xml;

        // GET NAMESPACES, IF ANY
        $nss = $obj->getNamespaces(TRUE);
        if (empty($nss)) return $xml;

        // CHANGE ns: INTO ns_
        $nsm = array_keys($nss);
        foreach ($nsm as $key) {
            // A REGULAR EXPRESSION TO MUNG THE XML
            $rgx
                = '#'               // REGEX DELIMITER
                . '('               // GROUP PATTERN 1
                . '\<'              // LOCATE A LEFT WICKET
                . '/?'              // MAYBE FOLLOWED BY A SLASH
                . preg_quote($key)  // THE NAMESPACE
                . ')'               // END GROUP PATTERN
                . '('               // GROUP PATTERN 2
                . ':{1}'            // A COLON (EXACTLY ONE)
                . ')'               // END GROUP PATTERN
                . '#'               // REGEX DELIMITER
            ;
            // INSERT THE UNDERSCORE INTO THE TAG NAME
            $rep
                = '$1'          // BACKREFERENCE TO GROUP 1
                . '_'           // LITERAL UNDERSCORE IN PLACE OF GROUP 2
            ;
            // PERFORM THE REPLACEMENT
            $xml =  preg_replace($rgx, $rep, $xml);
        }

        return $xml;
    }

    public function getQuote(array $data, $get_lowest = true)
    {
        $dimension_measurement = 'CM';
        $weight_measurement = 'KGS';

        // convert measurement from KGS/CM to LBS/IN when sender country is US
        if ($data['sender_country_code'] == 'US') {
            $data['packages'] = $this->convertMeasurement($data['packages'], 'kgs_to_lbs');

            $dimension_measurement = 'IN';
            $weight_measurement = 'LBS';
        }

        //create soap request
        $option['RequestOption'] = 'Shoptimeintransit';
        $request['Request'] = $option;

        /**
         * 01 - Daily Pickup
         * 03 - Customer Counter
         */
        // $pickuptype['Code'] = '03';
        // $request['PickupType'] = $pickuptype;

        // pick up date 
        // $DeliveryTimeInformation['PackageBillType'] = $data['package_type'] == 'NON_DOCUMENTS' ? '03' : '02';  // 03 - Non-Document; 02 - Document only
        $DeliveryTimeInformation['PackageBillType'] = '03';  // 03 - Non-Document; 02 - Document only
        $DeliveryTimeInformation['Pickup']['Date'] = str_replace("-", "", $data['dispatch_date']);
        $shipment['DeliveryTimeInformation'] = $DeliveryTimeInformation;

        $InvoiceLineTotal['CurrencyCode'] = 'EUR';
        $InvoiceLineTotal['MonetaryValue'] = '10';
        $shipment['InvoiceLineTotal'] = $InvoiceLineTotal;

        $customerclassification['Code'] = '01';
        $customerclassification['Description'] = 'Classfication';
        $request['CustomerClassification'] = $customerclassification;

        // shipper address - basically this is UPS account owner details
        $shipper['Name'] = 'Matthieu Delmas';
        $shipper['ShipperNumber'] = $this->shipper_number;
        $address['AddressLine'] = array(
            '149 avenue du Maine'
        );
        $address['City'] = 'PARIS';
        $address['PostalCode'] = '75014';
        $address['CountryCode'] = 'FR';
        $shipper['Address'] = $address;
        $shipment['Shipper'] = $shipper;

        // ship to address
        $shipto['Name'] = '';
        $addressTo['AddressLine'] = '';
        $addressTo['City'] = $data['receiver_city'];
        if (isset($data['receiver_state']) && $data['receiver_state'] != '') {
            $addressTo['StateProvinceCode'] = $data['receiver_state'];
        }
        $addressTo['PostalCode'] = str_replace(' ', '', $data['receiver_postcode']);

        if ($data['receiver_country_code'] == 'CA' && strlen($addressTo['PostalCode']) == 5) {
            $addressTo['PostalCode'] = $addressTo['PostalCode'] . 1;
        }

        $addressTo['CountryCode'] = $data['receiver_country_code'];

        $addressTo['ResidentialAddressIndicator'] = '';
        $shipto['Address'] = $addressTo;
        $shipment['ShipTo'] = $shipto;

        // ship from address 
        $shipfrom['Name'] = '';
        $addressFrom['AddressLine'] = '';
        $addressFrom['City'] = $data['sender_city'];
        if (isset($data['sender_state']) && $data['sender_state'] != '') {
            $addressFrom['StateProvinceCode'] = $data['sender_state'];
        }
        $addressFrom['PostalCode'] = str_replace(' ', '', $data['sender_postcode']);

        if ($data['sender_country_code'] == 'CA' && strlen($addressFrom['PostalCode']) == 5) {
            $addressFrom['PostalCode'] = $addressFrom['PostalCode'] . 1;
        }

        $addressFrom['CountryCode'] = $data['sender_country_code'];
        $shipfrom['Address'] = $addressFrom;
        $shipment['ShipFrom'] = $shipfrom;


        if (isset($data['insurance']) && $data['insurance'] == '1') {
            $per_p_ins = $data['insurance_value'] / count($data['packages']);
        }

        $PackagesArray = [];
        $package_total_weight = 0;
        foreach ($data['packages'] as $key => $item) {

            $packaging['Code'] = $data['package_type'] == 'NON_DOCUMENTS' ? '02' : '01';  // 02 = Package (package_type)
            $packaging['Description'] = 'Rate';
            $package['PackagingType'] = $packaging;

            if ($data['quote_type'] == 'full') {
                $dunit['Code'] = $dimension_measurement;
                $dimensions['Length'] = $item['length'];
                $dimensions['Width'] = $item['width'];
                $dimensions['Height'] = $item['height'];
                $dimensions['UnitOfMeasurement'] = $dunit;
                $package['Dimensions'] = $dimensions;
            }

            $punit['Code'] = $weight_measurement;
            $packageweight['Weight'] = $item['weight'];
            $packageweight['UnitOfMeasurement'] = $punit;
            $package['PackageWeight'] = $packageweight;

            if (isset($data['insurance']) && $data['insurance'] == '1') {
                $declaredValue['CurrencyCode'] = 'EUR';
                $declaredValue['MonetaryValue'] = "$per_p_ins";

                $packageServiceOptions['DeclaredValue'] = $declaredValue;
                $package['PackageServiceOptions'] = $packageServiceOptions;
            }


            $package_total_weight = $package_total_weight + (int) $item['weight'];

            $PackagesArray[] = $package;
        }

        if (count($PackagesArray) > 0) $shipment['Package'] = $PackagesArray;

        // required when used Shoptimeintransit
        $ShipmentTotalWeight['UnitOfMeasurement']['Code'] = $weight_measurement;
        $ShipmentTotalWeight['Weight'] = $package_total_weight;
        $shipment['ShipmentTotalWeight'] = $ShipmentTotalWeight;

        // NegotiatedRatesIndicator returns negotiated rate - this rate is negotiated with this shipper account 
        $shipmentRatingOptions['NegotiatedRatesIndicator'] = '';
        $shipment['ShipmentRatingOptions'] = $shipmentRatingOptions;

        $shipment['ShipmentServiceOptions'] = '';
        $shipment['LargePackageIndicator'] = '';
        $request['Shipment'] = $shipment;

        // echo '<pre>';
        // print_r($request);
        // exit();

        $mode = array(
            'soap_version' => 'SOAP_1_1',  // use soap 1.1 client
            'trace' => 1
        );

        // initialize soap client
        $client = new SoapClient($this->rate_wsdl, $mode);

        //set endpoint url
        $client->__setLocation($this->url . '/webservices/Rate');

        try {

            //create soap header
            $usernameToken['Username'] = $this->username;
            $usernameToken['Password'] = $this->password;
            $serviceAccessLicense['AccessLicenseNumber'] = $this->access_key;
            $upss['UsernameToken'] = $usernameToken;
            $upss['ServiceAccessToken'] = $serviceAccessLicense;

            $header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0', 'UPSSecurity', $upss);
            $client->__setSoapHeaders($header);

            $operation = "ProcessRate";

            //get response
            $resp = $client->__soapCall($operation, array($request));

            // echo '<pre>';
            // print_r($resp->RatedShipment);
            // exit();

            //save soap request and response to file
            file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': REQUEST: ' . $client->__getLastRequest() . "\n", FILE_APPEND);
            file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': RESPONSE: ' . $client->__getLastResponse() . "\n\n\n", FILE_APPEND);

            // get pickup rate in case pickup is required
            $pickup_rate = 0;
            if ($data['is_pickup_required']) {
                $pickup_rate = $this->getPickupRate($data);
            }

            // calculate insurance amount 
            // $data['insurance_value']
            $final_insurance = 0;
            if (isset($data['insurance']) && $data['insurance'] == '1') {
                $final_insurance = $data['insurance_value'] / 100;

                if ($final_insurance < 10.3) $final_insurance = 10.3;
            }

            // get currency conversion rate in case of sender country not being France (FR)
            $conversion_rate = 1;
            $rates = [];
            if (isset($resp->RatedShipment) && is_array($resp->RatedShipment)) {
                if (isset($resp->RatedShipment[0]->NegotiatedRateCharges->TotalCharge->CurrencyCode) && $resp->RatedShipment[0]->NegotiatedRateCharges->TotalCharge->CurrencyCode != 'EUR') {
                    $c_rate = $this->convertCurrency(1, $resp->RatedShipment[0]->TotalCharges->CurrencyCode, 'EUR');
                    // echo '<pre>';
                    // print_r($c_rate);
                    // exit();
                    if (!isset($c_rate->success)) {
                        return '';
                    }
                    $conversion_rate = $c_rate->result;
                }

                // simplify rates array 
                foreach ($resp->RatedShipment as $rate) {

                    // SaturdayDelivery is to be omitted
                    if (isset($rate->TimeInTransit->ServiceSummary) && $rate->TimeInTransit->ServiceSummary->SaturdayDelivery == 1) {
                        continue;
                    }

                    $carrierName = $this->carrierName;
                    $serviceCode = $rate->Service->Code;
                    $ServiceName = $rate->Service->Description;
                    $label = isset($this->ups_services[$rate->Service->Code]) ? $this->ups_services[$rate->Service->Code] : '';
                    $amount = round($rate->NegotiatedRateCharges->TotalCharge->MonetaryValue * $conversion_rate, 2);
                    $currency = 'EUR';
                    $cutoffTimeOffset = '';
                    $pickupWindowEarliestTime = isset($rate->TimeInTransit->ServiceSummary) ? date("H:i:s", strtotime($rate->TimeInTransit->ServiceSummary->EstimatedArrival->CustomerCenterCutoff)) : '09:00:00';
                    $pickupWindowLatestTime = isset($rate->TimeInTransit->ServiceSummary) ? date("H:i:s", strtotime($rate->TimeInTransit->ServiceSummary->EstimatedArrival->Pickup->Time)) : '17:00:00';

                    $deliveryTime = isset($rate->TimeInTransit->ServiceSummary) ? date("Y-m-d", strtotime($rate->TimeInTransit->ServiceSummary->EstimatedArrival->Arrival->Date)) : '';

                    $pickup = round($pickup_rate * $conversion_rate, 2);
                    $insurance = 0;
                    $insurance_mon = 0;
                    $extended_liability = 0;
                    $remote_area = 0;
                    $restricted_destination = 0;
                    $elevated_risk = 0;
                    $emergency_situation = 0;
                    $fuel_surcharge = 0;
                    $service_charge = 0;
                    $carrier_vat = 0;

                    if (isset($data['insurance']) && $data['insurance'] == '1') {
                        $insurance = $final_insurance;
                    }

                    if (isset($rate->NegotiatedRateCharges->TotalCharge)) {
                        $service_charge = round($rate->NegotiatedRateCharges->TotalCharge->MonetaryValue * $conversion_rate, 2) - $insurance;
                    }

                    // calculate vat 
                    $price_without_insurance = $amount - $insurance;
                    $price_without_vat = number_format((float)$price_without_insurance * 0.833333, 2, '.', '');
                    $carrier_vat = $price_without_insurance - $price_without_vat;


                    $amount += $pickup;


                    $rate = compact(
                        'carrierName',
                        'serviceCode',
                        'label',
                        'amount',
                        'pickup',
                        'insurance',
                        'insurance_mon',
                        'extended_liability',
                        'remote_area',
                        'restricted_destination',
                        'elevated_risk',
                        'emergency_situation',
                        'fuel_surcharge',
                        'service_charge',
                        'carrier_vat',
                        'currency',
                        'deliveryTime',
                        'pickupWindowEarliestTime',
                        'pickupWindowLatestTime',
                        'cutoffTimeOffset'
                    );

                    array_push($rates, $rate);
                }
            } else {
                if (isset($resp->RatedShipment->NegotiatedRateCharges->TotalCharge->CurrencyCode) && $resp->RatedShipment->NegotiatedRateCharges->TotalCharge->CurrencyCode != 'EUR') {
                    $c_rate = $this->convertCurrency(1, $resp->RatedShipment->TotalCharges->CurrencyCode, 'EUR');

                    if (!isset($c_rate->success)) {
                        return '';
                    }
                    $conversion_rate = $c_rate->result;
                }

                $rate = $resp->RatedShipment;

                // SaturdayDelivery is to be omitted
                if (isset($rate->TimeInTransit->ServiceSummary) && $rate->TimeInTransit->ServiceSummary->SaturdayDelivery == 1) {
                    return '';
                }

                $carrierName = $this->carrierName;
                $serviceCode = $rate->Service->Code;
                $ServiceName = $rate->Service->Description;
                $label = isset($this->ups_services[$rate->Service->Code]) ? $this->ups_services[$rate->Service->Code] : '';
                $amount = round($rate->NegotiatedRateCharges->TotalCharge->MonetaryValue * $conversion_rate, 2);
                $currency = 'EUR';
                $cutoffTimeOffset = '';
                $pickupWindowEarliestTime = isset($rate->TimeInTransit->ServiceSummary) ? date("H:i:s", strtotime($rate->TimeInTransit->ServiceSummary->EstimatedArrival->CustomerCenterCutoff)) : '09:00:00';
                $pickupWindowLatestTime = isset($rate->TimeInTransit->ServiceSummary) ? date("H:i:s", strtotime($rate->TimeInTransit->ServiceSummary->EstimatedArrival->Pickup->Time)) : '17:00:00';

                $deliveryTime = isset($rate->TimeInTransit->ServiceSummary) ? date("Y-m-d", strtotime($rate->TimeInTransit->ServiceSummary->EstimatedArrival->Arrival->Date)) : '';

                $pickup = round($pickup_rate * $conversion_rate, 2);
                $insurance = 0;
                $insurance_mon = 0;
                $extended_liability = 0;
                $remote_area = 0;
                $restricted_destination = 0;
                $elevated_risk = 0;
                $emergency_situation = 0;
                $fuel_surcharge = 0;
                $service_charge = 0;
                $carrier_vat = 0;

                if (isset($data['insurance']) && $data['insurance'] == '1') {
                    $insurance = $final_insurance;
                }

                if (isset($rate->NegotiatedRateCharges->TotalCharge)) {
                    $service_charge = round($rate->NegotiatedRateCharges->TotalCharge->MonetaryValue * $conversion_rate, 2) -  $insurance;
                }

                // calculate vat 
                $price_without_insurance = $amount - $insurance;
                $price_without_vat = number_format((float)$price_without_insurance * 0.833333, 2, '.', '');
                $carrier_vat = $price_without_insurance - $price_without_vat;

                $amount += $pickup;

                $rate = compact(
                    'carrierName',
                    'serviceCode',
                    'label',
                    'amount',
                    'pickup',
                    'insurance',
                    'insurance_mon',
                    'extended_liability',
                    'remote_area',
                    'restricted_destination',
                    'elevated_risk',
                    'emergency_situation',
                    'fuel_surcharge',
                    'service_charge',
                    'carrier_vat',
                    'currency',
                    'deliveryTime',
                    'pickupWindowEarliestTime',
                    'pickupWindowLatestTime',
                    'cutoffTimeOffset'
                );

                array_push($rates, $rate);
            }

            // $result = $rates;
            $result = [];

            // if ($get_lowest) {
            //     $fee = floatval($rates[0]['amount']) > 0 ? floatval($rates[0]['amount']) : 99999;
            //     $index = 0;
            //     foreach ($rates as $key => $rate) {
            //         if (floatval($rate['amount']) > 0 && $fee > floatval($rate['amount'])) {
            //             $index = $key;
            //             $fee = floatval($rate['amount']);
            //         }
            //     }
            //     $result = $rates[$index];
            // }

            foreach ($rates as $key => $rate) {
                if (in_array($rate['serviceCode'], ['08', '11', '65'])) {
                    $result[] = $rate;
                }
            }
        } catch (Exception $ex) {

            // echo '<pre>';
            // print_r($ex);
            // exit();
            // $plainXML = $this->mungXML(trim($client->__getLastResponse()));
            // $arrayResult = json_decode(json_encode(SimpleXML_Load_String($plainXML, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            // $arrayResult['soapenv_Body']['soapenv_Fault']['detail']['err_Errors']['err_ErrorDetail']

            //save soap request and response to file
            file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': REQUEST: ' . $client->__getLastRequest() . "\n", FILE_APPEND);
            file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': RESPONSE: ' . $client->__getLastResponse() . "\n\n\n", FILE_APPEND);
        }

        // echo '<pre>';
        // print_r($result);
        // exit();
        return $result;
    }

    public function addressValidation(array $data)
    {
        $accessRequest['AccessLicenseNumber'] = $this->access_key;
        $accessRequest['UserId'] = $this->username;
        $accessRequest['Password'] = $this->password;
        $avRequest['AccessRequest'] = $accessRequest;

        $address['City'] = $data['del_city'];

        if ($data['del_state'] && $data['del_state'] != '') {
            $address['StateProvinceCode'] = $data['del_state'];
        }

        $address['PostalCode'] = $data['del_postcode'];
        // $address['PostalCode'] = 'V8P1A1';

        $avRequest['AddressValidationRequest']['Address'] = $address;

        $transactionReference['CustomerContext'] = "Your Customer Context";
        $requestAction = "AV";
        $request['TransactionReference'] = $transactionReference;
        $request['RequestAction'] = $requestAction;

        $avRequest['AddressValidationRequest']['Request'] = $request;

        // echo '<pre>';
        // print_r($avRequest);
        // exit();

        $url = $this->url . '/rest/AV';
        $get_data = $this->callCurl('POST', $url, $avRequest);
        $response = json_decode($get_data, true);

        if (isset($response['AddressValidationResponse']['Response']['ResponseStatusCode'])) {
            if ($response['AddressValidationResponse']['Response']['ResponseStatusCode'] == 1) {
                return [
                    'validated' => true
                ];
            } else {
                return [
                    'validated' => false,
                    'message' => $response['AddressValidationResponse']['Response']['Error']['ErrorDescription'],
                ];
            }
        } else {
            throw new Exception(__("Sorry, UPS pickup rate is not found", "labal-courrier"));
        }
    }

    public function getPickupRate(array $data)
    {


        $pickupAddress['AddressLine'] = '315 Saddle Bridge Drive';
        $pickupAddress['City'] = $data['sender_city'];
        $pickupAddress['StateProvince'] = $data['sender_state'];
        $pickupAddress['PostalCode'] = $data['sender_postcode'];
        $pickupAddress['CountryCode'] = $data['sender_country_code'];
        $pickupAddress['ResidentialIndicator'] = "Y";

        $pickupRateRequest['PickupAddress'] = $pickupAddress;

        $pickupRateRequest['AlternateAddressIndicator'] = "Y";
        $pickupRateRequest['ServiceDateOption'] = "03";

        $pickupDateInfo['CloseTime'] = "1600";
        $pickupDateInfo['ReadyTime'] =  "1000";
        $pickupDateInfo['PickupDate'] = str_replace("-", "", $data['dispatch_date']);

        $pickupRateRequest['PickupDateInfo'] = $pickupDateInfo;

        $request['PickupRateRequest'] = $pickupRateRequest;

        $url = $this->url . '/ship/v1/pickups/rating';
        $get_data = $this->callCurl('POST', $url, $request);
        $response = json_decode($get_data, true);

        if (isset($response['PickupRateResponse']['Response']['ResponseStatus']['Code']) && $response['PickupRateResponse']['Response']['ResponseStatus']['Code'] == 1) {
            $pickup_currency = $response['PickupRateResponse']['RateResult']['CurrencyCode'];
            $pickup_rate = $response['PickupRateResponse']['RateResult']['GrandTotalOfAllCharge'];

            return $pickup_rate;
        } else {
            throw new Exception(__("Sorry, UPS pickup rate is not found", "labal-courrier"));
        }
    }

    public function processPickupCreation(array $data)
    {

        $getQuoteResult = unserialize($data['get_quote_result']);
        $serviceCode = $getQuoteResult['serviceCode'];

        $weight_measurement = 'KGS';

        // convert measurement from KGS/CM to LBS/IN when sender country is US
        if ($data['sender_country_code'] == 'US') {
            $weight_measurement = 'LBS';
        }

        //create soap request
        $requestoption['RequestOption'] = '1';
        $request['Request'] = $requestoption;

        $request['RatePickupIndicator'] = 'N';

        $account['AccountNumber'] = $this->shipper_number;
        $account['AccountCountryCode'] = 'FR';
        $shipper['Account'] = $account;
        $request['Shipper'] = $shipper;

        // TODO: update as per get_quote_result
        $pickupdateinfo['CloseTime'] = '1600';
        $pickupdateinfo['ReadyTime'] = '1100';
        $pickupdateinfo['PickupDate'] = str_replace("-", "", $data['dispatch_date']);
        $request['PickupDateInfo'] = $pickupdateinfo;

        $pickupaddress['CompanyName'] = $data['sender_company_name'];
        $pickupaddress['ContactName'] = $data['sender_first_name'];
        $pickupaddress['AddressLine'] = $data['sender_address'][0];
        $pickupaddress['City'] = $data['sender_city'];

        if (isset($data['sender_state']) && $data['sender_state'] != '') $pickupaddress['StateProvince'] = $data['sender_state'];

        $pickupaddress['PostalCode'] = $data['sender_postcode'];
        $pickupaddress['CountryCode'] = $data['sender_country_code'];
        $pickupaddress['ResidentialIndicator'] = $data['sender_trade_type'] == 'PR' ? 'Y' : 'N';   // Indicates if the pickup address is commercial or residential. it can be obtained from additional information step - professional/personal
        $phone['Number'] = $data['sender_phone_number'];
        $pickupaddress['Phone'] = $phone;
        $request['PickupAddress'] = $pickupaddress;

        $request['AlternateAddressIndicator'] = 'Y';

        $total_quantity = 0;
        $total_weight = 0;
        foreach ($data['items'] as $key => $item) {

            $quantity = $item['quantity'];

            if ($data['sender_country_code'] == 'US') {  // convert kgs to lbs 
                $gross_weight = round($item['gross_weight'] * 2.20462, 2);  // gross weight 
            } else {
                $gross_weight = $item['gross_weight'];
            }

            $total_quantity = $total_quantity + $quantity;
            $total_weight = $total_weight + $gross_weight;
        }

        $pickuppiece['ServiceCode'] = '0' . $serviceCode;
        $pickuppiece['Quantity'] = $total_quantity;       // total quantity of all items from Items array
        $pickuppiece['DestinationCountryCode'] = $data['receiver_country_code'];
        $pickuppiece['ContainerCode'] = '01';     // package or document ~ 01 = PACKAGE, 02 = UPS LETTER
        $request['PickupPiece'] = $pickuppiece;

        $totalweight['Weight'] = $total_weight;         // total weight of all items from Items array
        $totalweight['UnitOfMeasurement'] = $weight_measurement;
        $request['TotalWeight'] = $totalweight;

        $request['OverweightIndicator'] =  'N';
        $request['PaymentMethod'] = '01';     // Pay by shipper account
        $request['SpecialInstruction'] =  $data['special_pickup_instructions'];
        $request['ReferenceNumber'] = '';

        // global $wpdb, $table_prefix;
        // $billing_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_prefix}paygreen_billing_details WHERE shipment_id = %s", $data['lc_shipment_ID']));

        $cnfrmemailaddr =  array(
            $data['sender_email'],     // shipFrom email
            // $billing_details->email     // checkout page email 
        );
        $notification['ConfirmationEmailAddress'] = $cnfrmemailaddr;
        $notification['UndeliverableEmailAddress'] = '';
        $request['Notification'] = $notification;

        // save json request in log file 
        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': PC REQUEST: ' . json_encode($request) . "\n\n", FILE_APPEND);

        try {
            $mode = array(
                'soap_version' => 'SOAP_1_1',  // use soap 1.1 client
                'trace' => 1
            );

            // initialize soap client
            $client = new SoapClient($this->pickup_wsdl, $mode);

            //set endpoint url
            $client->__setLocation($this->url . '/webservices/Pickup');


            //create soap header
            $usernameToken['Username'] = $this->username;
            $usernameToken['Password'] = $this->password;
            $serviceAccessLicense['AccessLicenseNumber'] = $this->access_key;
            $upss['UsernameToken'] = $usernameToken;
            $upss['ServiceAccessToken'] = $serviceAccessLicense;

            $header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0', 'UPSSecurity', $upss);
            $client->__setSoapHeaders($header);

            //get response
            $this->pickup_resp = $client->__soapCall('ProcessPickupCreation', array($request));

            // save json reponse in log file 
            file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': PC RESPONSE: ' . json_encode($this->pickup_resp) . "\n\n\n", FILE_APPEND);
        } catch (Exception $ex) {
            // echo '<pre>';
            // print_r($ex);
            // exit();

            // save json reponse in log file 
            file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': PC RESPONSE: ' . json_encode($ex) . "\n\n\n", FILE_APPEND);
        }
    }

    public function createShipment(array $data)
    {
        $getQuoteResult = unserialize($data['get_quote_result']);
        $serviceCode = $getQuoteResult['serviceCode'];

        $dimension_measurement = 'CM';
        $weight_measurement = 'KGS';

        // convert measurement from KGS/CM to LBS/IN when sender country is US || maybe not necessary when return shipment is used 
        // if ($data['sender_country_code'] == 'US') {
        //     $data['packages'] = $this->convertMeasurement($data['packages'], 'kgs_to_lbs');

        //     $dimension_measurement = 'IN';
        //     $weight_measurement = 'LBS';
        // }

        //create json request
        $shipment['Description'] = $data['export_reason_type'];  // required 

        $shipper['Name'] = $data['sender_first_name'];
        $shipper['AttentionName'] = $data['sender_company_name'];
        $shipper['ShipperNumber'] = $this->shipper_number;
        $address['AddressLine'] = implode(', ', array_filter($data['sender_address'], fn ($value) => trim($value) != ''));
        $address['City'] = $data['sender_city'];
        $address['PostalCode'] = str_replace(' ', '', $data['sender_postcode']);

        if ($data['sender_country_code'] == 'CA' && strlen($address['PostalCode']) == 5) {
            $address['PostalCode'] = $address['PostalCode'] . 1;
        }

        $address['CountryCode'] = $data['sender_country_code'];
        $shipper['Address'] = $address;
        $phone1['Number'] = $data['sender_phone_number'];
        $shipper['Phone'] = $phone1;
        $shipment['Shipper'] = $shipper;

        $shipto['Name'] = $data['receiver_first_name'];
        $shipto['AttentionName'] = $data['receiver_company_name'];

        $addressTo['AddressLine'] = $data['receiver_address'];
        $addressTo['City'] = $data['receiver_city'];

        if ($data['receiver_state'] != '') $addressTo['StateProvinceCode'] = $data['receiver_state'];

        $addressTo['PostalCode'] = str_replace(' ', '', $data['receiver_postcode']);

        if ($data['receiver_country_code'] == 'CA' && strlen($addressTo['PostalCode']) == 5) {
            $addressTo['PostalCode'] = $addressTo['PostalCode'] . 1;
        }

        $addressTo['CountryCode'] = $data['receiver_country_code'];
        $phone2['Number'] = $data['receiver_phone_number'];
        $shipto['Address'] = $addressTo;
        $shipto['Phone'] = $phone2;

        if (!empty($data['receiver_tva_number'])) {
            $shipto['TaxIdentificationNumber'] = $data['receiver_tva_number'];
        }

        $shipment['ShipTo'] = $shipto;

        $shipfrom['Name'] = $data['sender_first_name'];
        $shipfrom['AttentionName'] = $data['sender_company_name'];

        $addressFrom['AddressLine'] = $data['sender_address'];
        $addressFrom['City'] = $data['sender_city'];

        if ($data['sender_state'] != '') $addressFrom['StateProvinceCode'] = $data['sender_state'];

        $addressFrom['PostalCode'] = str_replace(' ', '', $data['sender_postcode']);

        if ($data['sender_country_code'] == 'CA' && strlen($addressFrom['PostalCode']) == 5) {
            $addressFrom['PostalCode'] = $addressFrom['PostalCode'] . 1;
        }

        $addressFrom['CountryCode'] = $data['sender_country_code'];
        $phone3['Number'] = $data['sender_phone_number'];
        $shipfrom['Address'] = $addressFrom;
        $shipfrom['Phone'] = $phone3;

        // sender vat information 
        $vendorInfo['VendorCollectIDTypeCode'] = '0356';
        $vendorInfo['VendorCollectIDNumber'] = 'IMDEU1234567';
        // $shipfrom['VendorInfo'] = $vendorInfo;

        $shipment['ShipFrom'] = $shipfrom;



        $shipmentcharge['Type'] = '01';
        $billshipper['AccountNumber'] = $this->shipper_number;
        $shipmentcharge['BillShipper'] = $billshipper;
        $paymentinformation['ShipmentCharge'] = $shipmentcharge;
        $shipment['PaymentInformation'] = $paymentinformation;

        $service['Code'] = $serviceCode;
        $shipment['Service'] = $service;

        $shipmentRatingOptions['NegotiatedRatesIndicator'] = '';
        $shipment['ShipmentRatingOptions'] = $shipmentRatingOptions;

        if ($data['sender_country_code'] != 'FR') {
            $returnService['Code'] = '9';
            $shipment['ReturnService'] = $returnService;
        }

        $internationalForm['FormType'] = "08";
        $internationalForm['InvoiceNumber'] = '001';
        $internationalForm['InvoiceDate'] = date("Ymd");
        $internationalForm['PurchaseOrderNumber'] = '00112233';
        $internationalForm['TermsOfShipment'] = 'CFR';
        $internationalForm['ReasonForExport'] = $data['export_reason_type'];
        $internationalForm['Comments'] = $data['remarks'];
        // $internationalForm['DeclarationStatement'] = 'Your Declaration Statement';

        $soldTo['Option'] = '01';
        $soldTo['AttentionName'] = $data['receiver_company_name'];
        $soldTo['Name'] = $data['receiver_first_name'];
        $soldToPhone['Number'] = $data['receiver_phone_number'];
        $soldTo['Phone'] = $soldToPhone;
        $soldToAddress['AddressLine'] = $data['receiver_address'][0];
        $soldToAddress['City'] = $data['receiver_city'];

        if ($data['receiver_state'] != '') $soldToAddress['StateProvinceCode'] = $data['receiver_state'];

        $soldToAddress['PostalCode'] = str_replace(' ', '', $data['receiver_postcode']);
        $soldToAddress['CountryCode'] = $data['receiver_country_code'];
        $soldTo['Address'] = $soldToAddress;

        $soldTo['TaxIdentificationNumber'] = '1234213254';

        $contact['SoldTo'] = $soldTo;
        $internationalForm['Contacts'] = $contact;


        // prepare line items array 
        $exportLineItemsAr = [];
        foreach ($data['items'] as $key => $item) {

            $product['Description'] = $item['item_description'];
            $product['CommodityCode'] = $item['commodity_code'];
            $product['OriginCountryCode'] = $item['item_origin'];
            $unitProduct['Number'] = $item['quantity'];   // Total quantity of each commodity to be shipped
            $unitProduct['Value'] = $item['item_value'];    // Monetary amount used to specify the worth or price of the commodity
            $uom['Code'] = 'PC';
            $unitProduct['UnitOfMeasurement'] = $uom;
            $product['Unit'] = $unitProduct;

            if ($data['sender_country_code'] == 'US') {  // convert kgs to lbs 
                $productWeight['Weight'] = round($item['gross_weight'] * 2.20462, 2);  // gross weight 
            } else {
                $productWeight['Weight'] = $item['gross_weight'];
            }

            $uomForWeight['Code'] = $weight_measurement;
            $productWeight['UnitOfMeasurement'] = $uomForWeight;
            $product['ProductWeight'] = $productWeight;

            $exportLineItemsAr[] = $product;
        }
        $internationalForm['Product'] = $exportLineItemsAr;

        if (isset($data['insurance']) && $data['insurance'] == '1') {
            $insurance['MonetaryValue'] = $data['insurance_value'];
            $internationalForm['InsuranceCharges'] = $insurance;
        }

        $internationalForm['CurrencyCode'] = 'EUR';
        $shpServiceOptions['InternationalForms'] = $internationalForm;

        // invoice will be generated in our end 
        // if ($data['package_type'] == 'NON_DOCUMENTS') {
        //     // $shipment['ShipmentServiceOptions'] = $shpServiceOptions;
        // }

        if (isset($data['insurance']) && $data['insurance'] == '1') {
            $per_p_ins = $data['insurance_value'] / count($data['packages']);
        }

        // prepare package array 
        $PackagesArray = [];
        $package_total_weight = 0;
        foreach ($data['packages'] as $key => $item) {

            $package['Description'] = 'Package Description';   // required when ShipFrom is not France 
            $package['NumOfPieces'] = '1';
            // $packaging['Code'] = $data['package_type'] == 'NON_DOCUMENTS' ? '02' : '01';  // 02 = Package;
            $packaging['Code'] = '02';  // 02 = Package;
            $packaging['Description'] = "Piece";
            $package['Packaging'] = $packaging;
            $unit['Code'] = $dimension_measurement;
            $unit['Description'] = '';
            $dimensions['UnitOfMeasurement'] = $unit;
            $dimensions['Length'] = $item['length'];
            $dimensions['Width'] = $item['width'];
            $dimensions['Height'] = $item['height'];
            $package['Dimensions'] = $dimensions;
            $unit2['Code'] = $weight_measurement;
            $packageweight['UnitOfMeasurement'] = $unit2;
            $packageweight['Weight'] = $item['weight'];
            $package['PackageWeight'] = $packageweight;

            if (isset($data['insurance']) && $data['insurance'] == '1') {
                $declaredValue['CurrencyCode'] = 'EUR';
                $declaredValue['MonetaryValue'] = "$per_p_ins";

                $packageServiceOptions['DeclaredValue'] = $declaredValue;
                $package['PackageServiceOptions'] = $packageServiceOptions;
            }

            $package_total_weight = $package_total_weight + $item['weight'];

            $PackagesArray[] = $package;
        }

        $shipment['Package'] = $PackagesArray;

        $shipment['NumOfPiecesInShipment'] = (string)sizeof($PackagesArray);

        $labelimageformat['Code'] = 'PNG';
        $labelspecification['LabelImageFormat'] = $labelimageformat;
        $labelspecification['HTTPUserAgent'] = 'Mozilla/4.5';

        $ShipmentRequest['LabelSpecification'] = $labelspecification;
        $ShipmentRequest['Shipment'] = $shipment;

        $request['ShipmentRequest'] = $ShipmentRequest;

        $dispatch_confirmation_nummber = '';

        // if pickup is required, then call pickup API and confirm pickup first before placing shipment 
        if ($data['is_pickup_required']) {


            $this->processPickupCreation($data);
            $pickup_response = $this->pickup_resp;

            if (isset($pickup_response->Response->ResponseStatus->Code) && $pickup_response->Response->ResponseStatus->Code == 1) {
                $dispatch_confirmation_nummber = $pickup_response->PRN;
            } else {
                // send email to admin, notifying that pick request has failed
                $to = get_bloginfo('admin_email');
                $subject = 'Pickup Creation Failed';
                $body = 'Pickup creation is failed for the shipment ID ' . $data['lc_shipment_ID'];
                $headers = array('Content-Type: text/html; charset=UTF-8');

                add_filter('wp_mail_from_name', function ($name) {
                    return LABAL_COURRIER_EMAIL_FROM_NAME;
                });
                wp_mail($to, $subject, $body, $headers);
                remove_filter('wp_mail_from_name', function () {
                });
            }
        }

        $url = $this->url . '/ship/v1/shipments';
        $get_data = $this->callCurl('POST', $url, $request);
        $response = json_decode($get_data, true);

        // echo '<pre>';
        // print_r($response);
        // exit();

        // save json request and response in log file 
        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': SR REQUEST: ' . json_encode($request) . "\n\n", FILE_APPEND);
        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': SR RESPONSE: ' . json_encode($response) . "\n\n\n", FILE_APPEND);

        if (isset($response['ShipmentResponse']['Response']['ResponseStatus']['Code']) && $response['ShipmentResponse']['Response']['ResponseStatus']['Code'] == 1) {

            $labels = array();
            if (isset($response['ShipmentResponse']['ShipmentResults']['PackageResults'][0])) {
                $labels = array_map(fn ($item) => $item['ShippingLabel']['GraphicImage'], $response['ShipmentResponse']['ShipmentResults']['PackageResults']);
            } else {
                $labels[] = $response['ShipmentResponse']['ShipmentResults']['PackageResults']['ShippingLabel']['GraphicImage'];
            }

            return [
                'waybill_number' => $response['ShipmentResponse']['ShipmentResults']['ShipmentIdentificationNumber'],
                // 'waybill_number' => '1z' . time(),
                // 'label' => isset($response['ShipmentResponse']['ShipmentResults']['PackageResults'][0]) ? $response['ShipmentResponse']['ShipmentResults']['PackageResults'][0]['ShippingLabel']['GraphicImage'] : $response['ShipmentResponse']['ShipmentResults']['PackageResults']['ShippingLabel']['GraphicImage'],
                'label' => $labels,
                'dhl_shipment_id' => $response['ShipmentResponse']['ShipmentResults']['ShipmentIdentificationNumber'],
                // 'dhl_shipment_id' => '1z' . time(),
                'dispatch_confirmation_nummber' => $dispatch_confirmation_nummber, // grab from pickup API response 
                'pickup_details' => '' // grab from pickup API response 
            ];
        }

        return '';
    }

    public function uploadInvoice()
    {
    }

    public function getDropoffLocations($data)
    {
        $locations = [];

        $address = unserialize($data->sender_address);

        try {

            $accessRequestXML = new SimpleXMLElement("<AccessRequest></AccessRequest>");
            $locatorRequestXML = new SimpleXMLElement("<LocatorRequest ></LocatorRequest >");

            $accessRequestXML->addChild("AccessLicenseNumber", $this->access_key);
            $accessRequestXML->addChild("UserId", $this->username);
            $accessRequestXML->addChild("Password", $this->password);

            $request = $locatorRequestXML->addChild('Request');
            $request->addChild("RequestAction", "Locator");
            $request->addChild("RequestOption", "1");  // 1 or 3

            $originAddress = $locatorRequestXML->addChild('OriginAddress');
            $addressKeyFormat = $originAddress->addChild('AddressKeyFormat');
            $addressKeyFormat->addChild("AddressLine", "$address[0]");
            $addressKeyFormat->addChild("PoliticalDivision2", "$data->sender_city");

            if (isset($data->sender_state) && $data->sender_state != '') {
                $addressKeyFormat->addChild("PoliticalDivision1", "$data->sender_state");
            }

            $addressKeyFormat->addChild("PostcodePrimaryLow", "$data->sender_postcode");
            $addressKeyFormat->addChild("CountryCode", "$data->sender_country_code");

            $translate = $locatorRequestXML->addChild('Translate');
            $translate->addChild("LanguageCode", "ENG");

            $unitOfMeasurement = $locatorRequestXML->addChild('UnitOfMeasurement');
            $unitOfMeasurement->addChild("Code", "MI");

            // $locatorRequestXML->addChild("LocationID", "49249");

            $locationSearchCriteria = $locatorRequestXML->addChild('LocationSearchCriteria');
            $locationSearchCriteria->addChild("MaximumListSize", "10");
            $locationSearchCriteria->addChild("SearchRadius", "50");

            $requestXML = $accessRequestXML->asXML() . $locatorRequestXML->asXML();

            $form = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => "$requestXML"
                )
            );

            $url = 'https://onlinetools.ups.com/ups.app/xml/Locator';
            $request = stream_context_create($form);

            $browser = fopen($url, 'rb', false, $request);
            if (!$browser) {
                throw new Exception("Connection failed.");
            }

            // get response
            $response = stream_get_contents($browser);
            fclose($browser);



            if ($response == false) {
                throw new Exception("Bad data.");
            } else {
                // get response status
                $resp = json_decode(json_encode(simplexml_load_string($response)), true);

                if ($resp['Response']['ResponseStatusCode'] == 0) {
                    throw new Exception($resp['Response']['Error']['ErrorDescription']);
                }

                if (isset($resp['SearchResults']['DropLocation']['Geocode'])) {
                    $item = $resp['SearchResults']['DropLocation'];

                    $tmp['latitude'] = $item['Geocode']['Latitude'];
                    $tmp['longitude'] = $item['Geocode']['Longitude'];
                    $tmp['company_name'] = $item['AddressKeyFormat']['ConsigneeName'];
                    $tmp['address'] = $item['AddressKeyFormat']['AddressLine'];
                    $tmp['city'] = $item['AddressKeyFormat']['PoliticalDivision2'];
                    $tmp['state'] = isset($item['AddressKeyFormat']['PoliticalDivision1']) ? $item['AddressKeyFormat']['PoliticalDivision1'] : '';
                    $tmp['postcode'] = $item['AddressKeyFormat']['PostcodePrimaryLow'];
                    $tmp['country'] = $item['AddressKeyFormat']['CountryCode'];
                    $tmp['phone'] = isset($item['PhoneNumber']) ? $item['PhoneNumber'] : '';


                    $openingHours = [];
                    if (isset($item['OperatingHours']['StandardHours']['DayOfWeek']) && is_array($item['OperatingHours']['StandardHours']['DayOfWeek'])) {
                        $openingHours = $this->prepareOperatingHours($item['OperatingHours']['StandardHours']['DayOfWeek']);
                    } else if (isset($item['OperatingHours']['StandardHours'][0]['DayOfWeek']) && is_array($item['OperatingHours']['StandardHours'][0]['DayOfWeek'])) {

                        $key = array_search(10, array_column($item['OperatingHours']['StandardHours'], 'HoursType'));
                        $openingHours = $this->prepareOperatingHours($item['OperatingHours']['StandardHours'][$key]['DayOfWeek']);
                    }
                    $tmp['openingHours'] = $openingHours;

                    $locations[] = $tmp;
                } else {
                    foreach ($resp['SearchResults']['DropLocation'] as $key => $item) {
                        $tmp['latitude'] = $item['Geocode']['Latitude'];
                        $tmp['longitude'] = $item['Geocode']['Longitude'];
                        $tmp['company_name'] = $item['AddressKeyFormat']['ConsigneeName'];
                        $tmp['address'] = $item['AddressKeyFormat']['AddressLine'];
                        $tmp['city'] = $item['AddressKeyFormat']['PoliticalDivision2'];
                        $tmp['state'] = isset($item['AddressKeyFormat']['PoliticalDivision1']) ? $item['AddressKeyFormat']['PoliticalDivision1'] : '';
                        $tmp['postcode'] = $item['AddressKeyFormat']['PostcodePrimaryLow'];
                        $tmp['country'] = $item['AddressKeyFormat']['CountryCode'];
                        $tmp['phone'] = isset($item['PhoneNumber']) ? $item['PhoneNumber'] : '';


                        $openingHours = [];
                        if (isset($item['OperatingHours']['StandardHours']['DayOfWeek']) && is_array($item['OperatingHours']['StandardHours']['DayOfWeek'])) {
                            $openingHours = $this->prepareOperatingHours($item['OperatingHours']['StandardHours']['DayOfWeek']);
                        } else if (isset($item['OperatingHours']['StandardHours'][0]['DayOfWeek']) && is_array($item['OperatingHours']['StandardHours'][0]['DayOfWeek'])) {

                            $key = array_search(10, array_column($item['OperatingHours']['StandardHours'], 'HoursType'));
                            $openingHours = $this->prepareOperatingHours($item['OperatingHours']['StandardHours'][$key]['DayOfWeek']);
                        }
                        $tmp['openingHours'] = $openingHours;

                        $locations[] = $tmp;
                    }
                }
            }
        } catch (Exception $ex) {
            // echo '<pre>';
            // print_r($ex);

            return $locations;
        }

        return $locations;
    }

    public function prepareOperatingHours($days)
    {
        $weekDays = ['1' => 'Sunday', '2' => 'Monday', '3' => 'Tuesday', '4' => 'Wednesday', '5' => 'Thursday', '6' => 'Friday', '7' => 'Saturday'];
        $openingHours = [];
        foreach ($days as $key => $item) {
            if (isset($item['OpenHours']) && is_array($item['OpenHours'])) {
                foreach ($item['OpenHours'] as $key => $hour) {
                    if (strlen($hour) <= 3) {
                        $hour = '0' . $hour;
                    }
                    if (strlen($item['CloseHours'][$key]) <= 3) {
                        $item['CloseHours'][$key] = '0' . $item['CloseHours'][$key];
                    }
                    $openingHours[$weekDays[$item['Day']]][] = [date('H:i', strtotime($hour)), date('H:i', strtotime($item['CloseHours'][$key]))];
                }
            } else if (isset($item['OpenHours'])) {
                if (strlen($item['OpenHours']) <= 3) {
                    $item['OpenHours'] = '0' . $item['OpenHours'];
                }
                if (strlen($item['CloseHours']) <= 3) {
                    $item['CloseHours'] = '0' . $item['CloseHours'];
                }
                $openingHours[$weekDays[$item['Day']]][] = [date('H:i', strtotime($item['OpenHours'])), date('H:i', strtotime($item['CloseHours']))];
            }
        }

        return $openingHours;
    }

    public function getTrackingDetails($wbn)
    {
        //create soap request
        $req['RequestOption'] = '1';
        $tref['CustomerContext'] = 'Add description here';
        $req['TransactionReference'] = $tref;
        $request['Request'] = $req;
        $request['InquiryNumber'] = $wbn;
        $request['TrackingOption'] = '02';

        try {

            $mode = array(
                'soap_version' => 'SOAP_1_1',  // use soap 1.1 client
                'trace' => 1
            );

            // initialize soap client
            $client = new SoapClient($this->tracking_wsdl, $mode);

            //set endpoint url
            $client->__setLocation($this->url . '/webservices/Track');


            //create soap header
            $usernameToken['Username'] = $this->username;
            $usernameToken['Password'] = $this->password;
            $serviceAccessLicense['AccessLicenseNumber'] = $this->access_key;
            $upss['UsernameToken'] = $usernameToken;
            $upss['ServiceAccessToken'] = $serviceAccessLicense;

            $header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0', 'UPSSecurity', $upss);
            $client->__setSoapHeaders($header);



            //get response
            $resp = $client->__soapCall("ProcessTrack", array($request));

            //save soap request and response to file
            file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': TR REQUEST: ' . $client->__getLastRequest() . "\n", FILE_APPEND);
            file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': TR RESPONSE: ' . $client->__getLastResponse() . "\n\n\n", FILE_APPEND);

            // process response 
            if (isset($resp->Response->ResponseStatus->Code) && $resp->Response->ResponseStatus->Code == 1) {
                $result['shipment_events'] = $resp->Shipment->Package;
            }
        } catch (Exception $ex) {

            //save soap request and response to file
            file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': TR REQUEST: ' . $client->__getLastRequest() . "\n", FILE_APPEND);
            file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log-ups.txt', date("F j, Y, H:i:s") . ': TR RESPONSE: ' . $client->__getLastResponse() . "\n\n\n", FILE_APPEND);

            $result['error'] = 1;
            $result['message'] = $ex->detail->Errors->ErrorDetail->PrimaryErrorCode->Description;
        }

        return $result;
    }

    private function callCurl($method, $url, $data = [])
    {
        // HEADER DEFINITION
        $headers = [];

        array_push($headers, 'AccessLicenseNumber: ' . $this->access_key);
        array_push($headers, 'Username: ' . $this->username);
        array_push($headers, 'Password: ' . $this->password);
        array_push($headers, 'Content-Type: application/json');

        // CURL DEFINITION
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, true);
                $data = json_encode($data);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, true);
                $data = json_encode($data);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if (!empty($data))
                    $url = sprintf("%s/%s", $url, implode('/', $data));
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
}
