FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
        nginx \
        git \
        unzip \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        curl \
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

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy Nginx config file
COPY ./docker/nginx.conf /etc/nginx/sites-enabled/default

# Set working directory
WORKDIR /var/www

# Copy source code
COPY . .

# Install composer dependencies
RUN composer install --no-interaction --prefer-dist

# Expose port 80
EXPOSE 80

# Start Nginx and PHP-FPM
CMD service nginx start && php-fpm
