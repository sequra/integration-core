<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutCartUpdateException;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models
 */
class CartChange
{
    private const KEY_ADDRESS = 'address';
    private const KEY_SHIPPING_METHOD_REFERENCE = 'shippingMethodReference';
    private const KEY_EMAIL = 'email';

    private const MAX_SHIPPING_METHOD_REFERENCE_LENGTH = 128;
    private const MAX_EMAIL_LENGTH = 255;
    private const MAX_NAME_LENGTH = 128;
    private const MAX_ADDRESS_LINE_LENGTH = 255;
    private const MAX_POSTAL_CODE_LENGTH = 32;
    private const MAX_CITY_LENGTH = 128;
    private const MAX_COUNTRY_CODE_LENGTH = 2;
    private const MAX_REGION_ID_LENGTH = 32;
    private const MAX_MOBILE_PHONE_LENGTH = 32;

    private const COUNTRY_CODE_PATTERN = '/^[A-Za-z]{2}$/';
    private const MOBILE_PHONE_PATTERN = '/^[\d\s+().\/-]+$/u';
    private const NON_DIGIT_PATTERN = '/\D/';
    private const MIN_MOBILE_PHONE_DIGITS = 6;

    private const ENCODING = 'UTF-8';

    /**
     * @var CartAddressChange|null
     */
    protected $address;

    /**
     * @var string|null
     */
    protected $shippingMethodReference;

    /**
     * @var string|null
     */
    protected $email;

    /**
     * @param CartAddressChange|null $address
     * @param string|null $shippingMethodReference
     * @param string|null $email
     */
    public function __construct(?CartAddressChange $address, ?string $shippingMethodReference, ?string $email)
    {
        $this->address = $address;
        $this->shippingMethodReference = $shippingMethodReference;
        $this->email = $email;
    }

    /**
     * @param mixed $payload
     *
     * @return self
     *
     * @throws InvalidExpressCheckoutCartUpdateException
     */
    public static function fromPayload($payload): self
    {
        $data = self::decode($payload);

        $address = self::readKey($data, self::KEY_ADDRESS);
        $shippingMethodReference = self::readKey($data, self::KEY_SHIPPING_METHOD_REFERENCE);
        $email = self::readKey($data, self::KEY_EMAIL);

        if ($address === null && $shippingMethodReference === null && $email === null) {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        return new self(
            $address === null ? null : self::parseAddress($address),
            $shippingMethodReference === null ? null : self::parseShippingMethodReference($shippingMethodReference),
            $email === null ? null : self::parseEmail($email)
        );
    }

    /**
     * @return CartAddressChange|null
     */
    public function getAddress(): ?CartAddressChange
    {
        return $this->address;
    }

    /**
     * @return string|null
     */
    public function getShippingMethodReference(): ?string
    {
        return $this->shippingMethodReference;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param mixed $payload
     *
     * @return mixed[]
     *
     * @throws InvalidExpressCheckoutCartUpdateException
     */
    private static function decode($payload): array
    {
        if (!\is_string($payload)) {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        $data = \json_decode($payload, true);
        if (!\is_array($data)) {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        return $data;
    }

    /**
     * @param mixed[] $data
     * @param string $key
     *
     * @return mixed
     */
    private static function readKey(array $data, string $key)
    {
        return isset($data[$key]) ? $data[$key] : null;
    }

    /**
     * @param mixed $value
     *
     * @return string
     *
     * @throws InvalidExpressCheckoutCartUpdateException
     */
    private static function parseShippingMethodReference($value): string
    {
        $reference = self::readString($value, self::MAX_SHIPPING_METHOD_REFERENCE_LENGTH);
        if ($reference === '') {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        return $reference;
    }

    /**
     * @param mixed $value
     *
     * @return string
     *
     * @throws InvalidExpressCheckoutCartUpdateException
     */
    private static function parseEmail($value): string
    {
        $email = self::readString($value, self::MAX_EMAIL_LENGTH);
        if (\filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        return $email;
    }

    /**
     * @param mixed $value
     *
     * @return CartAddressChange
     *
     * @throws InvalidExpressCheckoutCartUpdateException
     */
    private static function parseAddress($value): CartAddressChange
    {
        if (!\is_array($value)) {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        $countryCode = self::readField($value, 'countryCode', self::MAX_COUNTRY_CODE_LENGTH, false);
        if ($countryCode !== null) {
            if (!\preg_match(self::COUNTRY_CODE_PATTERN, $countryCode)) {
                throw new InvalidExpressCheckoutCartUpdateException();
            }

            $countryCode = \strtoupper($countryCode);
        }

        $regionId = self::readField($value, 'regionId', self::MAX_REGION_ID_LENGTH, false);
        if ($regionId !== null && !\ctype_digit($regionId)) {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        return new CartAddressChange(
            (string)self::readField($value, 'givenName', self::MAX_NAME_LENGTH, true),
            (string)self::readField($value, 'surnames', self::MAX_NAME_LENGTH, true),
            (string)self::readField($value, 'addressLine1', self::MAX_ADDRESS_LINE_LENGTH, true),
            self::readField($value, 'addressLine2', self::MAX_ADDRESS_LINE_LENGTH, false),
            (string)self::readField($value, 'postalCode', self::MAX_POSTAL_CODE_LENGTH, true),
            (string)self::readField($value, 'city', self::MAX_CITY_LENGTH, true),
            $countryCode,
            $regionId,
            self::parseMobilePhone($value)
        );
    }

    /**
     * @param mixed[] $address
     *
     * @return string
     *
     * @throws InvalidExpressCheckoutCartUpdateException
     */
    private static function parseMobilePhone(array $address): string
    {
        $phone = (string)self::readField($address, 'mobilePhone', self::MAX_MOBILE_PHONE_LENGTH, true);
        $digits = \preg_replace(self::NON_DIGIT_PATTERN, '', $phone);
        if (
            \preg_match(self::MOBILE_PHONE_PATTERN, $phone) !== 1
            || !\is_string($digits)
            || \strlen($digits) < self::MIN_MOBILE_PHONE_DIGITS
        ) {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        return $phone;
    }

    /**
     * @param mixed[] $address
     * @param string $name
     * @param int $maxLength
     * @param bool $required
     *
     * @return string|null
     *
     * @throws InvalidExpressCheckoutCartUpdateException
     */
    private static function readField(array $address, string $name, int $maxLength, bool $required): ?string
    {
        $value = isset($address[$name]) ? self::readString($address[$name], $maxLength) : '';
        if ($value !== '') {
            return $value;
        }

        if ($required) {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        return null;
    }

    /**
     * @param mixed $value
     * @param int $maxLength
     *
     * @return string The trimmed value.
     *
     * @throws InvalidExpressCheckoutCartUpdateException
     */
    private static function readString($value, int $maxLength): string
    {
        if (!\is_string($value) || \mb_strlen($value, self::ENCODING) > $maxLength) {
            throw new InvalidExpressCheckoutCartUpdateException();
        }

        return \trim($value);
    }
}
