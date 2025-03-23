FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Set permissions for Composer
RUN mkdir -p /var/www/.composer && chown -R www-data:www-data /var/www/.composer

# Copy the rest of the application code
COPY --chown=www-data:www-data . /var/www/html

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Switch to www-data user for Laravel operations
USER www-data

# Install dependencies and optimize
RUN composer install --optimize-autoloader \
    && cp .env.example .env \
    && sed -i "s/APP_NAME=Laravel/APP_NAME=\"Ganesha Jeans\"/" .env \
    && sed -i "s/DB_CONNECTION=mysql/DB_CONNECTION=pgsql/" .env \
    && sed -i "s/DB_HOST=127.0.0.1/DB_HOST=db/" .env \
    && sed -i "s/DB_PORT=3306/DB_PORT=5432/" .env \
    && sed -i "s/DB_DATABASE=laravel/DB_DATABASE=ganesha-app/" .env \
    && sed -i "s/DB_USERNAME=root/DB_USERNAME=postgres/" .env \
    && sed -i "s/DB_PASSWORD=/DB_PASSWORD=postgres/" .env \
    && php artisan key:generate \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan vendor:publish --force --tag=livewire:assets \
    && php artisan filament:assets \
    && php artisan filament:cache-components \
    && php artisan storage:link

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]