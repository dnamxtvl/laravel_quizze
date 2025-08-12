FROM php:8.2-fpm

# Install dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    build-essential \
    mariadb-client \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    locales \
    libzip-dev \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libsodium-dev \
    pkg-config \
    libcurl4-openssl-dev \
    libssl-dev \
    supervisor \
    zlib1g-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        zip \
        gd \
        sodium \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer

# Set new UID for www-data
RUN usermod -u 1000 www-data

WORKDIR /var/www

# Copy composer files first to leverage cache
COPY composer.json composer.lock ./

# Install PHP dependencies (without scripts & dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Copy the rest of the application code
COPY . .

RUN composer run-script post-install-cmd || true

# Copy supervisor config
COPY ./docker/supervisord.conf /etc/supervisor/supervisord.conf

EXPOSE 9000

CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
