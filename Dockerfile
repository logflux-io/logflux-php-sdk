# LogFlux Agent PHP SDK Docker Build Environment
FROM php:8.1-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    make \
    curl \
    && docker-php-ext-install zip sockets \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /workspace

# Copy Composer files
COPY composer.json composer.lock* ./

# Install dependencies (for better caching)
RUN composer install --no-dev --no-scripts --no-autoloader || true

# Copy source code
COPY . .

# Install all dependencies and generate autoloader
RUN composer install --no-interaction --prefer-dist --dev

# Set environment variables
ENV PHP_IDE_CONFIG="serverName=logflux-php-sdk"

# Default command
CMD ["make", "help"]