FROM php:7.4-fpm

ARG UID

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y \
        gifsicle \
        jpegoptim \
        npm \
        optipng \
        pngquant \
        unzip && \
    apt-get autoremove -y && \
    apt-get autoclean -y

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN usermod -u $UID www-data && \
    groupmod -g $UID www-data && \
    mkdir -p /usr/src/xdam/vendor/bin && \
	install-php-extensions \
		@composer \
		bcmath \
		exif \
		gd \
		intl \
		opcache \
		pdo_mysql \
		sockets \
		uuid \
		xdebug-^3 \
		zip && \
	npm install -g svgo

COPY docker/php-fpm/config/php_dev.ini $PHP_INI_DIR/php.ini
COPY --chown=www-data:www-data ./ /usr/src/xdam

WORKDIR /usr/src/xdam/

ENV PATH /usr/src/xdam/vendor/bin:$PATH
USER www-data
