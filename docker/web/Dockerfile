FROM dunglas/frankenphp:php8.5 AS frankenphp_base

ENV TZ="Europe/Paris"

WORKDIR /app

VOLUME /app/var/share

RUN apt-get update && apt-get install -y --no-install-recommends \
    file \
    git \
    && rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    install-php-extensions redis \
    @composer-2 \
    intl \
    apcu \
    opcache \
    zip \
    pdo_pgsql \
    ;

RUN set -eux; git config --global --add safe.directory /app

ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY --link docker/web/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 docker/web/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link docker/web/Caddyfile /etc/frankenphp/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile" ]

FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev
ENV XDEBUG_MODE=off
ENV FRANKENPHP_WORKER_CONFIG=watch

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN set -eux; \
    install-php-extensions \
    xdebug \
    ;

COPY --link docker/web/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--watch" ]

FROM frankenphp_base AS frankenphp_transition

# prevent the reinstallation of vendors at every changes in the source code
COPY --link composer.json composer.lock symfony.lock ./
RUN set -eux; \
    composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

FROM node:24-alpine AS build_node

WORKDIR /app

COPY --link package.json pnpm-lock.yaml pnpm-workspace.yaml ./
COPY --link --from=frankenphp_transition /app/vendor/ ./vendor/
RUN --mount=type=cached,target=/pnpm/store,id=pnpm \
    set -eux && \
    corepack enable && \
    corepack install && \
    pnpm config set store-dir /pnpm/store --global && \
    pnpm install --frozen-lockfile

COPY --link webpack.config.js tailwind.config.js postcss.config.mjs ./
COPY --link assets ./assets
COPY --link templates ./templates

RUN set -eux && \
    ls -a && \
    pnpm run build

RUN rm -fr node_modules

FROM frankenphp_transition AS frankenphp_prod

ENV APP_ENV=prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link docker/web/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

COPY --link --exclude=docker/ . ./

COPY --link --from=build_node /app/public/build public/build

ARG APP_VERSION

ENV APP_VERSION=${APP_VERSION}

RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    chmod +x bin/console; sync;
