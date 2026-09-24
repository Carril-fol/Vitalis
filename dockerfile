FROM php:8.2-apache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    git \
    vim \
    unzip \
    libicu-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql intl zip opcache

RUN echo 'date.timezone=America/Argentina/Buenos_Aires' > /usr/local/etc/php/conf.d/tz.ini

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n    FallbackResource /index.php\n</Directory>\n' \
    > /etc/apache2/conf-enabled/symfony.conf

WORKDIR /var/www/html/

ENV APP_ENV=prod

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize \
    && php bin/console importmap:install \
    && php bin/console asset-map:compile \
    && php bin/console cache:warmup \
    && chown -R www-data:www-data var

EXPOSE 80

CMD php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration \
    && chown -R www-data:www-data var \
    && apache2-foreground
