FROM node:24-bookworm-slim AS node
FROM composer:2 AS composer
FROM php:8.4-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates curl git unzip libicu-dev libonig-dev libpq-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" bcmath intl mbstring pcntl pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
ARG DOCKER_UID=1000
ARG DOCKER_GID=1000
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && groupadd --non-unique --gid "${DOCKER_GID}" app \
    && useradd --non-unique --uid "${DOCKER_UID}" --gid app --create-home app \
    && mkdir -p /var/www/html \
    && chown app:app /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
WORKDIR /var/www/html
USER app
EXPOSE 8000 5173
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
