<?php
/**
 * See LICENSE.md for license details.
 */
namespace Dhl\Express\Webservice\Soap\Type\ShipmentRequest\LabelOptions;

use Dhl\Express\Webservice\Soap\Type\Common\YesNo;

/**
 * The RequestDHLCustomsInvoice field is used to indicate that you would like a copy of the waybill document
 * the default in a ShipmentRequest in N
 *
 * @api
 * @author Daniel Fairbrother <dfairbrother@datto.com>
 * @link   https://www.datto.com/
 */
class RequestDHLCustomsInvoice extends YesNo
{
}
