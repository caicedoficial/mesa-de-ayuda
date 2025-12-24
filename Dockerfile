# Multi-stage Dockerfile for CakePHP 5 Helpdesk System
# Base: Ubuntu 24.04 LTS with Nginx + PHP-FPM 8.3

# ============================================
# Stage 1: Development Environment
# ============================================
FROM ubuntu:24.04 AS development

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive \
    PHP_VERSION=8.3 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    TZ=America/Bogota

# Install system dependencies
RUN apt-get update && apt-get install -y \
    # Nginx web server
    nginx \
    # PHP 8.3 and extensions
    php8.3-fpm \
    php8.3-cli \
    php8.3-pgsql \
    php8.3-mysql \
    php8.3-sqlite3 \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-gd \
    php8.3-intl \
    php8.3-bcmath \
    php8.3-opcache \
    php8.3-redis \
    # System utilities
    curl \
    git \
    unzip \
    supervisor \
    # Timezone data
    tzdata \
    # Clean up
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Create application directory
WORKDIR /var/www/html

# Copy Nginx configuration
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Copy PHP-FPM configuration
COPY docker/php/php.ini /etc/php/8.3/fpm/php.ini
COPY docker/php/php-fpm.conf /etc/php/8.3/fpm/pool.d/www.conf

# Copy Supervisor configuration (manages Nginx + PHP-FPM)
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application files
COPY --chown=www-data:www-data . .

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Create required directories with correct permissions
RUN mkdir -p \
    tmp/cache/models \
    tmp/cache/persistent \
    tmp/cache/views \
    tmp/sessions \
    tmp/tests \
    logs \
    webroot/uploads/attachments \
    webroot/uploads/pqrs \
    webroot/uploads/profile_images \
    && chown -R www-data:www-data tmp logs webroot/uploads \
    && chmod -R 775 tmp logs webroot/uploads

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Start Supervisor (manages Nginx + PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# ============================================
# Stage 2: Production Environment (Optional)
# ============================================
FROM development AS production

# Remove development dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev --prefer-dist \
    && composer clear-cache

# Optimize PHP for production
RUN echo "opcache.enable=1" >> /etc/php/8.3/fpm/php.ini \
    && echo "opcache.memory_consumption=128" >> /etc/php/8.3/fpm/php.ini \
    && echo "opcache.interned_strings_buffer=8" >> /etc/php/8.3/fpm/php.ini \
    && echo "opcache.max_accelerated_files=10000" >> /etc/php/8.3/fpm/php.ini \
    && echo "opcache.validate_timestamps=0" >> /etc/php/8.3/fpm/php.ini

# Remove unnecessary files
RUN rm -rf tests .git .gitignore docker/
