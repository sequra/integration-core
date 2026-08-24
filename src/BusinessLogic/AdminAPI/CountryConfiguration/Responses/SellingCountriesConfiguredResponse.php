<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class SellingCountriesConfiguredResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses
 */
class SellingCountriesConfiguredResponse extends Response
{
    /**
     * @var bool
     */
    protected $configured;

    /**
     * @param bool $configured
     */
    public function __construct(bool $configured)
    {
        $this->configured = $configured;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'configured' => $this->configured
        ];
    }
}
