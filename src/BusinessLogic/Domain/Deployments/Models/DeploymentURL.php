<?php

namespace SeQura\Core\BusinessLogic\Domain\Deployments\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class DeploymentURL.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Deployments\Models
 */
class DeploymentURL extends DataTransferObject
{
    /**
     * @var string $apiBaseUrl
     */
    private $apiBaseUrl;

    /**
     * @var string $assetsBaseUrl
     */
    private $assetsBaseUrl;

    /**
     * @param string $apiBaseUrl
     * @param string $assetsBaseUrl
     */
    public function __construct(string $apiBaseUrl, string $assetsBaseUrl)
    {
        $this->apiBaseUrl = $apiBaseUrl;
        $this->assetsBaseUrl = $assetsBaseUrl;
    }

    /**
     * @return string
     */
    public function getApiBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }

    /**
     * @return string
     */
    public function getAssetsBaseUrl(): string
    {
        return $this->assetsBaseUrl;
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'api_base_url' => $this->apiBaseUrl,
            'assets_base_url' => $this->assetsBaseUrl,
        ];
    }

    /**
     * @param mixed[] $data
     *
     * @return DeploymentURL
     */
    public static function fromArray(array $data): DeploymentURL
    {
        return new self($data['api_base_url'], $data['assets_base_url']);
    }
}
