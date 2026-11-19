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
     * @param string $id
     * @param string $name
     * @param DeploymentURL|null $liveDeploymentURL
     * @param DeploymentURL|null $sandboxDeploymentURL
     */
    public function __construct(
        string $id,
        string $name,
        ?DeploymentURL $liveDeploymentURL,
        ?DeploymentURL $sandboxDeploymentURL
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->liveDeploymentURL = $liveDeploymentURL;
        $this->sandboxDeploymentURL = $sandboxDeploymentURL;
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
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'live' => $this->getLiveDeploymentURL()->toArray(),
            'sandbox' => $this->getSandboxDeploymentURL()->toArray(),
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
            DeploymentURL::fromArray($data['sandbox'] ?? [])
        );
    }
}
