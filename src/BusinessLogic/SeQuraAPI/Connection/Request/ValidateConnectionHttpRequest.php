<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Connection\Request;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ValidateConnectionRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Authorization\AuthorizedProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class ValidateConnectionHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Connection\Request
 */
class ValidateConnectionHttpRequest extends HttpRequest
{
    public function __construct(ValidateConnectionRequest $request)
    {
        parent::__construct(
            '/merchants/' . $request->getConnectionData()->getMerchantId() . '/payment_methods',
            [],
            [],
            $this->generateAuthHeaderForValidation($request->getConnectionData()->getAuthorizationCredentials())
        );
    }

    /**
     * Creates an authorization header for connection validation.
     *
     * @param AuthorizationCredentials $authorizationCredentials
     *
     * @return string[]
     */
    protected function generateAuthHeaderForValidation(AuthorizationCredentials $authorizationCredentials): array
    {
        $token = base64_encode(sprintf(
            '%s:%s',
            $authorizationCredentials->getUsername(),
            $authorizationCredentials->getPassword()
        ));

        return [AuthorizedProxy::AUTHORIZATION_HEADER_KEY => AuthorizedProxy::AUTHORIZATION_HEADER_VALUE_PREFIX . $token];
    }
}
