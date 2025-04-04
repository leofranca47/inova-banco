FROM php:8.4-apache

ARG user
ARG uid

RUN apt-get update \
    && apt-get install -y git zlib1g-dev libpng-dev \
    &&  apt-get install libcurl4-gnutls-dev libxml2-dev -y\
    && apt-get install libzip-dev -y\
    && docker-php-ext-install pdo pdo_mysql zip gd curl soap mysqli pcntl

# Instalação da extensão intl
RUN docker-php-ext-install intl

# Instalação de dependências
RUN apt-get update -y && \
    apt-get install -y libssh2-1-dev libssh2-1 && \
    rm -rf /var/lib/apt/lists/*

# Install xdebug
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=develop,coverage,debug,profile" >> /usr/local/etc/php/conf.d/xdebug.ini  \
    && echo "xdebug.discover_client_host=1" >> /usr/local/etc/php/conf.d/xdebug.ini  \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini  \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini  \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

COPY .docker/apache/. /etc/apache2/
COPY .docker/apache/php.ini /usr/local/etc/php

RUN a2ensite inova

RUN a2enmod rewrite

RUN service apache2 restart
