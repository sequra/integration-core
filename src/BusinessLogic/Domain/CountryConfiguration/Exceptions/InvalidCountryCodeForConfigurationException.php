<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;

/**
 * Class InvalidCountryCodeForConfigurationException
 *
 * @package SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions
 *
 * @deprecated No longer thrown by integration-core (see PAR-782). Class retained for backwards compatibility
 * with downstream plugins that catch it; will be removed in a future major release.
 */
class InvalidCountryCodeForConfigurationException extends BaseTranslatableException
{
}
