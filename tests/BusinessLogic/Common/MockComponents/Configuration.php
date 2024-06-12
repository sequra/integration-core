<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

class Configuration extends \SeQura\Core\Infrastructure\Configuration\Configuration
{
    /**
     * @inheritDoc
     */
    public function getIntegrationName(): string
    {
        return 'Test';
    }

    /**
     * @inheritDoc
     */
    public function getAsyncProcessUrl($guid): string
    {
        return 'url';
    }
}
