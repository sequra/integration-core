<?php

namespace SeQura\Core\Tests\Infrastructure\Common;

use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class TestServiceRegister.
 *
 * @package SeQura\Core\Tests\Infrastructure\Common
 */
class TestServiceRegister extends ServiceRegister
{
    /**
     * TestServiceRegister constructor.
     *
     * @inheritdoc
     */
    public function __construct(array $services = array())
    {
        // changing visibility so that Services could be reset in tests.
        parent::__construct($services);
    }
}
