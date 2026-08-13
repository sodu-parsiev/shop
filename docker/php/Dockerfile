FROM php:8.4-fpm-alpine

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
        bash \
        curl \
        icu-dev \
        libzip-dev \
        libpng-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Realign the image's www-data user with the host user so files written
# through the bind mount (storage/, bootstrap/cache/) keep host ownership.
RUN deluser www-data 2>/dev/null || true \
    && delgroup www-data 2>/dev/null || true \
    && addgroup -g "${GID}" www-data \
    && adduser -D -u "${UID}" -G www-data www-data

COPY php.ini /usr/local/etc/php/conf.d/99-dev.ini
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
