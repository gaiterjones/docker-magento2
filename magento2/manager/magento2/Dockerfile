FROM gaiterjones/phusion2404-apache2-php8-3:16 AS base
LABEL maintainer="paj@gaiterjones.com"
LABEL service="Magento 2 MANAGER Production Service"

# Use build arguments for customization
ARG SMTP
ARG APPDOMAIN
ENV CREATION_DATE=01032025

# Consolidate dependencies and reduce layers
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt/lists,sharing=locked \
    set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libsodium-dev \
        libonig-dev \
        libzip-dev \
        libpng-dev \
        libcurl3-dev \
        zlib1g-dev \
        libjpeg-turbo8 \
        libjpeg-turbo8-dev \
        libfreetype6 \
        libfreetype6-dev \
        libicu-dev \
        libxslt1-dev \
        nano \
        git \
        jpegoptim \
        pngcrush \
        unzip \
        libssl-dev \
        libpcre3 \
        libpcre3-dev \
        libgnutls28-dev \
        xz-utils \
    ; \
    # PHP Extensions
    docker-php-ext-configure gd \
        --enable-gd \
        --with-jpeg \
        --with-freetype \
    ; \
    docker-php-ext-install -j$(nproc) \
        gd \
        pdo_mysql \
        bcmath \
        mbstring \
        zip \
        intl \
        xsl \
        soap \
        opcache \
        sockets \
        sodium \
        mysqli \
    ; \
    # Cleanup
    apt-get purge -y \
        libcurl3-dev \
        libfreetype6-dev \
        libpng-dev \
        libjpeg-turbo8-dev \
    ; \
    apt-get autoremove -y; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Memcache Extension Installation
RUN set -eux; \
    cd /tmp; \
    curl -sSL -o php8.zip https://github.com/websupport-sk/pecl-memcache/archive/refs/tags/8.2.zip; \
    unzip php8.zip; \
    cd pecl-memcache-8.2; \
    /usr/local/bin/phpize; \
    ./configure --with-php-config=/usr/local/bin/php-config; \
    make; \
    make install; \
    echo "extension=memcache.so" > /usr/local/etc/php/conf.d/ext-memcache.ini; \
    rm -rf /tmp/pecl-memcache-8.2 php8.zip

# Install msmtp
RUN set -eux; \
    cd /tmp; \
    curl -sSL -o msmtp.tar.xz https://marlam.de/msmtp/releases/msmtp-1.8.28.tar.xz; \
    tar -xf msmtp.tar.xz; \
    cd msmtp-1.8.28; \
    ./configure; \
    make; \
    make install; \
    rm -rf /tmp/msmtp-1.8.28 msmtp.tar.xz; \
    touch /var/log/msmtp.log; \
    chmod 0644 /var/log/msmtp.log

# User and Permissions Management
RUN set -eux; \
    usermod -u 33 www-data; \
    deluser --remove-home ubuntu; \
    adduser --disabled-password --gecos '' magento; \
    usermod -u 1000 magento; \
    usermod -a -G www-data magento; \
    mkdir -p /var/www/dev/magento2/bin; \
    chsh -s /bin/bash www-data; \
    chown -R magento:www-data /var/www

# Node.js and Magepack
#RUN set -eux; \
#    curl -sL https://deb.nodesource.com/setup_20.x | bash -; \
#    apt-get -y install nodejs; \
#    npm install -g magepack --unsafe-perm=true --allow-root; \
#    apt-get clean; \
#    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Composer Installation
RUN set -eux; \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.5.7

# Docker CLI Installation
RUN set -eux; \
    cd /tmp; \
    curl -L -o docker-latest.tgz https://get.docker.com/builds/Linux/x86_64/docker-latest.tgz; \
    gzip -d docker-latest.tgz; \
    tar -xvf docker-latest.tar; \
    mv /tmp/docker/docker /usr/local/bin; \
    rm -rf /tmp/docker docker-latest.tar; \
    groupmod -g 994 systemd-journal; \
    addgroup --gid 999 docker; \
    usermod -aG docker www-data; \
    usermod -aG docker magento

# Copy Configuration Files
COPY ./php/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./apache/apache2.conf /etc/apache2/apache2.conf
COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./apache/remoteip.conf /etc/apache2/conf-available/remoteip.conf
COPY ./php/msmtprc /etc/msmtprc
COPY ./apache/.htpasswd /etc/apache2/.htpasswd
COPY ./phpRedisAdmin_config.inc.php /var/www/dev/phpRedisAdmin/includes/config.inc.php
COPY ./logrotate /etc/logrotate.d/magento2
COPY crontab /etc/cron.d/magento2-cron
COPY start.sh /usr/local/bin/startmanager.sh

# Apache and System Configuration
RUN set -eux; \
    # Configure Apache Environment
    rm -f /var/run/apache2.pid /run/apache2.pid; \
    # Apache Modules and Sites
    a2enmod rewrite expires headers remoteip; \
    a2enconf remoteip; \
    a2ensite 000-default.conf; \
    # Crontab Permissions
    chmod 0644 /etc/cron.d/magento2-cron; \
    crontab -u magento /etc/cron.d/magento2-cron; \
    # Startup Script
    chmod +x /usr/local/bin/startmanager.sh

# Environment and Path Configuration
ENV APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    APACHE_LOG_DIR=/var/log/apache2 \
    APACHE_PID_FILE=/var/run/apache2.pid \
    APACHE_RUN_DIR=/var/run/apache2 \
    APACHE_LOCK_DIR=/var/lock/apache2 \
    COMPOSER_HOME=/var/www/.composer \
    PATH=$PATH:/var/www/dev/magento2/bin

WORKDIR /var/www/dev/magento2

# Prepare for runtime
RUN sed -i "s/XMAILHOSTX/$SMTP/g" /etc/msmtprc && \
    sed -i "s/XMAILDOMAINX/$APPDOMAIN/g" /etc/msmtprc

CMD ["/usr/local/bin/startmanager.sh"]