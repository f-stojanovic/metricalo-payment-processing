FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    wget \
    && apt-get install -y \
    librabbitmq-dev \
    libssh-dev \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install \
    bcmath \
    sockets \
    pdo pdo_mysql pdo_sqlite \
    && pecl install amqp \
    && docker-php-ext-enable amqp

# Installing Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && composer --version

RUN apt-get -y install cron default-mysql-client
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR /app

# Copy application files into the container
COPY . /app

RUN composer install

# Expose port 9000 for the PHP-FPM server
EXPOSE 9000

# The startup command to run PHP-FPM
CMD ["/usr/local/bin/symfony", "server:start", "--allow-http", "--port=9000"]
