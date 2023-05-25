<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM;

use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;

class TestRepositoryRegistry extends RepositoryRegistry
{
    public static function cleanUp()
    {
        static::$repositories = array();
        static::$instantiated = array();
    }
}
