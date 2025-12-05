<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection;

use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ConnectionRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\OnboardingRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ReRegisterWebhookRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\ConnectionValidationResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\OnboardingDataResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\ReRegisterWebhookResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\SuccessfulConnectionResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Response\ErrorResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class ConnectionController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Connection
 */
class ConnectionController
{
    /**
     * @var ConnectionService
     */
    protected $connectionService;

    /**
     * @var StatisticalDataService
     */
    protected $statisticalDataService;

    /**
     * @param ConnectionService $connectionService
     * @param StatisticalDataService $statisticalDataService
     */
    public function __construct(ConnectionService $connectionService, StatisticalDataService $statisticalDataService)
    {
        $this->connectionService = $connectionService;
        $this->statisticalDataService = $statisticalDataService;
    }

    /**
     * Gets the onboarding data from the database.
     *
     * @return OnboardingDataResponse
     */
    public function getOnboardingData(): OnboardingDataResponse
    {
        return new OnboardingDataResponse(
            $this->connectionService->getAllConnectionData(),
            $this->statisticalDataService->getStatisticalData()
        );
    }

    /**
     * Validates connection data.
     *
     * @param ConnectionRequest $connectionRequest
     *
     * @return ConnectionValidationResponse
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     */
    public function isConnectionDataValid(ConnectionRequest $connectionRequest): ConnectionValidationResponse
    {
        try {
            $this->connectionService->isConnectionDataValid($connectionRequest->transformToDomainModel());
        } catch (BadMerchantIdException $e) {
            return new ConnectionValidationResponse(false, 'merchantId');
        } catch (WrongCredentialsException $e) {
            return new ConnectionValidationResponse(false, 'username/password');
        }

        return new ConnectionValidationResponse(true);
    }

    /**
     * Validates connection data.
     *
     * @param ConnectionRequest $connectionRequest
     *
     * @return Response
     */
    public function validateConnectionData(ConnectionRequest $connectionRequest): Response
    {
        try {
            $this->connectionService->isConnectionDataValid($connectionRequest->transformToDomainModel());
        } catch (BadMerchantIdException | InvalidEnvironmentException | WrongCredentialsException | HttpRequestException $e) {
            return new ErrorResponse($e);
        }

        return new SuccessfulConnectionResponse();
    }

    /**
     * Saves the connection data to the database.
     *
     * @param ConnectionRequest $connectionRequest
     *
     * @return Response
     */
    public function saveConnectionData(ConnectionRequest $connectionRequest): Response
    {
        try {
            $this->connectionService->saveConnectionData($connectionRequest->transformToDomainModel());
        } catch (InvalidEnvironmentException $e) {
            return new ErrorResponse($e);
        }

        return new SuccessfulConnectionResponse();
    }

    /**
     * Validates connection data.
     *
     * @param OnboardingRequest $onboardingRequest
     *
     * @return Response
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws CapabilitiesEmptyException
     */
    public function connect(OnboardingRequest $onboardingRequest): Response
    {
        try {
            $this->connectionService->connect($onboardingRequest->transformToDomainModel()->getConnections());
            $this->statisticalDataService->saveStatisticalData(
                new StatisticalData($onboardingRequest->transformToDomainModel()->isSendStatisticalData())
            );
        } catch (BadMerchantIdException $e) {
            return new ConnectionValidationResponse(false, 'merchantId');
        } catch (WrongCredentialsException $e) {
            return new ConnectionValidationResponse(false, $e->getMessage());
        }

        return new SuccessfulConnectionResponse();
    }

    /**
     * @param ReRegisterWebhookRequest $reRegisterWebhookRequest
     *
     * @return ReRegisterWebhookResponse
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     */
    public function reRegisterWebhooks(ReRegisterWebhookRequest $reRegisterWebhookRequest): Response
    {
        $this->connectionService->reRegisterWebhooks($reRegisterWebhookRequest->transformToDomainModel());

        return new ReRegisterWebhookResponse();
    }
}
