<?php
/**
 * See LICENSE.md for license details.
 */

namespace Dhl\Express\Api\Data\Request\Shipment;

/**
 * Export line item Interface.
 *
 * @api
 * @author   Mohamed Mahra <mahran@imarasoft.net>
 * @link     https://www.imarasoft.net/
 */
interface ExportLineItemInterface
{
    /**
     * Returns the number of the item.
     *
     * @return int
     */
    public function getItemNumber();

    /**
     * Returns the commodity code of the item.
     *
     * @return string
     */
    public function getCommodityCode();

    /**
     * Returns the quantity of the item.
     *
     * @return int
     */
    public function getQuantity();

    /**
     * Returns the quantity unit of measurement.
     *
     * @return string
     */
    public function getQuantityUOM();

    /**
     * Returns the description of the item.
     *
     * @return string
     */
    public function getItemDescription();

    /**
     * Returns the unit price of the item.
     *
     * @return float
     */
    public function getUnitPrice();

    /**
     * Returns the net weight of the item.
     *
     * @return float
     */
    public function getNetWeight();

    /**
     * Returns the gross weight of the item.
     *
     * @return float
     */
    public function getGrossWeight();

    /**
     * Returns the export reason type of the item.
     *
     * @return float
     */
    public function getExportReasonType();

    /**
     * Returns the manufacturing country code of the item.
     *
     * @return string
     */
    public function getManufacturingCountryCode();
}
