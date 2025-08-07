<?php

namespace SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class DeploymentNotFoundException.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions
 */
class DeploymentNotFoundException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 403;

    /**
     * @param ?Throwable $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Deployment not found.',
            'general.errors.deployment.notFound'
        ), $previous);
    }
}
