FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

COPY . /var/www/html

WORKDIR /var/www/html

RUN a2enmod rewrite

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

VOLUME ["/var/www/html"]
