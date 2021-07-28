FROM php:7.4-fpm-alpine

RUN apk update && apk add --no-cache git bash mysql php7-pdo_mysql \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
EXPOSE 9000

CMD ["php-fpm"]
