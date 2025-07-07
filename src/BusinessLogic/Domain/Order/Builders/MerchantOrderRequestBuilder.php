<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Builders;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\MerchantDataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\EventsWebhook;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;

/**
 * Class MerchantOrderRequestBuilder.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Builders
 */
class MerchantOrderRequestBuilder
{
    /**
     * @var ConnectionService $connectionService
     */
    private $connectionService;

    /**
     * @var CredentialsService $credentialsService
     */
    private $credentialsService;

    /**
     * @var MerchantDataProviderInterface $merchantDataProvider
     */
    private $merchantDataProvider;

    /**
     * @param ConnectionService $connectionService
     * @param CredentialsService $credentialsService
     * @param MerchantDataProviderInterface $merchantDataProvider
     */
    public function __construct(
        ConnectionService $connectionService,
        CredentialsService $credentialsService,
        MerchantDataProviderInterface $merchantDataProvider
    ) {
        $this->connectionService = $connectionService;
        $this->credentialsService = $credentialsService;
        $this->merchantDataProvider = $merchantDataProvider;
    }

    /**
     * @param string $countryCode
     * @param string $cartId
     *
     * @return Merchant
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidUrlException
     */
    public function build(string $countryCode, string $cartId): Merchant
    {
        $credentials = $this->credentialsService->getCredentialsByCountryCode($countryCode);

        if (!$credentials) {
            throw new CredentialsNotFoundException();
        }

        $merchantId = $credentials->getMerchantId();
        $defaultParameters = $this->getDefaultParameters($merchantId, $cartId);
        $eventsWebhook = new EventsWebhook(
            $this->merchantDataProvider->getEventsWebhookUrl(),
            array_merge($defaultParameters, $this->merchantDataProvider->getEventsWebhookParameters())
        );

        return new Merchant(
            $merchantId,
            $this->merchantDataProvider->getNotifyUrl(),
            array_merge(
                $this->merchantDataProvider->getNotificationParameters(),
                $defaultParameters
            ),
            $this->merchantDataProvider->getReturnUrl(),
            $this->merchantDataProvider->getApprovedCallback(),
            $this->merchantDataProvider->getEditUrl(),
            $this->merchantDataProvider->getAbortUrl(),
            $this->merchantDataProvider->getRejectedCallback(),
            $this->merchantDataProvider->getPartPaymentDetailsGetter(),
            $this->merchantDataProvider->getApprovedUrl(),
            $this->merchantDataProvider->getOptions(),
            $eventsWebhook
        );
    }

    /**
     * @param string $merchantId
     * @param string $cartId
     *
     * @return array<string, mixed>
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     */
    private function getDefaultParameters(string $merchantId, string $cartId): array
    {
        return [
            'storeId' => StoreContext::getInstance()->getStoreId(),
            'signature' => $this->generateSignature($merchantId, $cartId),
        ];
    }

    /**
     * @param string $merchantId
     * @param string $cartId
     *
     * @return string
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     */
    private function generateSignature(string $merchantId, string $cartId): string
    {
        $connectionData = $this->connectionService->getConnectionDataByMerchantId($merchantId);

        return hash_hmac(
            'sha256',
            implode(
                '_',
                [
                    $cartId,
                    $connectionData->getMerchantId(),
                    $connectionData->getAuthorizationCredentials()->getUsername()
                ]
            ),
            $connectionData->getAuthorizationCredentials()->getPassword()
        );
    }
}
