FROM php:8.2-apache

# Расширения PHP, без которых Laravel не заведётся
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip bcmath gd

# Говорим Apache, что точка входа Laravel — папка public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && a2enmod rewrite

# Наша папка public/icons не должна проигрывать встроенному Alias /icons/
RUN sed -i 's|^Alias /icons/|#Alias /icons/|' /etc/apache2/mods-enabled/alias.conf

# Composer приезжает из официального образа — одна строка вместо танцев с установкой
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Трюк с кэшем: сначала копируем ТОЛЬКО манифесты зависимостей
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader

# Потом — весь код проекта
COPY . .

# Секреты и собранный фронтенд — прямо в образ, чтобы переживать пересборки
COPY .env ./
COPY public/build ./public/build/

RUN composer dump-autoload --optimize

# Laravel пишет логи и кэш — даём права пользователю Apache
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
