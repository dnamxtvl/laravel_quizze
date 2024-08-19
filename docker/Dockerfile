FROM php:8.2-fpm

# Install dependencies
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
    libssl-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install kafka extention
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
        git \
        zlib1g-dev \
        unzip \
        && rm -r /var/lib/apt/lists/*

# PHP Extensions

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions & required packages
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd
RUN docker-php-ext-install sodium
RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer

# Install nodejs
ENV NODE_VERSION 20.10.0
ENV NODE_PACKAGE_URL  https://unofficial-builds.nodejs.org/download/release/v$NODE_VERSION/node-v$NODE_VERSION-linux-x64-musl.tar.gz

RUN apk add libstdc++
WORKDIR /opt
RUN wget $NODE_PACKAGE_URL  \
    && mkdir -p /opt/nodejs  \
    && tar -zxvf *.tar.gz --directory /opt/nodejs --strip-components=1  \
    && rm *.tar.gz  \
    && ln -s /opt/nodejs/bin/node /usr/local/bin/node  \
    && ln -s /opt/nodejs/bin/npm /usr/local/bin/npm

# Set new UID for user www-data
RUN usermod -u 1000 www-data

WORKDIR /var/www/
COPY . .
# Copy the supervisor configuration
COPY docker/supervisor/supervisor.conf /etc/supervisor/supervisor.conf
EXPOSE 9000
CMD ["supervisord", "-c", "/etc/supervisor/supervisor.conf"]

