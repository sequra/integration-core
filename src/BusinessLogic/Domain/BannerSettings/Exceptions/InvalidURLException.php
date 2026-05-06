<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions;

use SeQura\Core\Infrastructure\Exceptions\BaseException;

/**
 * Class InvalidURLException
 *
 * @package SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions
 */
class InvalidURLException extends BaseException
{
    /**
     * @var int
     */
    protected $code = 400;
}
