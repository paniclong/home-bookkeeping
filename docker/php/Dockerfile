FROM php:7.4-fpm-alpine

RUN apk update

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
EXPOSE 9000

CMD ["php-fpm"]
