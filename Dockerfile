FROM php:8.2-apache

RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Benerin konflik MPM
RUN a2dismod mpm_event mpm_worker || true
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* || true
RUN a2enmod mpm_prefork

# Pake entrypoint kustom
COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
