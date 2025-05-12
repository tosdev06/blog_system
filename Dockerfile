# Use the official PHP image
FROM php:8.2-apache

# Copy project files to Apache's web root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port 80
EXPOSE 80
