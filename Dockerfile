FROM php:8.2-apache

# Install required extensions: mysqli for DB connection and GD for image processing
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite if needed by the application (for friendly URLs)
RUN a2enmod rewrite

# Restart apache to ensure changes are applied
RUN service apache2 restart
