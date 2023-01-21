<?php
/**
 * See LICENSE.md for license details.
 */

namespace Dhl\Express\Api\Data\Request\Shipment;

/**
 * Export Declaration Interface.
 *
 * @api
 * @author   Mohamed Mahra <mahran@imarasoft.net>
 * @link     https://www.imarasoft.net/
 */
interface ExportDeclarationInterface
{
    /**
     * Returns the invoice data.
     *
     * @return string
     */
    public function getInvoiceDate();
    
    /**
     * Returns the Export Reason Type.
     *
     * @return string
     */
    public function getExportReasonType();
    
    /**
     * Returns the Export Reason.
     *
     * @return string
     */
    public function getExportReason();

}
