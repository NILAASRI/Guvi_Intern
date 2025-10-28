# Use official PHP + Apache image
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libssl-dev pkg-config git unzip ca-certificates && \
    docker-php-ext-install mysqli && \
    pecl install redis mongodb && \
    docker-php-ext-enable redis mongodb mysqli && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy application code
COPY . /var/www/html/

# Enable Apache modules for clean URLs and SSL
RUN a2enmod rewrite ssl

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose Render’s required port
EXPOSE 10000

# Tell Apache to listen on Render’s port
ENV PORT=10000
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

# ✅ Start Apache
CMD ["apache2-foreground"]

COPY composer.json composer.lock /var/www/html/
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-dev -d /var/www/html

