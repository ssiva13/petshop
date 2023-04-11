FROM php:8.2-fpm

# Add NodeSource Node.js Binary Distributions
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -

# Install dependencies
RUN apt-get update && apt-get install -y \
        nginx \
        git \
        unzip \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        curl \
        nodejs \
        build-essential \
        wget \
        vim \
        libzip-dev \
        libpq-dev

# Install PHP extensions
RUN docker-php-ext-install \
            pdo \
            pdo_mysql \
            mysqli \
            mbstring \
            exif \
            pcntl \
            bcmath \
            gd \
            zip \
            opcache \
        && pecl install xdebug \
        && docker-php-ext-enable xdebug

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy Nginx config file
COPY ./docker/nginx.conf /etc/nginx/sites-enabled/default

# Set working directory
WORKDIR /var/www

# Copy source code
COPY . .

# Install composer dependencies
RUN composer require ssiva/laravel-notify:dev-main --no-interaction --prefer-dist
RUN composer require ssiva/laravel-stripe:dev-main --no-interaction --prefer-dist
RUN composer require ssiva/currency-exchange:dev-main  --no-interaction --prefer-dist
RUN composer install --no-interaction --prefer-dist

# Install npm dependencies
RUN npm install

# Build npm dependencies
RUN npm run build

# Expose port 80
EXPOSE 80

# Start Nginx and PHP-FPM
CMD service nginx start && php-fpm
