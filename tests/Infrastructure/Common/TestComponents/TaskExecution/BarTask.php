<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

class BarTask extends FooTask
{
    public function execute(): void
    {
        parent::execute();

        $this->reportProgress(100);
    }
}
