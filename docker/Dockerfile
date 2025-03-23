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
    curl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Set permissions for Composer
USER root
RUN mkdir -p /var/www/.composer && chown -R www-data:www-data /var/www/.composer

# Switch to www-data user for Composer operations
USER www-data

# Install dependencies (without autoloader optimization for now)
RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application code
USER root
COPY --chown=www-data:www-data . /var/www/html

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

# Set permissions for all directories
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Switch to www-data user for Laravel operations
USER www-data

# Now generate the optimized autoloader and run other Composer scripts
RUN composer dump-autoload --optimize
RUN cp .env.example .env
RUN sed -i "s/APP_NAME=Laravel/APP_NAME=\"Ganesha Jeans\"/" .env
RUN sed -i "s/DB_CONNECTION=mysql/DB_CONNECTION=pgsql/" .env
RUN sed -i "s/DB_HOST=127.0.0.1/DB_HOST=db/" .env
RUN sed -i "s/DB_PORT=3306/DB_PORT=5432/" .env
RUN sed -i "s/DB_DATABASE=laravel/DB_DATABASE=ganesha-app/" .env
RUN sed -i "s/DB_USERNAME=root/DB_USERNAME=postgres/" .env
RUN sed -i "s/DB_PASSWORD=/DB_PASSWORD=postgres/" .env
RUN php artisan key:generate
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache
RUN php artisan vendor:publish --force --tag=livewire:assets
RUN php artisan filament:assets
RUN php artisan filament:cache-components
RUN php artisan storage:link

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]