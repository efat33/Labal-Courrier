<?php

class LC_Rate
{

    const LC_IMP = 'IMPORT';
    const LC_EXP = 'EXPORT';
    const LC_NOR = 'NORMAL';

    private $import_rate_categories;
    private $export_rate_categories;
    private $normal_rate_categories;
    private $vat;
    private $ups;

    private $eu_countries;

    private $col_zone;
    private $del_zone;

    function __construct()
    {
        require_once LABAL_COURRIER_PLUGIN_PATH . '/includes/api/ups/UPS.php';
        $this->ups = new UPS();

        $this->eu_countries = [
            'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'FI', 'FR', 'DE', 'GR',
            'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PF', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'
        ];

        $this->vat = 20 / 100;
        $this->import_rate_categories = array(
            [
                'category_id' => 'document-1-2',
                'title' => 'Documents up to 2kg',
                'package_type' => 'document',
                'min_weight' => 0.0,
                'max_weight' => 2.0,
            ],
            [
                'category_id' => 'package-1-5',
                'title' => 'Package 1kg to 5kg',
                'package_type' => 'package',
                'min_weight' => 0.0,
                'max_weight' => 5.0,
            ],
            [
                'category_id' => 'package-5-10',
                'title' => 'Package 5kg to 10kg',
                'package_type' => 'package',
                'min_weight' => 5.0,
                'max_weight' => 10.0,
            ],
            [
                'category_id' => 'package-10-15',
                'title' => 'Package 10kg to 15 kgs',
                'package_type' => 'package',
                'min_weight' => 10.0,
                'max_weight' => 15.0,
            ],
            [
                'category_id' => 'package-15-in',
                'title' => 'Package 15kg to infinity',
                'package_type' => 'package',
                'min_weight' => 15.0,
                'max_weight' => 100000,
            ],
        );
        $this->export_rate_categories = array(
            [
                'category_id' => 'document-1-2',
                'title' => 'Documents up to 2kg',
                'package_type' => 'document',
                'min_weight' => 0.0,
                'max_weight' => 2.0,
            ],
            [
                'category_id' => 'package-1-5',
                'title' => 'Package 1kg to 5kg',
                'package_type' => 'package',
                'min_weight' => 0.0,
                'max_weight' => 5.0,
            ],
            [
                'category_id' => 'package-5-10',
                'title' => 'Package 5kg to 10kg',
                'package_type' => 'package',
                'min_weight' => 5.0,
                'max_weight' => 10.0,
            ],
            [
                'category_id' => 'package-10-15',
                'title' => 'Package 10kg to 15 kgs',
                'package_type' => 'package',
                'min_weight' => 10.0,
                'max_weight' => 15.0,
            ],
            [
                'category_id' => 'package-15-in',
                'title' => 'Package 15kg to infinity',
                'package_type' => 'package',
                'min_weight' => 15.0,
                'max_weight' => 100000,
            ],
        );
        $this->normal_rate_categories = array(
            [
                'category_id' => 'package-1-15',
                'title' => 'Package 1kg to 15kg',
                'package_type' => 'package',
                'min_weight' => 0.0,
                'max_weight' => 15.0,
            ],
            [
                'category_id' => 'package-15-in',
                'title' => 'Package 15kg to Infinity',
                'package_type' => 'package',
                'min_weight' => 15.0,
                'max_weight' => 100000,
            ],
        );
    }

    public function get_import_rate_categories()
    {
        return $this->import_rate_categories;
    }

    public function get_export_rate_categories()
    {
        return $this->export_rate_categories;
    }

    public function get_normal_rate_categories()
    {
        return $this->normal_rate_categories;
    }

    public function get_coefficient($shipment_type, $package_type, $packages, $col_zone, $del_zone, $rate)
    {
        $prof_coef = false;
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $roles = (array) $user->roles;

            if (in_array('professional', $roles)) {
                $prof_coef = true;
            }
        }

        $this->del_zone = $del_zone;
        $this->col_zone = $col_zone;

        if ($shipment_type == self::LC_IMP && ($this->col_zone == '' || $this->col_zone == false)) {
            $shipment_type = self::LC_NOR;
        } else if ($shipment_type == self::LC_EXP && ($this->del_zone == '' || $this->del_zone == false)) {
            $shipment_type = self::LC_NOR;
        }

        $normal_weight = 0;
        $vol_weight = 0;
        foreach ($packages as $package) {
            $normal_weight += floatval($package['weight']) * intval($package['qty']);

            for ($x = 0; $x < (intval($package['qty'])); $x++) {
                $vol_weight += floatval((floatval($package['length']) * floatval($package['width']) * floatval($package['height'])) / 5000);
            }
        }

