<?php

namespace SeQura\Core\BusinessLogic\Domain\Log\Model;

/**
 * Class Log.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Log\Model
 */
class Log
{
    /**
     * @var string[] $content
     */
    private $content;

    /**
     * @param string[] $content
     */
    public function __construct(array $content)
    {
        $this->content = $content;
    }

    /**
     * @return string[]
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return $this->content;
    }
}
