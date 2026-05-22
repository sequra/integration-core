<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class BannerSettings
 *
 * @package SeQura\Core\BusinessLogic\Domain\BannerSettings\Models
 */
class BannerSettings extends DataTransferObject
{
    /**
     * @var Banner[] $bannerConfigs
     */
    protected $bannerConfigs;

    /**
     * @param Banner[] $bannerConfigs
     */
    public function __construct(array $bannerConfigs = [])
    {
        $this->bannerConfigs = $bannerConfigs;
    }

    /**
     * @return Banner[]
     */
    public function getBannerConfigs(): array
    {
        return $this->bannerConfigs;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'bannerConfigs' => Banner::toBatchArray($this->bannerConfigs),
        ];
    }
}
