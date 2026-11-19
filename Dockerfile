FROM php:8.2-apache

RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

CMD ["bash", "-lc", "set -eux; a2dismod mpm_event mpm_worker || true; rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* || true; a2enmod mpm_prefork; sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf; apache2ctl -t; exec apache2-foreground"]
