<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses;

use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\IdentificationFormResponse;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;

class ExpressCheckoutSolicitResponse extends IdentificationFormResponse
{
    /**
     * @var string|null
     */
    protected $orderUuid;

    /**
     * @var string|null
     */
    protected $metadataEndpoint;

    /**
     * @var string|null
     */
    protected $formUrl;

    public function __construct(
        SeQuraForm $identificationForm,
        ?string $orderUuid,
        ?string $metadataEndpoint,
        ?string $formUrl
    ) {
        parent::__construct($identificationForm);
        $this->orderUuid = $orderUuid;
        $this->metadataEndpoint = $metadataEndpoint;
        $this->formUrl = $formUrl;
    }

    public function getOrderUuid(): ?string
    {
        return $this->orderUuid;
    }

    public function getMetadataEndpoint(): ?string
    {
        return $this->metadataEndpoint;
    }

    public function getFormUrl(): ?string
    {
        return $this->formUrl;
    }

    public function toArray(): array
    {
        return [
            'identificationForm' => $this->identificationForm->getForm(),
            'orderUuid' => $this->orderUuid,
            'metadataEndpoint' => $this->metadataEndpoint,
            'formUrl' => $this->formUrl,
        ];
    }
}
