<?php

namespace SeQura\Core\BusinessLogic\Webhook\Validator;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\InvalidCartException;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\InvalidSignatureException;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\InvalidStateException;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\OrderNotFoundException;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;

/**
 * Class WebhookValidator
 *
 * @package SeQura\Core\BusinessLogic\Webhook\Validator
 */
class WebhookValidator
{
    const ALLOWED_STATES = ['approved', 'cancelled', 'needs_review'];

    /**
     * Validates webhook payload.
     *
     * @param Webhook $webhook
     *
     * @throws InvalidSignatureException
     * @throws InvalidStateException
     * @throws OrderNotFoundException
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     * @throws InvalidCartException
     */
    public function validate(Webhook $webhook): void
    {
        $order = $this->getSeQuraOrderByOrderReference($webhook->getOrderRef());

        if ($order === null) {
            throw new OrderNotFoundException(
                "SeQura order with reference {$webhook->getOrderRef()} is not found.",
                404
            );
        }

        if ($webhook->getSignature() !== $order->getMerchant()->getNotificationParameters()['signature']) {
            throw new InvalidSignatureException('Signature mismatch.', 400);
        }

        if (!in_array($webhook->sqState, self::ALLOWED_STATES)) {
            throw new InvalidStateException("Unknown event '{$webhook->sqState}'", 400);
        }
    }

    /**
     * Retrieves the SeQuraOrder by orderRef1
     *
     * @param string $orderRef
     *
     * @return SeQuraOrder|null
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    protected function getSeQuraOrderByOrderReference(string $orderRef): ?SeQuraOrder
    {
        $repository = RepositoryRegistry::getRepository(SeQuraOrder::getClassName());

        $filter = new QueryFilter();
        $filter->where('reference', Operators::EQUALS, $orderRef);

        /**
        * @var SeQuraOrder $order
        */
        $order = $repository->selectOne($filter);

        return $order;
    }
}
