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
            '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/',
            $includeSlashes
        );
    }

    /**
     * Get regular expression to validate a date or duration following ISO 8601
     */
    public function getDateOrDurationRegex(bool $includeSlashes = true): string
    {
        return $this->maybeStripSlashes(
            '/^((?:\d{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8]))|(?:\d{4}-(?:0[13-9]|1[0-2])-(?:29|30))|(?:\d{4}-(?:0[13578]|1[012])-(?:31))|(?:\d{2}(?:[02468][048]|[13579][26])-(?:02)-29)|(P(?:\d+Y)?(?:\d+M)?(?:\d+W)?(?:\d+D)?(?:T(?:\d+H)?(?:\d+M)?(?:\d+S)?)?))$/',
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
}
