# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Copy all files to web server root
COPY . /var/www/html/

# Install PHP extensions for MySQL and MongoDB
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
