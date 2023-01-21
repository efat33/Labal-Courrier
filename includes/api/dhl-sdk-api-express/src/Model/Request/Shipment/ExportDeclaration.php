<?php

/**
 * See LICENSE.md for license details.
 */

namespace Dhl\Express\Model\Request\Shipment;

use DateTime;
use Dhl\Express\Api\Data\Request\Shipment\ExportDeclarationInterface;
use Dhl\Express\Api\Data\Request\Shipment\ExportLineItemInterface;

/**
 * Package.
 *
 * @author   Ronny Gertler <ronny.gertler@netresearch.de>
 * @link     https://www.netresearch.de/
 */
class ExportDeclaration implements ExportDeclarationInterface
{

    /**
     * The invoice date of the item.
     *
     * @var DateTime
     */
    private $invoiceDate;

    /**
     * The invoice number of the item.
     *
     * @var string
     */
    private $invoiceNumber;

    /**
     * The express reason type of the item.
     *
     * @var string
     */
    private $exportReasonType;

    /**
     * The express reason of the item.
     *
     * @var string
     */
    private $exportReason;


    /**
     * Constructor.
     *
     * @param     $invoiceDate
     * @param string  $exportReasonType
     * @param string $exportReason
     */
    public function __construct(
        $invoiceDate,
        $invoiceNumber,
        $exportReasonType,
        $exportReason
    ) {
        $this->invoiceDate           = $invoiceDate;
        $this->invoiceNumber         = $invoiceNumber;
        $this->exportReasonType      = $exportReasonType;
        $this->exportReason          = $exportReason;
    }

    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    public function getExportReasonType()
    {
        return $this->exportReasonType;
    }

    public function getExportReason()
    {
        return $this->exportReason;
    }
}
