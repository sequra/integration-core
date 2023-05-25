<?php

namespace SeQura\Core\Tests\BusinessLogic\Common;

use SeQura\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\ServiceRegister;

class BaseSerializationTestCase extends BaseTestCase
{
    protected $serializable;

    /**
     * @return void
     */
    public function testNativeSerialization(): void
    {
        ServiceRegister::registerService(Serializer::CLASS_NAME, static function () {
            return new NativeSerializer();
        });

        self::assertEquals($this->serializable, Serializer::unserialize(Serializer::serialize($this->serializable)));
    }

    /**
     * @return void
     */
    public function testJsonSerialization(): void
    {
        ServiceRegister::registerService(Serializer::CLASS_NAME, static function () {
            return new JsonSerializer();
        });

        self::assertEquals($this->serializable, Serializer::unserialize(Serializer::serialize($this->serializable)));
    }
}
