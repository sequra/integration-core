<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;

/**
 * Class GeneralSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests
 */
class GeneralSettingsRequest extends Request
{
    /**
     * @var bool
     */
    private $sendOrderReportsPeriodicallyToSeQura;

    /**
     * @var bool|null
     */
    private $showSeQuraCheckoutAsHostedPage;

    /**
     * @var string[]|null
     */
    private $allowedIPAddresses;

    /**
     * @var string[]|null
     */
    private $excludedCategories;

    /**
     * @var string[]|null
     */
    private $excludedProducts;

    /**
     * @var string|null
     */
    private $replacementPaymentMethod;

    /**
     * @param bool $sendOrderReportsPeriodicallyToSeQura
     * @param bool|null $showSeQuraCheckoutAsHostedPage
     * @param string[]|null $allowedIPAddresses
     * @param string[]|null $excludedProducts
     * @param string[]|null $excludedCategories
     * @param string|null $replacementPaymentMethod
     */
    public function __construct(
        bool $sendOrderReportsPeriodicallyToSeQura,
        ?bool $showSeQuraCheckoutAsHostedPage,
        ?array $allowedIPAddresses,
        ?array $excludedProducts,
        ?array $excludedCategories,
        ?string $replacementPaymentMethod
    )
    {
        $this->sendOrderReportsPeriodicallyToSeQura = $sendOrderReportsPeriodicallyToSeQura;
        $this->showSeQuraCheckoutAsHostedPage = $showSeQuraCheckoutAsHostedPage;
        $this->allowedIPAddresses = $allowedIPAddresses;
        $this->excludedProducts = $excludedProducts;
        $this->excludedCategories = $excludedCategories;
        $this->replacementPaymentMethod = $replacementPaymentMethod;
    }

    /**
     * Transforms the request to a GeneralSettings object.
     *
     * @return GeneralSettings
     */
    public function transformToDomainModel(): object
    {
        return new GeneralSettings(
            $this->sendOrderReportsPeriodicallyToSeQura,
            $this->showSeQuraCheckoutAsHostedPage,
            $this->allowedIPAddresses,
            $this->excludedProducts,
            $this->excludedCategories,
            $this->replacementPaymentMethod
        );
    }
}
