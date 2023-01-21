<?php
/**
 * See LICENSE.md for license details.
 */
namespace Dhl\Express\Webservice\Soap\Type\ShipmentRequest;

use Dhl\Express\Webservice\Soap\Type\ShipmentRequest\LabelOptions\DHLCustomsInvoiceType;
use Dhl\Express\Webservice\Soap\Type\ShipmentRequest\LabelOptions\RequestDHLCustomsInvoice;
use Dhl\Express\Webservice\Soap\Type\ShipmentRequest\LabelOptions\RequestWaybillDocument;

/**
 * The LabelOptions section
 *
 * @api
 * @author Daniel Fairbrother <dfairbrother@datto.com>
 * @link   https://www.datto.com/
 */
class LabelOptions
{
    /**
     * The waybill document request option.
     *
     * @var RequestWaybillDocument
     */
    private $RequestWaybillDocument;

    /**
     * 
     *
     * @var RequestDHLCustomsInvoice
     */
    private $RequestDHLCustomsInvoice;

    /**
     * 
     *
     * @var DHLCustomsInvoiceType
     */
    private $DHLCustomsInvoiceType;

    /**
     * Constructor.
     *
     * @param RequestWaybillDocument $requestWaybillDocument The waybill document request option.
     */
    public function __construct(
        RequestWaybillDocument $requestWaybillDocument,
        RequestDHLCustomsInvoice $requestDHLCustomsInvoice,
        DHLCustomsInvoiceType $DHLCustomsInvoiceType
        )
    {
        $this->setRequestWaybillDocument($requestWaybillDocument);
        $this->setRequestDHLCustomsInvoice($requestDHLCustomsInvoice);
        $this->setDHLCustomsInvoiceType($DHLCustomsInvoiceType);
    }

    /**
     * Returns the request waybill document option.
     *
     * @return RequestWaybillDocument
     */
    public function getRequestWaybillDocument(): RequestWaybillDocument
    {
        return $this->RequestWaybillDocument;
    }

    /**
     * @return RequestDHLCustomsInvoice
     */
    public function getRequestDHLCustomsInvoice(): RequestDHLCustomsInvoice
    {
        return $this->RequestDHLCustomsInvoice;
    }

    /**
     * @return DHLCustomsInvoiceType
     */
    public function getDHLCustomsInvoiceType(): DHLCustomsInvoiceType
    {
        return $this->DHLCustomsInvoiceType;
    }

    /**
     * Sets the delivery option.
     *
     * @param RequestWaybillDocument $requestWaybillDocument The waybill document request option.
     *
     * @return self
     */
    public function setRequestWaybillDocument(RequestWaybillDocument $requestWaybillDocument): LabelOptions
    {
        $this->RequestWaybillDocument = $requestWaybillDocument;
        return $this;
    }

    /**
     * Sets RequestWaybillDocument
     *
     * @param RequestWaybillDocument $requestWaybillDocument
     *
     * @return self
     */
    public function setRequestDHLCustomsInvoice(RequestDHLCustomsInvoice $requestDHLCustomsInvoice): LabelOptions
    {
        $this->RequestDHLCustomsInvoice = $requestDHLCustomsInvoice;
        return $this;
    }

    /**
     * Sets DHLCustomsInvoiceType
     *
     * @param DHLCustomsInvoiceType $DHLCustomsInvoiceType
     *
     * @return self
     */
    public function setDHLCustomsInvoiceType(DHLCustomsInvoiceType $DHLCustomsInvoiceType): LabelOptions
    {
        $this->DHLCustomsInvoiceType = $DHLCustomsInvoiceType;
        return $this;
    }
}
