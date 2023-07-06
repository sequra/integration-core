<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection;

use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ConnectionRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\OnboardingRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\ConnectionSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\SuccessfulConnectionResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Response\ErrorResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
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
    private $connectionService;

    /**
     * @param ConnectionService $connectionService
     */
    public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    /**
     * Saves the onboarding data to the database.
     *
     * @param OnboardingRequest $onboardingRequest
     *
     * @return Response
     *
     * @throws InvalidEnvironmentException
     */
    public function saveOnboardingData(OnboardingRequest $onboardingRequest): Response
    {
        $this->connectionService->saveConnectionData($onboardingRequest->transformToDomainModel()->getConnectionData());

        return new SuccessfulConnectionResponse();
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
        } catch (BadMerchantIdException|InvalidEnvironmentException|WrongCredentialsException|HttpRequestException $e) {
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
     * Gets the connection data from the database.
     *
     * @return ConnectionSettingsResponse
     */
    public function getConnectionSettings(): ConnectionSettingsResponse
    {
        return new ConnectionSettingsResponse($this->connectionService->getConnectionData());
    }
}
