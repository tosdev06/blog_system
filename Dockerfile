FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Copy your PHP files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

EXPOSE 80
