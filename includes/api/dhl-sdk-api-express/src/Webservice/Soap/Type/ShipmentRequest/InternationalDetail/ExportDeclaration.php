<?php

namespace Dhl\Express\Webservice\Soap\Type\ShipmentRequest\InternationalDetail;

use Dhl\Express\Webservice\Soap\Type\ShipmentRequest\InternationalDetail\ExportDeclaration\InvoiceDate;
use Dhl\Express\Webservice\Soap\Type\ShipmentRequest\InternationalDetail\ExportDeclaration\ExportReasonType;
use Dhl\Express\Webservice\Soap\Type\ShipmentRequest\InternationalDetail\ExportDeclaration\ExportReason;
use Dhl\Express\Webservice\Soap\Type\ShipmentRequest\InternationalDetail\ExportDeclaration\ExportLineItems;

class ExportDeclaration
{
    /**
     * @var InvoiceDate
     */
    private $InvoiceDate;

    /**
     * @var InvoiceNumber
     */
    private $InvoiceNumber;

    /**
     * @var ExportReasonType
     */
    private $ExportReasonType;

    /**
     * @var ExportReason
     */
    private $ExportReason;

    /**
     * @var ExportLineItems
     */
    private $ExportLineItems;

    /**
     * Constructor
     * 
     * @param ExportLineItems
     */
    public function __construct(
        ExportLineItems $exportLineItems,
        $invoiceDate,
        $invoiceNumber,
        $exportReasonType,
        $exportReason
    ) {
        $this->setExportLineItems($exportLineItems);
        $this->setInvoiceDate($invoiceDate);
        $this->setInvoiceNumber($invoiceNumber);
        $this->setExportReasonType($exportReasonType);
        $this->setExportReason($exportReason);
    }

    /**
     * Sets ExportLineItems
     * 
     * @param ExportLineItems $exportLineItems the ExportLineItems
     * 
     * @return self
     */
    public function setExportLineItems(ExportLineItems $exportLineItems)
    {
        $this->ExportLineItems = $exportLineItems;
        return $this;
    }

    /**
     * Returns the ExportLineItems.
     *
     * @return self
     */
    public function getExportLineItems()
    {
        return $this->ExportLineItems;
    }

    /**
     * Sets InvoiceDate
     * 
     * @param string $InvoiceDate the InvoiceDate
     * 
     * @return self
     */
    public function setInvoiceDate(string $InvoiceDate)
    {
        $this->InvoiceDate = $InvoiceDate;
        return $this;
    }

    /**
     * Returns the InvoiceNumber.
     *
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->InvoiceNumber;
    }

    /**
     * Sets InvoiceNumber
     * 
     * @param string $InvoiceNumber the InvoiceNumber
     * 
     * @return self
     */
    public function setInvoiceNumber(string $InvoiceNumber)
    {
        $this->InvoiceNumber = $InvoiceNumber;
        return $this;
    }

    /**
     * Returns the InvoiceDate.
     *
     * @return string
     */
    public function getInvoiceDate()
    {
        return $this->InvoiceDate;
    }

    /**
     * Sets ExportReasonType
     * 
     * @param string $ExportReasonType the ExportReasonType
     * 
     * @return self
     */
    public function setExportReasonType(string $ExportReasonType)
    {
        $this->ExportReasonType = $ExportReasonType;
        return $this;
    }

    /**
     * Returns the ExportReasonType.
     *
     * @return string
     */
    public function getExportReasonType()
    {
        return $this->ExportReasonType;
    }

    /**
     * Sets ExportReason
     * 
     * @param string $ExportReason the ExportReason
     * 
     * @return self
     */
    public function setExportReason(string $ExportReason)
    {
        $this->ExportReason = $ExportReason;
        return $this;
    }

    /**
     * Returns the ExportReason.
     *
     * @return string
     */
    public function getExportReason()
    {
        return $this->ExportReason;
    }
}
