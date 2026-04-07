FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    build-base \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    wget \
    gnupg \
    openssh-client \
    mysql-client \
    postgresql-client

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    bcmath \
    ctype \
    curl \
    fileinfo \
    gd \
    json \
    mbstring \
    openssl \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    tokenizer \
    xml \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js for frontend assets
RUN apk add --no-cache nodejs npm

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Install Node dependencies
RUN npm install

# Build frontend assets
RUN npm run build

# Create necessary directories
RUN mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/logs && \
    chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8000

# Run Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0"]
