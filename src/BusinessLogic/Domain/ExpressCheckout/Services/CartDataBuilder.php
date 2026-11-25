<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\CartAddress;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\CartSummary;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\RegionOption;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ShippingMethodOption;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\RepositoryContracts\ExpressCheckoutSettingsRepositoryInterface;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services
 */
class CartDataBuilder
{
    private const TYPE = 'cartDataReady';

    /**
     * @var ExpressCheckoutSettingsRepositoryInterface
     */
    protected $settingsRepository;

    /**
     * @param ExpressCheckoutSettingsRepositoryInterface $settingsRepository
     */
    public function __construct(ExpressCheckoutSettingsRepositoryInterface $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @param CartSummary $summary
     * @param string|null $metadataEndpoint
     *
     * @return array<string, mixed>
     */
    public function build(CartSummary $summary, ?string $metadataEndpoint): array
    {
        $address = $this->addressToArray($summary->getShippingAddress());

        $data = [
            'type' => self::TYPE,
            'email' => $summary->getEmail(),
            'address' => $address,
            'shippingAddresses' => [$address],
            'shippingMethods' => $this->shippingMethodsInOrder(
                $summary->getShippingMethods(),
                $summary->getAppliedShippingMethodReference()
            ),
            'totalWithTax' => $summary->getTotalWithTax(),
            'storeName' => $summary->getStoreName(),
            'itemImages' => $summary->getItemImages(),
        ];

        if ($summary->getShippingCostWithTax() !== null) {
            $data['shippingCostWithTax'] = $summary->getShippingCostWithTax();
        }

        if ($metadataEndpoint !== null) {
            $data['endpoints'] = ['metadataEndpoint' => $metadataEndpoint];
        }

        if (!empty($summary->getRegionOptions())) {
            $data['regionOptions'] = $this->regionOptionsToArray($summary->getRegionOptions());
        }

        if ($summary->getStoreLogoUrl() !== null) {
            $data['storeLogoUrl'] = $summary->getStoreLogoUrl();
        }

        $termsAndConditionsUrl = $this->getTermsAndConditionsUrl();
        if ($termsAndConditionsUrl !== null) {
            $data['termsAndConditionsUrl'] = $termsAndConditionsUrl;
        }

        return $data;
    }

    /**
     * @param CartAddress $address
     *
     * @return array<string, string>
     */
    private function addressToArray(CartAddress $address): array
    {
        return [
            'reference' => $address->getReference(),
            'fullName' => $address->getFullName(),
            'givenName' => $address->getGivenName(),
            'surnames' => $address->getSurnames(),
            'addressLine1' => $address->getAddressLine1(),
            'addressLine2' => $address->getAddressLine2(),
            'postalCode' => $address->getPostalCode(),
            'city' => $address->getCity(),
            'countryCode' => $address->getCountryCode(),
            'regionId' => $address->getRegionId(),
            'mobilePhone' => $address->getMobilePhone(),
        ];
    }

    /**
     * @param ShippingMethodOption[] $methods
     * @param string|null $appliedReference
     *
     * @return array<int, array<string, string|int>>
     */
    private function shippingMethodsInOrder(array $methods, ?string $appliedReference): array
    {
        $ordered = array_values($methods);
        foreach ($ordered as $index => $method) {
            if ($appliedReference !== null && $method->getReference() === $appliedReference) {
                unset($ordered[$index]);
                array_unshift($ordered, $method);
                break;
            }
        }

        return array_map(static function (ShippingMethodOption $method) {
            return [
                'reference' => $method->getReference(),
                'name' => $method->getName(),
                'costWithTax' => $method->getCostWithTax(),
                'description' => $method->getDescription(),
            ];
        }, $ordered);
    }

    /**
     * @param RegionOption[] $regionOptions
     *
     * @return array<int, array<string, string>>
     */
    private function regionOptionsToArray(array $regionOptions): array
    {
        return array_values(array_map(static function (RegionOption $option) {
            return ['id' => $option->getId(), 'name' => $option->getName()];
        }, $regionOptions));
    }

    /**
     * @return string|null
     */
    private function getTermsAndConditionsUrl(): ?string
    {
        $settings = $this->settingsRepository->getExpressCheckoutSettings();

        return $settings !== null ? $settings->getTermsAndConditionsUrl() : null;
    }
}
