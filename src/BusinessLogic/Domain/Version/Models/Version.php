<?php

namespace SeQura\Core\BusinessLogic\Domain\Version\Models;

/**
 * Class Version
 *
 * @package SeQura\Core\BusinessLogic\Domain\Version\Models
 */
class Version
{
    /**
     * @var string
     */
    protected $current;
    /**
     * @var string | null
     */
    protected $new;
    /**
     * @var string | null
     */
    protected $downloadNewVersionUrl;

    /**
     * @param string $current
     * @param string|null $new
     * @param string|null $downloadNewVersionUrl
     */
    public function __construct(string $current, ?string $new, ?string $downloadNewVersionUrl)
    {
        $this->current = $current;
        $this->new = $new;
        $this->downloadNewVersionUrl = $downloadNewVersionUrl;
    }

    /**
     * @return string
     */
    public function getCurrent(): string
    {
        return $this->current;
    }

    /**
     * @param string $current
     */
    public function setCurrent(string $current): void
    {
        $this->current = $current;
    }

    /**
     * @return string|null
     */
    public function getNew(): ?string
    {
        return $this->new;
    }

    /**
     * @param string|null $new
     */
    public function setNew(?string $new): void
    {
        $this->new = $new;
    }

    /**
     * @return string|null
     */
    public function getDownloadNewVersionUrl(): ?string
    {
        return $this->downloadNewVersionUrl;
    }

    /**
     * @param string|null $downloadNewVersionUrl
     */
    public function setDownloadNewVersionUrl(?string $downloadNewVersionUrl): void
    {
        $this->downloadNewVersionUrl = $downloadNewVersionUrl;
    }
}
