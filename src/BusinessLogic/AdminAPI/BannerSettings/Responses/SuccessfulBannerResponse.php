<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class SuccessfulBannerResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses
 */
class SuccessfulBannerResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [];
    }
}
