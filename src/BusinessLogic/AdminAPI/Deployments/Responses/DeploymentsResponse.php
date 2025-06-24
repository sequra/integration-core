<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Deployments\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;

/**
 * Class DeploymentsResponse.
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Deployments\Responses
 */
class DeploymentsResponse extends Response
{
    /**
 * @var Deployment[]
*/
    private $deployments;

    /**
     * @param Deployment[] $deployments
     */
    public function __construct(array $deployments)
    {
        $this->deployments = $deployments;
    }

    /**
     * @return array<mixed,mixed>
     */
    public function toArray(): array
    {
        return array_map(
            function ($deployment) {
                return [
                    'id' => $deployment->getId(),
                    'name' => $deployment->getName()
                ];
            },
            $this->deployments
        );
    }
}
