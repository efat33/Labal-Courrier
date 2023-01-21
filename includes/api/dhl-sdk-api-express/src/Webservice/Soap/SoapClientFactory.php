<?php

/**
 * See LICENSE.md for license details.
 */

namespace Dhl\Express\Webservice\Soap;

/**
 * SOAP Client Factory.
 *
 * Prepare the SOAP client for web service access.
 *
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @link     https://www.netresearch.de/
 */
class SoapClientFactory
{
    const RATEBOOK_TEST_WSDL = 'https://wsbexpress.dhl.com/sndpt/expressRateBook?WSDL';
    const RATEBOOK_PROD_WSDL = 'https://wsbexpress.dhl.com/gbl/expressRateBook?WSDL';

    const TRACK_TEST_WSDL = 'https://wsbexpress.dhl.com/sndpt/glDHLExpressTrack?WSDL';
    const TRACK_PROD_WSDL = 'https://wsbexpress.dhl.com/gbl/glDHLExpressTrack?WSDL';

    /**
     * @param string $username
     * @param string $password
     * @param string $wsdl
     *
     * @return \SoapClient
     * @throws \SoapFault
     */
    public function create($username, $password, $wsdl = '')
    {
        $wsdl = $wsdl ?: self::RATEBOOK_PROD_WSDL;

        $options = [
            'features'           => SOAP_SINGLE_ELEMENT_ARRAYS,
            'trace'              => true, // Enable to log requests
            'exceptions'         => true,
            'soap_version'       => SOAP_1_2,
            'connection_timeout' => 10,
            'encoding'           => 'UTF-8',
            'cache_wsdl'         => WSDL_CACHE_DISK,
            'classmap'           => ClassMap::get(),
        ];

        $authFactory = new AuthHeaderFactory();
        $authHeader = $authFactory->create($username, $password);

        $client = new \SoapClient($wsdl, $options);
        // $client = new \SoapClient(null, array('trace' => 1, 'location' => 'https://wsbexpress.dhl.com:443/sndpt/expressRateBook', 'uri' => 'http://www.w3.org/2001/XMLSchema-instance'));
        $client->__setSoapHeaders([$authHeader]);

        return $client;
    }
}
