<?php
/**
 * See LICENSE.md for license details.
 */

namespace Dhl\Express\Model;

use Dhl\Express\Api\Data\Request\InsuranceInterface;
use Dhl\Express\Api\Data\Request\PackageInterface;
use Dhl\Express\Api\Data\Request\RecipientInterface;
use Dhl\Express\Api\Data\Request\Shipment\DangerousGoods\DryIceInterface;
use Dhl\Express\Api\Data\Request\Shipment\ExportDeclarationInterface;
use Dhl\Express\Api\Data\Request\Shipment\LabelOptionsInterface;
use Dhl\Express\Api\Data\Request\Shipment\ShipmentDetailsInterface;
use Dhl\Express\Api\Data\Request\Shipment\ShipperInterface;
use Dhl\Express\Api\Data\ShipmentRequestInterface;
use Dhl\Express\Model\Request\Recipient;
use Dhl\Express\Model\Request\Shipment\Shipper;
use MyDHL\Model\ExportDeclaration;

/**
 * Shipment Request.
 *
 * @author Ronny Gertler <ronny.gertler@netresearch.de>
 * @link   https://www.netresearch.de/
 */
class ShipmentRequest implements ShipmentRequestInterface
{
    /**
     * @var ShipmentDetailsInterface
     */
    private $shipmentDetails;

    /**
     * @var string
     */
    private $payerAccountNumber;

    /**
     * @var Shipper
     */
    private $shipper;

    /**
     * @var Recipient
     */
    private $recipient;

    /**
     * @var PackageInterface[]
     */
    private $packages;

    /**
     * @var ExportLineItemInterface[]
     */
    private $exportLineItems;

    /**
     * @var null|string
     */
    private $billingAccountNumber;

    /**
     * @var null|InsuranceInterface
     */
    private $insurance;

    /**
     * @var null|DryIceInterface
     */
    private $dryIce;

    /**
     * @var null|LabelOptionsInterface
     */
    private $labelOptions;

    /**
     * @var null|ExportDeclarationInterface
     */
    private $exportDeclaration;

    /**
     * SoapShipmentRequest constructor.
     *
     * @param ShipmentDetailsInterface $shipmentDetails
     * @param string $payerAccountNumber
     * @param Shipper $shipper
     * @param Recipient $recipient
     * @param PackageInterface[] $packages
     */
    public function __construct(
        ShipmentDetailsInterface $shipmentDetails,
        string $payerAccountNumber,
        ShipperInterface $shipper,
        RecipientInterface $recipient,
        array $packages,
        array $exportLineItems
    ) {
        $this->shipmentDetails = $shipmentDetails;
        $this->payerAccountNumber = $payerAccountNumber;
        $this->shipper = $shipper;
        $this->recipient = $recipient;
        $this->packages = $packages;
        $this->exportLineItems = $exportLineItems;
    }

    public function getShipmentDetails(): ShipmentDetailsInterface
    {
        return $this->shipmentDetails;
    }

    public function getPayerAccountNumber(): string
    {
        return (string) $this->payerAccountNumber;
    }

    public function getShipper(): ShipperInterface
    {
        return $this->shipper;
    }

    public function getRecipient(): RecipientInterface
    {
        return $this->recipient;
    }

    public function getPackages(): array
    {
        return $this->packages;
    }

    public function getExportLineItems(): array
    {
        return $this->exportLineItems;
    }

    public function getBillingAccountNumber(): string
    {
        return (string) $this->billingAccountNumber;
    }

    public function getInsurance(): ?InsuranceInterface
    {
        return $this->insurance;
    }

    public function getDryIce(): ?DryIceInterface
    {
        return $this->dryIce;
    }

    public function getLabelOptions(): ?LabelOptionsInterface
    {
        return $this->labelOptions;
    }

    public function getExportDeclaration(): ?ExportDeclarationInterface
    {
        return $this->exportDeclaration;
    }

    /**
     * Sets the billing account number.
     *
     * @param string $billingAccountNumber The billing account number
     *
     * @return ShipmentRequest
     */
    public function setBillingAccountNumber(string $billingAccountNumber): ShipmentRequestInterface
    {
        $this->billingAccountNumber = $billingAccountNumber;

        return $this;
    }

    /**
     * Sets the insurance instance.
     *
     * @param InsuranceInterface $insurance The insurance instance
     *
     * @return ShipmentRequest
     */
    public function setInsurance(InsuranceInterface $insurance): ShipmentRequestInterface
    {
        $this->insurance = $insurance;

        return $this;
    }

    /**
     * Sets the dry ice instance.
     *
     * @param DryIceInterface $dryIce The dry ice instance
     *
     * @return ShipmentRequest
     */
    public function setDryIce(DryIceInterface $dryIce): ShipmentRequestInterface
    {
        $this->dryIce = $dryIce;

        return $this;
    }

    /**
     * Sets the export declaration instance.
     *
     * @param ExportDeclaration $exportDeclaration The export declaration instance
     *
     * @return ShipmentRequest
     */
    public function setExportDeclaration(ExportDeclarationInterface $exportDeclaration): ShipmentRequestInterface
    {
        $this->exportDeclaration = $exportDeclaration;
        
        return $this;
    }

    /**
     * Sets the label options instance.
     *
     * @param LabelOptionsInterface $labelOptions The label options instance
     *
     * @return ShipmentRequest
     */
    public function setLabelOptions(LabelOptionsInterface $labelOptions): ShipmentRequestInterface
    {
        $this->labelOptions = $labelOptions;

        return $this;
    }
}
