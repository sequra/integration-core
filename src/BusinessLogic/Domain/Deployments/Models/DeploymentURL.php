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
     * @var string $portalBaseUrl
     */
    private $portalBaseUrl;

    /**
     * @param string $apiBaseUrl
     * @param string $assetsBaseUrl
     * @param string $portalBaseUrl URL of the SeQura portal, empty when the deployment does not name one
     */
    public function __construct(string $apiBaseUrl, string $assetsBaseUrl, string $portalBaseUrl = '')
    {
        $this->apiBaseUrl = $apiBaseUrl;
        $this->assetsBaseUrl = $assetsBaseUrl;
        $this->portalBaseUrl = $portalBaseUrl;
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
     * @return string
     */
    public function getPortalBaseUrl(): string
    {
        return $this->portalBaseUrl;
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'api_base_url' => $this->apiBaseUrl,
            'assets_base_url' => $this->assetsBaseUrl,
            'portal_base_url' => $this->portalBaseUrl,
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return DeploymentURL
     */
    public static function fromArray(array $data): DeploymentURL
    {
        return new self($data['api_base_url'], $data['assets_base_url'], $data['portal_base_url'] ?? '');
    }
}
