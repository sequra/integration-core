<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\BusinessLogic\Domain\URL\Exceptions\InvalidUrlException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;

/**
 * Class ConnectionService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Services
 */
class ConnectionService
{
    /**
     * Page of the SeQura portal where a merchant configures the store integrations of their account
     */
    private const PORTAL_STORE_INTEGRATIONS_PATH = '/development/store-integrations';

    /**
     * Env var key that, when explicitly truthy, skips store integration (and therefore webhook) registration
     * for sandbox connections.
     */
    public const SKIP_STORE_INTEGRATION_REGISTRATION_ENV = 'SEQURA_SKIP_STORE_INTEGRATION_REGISTRATION';

    /**
     * @var ConnectionDataRepositoryInterface $connectionDataRepository
     */
    protected $connectionDataRepository;

    /**
     * @var CredentialsService $credentialsService
     */
    protected $credentialsService;

    /**
     * @var StoreIntegrationService $storeIntegrationService
     */
    protected $storeIntegrationService;

    /**
     * @var DeploymentsRepositoryInterface $deploymentsRepository
     */
    protected $deploymentsRepository;

    /**
     * @param ConnectionDataRepositoryInterface $connectionDataRepository
     * @param CredentialsService $credentialsService
     * @param StoreIntegrationService $storeIntegrationService
     * @param DeploymentsRepositoryInterface $deploymentsRepository
     */
    public function __construct(
        ConnectionDataRepositoryInterface $connectionDataRepository,
        CredentialsService $credentialsService,
        StoreIntegrationService $storeIntegrationService,
        DeploymentsRepositoryInterface $deploymentsRepository
    ) {
        $this->connectionDataRepository = $connectionDataRepository;
        $this->credentialsService = $credentialsService;
        $this->storeIntegrationService = $storeIntegrationService;
        $this->deploymentsRepository = $deploymentsRepository;
    }

    /**
     * @param ConnectionData[] $connections
     *
     * @return ConnectionData[] The connections that were connected, without the ones left without credentials
     *
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     * @throws PaymentMethodNotFoundException
     * @throws CapabilitiesEmptyException
     * @throws InvalidUrlException
     */
    public function connect(array $connections): array
    {
        $errors = [];
        $connected = [];

        foreach ($connections as $connectionData) {
            // A deployment the merchant left without credentials is left untouched, not connected and
            // not disconnected either: disconnecting has its own endpoint
            if (!$this->hasCredentials($connectionData)) {
                continue;
            }

            try {
                $credentials = $this->credentialsService->validateAndUpdateCredentials($connectionData);
                $this->credentialsService->updateCountryConfigurationWithNewMerchantIdsAndRemoveOldPaymentMethods($credentials);
                $this->registerWebhooks($connectionData);
                $this->saveConnectionData($connectionData);
                $connected[] = $connectionData;
            } catch (WrongCredentialsException $exception) {
                $errors[] = $connectionData->getDeployment();
            }
        }

        if (!empty($errors)) {
            throw new WrongCredentialsException(null, $errors);
        }

        return $connected;
    }

    /**
     * Tells whether a connection carries the credentials a deployment is connected with.
     *
     * @param ConnectionData $connectionData
     *
     * @return bool
     */
    private function hasCredentials(ConnectionData $connectionData): bool
    {
        $credentials = $connectionData->getAuthorizationCredentials();

        return $credentials->getUsername() !== '' && $credentials->getPassword() !== '';
    }

    /**
     * Attempts to send an HTTP request to the SeQura API with the provided connection data.
     *
     * @param ConnectionData $connectionData
     *
     * @return bool
     *
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     * @throws BadMerchantIdException
     */
    public function isConnectionDataValid(ConnectionData $connectionData): bool
    {
        $this->credentialsService->validateAndUpdateCredentials($connectionData);

        return true;
    }

    /**
     * Retrieves connection data from the database via connection data repository.
     *
     * @return ConnectionData[]
     */
    public function getAllConnectionData(): array
    {
        return $this->connectionDataRepository->getAllConnectionSettings();
    }