        $rate_categories = $this->get_rate_categories($shipment_type);

        $weight = ($normal_weight > $vol_weight) ? $normal_weight : $vol_weight;

        if ($weight > 2) {
            $package_type = 'Package';
        }

        $rate_category_id = $this->get_category_id($rate_categories, $weight, $package_type, $shipment_type);

        $rates = [];
        if ($shipment_type == self::LC_IMP) {
            if ($rate['carrierName'] == 'UPS') {
                if ($prof_coef) {
                    $option = get_user_meta(get_current_user_id(), 'ups_' . $rate['label'] . '_import_rate_' . $this->col_zone, true);
                    if (empty($option)) {
                        $option = get_option('ups_' . $rate['label'] . '_import_rate_' . $this->col_zone);
                    }
                } else {
                    $option = get_option('ups_' . $rate['label'] . '_import_rate_' . $this->col_zone);
                }
            } else {
                if ($prof_coef) {
                    $option = get_user_meta(get_current_user_id(), 'lc_import_export_rate_' . $this->col_zone, true);
                    if (empty($option)) {
                        $option = get_option('lc_import_export_rate_' . $this->col_zone);
                    }
                } else {
                    $option = get_option('lc_import_export_rate_' . $this->col_zone);
                }
            }

            $rates = $option['import_rate']['coefficient'];
        } elseif ($shipment_type == self::LC_EXP) {
            if ($rate['carrierName'] == 'UPS') {
                if ($prof_coef) {
                    $option = get_user_meta(get_current_user_id(), 'ups_' . $rate['label'] . '_export_rate_' . $this->del_zone, true);
                    if (empty($option)) {
                        $option = get_option('ups_' . $rate['label'] . '_export_rate_' . $this->del_zone);
                    }
                } else {
                    $option = get_option('ups_' . $rate['label'] . '_export_rate_' . $this->del_zone);
                }
            } else {
                if ($prof_coef) {
                    $option = get_user_meta(get_current_user_id(), 'lc_import_export_rate_' . $this->del_zone, true);
                    if (empty($option)) {
                        $option = get_option('lc_import_export_rate_' . $this->del_zone);
                    }
                } else {
                    $option = get_option('lc_import_export_rate_' . $this->del_zone);
                }
            }

            $rates = $option['export_rate']['coefficient'];
        } else {
            if ($prof_coef) {
                $option = get_user_meta(get_current_user_id(), 'lc_normal_rate', true);
                if (empty($option)) {
                    $option = get_option('lc_normal_rate');
                }
            } else {
                $option = get_option('lc_normal_rate');
            }

            $rates = $option['coefficient'];
        }

        $coefficient = floatval($rates[$rate_category_id]);

        // if ($rate['serviceCode'] == 65) {
        //     echo '<br>serviceCode = ' . $rate['serviceCode'];
        //     echo '<br>shipment_type = ' . $shipment_type;
        //     echo '<br>del_zone = ' . $del_zone;
        //     echo '<br>col_zone = ' . $col_zone;
        //     echo '<br>coefficient = ' . $coefficient;
        //     exit;
        // }


        if (!$coefficient && $rate['carrierName'] != 'UPS') {
            throw new Exception('Sorry, Something went wrong! Please contact us to assist you.');
        }

