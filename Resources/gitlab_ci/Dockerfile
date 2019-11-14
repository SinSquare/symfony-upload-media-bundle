ARG PHP_VERSION=php:7.3-fpm
FROM $PHP_VERSION

# Install PHP extensions and PECL modules.
RUN buildDeps=" \
        libbz2-dev \
        procps \
    " \
    runtimeDeps=" \
        curl \
        git \
        libicu-dev \
        libpq-dev \
        libxml2-dev \
        gnupg2 \
        unzip \
        libzip-dev \
        sqlite3 \
        libgmp-dev \
    " \
    && apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y $buildDeps $runtimeDeps \
    && docker-php-ext-install bcmath bz2 calendar iconv intl mbstring zip \
    && docker-php-ext-configure gmp \
    && docker-php-ext-install gmp \
    && docker-php-ext-install opcache && docker-php-ext-configure opcache --enable-opcache \
    && pecl install -o ds \
    && docker-php-ext-enable ds \
    && apt-get purge -y --auto-remove $buildDeps \
    && rm -r /var/lib/apt/lists/*

RUN pecl install pcov && docker-php-ext-enable pcov

RUN usermod -u 1000 www-data

# Install Composer.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && ln -s $(composer config --global home) /root/composer
ENV PATH $PATH:/root/composer/vendor/bin