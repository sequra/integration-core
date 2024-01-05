<?php

namespace SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\FailedToRetrieveShopPaymentMethodsException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\ShopPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\Integration\ShopPaymentMethods\ShopPaymentMethodsServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class ShopPaymentMethodService
 *
 * @package SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services
 */
class ShopPaymentMethodService
{
    /**
     * @var ShopPaymentMethodsServiceInterface
     */
    private $integrationPaymentMethodsService;

    /**
     * @param ShopPaymentMethodsServiceInterface $integrationPaymentMethodsService
     */
    public function __construct(ShopPaymentMethodsServiceInterface $integrationPaymentMethodsService)
    {
        $this->integrationPaymentMethodsService = $integrationPaymentMethodsService;
    }

    /**
     * @return ShopPaymentMethod[]
     *
     * @throws FailedToRetrieveShopPaymentMethodsException
     */
    public function getShopPaymentMethods(): array
    {
        try {
            return $this->integrationPaymentMethodsService->getShopPaymentMethods();
        } catch (Exception $e) {
            throw new FailedToRetrieveShopPaymentMethodsException(new TranslatableLabel('Failed to retrieve active payment methods.', 'general.errors.generalSettings.paymentMethods'));
        }
    }
}
