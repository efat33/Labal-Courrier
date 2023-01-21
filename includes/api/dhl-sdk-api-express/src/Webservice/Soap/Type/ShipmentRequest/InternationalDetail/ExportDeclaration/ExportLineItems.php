<?php

namespace Dhl\Express\Webservice\Soap\Type\ShipmentRequest\InternationalDetail\ExportDeclaration;

use Dhl\Express\Webservice\Soap\Type\ShipmentRequest\InternationalDetail\ExportDeclaration\ExportLineItems\ExportLineItem;

class ExportLineItems
{

    /**
     * Dimensions of the package.
     *
     * @var array
     */
    private $ExportLineItem;

    /**
     * Constructor.
     *
     * @param array $exportLineItems
     */
    public function __construct($exportLineItem)
    {
        $this->setExportLineItem($exportLineItem);
    }


    /**
     * Returns the ExportLineItems.
     *
     * @return array
     */
    public function getExportLineItem()
    {
        return $this->ExportLineItem;
    }


    /**
     * Sets the ExportLineItem.
     *
     * @param array $ExportLineItem
     *
     * @return self
     */
    public function setExportLineItem($exportLineItem)
    {
        $this->ExportLineItem = $exportLineItem;
        return $this;
    }
}
