# syntax=docker/dockerfile:1

ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-cli AS base
# php:8.0-cli is frozen on EOL Debian 11 "bullseye" (PHP 8.0 itself is EOL
# upstream, so this tag no longer gets rebuilt against a current Debian base),
# and bullseye's live apt mirrors have since dropped those exact package
# builds. Pin to Debian's permanent snapshot archive for that base only -
# the sources.list entries for it already ship commented out in the image.
RUN if grep -q bullseye /etc/os-release; then \
		echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/99snapshot-no-valid-until \
		&& sed -i \
			-e 's@^# deb http://snapshot.debian.org@deb http://snapshot.debian.org@' \
			-e 's@^deb http://deb.debian.org@# deb http://deb.debian.org@' \
			/etc/apt/sources.list; \
	fi
RUN apt-get update -y \
    && apt-get install libxml2-dev libtidy-dev libzip-dev wget zip -y \
    && docker-php-ext-install xml \
    && docker-php-ext-install tidy \
    && docker-php-ext-install zip \
	&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
	&& mkdir /var/target-lib

FROM base AS dev
RUN apt-get install bash -y \
	&& pecl install xdebug \
	&& docker-php-ext-enable xdebug

# Documentation
FROM alpine AS generate-docs
RUN apk --no-cache add bash doxygen graphviz pandoc-cli