<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;

/**
 * Class GeneralSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests
 */
class GeneralSettingsRequest extends Request
{
    use SaveGeneralSettingsRequestTrait;
}
