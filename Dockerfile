# Use official PHP + Apache base image
FROM php:8.1-apache

# Install necessary system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && pecl install redis mongodb \
    && docker-php-ext-enable redis mongodb

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy your project files into the container
COPY . /var/www/html

# Expose Apache port
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
