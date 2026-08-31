FROM php:8.3-cli-trixie AS base

ENV DEBIAN_FRONTEND=noninteractive \
    PUPPETEER_SKIP_DOWNLOAD=true \
    CHROME_PATH=/usr/bin/chromium \
    PHP_CLI_SERVER_WORKERS=4

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        chromium \
        nodejs \
        npm \
        unzip \
        git \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libxml2-dev \
        libonig-dev \
        libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_sqlite zip gd intl bcmath \
    && rm -rf /var/lib/apt/lists/*

RUN npm install -g puppeteer@24 \
    && npm cache clean --force

RUN { \
        echo "upload_max_filesize = 512M"; \
        echo "post_max_size = 512M"; \
        echo "memory_limit = 512M"; \
    } > /usr/local/etc/php/conf.d/cotiz.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

FROM base AS app

COPY composer.json composer.lock ./
RUN composer config --global source-fallback true \
    && composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && chmod +x docker/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
