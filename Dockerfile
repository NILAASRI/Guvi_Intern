# Use official PHP Apache image
FROM php:8.2-apache

# Enable SSL, mysqli, redis, and mongodb extensions
RUN apt-get update && apt-get install -y \
    libssl-dev pkg-config git unzip && \
    docker-php-ext-install mysqli && \
    pecl install redis mongodb && \
    docker-php-ext-enable redis mongodb mysqli

# Copy app code to container
COPY . /var/www/html/

# Enable Apache rewrite & SSL
RUN a2enmod rewrite ssl

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose the port Render expects
EXPOSE 10000

# Start Apache
CMD ["apache2-foreground"]
