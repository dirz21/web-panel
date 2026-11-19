FROM php:8.2-cli

RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

WORKDIR /var/www/html
COPY . /var/www/html

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:$PORT"]
