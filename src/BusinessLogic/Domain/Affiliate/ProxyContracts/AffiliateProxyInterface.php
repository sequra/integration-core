<?php

namespace SeQura\Core\BusinessLogic\Domain\Affiliate\ProxyContracts;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateCancellation;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateConversion;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Interface AffiliateProxyInterface.
 *
 * Outbound affiliate postbacks. The payload leaves the core already shaped for the final
 * destination; the runtime routing only re-routes by path and neither transforms the body
 * nor injects credentials.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Affiliate\ProxyContracts
 */
interface AffiliateProxyInterface
{
    /**
     * Sends the conversion postback (routed to the affiliate network).
     *
     * @param AffiliateConversion $conversion
     *
     * @return bool True when the destination accepted the postback (2xx); a rejection or
     *     transport failure raises rather than returning false -- see @throws.
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException When the merchant is not connected.
     * @throws CredentialsNotFoundException When the merchant has no stored credentials.
     * @throws DeploymentNotFoundException When the merchant's deployment cannot be resolved.
     */
    public function sendConversion(AffiliateConversion $conversion): bool;

    /**
     * Sends the cancellation postback (routed to seQura's conversion-status webhook).
     *
     * @param AffiliateCancellation $cancellation
     *
     * @return bool True when the destination accepted the postback (2xx); a rejection or
     *     transport failure raises rather than returning false -- see @throws.
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException When the merchant is not connected.
     * @throws CredentialsNotFoundException When the merchant has no stored credentials.
     * @throws DeploymentNotFoundException When the merchant's deployment cannot be resolved.
     */
    public function sendCancellation(AffiliateCancellation $cancellation): bool;
}
