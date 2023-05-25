<?php

namespace SeQura\Core\Tests\Infrastructure\ORM;

use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use PHPUnit\Framework\TestCase;

/**
 * Class EntityConfigurationTest
 * @package SeQura\Core\Tests\Infrastructure\ORM
 */
class EntityConfigurationTest extends TestCase
{
    public function testEntityConfiguration()
    {
        $map = new IndexMap();
        $type = 'test';
        $config = new EntityConfiguration($map, $type);

        $this->assertEquals($map, $config->getIndexMap());
        $this->assertEquals($type, $config->getType());
    }
}
