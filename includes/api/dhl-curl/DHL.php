<?php

require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/utilities/arrayToXml.php';

class DHL
{
    private $url;
    private $username;
    private $password;
    private $account_number;
    private $api_key;
    private $carrierName;
    private $carrierId;

    function __construct($username, $password, $account_number)
    {
        $this->url = 'https://wsbexpress.dhl.com:443/sndpt/';
        // $this->url = 'https://wsbexpress.dhl.com:443/gbl/';
        $this->username = $username;
        $this->password = $password;
        $this->account_number = $account_number;
        $this->api_key = 'DUoXtnNYB8PpCNNmpaShKLh7zQ0ZoyEJ';
        $this->carrierName = 'DHL Express';
        $this->carrierId = 'DHL';
    }

    private function get_timezone_offset_by_country($country_code)
    {
        $timezone = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country_code);
        $target_time_zone = new DateTimeZone($timezone[0]);
        $date_time = new DateTime('now ', $target_time_zone);
        return $date_time->format('P');
    }

    public function getQuote(array $data, $get_lowest = true)
    {
        // print_r($data); die;
        $root = [
            'rootElementName' => 'env:Envelope',
            '_attributes' => [
                'xmlns:env' => "http://www.w3.org/2003/05/soap-envelope",
                'xmlns:ns1' => "http://scxgxtt.phx-dc.dhl.com/euExpressRateBook/RateMsgRequest",
                'xmlns:ns2' => "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd",
                'xmlns:ns3' => "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd",
            ],
        ];

        $PackagesAr = [];
        $package_number = 1;

        foreach ($data['packages'] as $key => $package) {
            // for ($x = 1; $x <= intval($package['qty']); $x++) {
            $requestedPackages = [
                '_attributes' => [
                    'number' => $package_number,
                ],
                'Weight' => [
                    'Value' => $package['weight']
                ],
                'Dimensions' => [
                    'Length' => $package['length'],
                    'Width' => $package['width'],
                    'Height' => $package['height'],
                ]
            ];

            $PackagesAr['__custom:RequestedPackages:' . $package_number] = $requestedPackages;
            $package_number++;
            // }
        }
        // echo '<pre>';
        // print_r($PackagesAr);
        // exit();
        $RequestedShipment = [
            'DropOffType' => ($data['is_pickup_required']) ? 'REQUEST_COURIER' : 'REGULAR_PICKUP',
            'NextBusinessDay' => 'N',
            'Ship' => [
                'Shipper' => [
                    'City' => $data['sender_city'],
                    'PostalCode' => $data['sender_postcode'],
                    'CountryCode' => $data['sender_country_code']
                ],
                'Recipient' => [
                    'StreetLines' => 'XYZ',
                    'City' => $data['receiver_city'],
                    'PostalCode' => $data['receiver_postcode'],
                    'CountryCode' => $data['receiver_country_code']
                ]
            ],
            'Packages' => [
                $PackagesAr
            ],
            'ShipTimestamp' => $data['dispatch_date'] . 'T00:00:00 GMT+00:00',
            'UnitOfMeasurement' => 'SI',
            'Content' => $data['package_type'],
            'PaymentInfo' => 'DAP',
            'Account' => $this->account_number,

            'RequestValueAddedServices' => 'N',
            'GetDetailedRateBreakdown' => 'Y',
        ];

        $Service = [];
        if ($data['package_type'] == 'DOCUMENTS') {
            $Service['ServiceType'] = 'IB';
        } else {
            $Service['ServiceType'] = 'II';
            $Service['ServiceValue'] = $data['insurance_value'];
            $Service['CurrencyCode'] = 'EUR';
        }

        if (isset($data['insurance']) && $data['insurance'] == '1') {
            $RequestedShipment['SpecialServices'] = [
                'Service' => $Service
            ];
        }

        $array = [
            'env:Header' => [
                'ns2:Security' => [
                    '_attributes' => [
                        'env:mustUnderstand' => "true"
                    ],
                    'ns2:UsernameToken' => [
                        'ns2:Username' => $this->username,
                        'ns2:Password' => $this->password,
                        'ns2:Nonce' => 'DZERtXbXk4BAoUk6f99rTwGpw0Q=',
                        'ns3:Created' => '2021-09-20T12:50:33Z',
                    ]
                ]
            ],

            'env:Body' => [
                'ns1:RateRequest' => [
                    'RequestedShipment' => $RequestedShipment
                ]
            ],
        ];

        $arrayToXml = convert_array_to_xml($array, $root);
        $xml = $arrayToXml->dropXmlDeclaration()->toXml();
        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log.txt', date("F j, Y, H:i:s") . ': REQUEST: ' . $xml . "\n\n", FILE_APPEND);
        // echo $xml;
        // die;
        // return json_encode($data);
        // var_dump($this->callApi($xml));
        // die;
        // print_r($xml); die;

        $xml_response = $this->callApi($xml, 'expressRateBook');
        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log.txt', date("F j, Y, H:i:s") . ': RESPONSE: ' . $xml_response . "\n\n", FILE_APPEND);

        $xml_response = utf8_encode($xml_response);
        $xml_response = str_replace('xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '', $xml_response);
        $xml_response = str_replace('xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"', '', $xml_response);
        $xml_response = str_replace('xmlns:rateresp="http://scxgxtt.phx-dc.dhl.com/euExpressRateBook/RateMsgResponse"', '', $xml_response);

        // echo $xml_response; die;
        $shippingResponse = simplexml_load_string($xml_response);
        $array_xml = json_decode(json_encode(simplexml_load_string($xml_response)), true);
        $array_xml = $array_xml['SOAP-ENV:Body']['rateresp:RateResponse'];

        if (isset($array_xml['Provider']['Notification'][0])) {
            if ($array_xml['Provider']['Notification'][0]['@attributes']['code'] != '0') {
                return [
                    'carrier_name' => $this->carrierName,
                    'error' => 1,
                    'error_code' => $array_xml['Provider']['Notification'][0]['@attributes']['code'],
                    'error_message' => $array_xml['Provider']['Notification'][0]['Message']
                ];
            }
        } else {
            if ($array_xml['Provider']['Notification']['@attributes']['code'] != '0') {
                return [
                    'carrier_name' => $this->carrierName,
                    'error' => 1,
                    'error_code' => $array_xml['Provider']['Notification']['@attributes']['code'],
                    'error_message' => $array_xml['Provider']['Notification']['Message']
                ];
            }
        }

        $rates = [];
        if (isset($array_xml['Provider']['Service']['TotalNet'])) {
            $rate = $array_xml['Provider']['Service'];
            $carrierName = $this->carrierName;
            $serviceCode = $rate['@attributes']['type'];
            $ServiceName = $rate['ServiceName'];
            $label = $rate['Charges']['Charge'][0]['ChargeType'];
            $amount = $rate['TotalNet']['Amount'];
            $currency = $rate['TotalNet']['Currency'];
            $cutoffTimeOffset = $rate['CutoffTimeOffset'];
            $pickupWindowEarliestTime = $rate['PickupWindowEarliestTime'];
            $pickupWindowLatestTime = $rate['PickupWindowLatestTime'];
            $date = new \DateTime($rate['DeliveryTime']);
            $deliveryTime = $date->format('Y-m-d');

            $insurance = 0;
            $remote_area = 0;
            $extended_liability = 0;
            $restricted_destination = 0;
            $elevated_risk = 0;
            $emergency_situation = 0;
            $fuel_surcharge = 0;
            $service_charge = 0;
            $carrier_vat = 0;

            foreach ($rate['Charges']['Charge'] as $charge) {
                if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'II' || $charge['ChargeCode'] == 'IE')) {
                    $insurance = $charge['ChargeAmount'];
                }
                if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'IB')) {
                    $extended_liability = $charge['ChargeAmount'];
                }
                if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'OO')) {
                    $remote_area = $charge['ChargeAmount'];
                }
                if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'CB')) { // RESTRICTED DESTINATION
                    $restricted_destination = $charge['ChargeAmount'];
                }
                if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'CA')) { //ELEVATED RISK
                    $elevated_risk = $charge['ChargeAmount'];
                }
                // newly added  12-05-2022
                if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'CR')) {
                    $emergency_situation = $charge['ChargeAmount'];
                }
                if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'FF')) {
                    $fuel_surcharge = $charge['ChargeAmount'];
                }
                if (isset($charge['ChargeName']) && ($charge['ChargeName'] == $ServiceName)) {
                    $service_charge = $charge['ChargeAmount'];
                }
            }

            // vat calculation 
            if (isset($rate['TotalChargeTypes']['TotalChargeType'])) {
                $totalChargeType = $rate['TotalChargeTypes']['TotalChargeType'];
                $key = array_search('STTXA', array_column($totalChargeType, 'Type'));
                if ($key != '') {
                    $carrier_vat = $totalChargeType[$key]['Amount'];
                }
            }

            $rate = compact(
                'carrierName',
                'serviceCode',
                'label',
                'amount',
                'insurance',
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
        } else {
            foreach ($array_xml['Provider']['Service'] as $rate) {
                $carrierName = $this->carrierName;
                $serviceCode = $rate['@attributes']['type'];
                $ServiceName = $rate['ServiceName'];
                $label = $rate['Charges']['Charge'][0]['ChargeType'];
                $amount = $rate['TotalNet']['Amount'];
                $currency = $rate['TotalNet']['Currency'];
                $cutoffTimeOffset = $rate['CutoffTimeOffset'];
                $pickupWindowEarliestTime = $rate['PickupWindowEarliestTime'];
                $pickupWindowLatestTime = $rate['PickupWindowLatestTime'];
                $date = new \DateTime($rate['DeliveryTime']);
                $deliveryTime = $date->format('Y-m-d');

                $insurance = 0;
                $extended_liability = 0;
                $remote_area = 0;
                $restricted_destination = 0;
                $elevated_risk = 0;
                $emergency_situation = 0;
                $fuel_surcharge = 0;
                $service_charge = 0;
                $carrier_vat = 0;

                foreach ($rate['Charges']['Charge'] as $charge) {
                    if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'II' || $charge['ChargeCode'] == 'IE')) {
                        $insurance = $charge['ChargeAmount'];
                    }
                    if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'IB')) {
                        $extended_liability = $charge['ChargeAmount'];
                    }
                    if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'OO')) {
                        $remote_area = $charge['ChargeAmount'];
                    }
                    if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'CB')) {
                        $restricted_destination = $charge['ChargeAmount'];
                    }
                    if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'CA')) {
                        $elevated_risk = $charge['ChargeAmount'];
                    }
                    // newly added  12-05-2022
                    if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'CR')) {
                        $emergency_situation = $charge['ChargeAmount'];
                    }
                    if (isset($charge['ChargeCode']) && ($charge['ChargeCode'] == 'FF')) {
                        $fuel_surcharge = $charge['ChargeAmount'];
                    }
                    if (isset($charge['ChargeName']) && ($charge['ChargeName'] == $ServiceName)) {
                        $service_charge = $charge['ChargeAmount'];
                    }
                }

                // vat calculation 
                if (isset($rate['TotalChargeTypes']['TotalChargeType'])) {
                    $totalChargeType = $rate['TotalChargeTypes']['TotalChargeType'];

                    $key = array_search('STTXA', array_column($totalChargeType, 'Type'));
                    if ($key != '') {
                        $carrier_vat = $totalChargeType[$key]['Amount'];
                    }
                }


                $rate = compact(
                    'carrierName',
                    'serviceCode',
                    'label',
                    'amount',
                    'insurance',
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
        }

        $result = $rates;

        if ($get_lowest) {
            $fee = floatval($rates[0]['amount']) > 0 ? floatval($rates[0]['amount']) : 99999;
            $index = 0;
            foreach ($rates as $key => $rate) {
                if (floatval($rate['amount']) > 0 && $fee > floatval($rate['amount'])) {
                    $index = $key;
                    $fee = floatval($rate['amount']);
                }
            }
            $result = $rates[$index];
        }

        return $result;
    }

    public function updatePickup(array $data)
    {
        $root = [
            'rootElementName' => 'SOAP-ENV:Envelope',
            '_attributes' => [
                'xmlns:SOAP-ENV' => "http://schemas.xmlsoap.org/soap/envelope/",
                'xmlns:ns1' => "http://scxgxtt.phx-dc.dhl.com/euExpressRateBook/ShipmentMsgRequest",
                'xmlns:ns2' => "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd",
                'xmlns:ns3' => "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd",
            ],
        ];


        $PackagesAr = [];
        $package_number = 1;
        foreach ($data['packages'] as $key => $package) {
            for ($x = 1; $x <= intval($package['qty']); $x++) {
                $requestedPackages = [
                    '_attributes' => [
                        'number' => $package_number
                    ],
                    'Weight' => $package['weight'],
                    'Dimensions' => [
                        'Length' => $package['length'],
                        'Width' => $package['width'],
                        'Height' => $package['height'],
                    ],
                    'CustomerReferences' => 'ABC'
                ];
                $package_number++;
                $PackagesAr['__custom:RequestedPackages:' . ($key + 1)] = $requestedPackages;
            }
        }

        $ShipperAddress = [
            'City' => 'Paris',
            'PostalCode' => '75014',
            'CountryCode' => 'FR',
        ];

        if (isset($data['sender_address'][0])) {
            $ShipperAddress['StreetLines']  = $data['sender_address'][0];
        }
        if (isset($data['sender_address'][1]) && !empty($data['sender_address'][1])) {
            $ShipperAddress['StreetLines2']  = $data['sender_address'][1];
        }
        if (isset($data['sender_address'][2]) && !empty($data['sender_address'][2])) {
            $ShipperAddress['StreetLines3']  = $data['sender_address'][2];
        }

        $array = [
            'SOAP-ENV:Header' => [
                'ns2:Security' => [
                    '_attributes' => [
                        'SOAP-ENV:mustUnderstand' => "1"
                    ],
                    'ns2:UsernameToken' => [
                        'ns2:Username' => $this->username,
                        'ns2:Password' => $this->password,
                        'ns2:Nonce' => 'DZERtXbXk4BAoUk6f99rTwGpw0Q=',
                        'ns3:Created' => '2021-09-20T12:50:33Z',
                    ]
                ]
            ],

            'SOAP-ENV:Body' => [
                'ns1:UpdatePickUpRequest' => [
                    // 'DispatchConfirmationNumber' => $data['dispatch_number'],
                    'DispatchConfirmationNumber' => 'PRG211007030488',
                    'OriginalShipperAccountNumber' => $this->account_number,
                    'PickUpShipment' => [
                        'ShipmentDetails' => [
                            'ShipmentDetail' => [
                                'UnitOfMeasurement' => 'SI',
                                'Packages' => $PackagesAr
                            ]
                        ],
                        'PickupTimestamp' => $data['dispatch_date'] . 'T' . $data['e_pickup_time'] .  ':00GMT+02:00',
                        'PickupLocationCloseTime' => $data['l_pickup_time'],
                        'Billing' => [
                            'ShipperAccountNumber' => $this->account_number,
                            'ShippingPaymentType' => 'R',
                            'BillingAccountNumber' => $this->account_number
                        ],
                        'Ship' => [
                            'Shipper' => [
                                'Contact' => [
                                    'PersonName' => $data['sender_first_name'] . ' ' . $data['sender_last_name'],
                                    'CompanyName' => $data['sender_company_name'],
                                    'PhoneNumber' => $data['sender_phone_number'],
                                ],
                                'Address' => $ShipperAddress
                            ]
                        ]

                    ]
                ]
            ],
        ];

        $arrayToXml = convert_array_to_xml($array, $root);
        $xml = $arrayToXml->dropXmlDeclaration()->toXml();

        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log.txt', date("F j, Y, H:i:s") . ': UP REQUEST: ' . $xml . "\n\n", FILE_APPEND);

        // return json_encode($data);
        // var_dump($this->callApi($xml));
        // die;
        // print_r($xml); die;
        $xml_response = $this->callApi($xml, 'updatePickup');
        // print_r($xml_response); die;
        $xml_response = str_replace('xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '', $xml_response);
        $xml_response = str_replace('xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"', '', $xml_response);
        $xml_response = str_replace('xmlns:updatePickupRes="http://scxgxtt.phx-dc.dhl.com/ExpressUpdatePickUp/UpdatePickUpResponse"', '', $xml_response);
        // $shippingResponse = simplexml_load_string($xml_response);
        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log.txt', date("F j, Y, H:i:s") . ': UP RESPONSE: ' . $xml_response, FILE_APPEND);
        $array_xml = json_decode(json_encode(simplexml_load_string($xml_response)), true);
        $response = $array_xml['SOAP-ENV:Body']['updatePickupRes:UpdatePickUpResponse'];

        if ($response['Notification']['Message'] == 'Successfully Updated') {
            return true;
        } else {
            return false;
        }
        // var_dump([
        //     'package_result' => $response['PackagesResult'],
        //     'label' => $response['LabelImage']['GraphicImage'],
        //     'invoice' => $response['Documents']['Document']['DocumentImage'],
        //     'dhl_shipment_id' => $response['ShipmentIdentificationNumber'],
        //     'pickup_details' => $response['PickupDetails']
        // ]); die;
        // return [
        //     'package_result' => $response['PackagesResult'],
        //     'label' => $response['LabelImage']['GraphicImage'],
        //     'invoice' => $response['Documents']['Document']['DocumentImage'],
        //     'dhl_shipment_id' => $response['ShipmentIdentificationNumber'],
        //     'dispatch_confirmation_nummber' => $response['DispatchConfirmationNumber'],
        //     'pickup_details' => $response['PickupDetails']
        // ];
    }

    public function createShipment(array $data)
    {
        $getQuoteResult = unserialize($data['get_quote_result']);

        $ShipmentType = $getQuoteResult['serviceCode'];

        $root = [
            'rootElementName' => 'SOAP-ENV:Envelope',
            '_attributes' => [
                'xmlns:SOAP-ENV' => "http://schemas.xmlsoap.org/soap/envelope/",
                'xmlns:ns1' => "http://scxgxtt.phx-dc.dhl.com/euExpressRateBook/ShipmentMsgRequest",
                'xmlns:ns2' => "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd",
                'xmlns:ns3' => "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd",
            ],
        ];

        // Construct sender address
        // $senderAddress = [
        //     'City' => 'Paris',
        //     'PostalCode' => '75014',
        //     'CountryCode' => 'FR',
        // ];
        $senderAddress = [
            'City' => $data['sender_city'],
            'CountryCode' => $data['sender_country_code'],
        ];
        if (isset($data['sender_address'][0])) {
            $senderAddress['StreetLines']  = $data['sender_address'][0];
        }
        if (isset($data['sender_address'][1]) && !empty($data['sender_address'][1])) {
            $senderAddress['StreetLines2']  = $data['sender_address'][1];
        }
        if (isset($data['sender_address'][2]) && !empty($data['sender_address'][2])) {
            $senderAddress['StreetLines3']  = $data['sender_address'][2];
        }

        if (isset($data['sender_postcode']) && $data['sender_postcode'] != '') {
            $senderAddress['PostalCode'] = $data['sender_postcode'];
        } else {
            $senderAddress['PostalCode'] = '';
        }

        if (isset($data['sender_suburb']) && $data['sender_suburb'] != '') {
            $senderAddress['Suburb'] = $data['sender_suburb'];
        }
        if (isset($data['sender_state']) && $data['sender_state'] != '') {
            $senderAddress['StateOrProvinceCode'] = $data['sender_state'];
        }

        // Construct receiver address
        $receiverAddress = [
            'City' => $data['receiver_city'],
            'CountryCode' => $data['receiver_country_code'],
        ];
        if (isset($data['receiver_address'][0])) {
            $receiverAddress['StreetLines']  = $data['receiver_address'][0];
        }
        if (isset($data['receiver_address'][1]) && !empty($data['receiver_address'][1])) {
            $receiverAddress['StreetLines2']  = $data['receiver_address'][1];
        }
        if (isset($data['receiver_address'][2]) && !empty($data['receiver_address'][2])) {
            $receiverAddress['StreetLines3']  = $data['receiver_address'][2];
        }

        if (isset($data['receiver_postcode']) && $data['receiver_postcode'] != '') {
            $receiverAddress['PostalCode'] = $data['receiver_postcode'];
        } else {
            $receiverAddress['PostalCode'] = '';
        }
        if (isset($data['receiver_suburb']) && $data['receiver_suburb'] != '') {
            $receiverAddress['Suburb'] = $data['receiver_suburb'];
        }
        if (isset($data['receiver_state']) && $data['receiver_state'] != '') {
            $receiverAddress['StateOrProvinceCode'] = $data['receiver_state'];
        }


        $exportLineItemsAr = [];
        foreach ($data['items'] as $key => $item) {
            $exportLineItem = [
                'CommodityCode' => $item['commodity_code'],
                // 'ExportReasonType' => 'PERMANENT',
                'ItemNumber' => $key + 1,
                'Quantity' => $item['quantity'],
                'QuantityUnitOfMeasurement' => $item['units'],
                'ItemDescription' => $item['item_description'],
                'UnitPrice' => $item['item_value'],
                'NetWeight' => $item['net_weight'], // $item['net_weight']
                'GrossWeight' => $item['gross_weight'], // $item['gross_weight']
                'ManufacturingCountryCode' => $item['item_origin'],
            ];
            $exportLineItemsAr['__custom:ExportLineItem:' . ($key + 1)] = $exportLineItem;
        }


        $PackagesAr = [];
        $package_number = 1;
        foreach ($data['packages'] as $key => $package) {
            for ($x = 1; $x <= intval($package['qty']); $x++) {
                $requestedPackages = [
                    '_attributes' => [
                        'number' => $package_number
                    ],
                    'Weight' => $package['weight'],
                    'Dimensions' => [
                        'Length' => $package['length'],
                        'Width' => $package['width'],
                        'Height' => $package['height'],
                    ],
                    'CustomerReferences' => 'ABC'
                ];
                $package_number++;
                $PackagesAr['__custom:RequestedPackages:' . ($key + 1)] = $requestedPackages;
            }
        }

        $recipient = [
            'BusinessPartyTypeCode' => $data['receiver_trade_type'],
            'Contact' => [
                'PersonName' => $data['receiver_first_name'] . ' ' . $data['receiver_last_name'],
                'CompanyName' => $data['receiver_company_name'],
                'PhoneNumber' => $data['receiver_phone_number'],
                'EmailAddress' => $data['receiver_email'],
            ],
            'Address' => $receiverAddress,
        ];

        // if (!empty($data['receiver_tva_number'])) {
        //     $recipient['RegistrationNumbers'] = [
        //         'RegistrationNumber' => [
        //             'Number' => $data['receiver_tva_number'],
        //             'NumberTypeCode' => 'VAT',
        //             'NumberIssuerCountryCode' => $data['receiver_country_code'],
        //         ]
        //     ];
        // }

        $RRegistrationNumbersAr = [];
        $r_n_r = 1;
        if (!empty($data['receiver_tva_number'])) {
            $VATRegistrationNumber = [
                'Number' => $data['receiver_tva_number'],
                'NumberTypeCode' => 'VAT',
                'NumberIssuerCountryCode' => $data['receiver_country_code'],
            ];
            $RRegistrationNumbersAr['__custom:RegistrationNumber:' . $r_n_r] = $VATRegistrationNumber;

            $r_n_r++;
        }

        if (!empty($data['receiver_eori_number'])) {
            $EORRegistrationNumber = [
                'Number' => $data['receiver_eori_number'],
                'NumberTypeCode' => 'EOR',
                'NumberIssuerCountryCode' => $data['receiver_country_code'],
            ];
            $RRegistrationNumbersAr['__custom:RegistrationNumber:' . $r_n_r] = $EORRegistrationNumber;

            $r_n_r++;
        }

        if (sizeof($RRegistrationNumbersAr) > 0) $recipient['RegistrationNumbers'] = [$RRegistrationNumbersAr];

        $shipper = [
            'BusinessPartyTypeCode' => $data['sender_trade_type'],
            'Contact' => [
                'PersonName' => $data['sender_first_name'] . ' ' . $data['sender_last_name'],
                'CompanyName' => $data['sender_company_name'],
                'PhoneNumber' => $data['sender_phone_number'],
                'EmailAddress' => $data['sender_email'],
            ],
            'Address' => $senderAddress,

        ];

        // if (!empty($data['sender_tva_number'])) {
        //     $shipper['RegistrationNumbers'] = [
        //         'RegistrationNumber' => [
        //             'Number' => $data['sender_tva_number'],
        //             'NumberTypeCode' => 'EOR',
        //             'NumberIssuerCountryCode' => $data['sender_country_code'],
        //         ]
        //     ];
        // }

        $RegistrationNumbersAr = [];
        $r_n_s = 1;
        if (!empty($data['sender_tva_number'])) {
            $VATRegistrationNumber = [
                'Number' => $data['sender_tva_number'],
                'NumberTypeCode' => 'VAT',
                'NumberIssuerCountryCode' => $data['sender_country_code'],
            ];
            $RegistrationNumbersAr['__custom:RegistrationNumber:' . $r_n_s] = $VATRegistrationNumber;

            $r_n_s++;
        }

        if (!empty($data['sender_eori_number'])) {
            $EORRegistrationNumber = [
                'Number' => $data['sender_eori_number'],
                'NumberTypeCode' => 'EOR',
                'NumberIssuerCountryCode' => $data['sender_country_code'],
            ];
            $RegistrationNumbersAr['__custom:RegistrationNumber:' . $r_n_s] = $EORRegistrationNumber;

            $r_n_s++;
        }

        if (!empty($data['sender_id_number'])) {
            $TAXRegistrationNumber = [
                'Number' => $data['sender_id_number'],
                'NumberTypeCode' => 'SDT',
                'NumberIssuerCountryCode' => $data['sender_country_code'],
            ];
            $RegistrationNumbersAr['__custom:RegistrationNumber:' . $r_n_s] = $TAXRegistrationNumber;

            $r_n_s++;
        }

        if (sizeof($RegistrationNumbersAr) > 0) $shipper['RegistrationNumbers'] = [$RegistrationNumbersAr];

        $ExportDeclaration = [
            'InvoiceDate' => date("Y-m-d"),
            'InvoiceNumber' => '001',
            'PayerGSTVAT' => "Receiver will pay",
        ];

        if ($data['remarks'] != '') {
            $ExportDeclaration['Remarks'] = [
                'Remark' => [
                    'RemarkDescription' => $data['remarks']
                ]
            ];
        }

        $LabelOption = [
            'RequestWaybillDocument' => 'Y',
        ];
        if ($data['package_type'] == 'NON_DOCUMENTS') {
            $ExportDeclaration['ExportLineItems'] = [$exportLineItemsAr];
            $ExportDeclaration['ExportReasonType'] = $data['export_reason_type'];
            $LabelOption['RequestDHLCustomsInvoice'] = 'N';
            $LabelOption['DHLCustomsInvoiceType'] = 'COMMERCIAL_INVOICE';
        }

        $Commodities = [
            'Description' => !empty($data['shipment_description']) ? $data['shipment_description'] : 'Shipment'
        ];

        if (!empty($data['total_customs_value']) && $data['total_customs_value'] > 0) {
            $Commodities['CustomsValue'] = $data['total_customs_value'];
        }




        $InternationalDetail = [[
            'Commodities' => $Commodities,
            'Content' => $data['package_type'],
        ],];

        if ($data['package_type'] == 'NON_DOCUMENTS') {
            $InternationalDetail[0]['ExportDeclaration'] = $ExportDeclaration;
        }

        $ShipmentInfo = [
            'DropOffType' => ($data['is_pickup_required']) ? 'REQUEST_COURIER' : 'REGULAR_PICKUP',
            'ServiceType' => $ShipmentType,
            'Billing' => [
                'ShipperAccountNumber' => $this->account_number,
                'ShippingPaymentType' => 'R',
                'BillingAccountNumber' => $this->account_number,
            ],
            'Currency' => 'EUR',
            'PaperlessTradeEnabled' => 0,
            'UnitOfMeasurement' => 'SI',
            'LabelOptions' => $LabelOption,
            'RequestPickupDetails' => 'Y',
        ];

        $Service = [];
        if ($data['package_type'] == 'DOCUMENTS') {
            $Service['ServiceType'] = 'IB';
        } else {
            $Service['ServiceType'] = 'II';
            $Service['ServiceValue'] = $data['insurance_value'];
            $Service['CurrencyCode'] = 'EUR';
        }

        if (isset($data['insurance']) && $data['insurance'] == '1') {
            $ShipmentInfo['SpecialServices'] = [
                'Service' => $Service
            ];
        }

        $RequestedShipment = [
            'ShipmentInfo' => $ShipmentInfo,
            // 'ShipTimestamp' => $data['dispatch_date'] . 'T00:00:00 GMT+' . $this->get_timezone_offset_by_country($data['sender_country_code']),
            'PaymentInfo' => 'DAP',
            'InternationalDetail' => $InternationalDetail,
            'Ship' => [
                'Shipper' => $shipper,
                'Recipient' => $recipient,
            ],
            'Packages' => [
                $PackagesAr
            ]
        ];
        if ($data['is_pickup_required']) {
            $RequestedShipment['ShipTimestamp'] = $data['dispatch_date'] . 'T' . $data['earliest_pickup_time'] . ':00 GMT' . $this->get_timezone_offset_by_country($data['sender_country_code']);

            $RequestedShipment['PickupLocation'] = $data['pickup_location'];

            $RequestedShipment['PickupLocationCloseTime'] = $data['latest_pickup_time'];

            if ($data['special_pickup_instructions'] != '')
                $RequestedShipment['SpecialPickupInstruction'] = $data['special_pickup_instructions'];
        } else {
            $RequestedShipment['ShipTimestamp'] = $data['dispatch_date'] . 'T01:00:00 GMT' . $this->get_timezone_offset_by_country($data['sender_country_code']);
        }

        // print_r($exportLineItemsAr);
        // die;

        $array = [
            'SOAP-ENV:Header' => [
                'ns2:Security' => [
                    '_attributes' => [
                        'SOAP-ENV:mustUnderstand' => "1"
                    ],
                    'ns2:UsernameToken' => [
                        'ns2:Username' => $this->username,
                        'ns2:Password' => $this->password,
                        'ns2:Nonce' => 'DZERtXbXk4BAoUk6f99rTwGpw0Q=',
                        'ns3:Created' => '2021-09-20T12:50:33Z',
                    ]
                ]
            ],
            'SOAP-ENV:Body' => [
                'ns1:ShipmentRequest' => [
                    'RequestedShipment' => $RequestedShipment,
                ],
            ],
        ];


        // $arrayToXml = new ArrayToXml($array, $root);
        $arrayToXml = convert_array_to_xml($array, $root);
        $xml = $arrayToXml->dropXmlDeclaration()->toXml();

        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log.txt', date("F j, Y, H:i:s") . ': SR REQUEST: ' . $xml . "\n\n", FILE_APPEND);

        // echo '<pre>';
        // print_r($RequestedShipment);
        // exit();

        // return json_encode($data);
        // echo ($xml);
        // die;

        $xml_response = $this->callApi($xml, 'expressRateBook');
        // print_r($xml_response);
        // die;
        $xml_response = utf8_encode($xml_response);
        $xml_response = str_replace('xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '', $xml_response);
        $xml_response = str_replace('xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"', '', $xml_response);
        $xml_response = str_replace('xmlns:shipresp="http://scxgxtt.phx-dc.dhl.com/euExpressRateBook/ShipmentMsgResponse"', '', $xml_response);

        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log.txt', date("F j, Y, H:i:s") . ': SR RESPONSE: ' . $xml_response, FILE_APPEND);
        $shippingResponse = simplexml_load_string($xml_response);
        $array_xml = json_decode(json_encode(simplexml_load_string($xml_response)), true);
        $response = $array_xml['SOAP-ENV:Body']['shipresp:ShipmentResponse'];

        // echo '<pre>';
        // print_r($response);
        // exit();

        return [
            'waybill_number' => $response['ShipmentIdentificationNumber'],
            'label' => $response['LabelImage']['GraphicImage'],
            'invoice' => $response['Documents']['Document']['DocumentImage'],
            'dhl_shipment_id' => $response['ShipmentIdentificationNumber'],
            'dispatch_confirmation_nummber' => $response['DispatchConfirmationNumber'],
            'pickup_details' => $response['PickupDetails']
        ];
        // print_r(['LabelImage']);
        // die;
        // return $result;
    }

    public function getDropoffLocations($data)
    {
        $address = unserialize($data->sender_address);

        $params = [
            'countryCode' => "$data->sender_country_code",
            'addressLocality' => "$data->sender_city",  // city
            'postalCode' => "$data->sender_postcode",
            'streetAddress' => "$address[0]",
            'radius' => 100000,
            'limit' => 10,
        ];

        // $api_url = 'https://api-sandbox.dhl.com/location-finder/v1/find-by-address'; // test 
        $api_url = 'https://api.dhl.com/location-finder/v1/find-by-address'; // live
        $url = $api_url . '?' . \http_build_query($params);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "$url",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "DHL-API-Key: $this->api_key"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new Exception("cURL Error #:" . $err);
        }

        $resp = json_decode($response);

        $locations = [];

        if (isset($resp->locations) && is_array($resp->locations)) {
            foreach ($resp->locations as $key => $item) {

                // check if the location provides drop-off service 
                if (in_array('parcel:drop-off', $item->serviceTypes)) {
                    $tmp['latitude'] = $item->place->geo->latitude;
                    $tmp['longitude'] = $item->place->geo->longitude;
                    $tmp['company_name'] = $item->name;
                    $tmp['address'] = $item->place->address->streetAddress;
                    $tmp['city'] = $item->place->address->addressLocality;
                    $tmp['state'] = '';
                    $tmp['postcode'] = $item->place->address->postalCode;
                    $tmp['country'] = $item->place->address->countryCode;
                    $tmp['phone'] = '';

                    $openingHours = [];
                    if (isset($item->openingHours) && is_array($item->openingHours)) {
                        foreach ($item->openingHours as $key => $item) {
                            $day = str_replace("http://schema.org/", "", $item->dayOfWeek);
                            $openingHours[$day][] = [date('H:i', strtotime($item->opens)), date('H:i', strtotime($item->closes))];
                        }
                    }
                    $tmp['openingHours'] = $openingHours;

                    $locations[] = $tmp;
                }
            }
        }

        return $locations;
    }

    public function getTrackingDetails($wbn)
    {
        // print_r($data); die;
        $root = [
            'rootElementName' => 'env:Envelope',
            '_attributes' => [
                'xmlns:env' => "http://www.w3.org/2003/05/soap-envelope",
                'xmlns:ns1' => "http://scxgxtt.phx-dc.dhl.com/euExpressRateBook/RateMsgRequest",
                'xmlns:ns2' => "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd",
                'xmlns:ns3' => "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd",
            ],
        ];

        $array = [
            'env:Header' => [
                'ns2:Security' => [
                    '_attributes' => [
                        'env:mustUnderstand' => "true"
                    ],
                    'ns2:UsernameToken' => [
                        'ns2:Username' => $this->username,
                        'ns2:Password' => $this->password,
                        'ns2:Nonce' => 'DZERtXbXk4BAoUk6f99rTwGpw0Q=',
                        'ns3:Created' => '2021-09-20T12:50:33Z',
                    ]
                ]
            ],

            'env:Body' => [
                'trackShipmentRequest' => [
                    'trackingRequest' => [
                        'TrackingRequest' => [
                            'Request' => [
                                'ServiceHeader' => [
                                    'MessageTime' => '2010-11-24T00:17:20Z',
                                    'MessageReference' => '1234567891234567891234567891'
                                ]
                            ],
                            'AWBNumber' => [
                                'ArrayOfAWBNumberItem' => $wbn
                            ],
                            'LevelOfDetails' => 'ALL_CHECKPOINTS',
                            'PiecesEnabled' => 'S'
                        ]
                    ]
                ]
            ],
        ];

        $arrayToXml = convert_array_to_xml($array, $root);
        $xml = $arrayToXml->dropXmlDeclaration()->toXml();
        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log.txt', date("F j, Y, H:i:s") . ': TR REQUEST: ' . $xml . "\n\n", FILE_APPEND);
        // echo $xml;
        // die;
        // return json_encode($data);
        // var_dump($this->callApi($xml));
        // die;
        // print_r($xml); die;
        $xml_response = $this->callApi($xml, 'glDHLExpressTrack');

        file_put_contents(LABAL_COURRIER_PLUGIN_PATH . '/log.txt', date("F j, Y, H:i:s") . ': TR RESPONSE: ' . $xml_response . "\n\n", FILE_APPEND);

        // echo $xml_response;
        // die;
        $xml_response = utf8_encode($xml_response);
        $xml_response = str_replace('xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '', $xml_response);
        $xml_response = str_replace('xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"', '', $xml_response);
        $xml_response = str_replace('xmlns:ser-root="http://scxgxtt.phx-dc.dhl.com/glDHLExpressTrack/providers/services/trackShipment"', '', $xml_response);
        $xml_response = str_replace('xmlns:ns="http://www.dhl.com"', '', $xml_response);

        // echo $xml_response; die;
        $shippingResponse = simplexml_load_string($xml_response);
        $array_xml = json_decode(json_encode(simplexml_load_string($xml_response)), true);
        // print_r(); die;
        $ArrayOfAWBInfoItem = $array_xml['SOAP-ENV:Body']['ser-root:trackShipmentRequestResponse']['trackingResponse']['ns:TrackingResponse']['AWBInfo']['ArrayOfAWBInfoItem'];

        $status = $ArrayOfAWBInfoItem['Status']['ActionStatus'];

        $result = ['error' => 0];
        if ($status != "Success") {
            $result['error'] = 1;
            $result['message'] = $status;
        } else {
            $ArrayOfShipmentEventItem = $ArrayOfAWBInfoItem['ShipmentInfo']['ShipmentEvent']['ArrayOfShipmentEventItem'];
            $result['shipment_events'] = $ArrayOfShipmentEventItem;
            $result['shipper'] = $ArrayOfAWBInfoItem['ShipmentInfo']['Shipper'];
            $result['consignee'] = $ArrayOfAWBInfoItem['ShipmentInfo']['Consignee'];
        }
        return $result;
    }
    private function callApi(string $xml, $endpoint)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/xml'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}
