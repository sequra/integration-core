<?php

namespace SeQura\Core\BusinessLogic\Utility;

use DateTime;

/**
 * Class StringValidator
 *
 * @package SeQura\Core\BusinessLogic\Utility
 */
class StringValidator
{
    /**
     * Returns true if a string is a ISO 8601 formatted date.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isISO8601Date(string $string): bool
    {
        return (bool)DateTime::createFromFormat('Y-m-d', $string);
    }

    /**
     * Returns true if a string is a ISO 8601 formatted timestamp.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isISO8601Timestamp(string $string): bool
    {
        return (bool)DateTime::createFromFormat('Y-m-d\TH:i:sP', $string);
    }

    /**
     * Returns true if a string is a ISO 8601 formatted duration.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isISO8601Duration(string $string): bool
    {
        return (bool)preg_match('/^P(\d+Y)?(\d+M)?(\d+D)?(T(\d+H)?(\d+M)?(\d+S)?)?$/', $string);
    }

    /**
     * Returns true if a string is between provided min and max length.
     *
     * @param string $string
     * @param int $minLength
     * @param int $maxLength
     *
     * @return bool
     */
    public static function isStringLengthBetween(string $string, int $minLength, int $maxLength): bool
    {
        return strlen($string) >= $minLength && strlen($string) <= $maxLength;
    }

    /**
     * Return true if a string is a valid url.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isValidUrl(string $string): bool
    {
        return (bool)filter_var($string, FILTER_VALIDATE_URL);
    }
}
