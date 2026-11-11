FROM php:8.2-cli-alpine

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql fileinfo

WORKDIR /app
COPY . /app

EXPOSE 8080

CMD php -S 0.0.0.0:${PORT:-8080} -t public

