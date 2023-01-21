<?php

/**
 * See LICENSE.md for license details.
 */

namespace Dhl\Express\Model\Request\Shipment;

use Dhl\Express\Api\Data\Request\Shipment\ExportLineItemInterface;

/**
 * Package.
 *
 * @author   Ronny Gertler <ronny.gertler@netresearch.de>
 * @link     https://www.netresearch.de/
 */
class ExportLineItem implements ExportLineItemInterface
{
    /**
     * Units of measurement (Quantity).
     */
    const UOM_QUANTITY_BOX = 'BOX';
    const UOM_QUANTITY_2GM = '2GM';
    const UOM_QUANTITY_2M = '2M';
    const UOM_QUANTITY_2M3 = '2M3';
    const UOM_QUANTITY_3M3 = '3M3';
    const UOM_QUANTITY_M3 = 'BOX';
    const UOM_QUANTITY_DPR = 'DPR';
    const UOM_QUANTITY_DOZ = 'DOZ';
    const UOM_QUANTITY_2NO = '2NO';
    const UOM_QUANTITY_PCS = 'PCS';
    const UOM_QUANTITY_GM = 'GM';
    const UOM_QUANTITY_GRS = 'GRS';
    const UOM_QUANTITY_KG = 'KG';
    const UOM_QUANTITY_L = 'L';
    const UOM_QUANTITY_M = 'M';
    const UOM_QUANTITY_3GM = '3GM';
    const UOM_QUANTITY_3L = '3L';
    const UOM_QUANTITY_X = 'X';
    const UOM_QUANTITY_NO = 'NO';
    const UOM_QUANTITY_2KG = '2KG';
    const UOM_QUANTITY_PRS = 'PRS';
    const UOM_QUANTITY_2L = '2L';
    const UOM_QUANTITY_3KG = '3KG';
    const UOM_QUANTITY_CM2 = 'CM2';
    const UOM_QUANTITY_2M2 = '2M2';
    const UOM_QUANTITY_3M2 = '3M2';
    const UOM_QUANTITY_M2 = 'M2';
    const UOM_QUANTITY_4M2 = '4M2';
    const UOM_QUANTITY_3M = '3M';
    const UOM_QUANTITY_CM = 'CM';
    const UOM_QUANTITY_CONE = 'CONE';
    const UOM_QUANTITY_CT = 'CT';
    const UOM_QUANTITY_EA = 'EA';
    const UOM_QUANTITY_LBS = 'LBS';
    const UOM_QUANTITY_RILL = 'RILL';
    const UOM_QUANTITY_ROLL = 'ROLL';
    const UOM_QUANTITY_SET = 'SET';
    const UOM_QUANTITY_TU = 'TU';
    const UOM_QUANTITY_YDS = 'YDS';

    /**
     * Units of measurement (dimension).
     */
    const UOM_DIMENSION_CM = 'CM';
    const UOM_DIMENSION_IN = 'IN';
    const UOM_DIMENSION_MM = 'MM';
    const UOM_DIMENSION_M = 'M';
    const UOM_DIMENSION_FT = 'FT';
    const UOM_DIMENSION_YD = 'YD';

    /**
     * The number of the item in the list of all items.
     *
     * @var int
     */
    private $itemNumber;

    /**
     * commodity code of the item.
     *
     * @var int
     */
    private $commodityCode;

    /**
     * The quantity of the item.
     *
     * @var float
     */
    private $quantity;

    /**
     * The unit of measurement for the item quantity.
     *
     * @var string
     */
    private $quantityUOM;

    /**
     * The description of the item.
     *
     * @var string
     */
    private $itemDescription;

    /**
     * The unit price of the item.
     *
     * @var float
     */
    private $unitPrice;

    /**
     * The net weight of the item.
     *
     * @var float
     */
    private $netWeight;

    /**
     * The gross weight of the item.
     *
     * @var float
     */
    private $grossWeight;

    /**
     * The manufacturing country code of the item.
     *
     * @var float
     */
    private $manufacturingCountryCode;

