FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# System deps
RUN apk add --no-cache \
    bash git curl zip unzip icu-dev oniguruma-dev \
    libpq-dev postgresql-client \
    libzip-dev \
    nodejs npm \
    supervisor

# PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
    intl pdo pdo_pgsql mbstring opcache zip exif



# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Supervisor (опционально: queue/cron)
COPY docker/php/supervisord.conf /etc/supervisord.conf

EXPOSE 9000

CMD ["php-fpm"]
