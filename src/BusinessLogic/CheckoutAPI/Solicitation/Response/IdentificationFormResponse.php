<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;

/**
 * Class IdentificationFormResponse
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response
 */
class IdentificationFormResponse extends Response
{
    /**
     * @var SeQuraForm
     */
    protected $identificationForm;

    public function __construct(SeQuraForm $identificationForm)
    {
        $this->identificationForm = $identificationForm;
    }

    /**
     * @return SeQuraForm
     */
    public function getIdentificationForm(): SeQuraForm
    {
        return $this->identificationForm;
    }

    public function toArray(): array
    {
        return [
            'identificationForm' => $this->identificationForm->getForm()
        ];
    }
}
