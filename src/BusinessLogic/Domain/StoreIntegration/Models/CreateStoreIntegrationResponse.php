<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidLocationHeaderException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\LocationHeaderEmptyException;

/**
 * Class CreateStoreIntegrationResponse.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models
 */
class CreateStoreIntegrationResponse
{
    /**
     * @var string $integrationId
     */
    private $integrationId;

    /**
     * @param string $integrationId
     */
    public function __construct(string $integrationId)
    {
        $this->integrationId = $integrationId;
    }

    /**
     * @return string
     */
    public function getIntegrationId(): string
    {
        return $this->integrationId;
    }

    /**
     * @param string $locationHeader
     *
     * @return self
     *
     * @throws LocationHeaderEmptyException
     * @throws InvalidLocationHeaderException
     */
    public static function fromLocationHeader(string $locationHeader): self
    {
        if (empty($locationHeader)) {
            throw new LocationHeaderEmptyException();
        }

        $path = parse_url($locationHeader, PHP_URL_PATH);

        if (preg_match('#store_integrations/(\d+)#', $path, $matches)) {
            return new self($matches[1]);
        }

        throw new InvalidLocationHeaderException();
    }
}