        return $coefficient;
    }

    private function get_rate_categories($shipment_type)
    {
        $rate_category = [];
        if ($shipment_type == self::LC_IMP) {
            $rate_category = $this->import_rate_categories;
        } elseif ($shipment_type == self::LC_EXP) {
            $rate_category = $this->export_rate_categories;
        } else {
            $rate_category = $this->normal_rate_categories;
        }
        return $rate_category;
    }

    private function get_category_id($rate_categories, $total_weight, $package_type, $shipment_type)
    {
        $category_id = '';
        foreach ($rate_categories as $category) {
            if (
                (ucfirst($category['package_type']) == $package_type
                    && $category['min_weight'] < $total_weight
                    && $category['max_weight'] >= $total_weight)
                || ($shipment_type == self::LC_NOR // If normal shipment do not consider whether the "package_type" is documnet or package
                    && $category['min_weight'] < $total_weight
                    && $category['max_weight'] >= $total_weight)
            ) {
                $category_id = $category['category_id'];
            }
        }
        return $category_id;
    }

    public function calculate_rate($shipment_type, $coefficient, $carrier_rates, $is_insurance, $vat_available = false, $data)
    {
        $zone_dhl = new LC_Zone;
        $zone_ups = new UPS_Zone;

        $is_vat_applicable_to_margin = false;
        $is_vat_applicable_to_carrier = false;

        // echo '<pre>';
        // print_r($carrier_rates);
        // exit();
        if (in_array($data['col_country'], $this->eu_countries) && in_array($data['del_country'], $this->eu_countries)) {
            $is_vat_applicable_to_margin = true;
            $is_vat_applicable_to_carrier = true;
        } else if ($shipment_type == self::LC_IMP) {
            $is_vat_applicable_to_margin = true;
        }

        foreach ($carrier_rates as $key => $rate) {
            $carrier_rate = $rate;

            if ($rate['carrierName'] == 'UPS') {
                if ($shipment_type == self::LC_EXP) {
                    $col_zone = ($data['col_country'] != LC_ORG_COUNTRY) ? $zone_ups->get_zone_by_country($data['col_country'], $rate['label'] . '_sending_zone') : false;
                    $del_zone = ($data['del_country'] != LC_ORG_COUNTRY) ? $zone_ups->get_zone_by_country($data['del_country'], $rate['label'] . '_sending_zone') : false;
                } else {
                    $col_zone = ($data['col_country'] != LC_ORG_COUNTRY) ? $zone_ups->get_zone_by_country($data['col_country'], $rate['label'] . '_receiving_zone') : false;
                    $del_zone = ($data['del_country'] != LC_ORG_COUNTRY) ? $zone_ups->get_zone_by_country($data['del_country'], $rate['label'] . '_receiving_zone') : false;
                }
            } else {
                $col_zone = ($data['col_country'] != LC_ORG_COUNTRY) ? $zone_dhl->get_zone_by_country($data['col_country']) : false;
                $del_zone = ($data['del_country'] != LC_ORG_COUNTRY) ? $zone_dhl->get_zone_by_country($data['del_country']) : false;
            }

            // determine coeffient, it varies depending on the courrier service 
            $coefficient = $this->get_coefficient($shipment_type, $data['package_type'], $data['package'], $col_zone, $del_zone, $rate);

            if (!$coefficient) {
                unset($carrier_rates[$key]);
                continue;
            }

            $carrier_amount = floatval($rate['amount']);
            $insurance = $rate['insurance'];
            $extended_liability = $rate['extended_liability'];
            $remote_area = $rate['remote_area'];
            $restricted_destination = $rate['restricted_destination'];
            $elevated_risk = $rate['elevated_risk'];
            $emergency_situation = $rate['emergency_situation'];
            $fuel_surcharge = $rate['fuel_surcharge'];
            $service_charge = $rate['service_charge'];

            $carrier_core_amount = $service_charge + $fuel_surcharge;
            $carrier_other_amount = $carrier_amount - $carrier_core_amount;

            // if ($insurance) {
            //     $carrier_amount = $carrier_amount - $insurance;
            // }
            // if ($extended_liability) {
            //     $carrier_amount = $carrier_amount - $extended_liability;
            // }
            // if ($remote_area) {
            //     $carrier_amount = $carrier_amount - $remote_area;
            // }
            // if ($restricted_destination) {
            //     $carrier_amount = $carrier_amount - $restricted_destination;
            // }
            // if ($elevated_risk) {
            //     $carrier_amount = $carrier_amount - $elevated_risk;
            // }

            // $total = $carrier_amount * floatval($coefficient);
            $total = $carrier_core_amount * floatval($coefficient);

            $labal_margin = $total - $carrier_core_amount;

            if ($is_vat_applicable_to_margin) { // if shipment type is IMPORT add VAT
                // $carrier_rates[$key]['vat_amount'] = (($total - $carrier_amount) * $this->vat);
                // $total = $total + (($total - $carrier_amount) * $this->vat);

                $carrier_rates[$key]['vat_amount'] = number_format((float)(($total - $carrier_core_amount) * $this->vat), 2, '.', '');
                $total = $total + (($total - $carrier_core_amount) * $this->vat);

                $carrier_rates[$key]['VAT'] = $this->vat;
            }

            $carrier_rates[$key]['is_vat_applicable_to_margin'] = $is_vat_applicable_to_margin ? 1 : 0;
            $carrier_rates[$key]['is_vat_applicable_to_carrier'] = $is_vat_applicable_to_carrier ? 1 : 0;

            // if ($insurance) {
            //     $total = $total + $insurance;
            // }
            // if ($extended_liability) {
            //     $total = $total + $extended_liability;
            // }
            // if ($remote_area) {
            //     $total = $total + $remote_area;
            // }
            // if ($restricted_destination) {
            //     $total = $total + $restricted_destination;
            // }
            // if ($elevated_risk) {
            //     $total = $total + $elevated_risk;
            // }

            $total = $total + $carrier_other_amount;

            $carrier_rates[$key]['amount'] = number_format((float)$total, 2, '.', '');
            $carrier_rates[$key]['labal_margin'] = number_format((float)$labal_margin, 2, '.', '');
            $carrier_rates[$key]['carrier_rate'] = number_format((float)$carrier_rate['amount'], 2, '.', '');
        }

        $carrier_rates = array_values($carrier_rates);

        // echo '<pre>';
        // print_r($carrier_rates);
        // exit();
        return $carrier_rates;
    }

    public function calculate_rate_debug($shipment_type, $coefficient, $carrier_rates, $is_insurance)
    {
        echo "<pre>";
        foreach ($carrier_rates as   $key => $carrier_rate) {
            $carrier_amount = floatval($carrier_rate['amount']);
            echo 'DHL Total Charge: <b>' . $carrier_amount . "</b>\n"; //

            $insurance = $carrier_rate['insurance'];
            echo 'Insurance Amount : <b>' . $insurance . "</b>\n"; //

            $extended_liability = $carrier_rate['extended_liability'];
            echo 'Extended Liability Amount : <b>' . $extended_liability . "</b>\n"; //

            $remote_area = $carrier_rate['remote_area'];
            echo 'Remote Area Charge : <b>' . $remote_area . "</b>\n"; //

            $restricted_destination = $carrier_rate['restricted_destination'];
            echo 'Restricted Destination Charge : <b>' . $restricted_destination . "</b>\n"; //

            $elevated_risk = $carrier_rate['elevated_risk'];
            echo 'Elevated Risk Charge : <b>' . $elevated_risk . "</b>\n\n"; //

            if ($insurance) {
                $carrier_amount = $carrier_amount - $insurance;
                echo 'DHL Total Charge - Insurance Amount : <b>' . $carrier_amount . "</b>\n\n"; //
            }
            if ($extended_liability) {
                $carrier_amount = $carrier_amount - $extended_liability;
                echo 'DHL Total Charge - Extended Liability Amount : <b>' . $carrier_amount . "</b>\n\n"; //
            }
            if ($remote_area) {
                $carrier_amount = $carrier_amount - $remote_area;
                echo 'DHL Total Charge - Remote Area Charge : <b>' . $carrier_amount . "</b>\n\n"; //
            }
            if ($restricted_destination) {
                $carrier_amount = $carrier_amount - $restricted_destination;
                echo 'DHL Total Charge - Restricted Destination Charge : <b>' . $carrier_amount . "</b>\n\n"; //
            }
            if ($elevated_risk) {
                $carrier_amount = $carrier_amount - $elevated_risk;
                echo 'DHL Total Charge - Elevated Risk Charge : <b>' . $carrier_amount . "</b>\n\n"; //
            }

            echo 'Coefficient : <b>' . $coefficient . "</b>\n"; //
            $total = $carrier_amount * floatval($coefficient);
            echo 'Labal Courrier Margin Added : <b>' . $total . "</b>\n\n"; //
            if ($shipment_type == self::LC_IMP) { // if shipment type is IMPORT add VAT
                echo 'Is Import : ' . '<b>YES</b>' . "\n"; //
                echo 'VAT : ' . (($total - $carrier_amount) * $this->vat) . "\n"; //
                $total = $total + (($total - $carrier_amount) * $this->vat);
                echo 'VAT Added : ' . $total . "\n"; //
            } else {
                echo 'Is Import : ' . '<b>NO</b>' . "\n"; //
                echo 'VAT : ' . '<b>NO VAT</b>' . "\n"; //
            }
            echo 'Total Shipping Charge : <b>' . $total . "</b>\n\n";
            if ($insurance) {
                $total = $total + $insurance;
                echo 'Total Shipping Charge + Insurance : <b>' . $total . "</b>\n\n";
            }
            if ($extended_liability) {
                $total = $total + $extended_liability;
                echo 'Total Shipping Charge + Extended Liability : <b>' . $total . "</b>\n\n";
            }
            if ($remote_area) {
                $total = $total + $remote_area;
                echo 'Total Shipping Charge + Remote Area Charge : <b>' . $total . "</b>\n\n";
            }
            if ($restricted_destination) {
                $total = $total + $restricted_destination;
                echo 'Total Shipping Charge + Restricted Destination Charge : <b>' . $total . "</b>\n\n";
            }
            if ($elevated_risk) {
                $total = $total + $elevated_risk;
                echo 'Total Shipping Charge + Elevated Risk Charge : <b>' . $total . "</b>\n\n";
            }
            $carrier_rates[$key]['amount'] = number_format((float)$total, 2, '.', '');;
        }

        echo "</pre>";
        die;
        return $carrier_rates;
    }
}
