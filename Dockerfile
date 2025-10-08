# =====================
# Base PHP + Apache image
# =====================
FROM php:8.1-apache

# =====================
# Install system dependencies
# =====================
RUN apt-get update && apt-get install -y \
    libssl-dev \
    libzip-dev \
    unzip \
    git \
    wget \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# =====================
# Install PHP extensions
# =====================
RUN docker-php-ext-install pdo_mysql \
    && pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis

# =====================
# Enable Apache modules (rewrite)
# =====================
RUN a2enmod rewrite

# =====================
# Set working directory
# =====================
WORKDIR /var/www/html

# =====================
# Copy project files
# =====================
COPY . /var/www/html

# =====================
# Install Composer dependencies
# =====================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# =====================
# Expose port 80
# =====================
EXPOSE 80

# =====================
# Start Apache
# =====================
CMD ["apache2-foreground"]
