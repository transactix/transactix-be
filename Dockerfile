# Use the official FrankenPHP image
FROM dunglas/frankenphp:latest

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application code
COPY . .

# Copy Caddyfile to the correct location
COPY Caddyfile /etc/caddy/Caddyfile

# Set proper permissions first
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache \
    && chmod +x /app/artisan

# Install PHP dependencies (skip scripts initially)
RUN composer install --optimize-autoloader --no-dev --no-scripts

# Run composer scripts manually
RUN composer run-script post-autoload-dump

# Generate application key if not set
RUN php artisan key:generate --force

# Run Laravel optimizations
RUN php artisan optimize

# Expose ports
EXPOSE 80 443

# Start FrankenPHP
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
