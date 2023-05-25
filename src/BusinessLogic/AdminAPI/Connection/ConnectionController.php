<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection;

use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ConnectionRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\SuccessfulConnectionResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Response\ErrorResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
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
     * Validates connection data.
     *
     * @param ConnectionRequest $connectionRequest
     *
     * @return Response
     */
    public function validateConnectionData(ConnectionRequest $connectionRequest): Response
    {
        try {
            $this->connectionService->isConnectionDataValid($this->transformRequestToConnectionData($connectionRequest));
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
            $this->connectionService->saveConnectionData($this->transformRequestToConnectionData($connectionRequest));
        } catch (InvalidEnvironmentException $e) {
            return new ErrorResponse($e);
        }

        return new SuccessfulConnectionResponse();
    }

    /**
     * Creates a connection data instance from the given connection request.
     *
     * @param ConnectionRequest $connectionRequest
     *
     * @return ConnectionData
     *
     * @throws InvalidEnvironmentException
     */
    private function transformRequestToConnectionData(ConnectionRequest $connectionRequest): ConnectionData
    {
        return new ConnectionData(
            $connectionRequest->getEnvironment(),
            $connectionRequest->getMerchantId(),
            new AuthorizationCredentials($connectionRequest->getUsername(), $connectionRequest->getPassword())
        );
    }
}
