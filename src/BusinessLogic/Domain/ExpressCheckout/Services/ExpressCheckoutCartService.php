<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\ExpressCheckoutCartNotEligibleException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\ExpressCheckoutCartUnavailableException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutCartUpdateException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidShippingMethodReferenceException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\CartChange;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\SolicitedCartSummary;
use SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout\ExpressCheckoutCartProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout\ExpressCheckoutCartUpdaterInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestStates;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services
 */
class ExpressCheckoutCartService
{
    private const FLOW_TOKEN_MAX_LENGTH = 32;
    private const FLOW_TOKEN_PATTERN = '/^[a-zA-Z0-9]{32}$/';

    /**
     * @var ExpressCheckoutCartProviderInterface
     */
    protected $cartProvider;

    /**
     * @var ExpressCheckoutService
     */
    protected $expressCheckoutService;

    /**
     * @var CartDataBuilder
     */
    protected $cartDataBuilder;

    /**
     * @var FlowTokenRegistry
     */
    protected $flowTokenRegistry;

    /**
     * @var SeQuraOrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ExpressCheckoutCartUpdaterInterface
     */
    protected $cartUpdater;

    /**
     * @param ExpressCheckoutCartProviderInterface $cartProvider
     * @param ExpressCheckoutService $expressCheckoutService
     * @param CartDataBuilder $cartDataBuilder
     * @param FlowTokenRegistry $flowTokenRegistry
     * @param SeQuraOrderRepositoryInterface $orderRepository
     * @param ExpressCheckoutCartUpdaterInterface $cartUpdater
     */
    public function __construct(
        ExpressCheckoutCartProviderInterface $cartProvider,
        ExpressCheckoutService $expressCheckoutService,
        CartDataBuilder $cartDataBuilder,
        FlowTokenRegistry $flowTokenRegistry,
        SeQuraOrderRepositoryInterface $orderRepository,
        ExpressCheckoutCartUpdaterInterface $cartUpdater
    ) {
        $this->cartProvider = $cartProvider;
        $this->expressCheckoutService = $expressCheckoutService;
        $this->cartDataBuilder = $cartDataBuilder;
        $this->flowTokenRegistry = $flowTokenRegistry;
        $this->orderRepository = $orderRepository;
        $this->cartUpdater = $cartUpdater;
    }

    /**
     * @param string $cartId
     *
     * @return SolicitedCartSummary|null Null when the delivery country has no configured merchant.
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidUrlException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws \Exception
     */
    public function solicitCartSummary(string $cartId): ?SolicitedCartSummary
    {
        $builder = $this->cartProvider->getCreateOrderRequestBuilder($cartId);
        $form = $this->expressCheckoutService->solicit($builder, true);
        if ($form === null) {
            return null;
        }

        $summary = $this->cartProvider->getCartSummary($cartId);
        $cartData = $this->cartDataBuilder->build($summary, $form->getMetadataEndpoint());
        $flowToken = $this->flowTokenRegistry->mint($cartId);

        return new SolicitedCartSummary($form, $flowToken, $cartData);
    }

    /**
     * @param mixed $flowToken
     * @param mixed $payload
     *
     * @return array<string, mixed> The cartDataReady payload, with reloadUrl when the form has one.
     *
     * @throws InvalidExpressCheckoutCartUpdateException
     * @throws InvalidShippingMethodReferenceException
     * @throws ExpressCheckoutCartUnavailableException
     * @throws ExpressCheckoutCartNotEligibleException
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidUrlException
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function updateCart($flowToken, $payload): array
    {
        $token = $this->assertFlowTokenShape($flowToken);
        $change = CartChange::fromPayload($payload);

        $cartId = $this->flowTokenRegistry->resolve($token);
        if ($cartId === null || $this->isPlaced($cartId) || !$this->cartUpdater->isCartOpen($cartId)) {
            throw new ExpressCheckoutCartUnavailableException();
        }

        if (!$this->cartUpdater->applyCartChange($cartId, $change)) {
            throw new ExpressCheckoutCartNotEligibleException();
        }

        $builder = $this->cartProvider->getCreateOrderRequestBuilder($cartId);
        $form = $this->expressCheckoutService->solicit($builder, true);
        if ($form === null) {
            throw new ExpressCheckoutCartNotEligibleException();
        }

        $cartData = $this->cartDataBuilder->build(
            $this->cartProvider->getCartSummary($cartId),
            $form->getMetadataEndpoint()
        );
        $formUrl = $form->getFormUrl();
        if ($formUrl !== null) {
            $cartData['reloadUrl'] = $formUrl;
        }

        return $cartData;
    }

    /**
     * @param mixed $flowToken
     *
     * @return string
     *
     * @throws InvalidExpressCheckoutCartUpdateException
     */
    private function assertFlowTokenShape($flowToken): string
    {
        if (
            !\is_string($flowToken)
            || \strlen($flowToken) > self::FLOW_TOKEN_MAX_LENGTH
            || !\preg_match(self::FLOW_TOKEN_PATTERN, \trim($flowToken))
        ) {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        return \trim($flowToken);
    }

    /**
     * @param string $cartId
     *
     * @return bool
     */
    private function isPlaced(string $cartId): bool
    {
        $order = $this->orderRepository->getByCartId($cartId);

        return $order !== null
            && \in_array($order->getState(), [OrderRequestStates::CONFIRMED, OrderRequestStates::ON_HOLD], true);
    }
}
