# syntax=docker/dockerfile:1

# Run and test
FROM php:7.3-cli AS base
RUN apt-get update -y \
    && apt-get install libxml2-dev libtidy-dev libzip-dev wget zip -y \
    && docker-php-ext-install xml \
    && docker-php-ext-install tidy \
    && docker-php-ext-install zip \
	&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
	&& mkdir /var/target-lib

FROM base AS dev
RUN apt-get install bash -y \
	&& pecl install xdebug-3.1.6 \
	&& docker-php-ext-enable xdebug

# Documentation
FROM alpine AS generate-docs
RUN apk --no-cache add bash doxygen graphviz pandoc-cli