<?php

namespace SeQura\Core\Infrastructure\Utility;

/**
 * Class RegexProvider.
 *
 * @package SeQura\Core\Infrastructure\Utility
 */
class RegexProvider
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Get regular expression to validate an IPv4 or IPv6 address
     */
    public function getIpRegex(bool $includeSlashes = true): string
    {
        return $this->maybeStripSlashes(
            '/^(((25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?))|([0-9a-fA-F]{1,4}:){7}([0-9a-fA-F]{1,4}))$/',
            $includeSlashes
        );
    }

    /**
     * Get regular expression to validate a date or duration following ISO 8601
     */
    public function getDateOrDurationRegex(bool $includeSlashes = true): string
    {
        // ISO 8601 date patterns:
        // 1. yyyy-mm-dd for non-leap years (Feb 1-28)
        $dateNonLeap = '\d{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8])';
        // 2. yyyy-mm-dd for months with 29 or 30 days (excluding February 29)
        $dateMonth29Or30 = '\d{4}-(?:0[13-9]|1[0-2])-(?:29|30)';
        // 3. yyyy-mm-dd for months with 31 days
        $dateMonth31 = '\d{4}-(?:0[13578]|1[012])-(?:31)';
        // 4. yyyy-mm-dd for leap year February 29
        $dateLeapFeb29 = '\d{2}(?:[02468][048]|[13579][26])-(?:02)-29';
        // ISO 8601 duration pattern (e.g., P3Y6M4DT12H30M5S)
        $duration = 'P(?:\d+Y)?(?:\d+M)?(?:\d+W)?(?:\d+D)?(?:T(?:\d+H)?(?:\d+M)?(?:\d+S)?)?';

        // Combine all patterns with alternation
        $fullPattern =
            '/^(' .
                '(?:' . $dateNonLeap . ')' . '|' .
                '(?:' . $dateMonth29Or30 . ')' . '|' .
                '(?:' . $dateMonth31 . ')' . '|' .
                '(?:' . $dateLeapFeb29 . ')' . '|' .
                '(' . $duration . ')' .
            ')$/';

        // Inline documentation:
        // - $dateNonLeap: yyyy-mm-dd for non-leap years (Feb 1-28)
        // - $dateMonth29Or30: yyyy-mm-dd for months with 29 or 30 days (excluding Feb 29)
        // - $dateMonth31: yyyy-mm-dd for months with 31 days
        // - $dateLeapFeb29: yyyy-mm-dd for leap year Feb 29
        // - $duration: ISO 8601 duration (PnYnMnDTnHnMnS)

        return $this->maybeStripSlashes(
            $fullPattern,
            $includeSlashes
        );
    }

    /**
     * Get regular expression to validate an email address
     */
    public function getEmailRegex(bool $includeSlashes = true): string
    {
        return $this->maybeStripSlashes(
            '/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/',
            $includeSlashes
        );
    }

    /**
     * Get regular expression to validate a URL
     */
    public function getUrlRegex(bool $includeSlashes = true): string
    {
        return $this->maybeStripSlashes(
            '/(https?:\/\/)([\w\-])+\.([a-zA-Z]{2,63})([\/\w-]*)*\/?\??([^#\n\r]*)?#?([^\n\r]*)/',
            $includeSlashes
        );
    }

    /**
     * Maybe strip slashes from a regex
     */
    private function maybeStripSlashes(string $regex, bool $includeSlashes): string
    {
        return $includeSlashes ? $regex : substr($regex, 1, -1);
    }

    /**
     * Returns all regexes as an associative array so they can be easily converted to JSON.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'ip' => $this->getIpRegex(false),
            'dateOrDuration' => $this->getDateOrDurationRegex(false),
            'email' => $this->getEmailRegex(false),
            'url' => $this->getUrlRegex(false),
        ];
    }
}
