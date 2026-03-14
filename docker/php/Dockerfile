FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    mariadb-client \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    zip \
    unzip

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd intl mbstring

WORKDIR /var/www/backend

CMD ["php-fpm", "-F"]