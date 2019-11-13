FROM php:7.3-fpm

ARG  WITH_XDEBUG=true

# Install PHP extensions and PECL modules.
RUN buildDeps=" \
        libbz2-dev \
        procps \
    " \
    runtimeDeps=" \
        curl \
        git \
        libpq-dev \
        libxml2-dev \
        gnupg2 \
        unzip \
        libzip-dev \
    " \
    && apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y $buildDeps $runtimeDeps \
    && docker-php-ext-install bcmath bz2 calendar iconv intl mbstring zip \
    && docker-php-ext-install gd \
    && apt-get -y install libgmp-dev \
    && docker-php-ext-configure gmp \
    && docker-php-ext-install gmp \
    && apt-get purge -y --auto-remove $buildDeps \
    && rm -r /var/lib/apt/lists/*
    
RUN if [ "$WITH_XDEBUG" = "true" ] ; then yes | pecl install xdebug && docker-php-ext-enable xdebug; fi 

RUN usermod -u 1000 www-data

# Install Composer.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && ln -s $(composer config --global home) /root/composer
ENV PATH $PATH:/root/composer/vendor/bin