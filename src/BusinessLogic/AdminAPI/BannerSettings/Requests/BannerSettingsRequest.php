<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageTooLargeException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\EmptyBannerParameterException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerInput;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsTransformer;

/**
 * Class BannerSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Requests
 */
class BannerSettingsRequest extends Request
{
    /**
     * @var array<int, array<string, string>>
     */
    protected $bannerConfigs;

    /**
     * @param array<int, array<string, string>> $bannerConfigs
     */
    public function __construct(array $bannerConfigs = [])
    {
        $this->bannerConfigs = $bannerConfigs;
    }

    /**
     * @return BannerInput[]
     *
     * @throws BannerImageTooLargeException
     * @throws EmptyBannerParameterException
     */
    public function transformToDomainModel(): array
    {
        return (new BannerSettingsTransformer())->transform($this->bannerConfigs);
    }
}