    /**
     * Returns the URL of the SeQura portal page where the store is configured, or null
     * when the store is not connected yet or nothing is known about its deployment.
     *
     * @param ConnectionData[]|null $connections Connections of the store, read when not given
     *
     * @return string|null
     */
    public function getPortalUrl(?array $connections = null): ?string
    {
        $connections = $connections ?? $this->getAllConnectionData();

        if (empty($connections)) {
            return null;
        }

        $portalUrl = $this->getPortalBaseUrl(reset($connections));

        return $portalUrl === null ? null : $portalUrl . self::PORTAL_STORE_INTEGRATIONS_PATH;
    }

    /**
     * @param ConnectionData $connection
     *
     * @return string|null
     */
    public function getPortalBaseUrl(ConnectionData $connection): ?string
    {
        $deployment = $this->deploymentsRepository->getDeploymentById($connection->getDeployment());

        if (!$deployment) {
            return null;
        }

        $deploymentUrl = $connection->isLive() ?
            $deployment->getLiveDeploymentURL() :
            $deployment->getSandboxDeploymentURL();
        $portalUrl = $deploymentUrl ? $deploymentUrl->getPortalBaseUrl() : '';

        return $portalUrl === '' ? null : rtrim($portalUrl, '/');
    }

    /**
     * Retrieves connection data from the database via connection data repository.
     *
     * @param string $deployment
     *
     * @return ConnectionData|null
     */
    public function getConnectionDataByDeployment(string $deployment): ?ConnectionData
    {
        return $this->connectionDataRepository->getConnectionDataByDeploymentId($deployment);
    }

    /**
     * @return Credentials[]
     *
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function getCredentials(): array
    {
        $credentials = $this->credentialsService->getCredentials();

        if (!empty($credentials)) {
            return $credentials;
        }

        $connections = $this->getAllConnectionData();
        $credentials = [];
        foreach ($connections as $connectionData) {
            $credentials = $this->credentialsService->validateAndUpdateCredentials($connectionData);
        }

        return $credentials;
    }

    /**
     * Calls the repository to save the connection data to the database.
     *
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    public function saveConnectionData(ConnectionData $connectionData): void
    {
        $this->connectionDataRepository->setConnectionData($connectionData);
    }

    /**
     * Fetches connection data based on merchant ID. Firstly, credentials are fetched, and then based on deployment,
     * Connection data are fetched.
     *
     * @param string $merchantId
     *
     * @return ConnectionData
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     */
    public function getConnectionDataByMerchantId(string $merchantId): ConnectionData
    {
        $credentials = $this->credentialsService->getCredentialsByMerchantId($merchantId);

        if (!$credentials) {
            throw new CredentialsNotFoundException();
        }

        $connectionData = $this->connectionDataRepository->getConnectionDataByDeploymentId($credentials->getDeployment());

        if (!$connectionData) {
            throw new ConnectionDataNotFoundException();
        }

        return $connectionData;
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidUrlException
     */
    public function reRegisterWebhooks(ConnectionData $connectionData): void
    {
        $this->registerWebhooks($connectionData);
        $this->saveConnectionData($connectionData);
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidUrlException
     */
    protected function registerWebhooks(ConnectionData $connectionData): void
    {
        if (self::shouldSkipStoreIntegrationRegistration($connectionData)) {
            Logger::logWarning(
                'Store integration registration skipped because '
                . self::SKIP_STORE_INTEGRATION_REGISTRATION_ENV . ' is set. '
                . 'Webhooks will not be registered for this merchant.',
                'Core',
                [
                    new LogContextData('merchantId', $connectionData->getMerchantId()),
                    new LogContextData('deployment', $connectionData->getDeployment()),
                ]
            );

            return;
        }

        $this->storeIntegrationService->createStoreIntegration($connectionData);
    }

    /**
     * Tells whether store integration registration must be skipped, based on an env var. This is a local
     * development aid, so it is honored in sandbox only: setting the variable against a live merchant has
     * no effect. Only an explicit truthy value ("1" or "true", case-insensitive, trimmed) enables the skip;
     * anything else, including an absent or empty value, means "do not skip".
     *
     * @param ConnectionData $connectionData
     *
     * @return bool
     */
    protected static function shouldSkipStoreIntegrationRegistration(ConnectionData $connectionData): bool
    {
        if (!$connectionData->isSandbox()) {
            return false;
        }

        $flag = getenv(self::SKIP_STORE_INTEGRATION_REGISTRATION_ENV);
        if (!\is_string($flag)) {
            return false;
        }

        return \in_array(strtolower(trim($flag)), ['1', 'true'], true);
    }
}
