FROM php:8.3-fpm

## set your user name
ARG user=ronan
ARG uid=1000

# Executar como root
USER root

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    bash \
    tar

RUN curl -L https://github.com/stripe/stripe-cli/releases/download/v1.25.0/stripe_1.25.0_linux_x86_64.tar.gz -o stripe.tar.gz
RUN tar -xvf stripe.tar.gz
RUN mv stripe /usr/local/bin/stripe
RUN chmod +x /usr/local/bin/stripe
RUN rm stripe.tar.gz

# Verificar se o Stripe foi instalado corretamente
RUN stripe --version

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Install redis
RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

# Set working directory
WORKDIR /var/www

# Copy custom configurations PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

#USER $user
