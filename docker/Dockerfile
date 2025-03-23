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

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy existing application directory
COPY . /var/www/html

# Run Composer and Laravel commands
RUN composer install --no-interaction --no-dev --optimize-autoloader
RUN composer dump-autoload
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache
RUN php artisan vendor:publish --force --tag=livewire:assets
RUN php artisan filament:assets
RUN php artisan filament:cache-components
RUN php artisan migrate --force
RUN php artisan key:generate
RUN php artisan storage:link

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 777 /var/www/html/storage

# Expose port 9000
EXPOSE 9000

# Use entrypoint script
CMD ["/usr/local/bin/entrypoint.sh"]