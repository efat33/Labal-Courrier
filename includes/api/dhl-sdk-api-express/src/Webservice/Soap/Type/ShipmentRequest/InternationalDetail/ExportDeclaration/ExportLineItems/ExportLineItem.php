<?php

namespace Dhl\Express\Webservice\Soap\Type\ShipmentRequest\InternationalDetail\ExportDeclaration\ExportLineItems;

use Dhl\Express\Webservice\Soap\Type\ShipmentRequest\InternationalDetail\ExportDeclaration\ExportLineItems\ExportLineItem\ItemNumber;

class ExportLineItem
{
    
    /**
     * Sequence number of the item
     *
     * @var ItemNumber
     */
    private $ItemNumber;

    /**
     * Commodity code of the item
     *
     * @var string
     */
    private $CommodityCode;

    /**
     * Quantity of the item
     *
     * @var int
     */
    private $Quantity;

    /**
     * Quantity unit of measurement of the item
     *
     * @var string
     */
    private $QuantityUnitOfMeasurement;

    /**
     * item description of the item
     *
     * @var string
     */
    private $ItemDescription;

    /**
     * unit price of the item
     *
     * @var string
     */
    private $UnitPrice;

    /**
     * Net weight of the item
     *
     * @var string
     */
    private $NetWeight;

    /**
     * gross weight of the item
     *
     * @var string
     */
    private $GrossWeight;

    /**
     * Manufacturing country code of the item
     *
     * @var string
     */
    private $ManufacturingCountryCode;

    /**
     * export reason type of the item
     *
     * @var string
     */
    private $ExportReasonType;

    /**
     * Constructor.
     *
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
        $manufacturingCountrycode,
        $exportReasonType
    ) {
        $this->setItemNumber($itemNumber)
            ->setCommodityCode($commodityCode)
            ->setQuantity($quantity)
            ->setQuantityUOM($quantityUOM)
            ->setItemDescription($itemDescription)
            ->setUnitPrice($unitPrice)
            ->setNetWeight($netWeight)
            ->setGrossWeight($grossWeight)
            ->setManufacturingCountryCode($manufacturingCountrycode)
            ->setExportReasonType($exportReasonType);
    }

    public function setItemNumber($itemNumber)
    {
        // $this->ItemNumber = new ItemNumber($itemNumber);
        $this->ItemNumber =$itemNumber;
        return $this;
    }

    public function setCommodityCode($commodityCode)
    {
        $this->CommodityCode = $commodityCode;
        return $this;
    }

    public function setQuantity($quantity)
    {
        $this->Quantity = $quantity;
        return $this;
    }

    public function setQuantityUOM($quantityUOM)
    {
        $this->QuantityUnitOfMeasurement = $quantityUOM;
        return $this;
    }

    public function setItemDescription($itemDescription)
    {
        $this->ItemDescription = $itemDescription;
        return $this;
    }

    public function setUnitPrice($unitPrice)
    {
        $this->UnitPrice = $unitPrice;
        return $this;
    }

    public function setNetWeight($netWeight)
    {
        $this->NetWeight = $netWeight;
        return $this;
    }

    public function setGrossWeight($grossWeight)
    {
        $this->GrossWeight = $grossWeight;
        return $this;
    }

    public function setManufacturingCountryCode($manufacturingCountrycode)
    {
        $this->ManufacturingCountryCode = $manufacturingCountrycode;
        return $this;
    }

    public function setExportReasonType($exportReasonType)
    {
        $this->ExportReasonType = $exportReasonType;
        return $this;
    }

    public function getItemNumber()
    {
        return $this->ItemNumber;
    }

    public function getCommodityCode()
    {
        return $this->CommodityCode;
    }

    public function getQuantity()
    {
        return $this->Quantity;
    }

    public function getQuantityUOM()
    {
        return $this->QuantityUnitOfMeasurement;
    }

    public function getItemDescription()
    {
        return $this->ItemDescription;
    }

    public function getUnitPrice()
    {
        return $this->UnitPrice;
    }

    public function getNetWeight()
    {
        return $this->NetWeight;
    }

    public function getGrossWeight()
    {
        return $this->GrossWeight;
    }

    public function getManufacturingCountryCode()
    {
        return $this->ManufacturingCountryCode;
    }

    public function getExportReasonType()
    {
        return $this->ExportReasonType;
    }
}
