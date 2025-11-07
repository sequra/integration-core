FROM php:7.2-cli-alpine

# Build arguments for dynamic XDebug configuration
ARG XDEBUG_IDEKEY=PHPSTORM
ARG XDEBUG_CONFIG="idekey=PHPSTORM mode=req"
ARG PHP_IDE_CONFIG=serverName=localhost

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS

RUN pecl install xdebug-2.9.8 && \
    docker-php-ext-enable xdebug

# Configure XDebug for both VSCode and PhpStorm compatibility
RUN echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_autostart=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_mode=req" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.idekey=${XDEBUG_IDEKEY}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.default_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_connect_back=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log_level=7" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN apk del .build-deps

WORKDIR /app

# Set environment variables for XDebug IDE configuration
ENV XDEBUG_CONFIG=${XDEBUG_CONFIG}
ENV PHP_IDE_CONFIG=${PHP_IDE_CONFIG}