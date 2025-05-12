FROM php:8.2-apache

# Install system dependencies and MySQLi
RUN apt-get update && \
    apt-get install -y libzip-dev && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli

# Copy files (exclude unnecessary files with .dockerignore)
COPY . /var/www/html/

# Set permissions (fixes common Apache issues)
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80
