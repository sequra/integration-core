<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents;

use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;

class TestConfigurationManager extends ConfigurationManager
{
    protected $context = 'test';

    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    public function __construct()
    {
        parent::__construct();

        static::$instance = $this;
    }
}
