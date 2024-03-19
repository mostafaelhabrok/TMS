# php image
FROM php:8.1.6-cli

# Allow Composer to run as superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP extensions (MySQL and Zip)
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli
RUN apt-get update -y && apt-get install -y libmcrypt-dev

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install additional dependencies for Zip extension
RUN apt-get update && apt-get install -y \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install zip

# Set working directory
WORKDIR /app

# Copy application files into container
COPY . /app

# Install PHP dependencies using Composer
RUN composer install

# make .env file
RUN composer run-script 'post-root-package-install'

# Replace database host, database name, and password in .env file
RUN sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/' /app/.env
RUN sed -i 's/DB_DATABASE=laravel/DB_DATABASE=tms/' /app/.env
RUN sed -i 's/DB_PASSWORD=/DB_PASSWORD=root/' /app/.env

# Generate application key
RUN php artisan key:generate

# port 8000
EXPOSE 8000

# Start the Laravel application
CMD php artisan serve --host=0.0.0.0 --port=8000
