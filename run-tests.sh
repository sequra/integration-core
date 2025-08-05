#!/usr/bin/env bash

echo -e "\e[32mUnit tests: PHP 7.4\e[39m"
/usr/bin/php7.4 ./vendor/bin/phpunit --configuration ./phpunit.xml

echo -e "\e[32mUnit tests: PHP 8.0\e[39m"
/usr/bin/php8.0 ./vendor/bin/phpunit --configuration ./phpunit.xml

echo -e "\e[32mUnit tests: PHP 8.1\e[39m"
/usr/bin/php8.1 ./vendor/bin/phpunit --configuration ./phpunit.xml

echo -e "\e[32mUnit tests: PHP 8.2\e[39m"
/usr/bin/php8.2 ./vendor/bin/phpunit --configuration ./phpunit.xml

echo -e "\e[32mUnit tests: PHP 8.3\e[39m"
/usr/bin/php8.3 ./vendor/bin/phpunit --configuration ./phpunit.xml

echo -e "\e[32mUnit tests: PHP 8.4\e[39m"
/usr/bin/php8.4 ./vendor/bin/phpunit --configuration ./phpunit.xml

echo -e "\e[32mPHP_CodeSniffer\e[39m"
vendor/bin/phpcs --standard=.phpcs.xml.dist --warning-severity=0 .

echo -e "\e[32mPHPStan\e[39m"
vendor/bin/phpstan analyse src/  phpstan.neon --memory-limit=512M
