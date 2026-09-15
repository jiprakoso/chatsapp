FROM ci4-php82-fpm:8.2

# Install redis extension for PHP
RUN apt-get update && apt-get install -y libssl-dev && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*