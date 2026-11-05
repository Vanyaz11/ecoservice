# Образ для размещения на хостинге с поддержкой Docker (Render, Railway, Amvera и т. п.)
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libzip-dev \
    && docker-php-ext-install pdo_pgsql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Корневой каталог сайта — public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf

COPY . /var/www/html
RUN mkdir -p /var/www/html/uploads/materials /var/www/html/exports \
    && chown -R www-data:www-data /var/www/html/uploads /var/www/html/exports

# Хостинги передают порт в переменной PORT
CMD ["sh", "-c", "sed -i \"s/80/${PORT:-80}/g\" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && apache2-foreground"]
