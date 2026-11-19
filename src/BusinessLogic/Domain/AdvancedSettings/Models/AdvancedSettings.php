<?php

namespace SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models;

/**
 * Class AdvancedSettings.
 *
 * @package SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models
 */
class AdvancedSettings
{
    /**
     * @var bool $isEnabled
     */
    private $isEnabled;
    /**
     * @var int $level
     */
    private $level;

    /**
     * @param bool $isEnabled
     * @param int $level
     */
    public function __construct(bool $isEnabled, int $level)
    {
        $this->isEnabled = $isEnabled;
        $this->level = $level;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'isEnabled' => $this->isEnabled,
            'level' => $this->level,
        ];
    }
}
