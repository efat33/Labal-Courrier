<?php

/**
 * See LICENSE.md for license details.
 */

namespace Dhl\Express\Model\Request\Shipment;

use Dhl\Express\Api\Data\Request\Shipment\LabelOptionsInterface;

class LabelOptions implements LabelOptionsInterface
{

    /**
     * Customs invoice types.
     *
     */
    const DHL_CUSTOMS_INVIOCE_TYPE_COMMERCIAL_INVOICE = 'COMMERCIAL_INVOICE';


    /**
     * @var bool
     */
    private $waybillDocumentRequested;

    /**
     * @var bool
     */
    private $isDHLCustomsInvoiceRequested;

    /**
     * @var string
     */
    private $DHLCustomsInvoiceType;

    public function __construct(bool $waybillDocumentRequested, $isDHLCustomsInvoiceRequested, $DHLCustomsInvoiceType)
    {
        $this->waybillDocumentRequested = $waybillDocumentRequested;
        $this->isDHLCustomsInvoiceRequested = $isDHLCustomsInvoiceRequested;
        $this->DHLCustomsInvoiceType = $DHLCustomsInvoiceType;
    }

    public function isWaybillDocumentRequested(): bool
    {
        return $this->waybillDocumentRequested;
    }

    public function isDHLCustomsInvoiceRequested(): bool
    {
        return $this->isDHLCustomsInvoiceRequested;
    }

    public function getDHLCustomsInvoiceType(): string
    {
        return $this->DHLCustomsInvoiceType;
    }
}
