<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\CredentialsRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiInvalidUrlParameterException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiUnauthorizedException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class CredentialsService.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Services
 */
class CredentialsService
{
    /**
     * @var ConnectionProxyInterface $connectionProxy
     */
    protected $connectionProxy;
    /**
     * @var CredentialsRepositoryInterface $credentialsRepository
     */
    protected $credentialsRepository;

    /**
     * @var CountryConfigurationRepositoryInterface $countryConfigurationRepository
     */
    protected $countryConfigurationRepository;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    protected $paymentMethodRepository;

    /**
     * @param ConnectionProxyInterface $connectionProxy
     * @param CredentialsRepositoryInterface $credentialsRepository
     * @param CountryConfigurationRepositoryInterface $countryConfigurationRepository
     * @param PaymentMethodRepositoryInterface $paymentMethodRepository
     */
    public function __construct(
        ConnectionProxyInterface $connectionProxy,
        CredentialsRepositoryInterface $credentialsRepository,
        CountryConfigurationRepositoryInterface $countryConfigurationRepository,
        PaymentMethodRepositoryInterface $paymentMethodRepository
    ) {
        $this->connectionProxy = $connectionProxy;
        $this->credentialsRepository = $credentialsRepository;
        $this->countryConfigurationRepository = $countryConfigurationRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * Attempts to send an HTTP request to the SeQura API with the provided connection data.
     * If response is successful, credentials entities are saved to database.
     *
     * @param ConnectionData $connectionData
     *
     * @return Credentials[]
     *
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     * @throws BadMerchantIdException
     */
    public function validateAndUpdateCredentials(ConnectionData $connectionData): array
    {
        try {
            $credentials = $this->connectionProxy->getCredentials(new CredentialsRequest($connectionData));
        } catch (HttpApiUnauthorizedException $exception) {
            throw new WrongCredentialsException();
        } catch (HttpApiInvalidUrlParameterException $exception) {
            throw new BadMerchantIdException();
        }

        $this->credentialsRepository->deleteCredentialsByDeploymentId($connectionData->getDeployment());
        $this->credentialsRepository->setCredentials($credentials);

        return $credentials;
    }

    /**
     * Updates country configuration with new merchant ids and remove payment methods with old merchant ids
     *
     * @param Credentials[] $credentials
     *
     * @return void
     *
     * @throws PaymentMethodNotFoundException
     */
    public function updateCountryConfigurationWithNewMerchantIdsAndRemoveOldPaymentMethods(array $credentials): void
    {
        $countryConfigurations = $this->countryConfigurationRepository->getCountryConfiguration();
        if (!$countryConfigurations) {
            return;
        }

        $newMerchantIds = [];
        foreach ($credentials as $credential) {
            $newMerchantIds[$credential->getCountry()] = $credential->getMerchantId();
        }

        foreach ($countryConfigurations as $configuration) {
            if (array_key_exists($configuration->getCountryCode(), $newMerchantIds)) {
                $this->paymentMethodRepository->deletePaymentMethods($configuration->getMerchantId());
                $configuration->setMerchantId($newMerchantIds[$configuration->getCountryCode()]);
            }
        }

        $this->countryConfigurationRepository->setCountryConfiguration($countryConfigurations);
    }

    /**
     * @return Credentials[]
     */
    public function getCredentials(): array
    {
        return $this->credentialsRepository->getCredentials();
    }

    /**
     * Returns credentials by given county code
     *
     * @param string $countryCode
     *
     * @return Credentials|null
     */
    public function getCredentialsByCountryCode(string $countryCode): ?Credentials
    {
        return $this->credentialsRepository->getCredentialsByCountryCode($countryCode);
    }

    /**
     * Returns credentials by given merchant ID
     *
     * @param string $merchantId
     *
     * @return Credentials|null
     */
    public function getCredentialsByMerchantId(string $merchantId): ?Credentials
    {
        return $this->credentialsRepository->getCredentialsByMerchantId($merchantId);
    }

     /**
     * Get the merchant ID by country code.
     *
     * @throws CredentialsNotFoundException
     */
    public function getMerchantIdByCountryCode(string $countryCode): string
    {
        $credentials = $this->getCredentialsByCountryCode($countryCode);
        if (!$credentials) {
            throw new CredentialsNotFoundException();
        }
        return $credentials->getMerchantId();
    }
}