    /**
     * The express reason type of the item.
     *
     * @var float
     */
    private $exportReasonType;


    /**
     * Constructor.
     *
     * @param int    $sequenceNumber
     * @param float  $weight
     * @param string $weightUOM
     * @param float  $length
     * @param float  $width
     * @param float  $height
     * @param string $dimensionsUOM
     * @param string $customerReferences
     */
    public function __construct(
        $itemNumber,
        $commodityCode,
        $quantity,
        $quantityUOM,
        $itemDescription,
        $unitPrice,
        $netWeight,
        $grossWeight,
        $manufacturingCountryCode,
        $exportReasonType
    ) {
        $quantityUOMs = [
            self::UOM_QUANTITY_BOX,
            self::UOM_QUANTITY_2GM,
            self::UOM_QUANTITY_2M,
            self::UOM_QUANTITY_2M3,
            self::UOM_QUANTITY_3M3,
            self::UOM_QUANTITY_M3,
            self::UOM_QUANTITY_DPR,
            self::UOM_QUANTITY_DOZ,
            self::UOM_QUANTITY_2NO,
            self::UOM_QUANTITY_PCS,
            self::UOM_QUANTITY_GM,
            self::UOM_QUANTITY_GRS,
            self::UOM_QUANTITY_KG,
            self::UOM_QUANTITY_L,
            self::UOM_QUANTITY_M,
            self::UOM_QUANTITY_3GM,
            self::UOM_QUANTITY_3L,
            self::UOM_QUANTITY_X,
            self::UOM_QUANTITY_NO,
            self::UOM_QUANTITY_2KG,
            self::UOM_QUANTITY_PRS,
            self::UOM_QUANTITY_2L,
            self::UOM_QUANTITY_3KG,
            self::UOM_QUANTITY_CM2,
            self::UOM_QUANTITY_2M2,
            self::UOM_QUANTITY_3M2,
            self::UOM_QUANTITY_M2,
            self::UOM_QUANTITY_4M2,
            self::UOM_QUANTITY_3M,
            self::UOM_QUANTITY_CM,
            self::UOM_QUANTITY_CONE,
            self::UOM_QUANTITY_CT,
            self::UOM_QUANTITY_EA,
            self::UOM_QUANTITY_LBS,
            self::UOM_QUANTITY_RILL,
            self::UOM_QUANTITY_ROLL,
            self::UOM_QUANTITY_SET,
            self::UOM_QUANTITY_TU,
            self::UOM_QUANTITY_YDS
        ];

        if (!\in_array($quantityUOM, $quantityUOMs, true)) {
            throw new \InvalidArgumentException('The quantity UOM must be one of ' . implode(', ', $quantityUOMs));
        }
        $this->sequenceNumber           = $itemNumber;
        $this->commodityCode            = $commodityCode;
        $this->quantity                 = $quantity;
        $this->quantityUOM              = $quantityUOM;
        $this->itemDescription          = $itemDescription;
        $this->unitPrice                = $unitPrice;
        $this->netWeight                = $netWeight;
        $this->grossWeight              = $grossWeight;
        $this->manufacturingCountryCode = $manufacturingCountryCode;
        $this->exportReasonType         = $exportReasonType;
    }

    public function getItemNumber()
    {
        return (int) $this->sequenceNumber;
    }
    public function getCommodityCode()
    {
        return (string) $this->commodityCode;
    }
    public function getQuantity()
    {
        return (int) $this->quantity;
    }
    public function getQuantityUOM()
    {
        return (string) $this->quantityUOM;
    }
    public function getItemDescription()
    {
        return (string) $this->itemDescription;
    }
    public function getUnitPrice()
    {
        return (float) $this->unitPrice;
    }
    public function getNetWeight()
    {
        return (float) $this->netWeight;
    }
    public function getGrossWeight()
    {
        return (float) $this->grossWeight;
    }
    public function getExportReasonType()
    {
        return (string) $this->exportReasonType;
    }
    public function getManufacturingCountryCode()
    {
        return (string) $this->manufacturingCountryCode;
    }
}
