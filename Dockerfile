FROM php:8.4-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    curl \
    zip \
    unzip \
    git \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql gd \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
