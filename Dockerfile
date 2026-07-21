FROM php:8.3-fpm

WORKDIR /var/www/html

RUN sed -i 's/deb.debian.org/mirror-linux.runflare.com/g' /etc/apt/sources.list.d/debian.sources 2>/dev/null || true && \
    sed -i 's/deb.debian.org/mirror-linux.runflare.com/g' /etc/apt/sources.list 2>/dev/null || true && \
    sed -i 's/security.debian.org/mirror-linux.runflare.com/g' /etc/apt/sources.list.d/debian.sources 2>/dev/null || true && \
    sed -i 's/security.debian.org/mirror-linux.runflare.com/g' /etc/apt/sources.list 2>/dev/null || true

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libcurl4-openssl-dev \
    unzip \
    netcat-openbsd \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy composer from official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy your application code
COPY . /var/www/html

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

USER www-data

# Install composer dependencies optimized for production
RUN composer install --no-dev --optimize-autoloader

USER root

# Copy entrypoint script that waits for DB and runs migrations
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 8000
EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
