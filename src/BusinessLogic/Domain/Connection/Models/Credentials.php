<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class Credentials.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Models
 */
class Credentials extends DataTransferObject
{
    /**
     * @var string $merchantId
     */
    private $merchantId;

    /**
     * @var string $country
     */
    private $country;

    /**
     * @var string $currency
     */
    private $currency;

    /**
     * @var string $assetsKey
     */
    private $assetsKey;

    /**
     * @var array<string, mixed> $payload
     */
    private $payload;

    /**
     * @var string
     */
    private $deployment;

    /**
     * @param string $merchantId
     * @param string $country
     * @param string $currency
     * @param string $assetsKey
     * @param array<string, mixed> $payload
     * @param string $deployment
     */
    public function __construct(
        string $merchantId,
        string $country,
        string $currency,
        string $assetsKey,
        array $payload,
        string $deployment
    ) {
        $this->merchantId = $merchantId;
        $this->country = $country;
        $this->currency = $currency;
        $this->assetsKey = $assetsKey;
        $this->payload = $payload;
        $this->deployment = $deployment;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getAssetsKey(): string
    {
        return $this->assetsKey;
    }

    /**
     * @return array<string>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getDeployment(): string
    {
        return $this->deployment;
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        $arrayOfData = [
            'merchantId' => $this->merchantId,
            'country' => $this->country,
            'currency' => $this->currency,
            'assetsKey' => $this->assetsKey,
            'payload' => [],
            'deployment' => $this->deployment
        ];

        foreach ($this->payload as $key => $value) {
            $arrayOfData['payload'][$key] = $value;
        }

        return $arrayOfData;
    }

    /**
     * @param array<mixed, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['merchantId'] ?? '',
            $data['country'] ?? '',
            $data['currency'] ?? '',
            $data['assetsKey'] ?? '',
            $data['payload'] ?? [],
            $data['deployment'] ?? ''
        );
    }

    /**
     * Are services enabled in the contract options.
     */
    public function isEnabledForServices(): bool
    {
        return $this->hasContractOption('services');
    }

    /**
     * Is first service payment delay allowed in the contract options.
     */
    public function isAllowFirstServicePaymentDelay(): bool
    {
        return $this->hasContractOption('allow_first_instalment_delay');
    }

    /**
     * Are registration items allowed in the contract options.
     */
    public function isAllowServiceRegistrationItems(): bool
    {
        return $this->hasContractOption('with_registration');
    }

    /**
     * Check if a specific contract option is present.
     */
    private function hasContractOption(string $option): bool
    {
        if (!isset($this->payload['contract_options']) || !is_array($this->payload['contract_options'])) {
            return false;
        }
        return in_array($option, $this->payload['contract_options'], true);
    }
}
