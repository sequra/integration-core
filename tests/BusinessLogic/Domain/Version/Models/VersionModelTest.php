<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Version\Models;

use SeQura\Core\BusinessLogic\Domain\Version\Models\Version;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class VersionModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Version\Models
 */
class VersionModelTest extends BaseTestCase
{
    public function testSettersAndGetters(): void
    {
        $version = new Version('v1.0.0', 'v1.1.1', 'test');

        $version->setCurrent('v2.2.2');
        $version->setNew('v.3.2.1');
        $version->setDownloadNewVersionUrl('test url');

        self::assertEquals('v2.2.2', $version->getCurrent());
        self::assertEquals('v.3.2.1', $version->getNew());
        self::assertEquals('test url', $version->getDownloadNewVersionUrl());
    }
}
