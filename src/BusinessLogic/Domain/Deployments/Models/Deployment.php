<?php

namespace SeQura\Core\BusinessLogic\Domain\Deployments\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class Deployment.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Deployments\Models
 */
class Deployment extends DataTransferObject
{
    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var ?DeploymentURL $liveDeploymentURL
     */
    private $liveDeploymentURL;

    /**
     * @var ?DeploymentURL $sandboxDeploymentURL
     */
    private $sandboxDeploymentURL;

    /**
     * @var array<string, string> $description
     */
    private $description;

    /**
     * @var string $logoUrl
     */
    private $logoUrl;

    /**
     * @param string $id
     * @param string $name
     * @param DeploymentURL|null $liveDeploymentURL
     * @param DeploymentURL|null $sandboxDeploymentURL
     * @param array<mixed, mixed> $description
     * @param string $logoUrl
     */
    public function __construct(
        string $id,
        string $name,
        ?DeploymentURL $liveDeploymentURL,
        ?DeploymentURL $sandboxDeploymentURL,
        array $description = [],
        string $logoUrl = ''
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->liveDeploymentURL = $liveDeploymentURL;
        $this->sandboxDeploymentURL = $sandboxDeploymentURL;
        $this->description = self::normaliseDescription($description);
        $this->logoUrl = self::normaliseLogoUrl($logoUrl);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return DeploymentURL|null
     */
    public function getLiveDeploymentURL(): ?DeploymentURL
    {
        return $this->liveDeploymentURL;
    }

    /**
     * @return DeploymentURL|null
     */
    public function getSandboxDeploymentURL(): ?DeploymentURL
    {
        return $this->sandboxDeploymentURL;
    }

    /**
     * @return array<string, string>
     */
    public function getDescription(): array
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getLogoUrl(): string
    {
        return $this->logoUrl;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'live' => $this->getLiveDeploymentURL()->toArray(),
            'sandbox' => $this->getSandboxDeploymentURL()->toArray(),
            'description' => $this->getDescription(),
            'logo_url' => $this->getLogoUrl(),
        ];
    }

    /**
     * @param mixed[] $data
     *
     * @return Deployment
     */
    public static function fromArray(array $data): Deployment
    {
        return new self(
            $data['id'] ?? '',
            $data['name'] ?? '',
            DeploymentURL::fromArray($data['live'] ?? []),
            DeploymentURL::fromArray($data['sandbox'] ?? []),
            \is_array($data['description'] ?? null) ? $data['description'] : [],
            \is_string($data['logo_url'] ?? null) ? $data['logo_url'] : ''
        );
    }

    /**
     * @param array<mixed, mixed> $description
     *
     * @return array<string, string>
     */
    private static function normaliseDescription(array $description): array
    {
        $normalised = [];
        foreach ($description as $language => $text) {
            if (\is_string($language) && \is_string($text)) {
                $normalised[$language] = $text;
            }
        }

        return $normalised;
    }

    private static function normaliseLogoUrl(string $logoUrl): string
    {
        if (\strpos($logoUrl, 'https://') !== 0 || \filter_var($logoUrl, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        return $logoUrl;
    }
}
