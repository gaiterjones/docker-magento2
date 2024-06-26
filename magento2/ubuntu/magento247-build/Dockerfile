FROM gaiterjones/phusion2204-apache2-php8-3:6
LABEL maintainer="paj@gaiterjones.com"
LABEL description="Magento 2 PHP-APACHE Service"

ENV MAGENTO_VERSION 2.4.7

# dependencies
RUN requirements="libsodium-dev libonig-dev libzip-dev libpng-dev libcurl3-dev zlib1g-dev libpng-dev libjpeg-turbo8 libjpeg-turbo8-dev libfreetype6 libfreetype6-dev libicu-dev libxslt1-dev msmtp nano git" \
    && apt-get update && apt-get install -y $requirements && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd \
      --enable-gd \
      --with-jpeg \
      --with-freetype \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-install -j$(nproc) mbstring \
    && docker-php-ext-install -j$(nproc) zip \
    && docker-php-ext-install -j$(nproc) intl \
    && docker-php-ext-install -j$(nproc) xsl \
    && docker-php-ext-install -j$(nproc) soap \
    && docker-php-ext-install -j$(nproc) opcache \
    && docker-php-ext-install -j$(nproc) sockets \
    && docker-php-ext-install -j$(nproc) sodium \
    && requirementsToRemove="libcurl3-dev libfreetype6-dev libpng-dev libjpeg-turbo8-dev" \
    && apt-get purge --auto-remove -y $requirementsToRemove

# Install XDEBUG extension
# Xdebug replaces PHP's var_dump() function for displaying variables.
# https://xdebug.org/download.php
# confirm => php -m | grep -i xdebug
RUN set -x \
   && pecl install xdebug \
   && docker-php-ext-enable xdebug

# Install memcache extension for PHP8
#
RUN set -x \
    && apt-get update && apt-get install -y --no-install-recommends unzip libssl-dev libpcre3 libpcre3-dev \
    && cd /tmp \
    && curl -sSL -o php8.zip https://github.com/websupport-sk/pecl-memcache/archive/refs/tags/8.2.zip \
    && unzip php8.zip \
    && cd pecl-memcache-8.2 \
    && /usr/local/bin/phpize \
    && ./configure --with-php-config=/usr/local/bin/php-config \
    && make \
    && make install \
    && echo "extension=memcache.so" > /usr/local/etc/php/conf.d/ext-memcache.ini \
    && rm -rf /tmp/pecl-memcache-8.2 php8.zip

# install composer v2.5.7
#
RUN set -x \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.5.7

# prepare Mage source
#
# add Magento 2 file owner
#
# ensure www-data is group 33
# add new file owner group 1000
# file owner member of www-data
#

COPY ./auth.json /var/www/.composer/
# Magento repo auth config
#
ARG MAGENTO_REPO_USERNAME
ARG MAGENTO_REPO_PASSWORD
RUN set -x \
	&& sed -i "s/XUSERNAMEX/$MAGENTO_REPO_USERNAME/g" /var/www/.composer/auth.json \
	&& sed -i "s/XPASSWORDX/$MAGENTO_REPO_PASSWORD/g" /var/www/.composer/auth.json \
	&& cat /var/www/.composer/auth.json
ENV COMPOSER_HOME=/var/www/.composer
RUN set -x \
    && mkdir /var/www/dev \
    && rm -rf /var/www/html/* \
    && mkdir /var/www/dev/magento2 \
    && usermod -u 33 www-data \
    && adduser --disabled-password --gecos '' magento \
    && usermod -u 1000 magento \
    && usermod -a -G www-data magento \
    && chown -R magento:www-data /var/www

# composer create new magento2 project
#
RUN chsh -s /bin/bash magento \
    && su magento -c "cd /var/www/dev/magento2 && composer create-project --repository-url=https://repo.magento.com/ magento/project-community-edition=$MAGENTO_VERSION /var/www/dev/magento2 && mkdir /var/www/dev/magento2/composer_home"
COPY ./healthcheck.php /var/www/dev/magento2/

# reset permissions
#
RUN chown -R magento:www-data /var/www/dev/magento2/

# install magerun 2
#
RUN set -x \
	&& cd /tmp \
	&& curl -s -o n98-magerun2.phar https://files.magerun.net/n98-magerun2.phar \
	&& chmod +x ./n98-magerun2.phar \
	&& mv ./n98-magerun2.phar /usr/local/bin/

# php RedisAdmin
# https://github.com/erikdubbelboer/phpRedisAdmin
#
RUN su magento -c "cd /var/www/dev && composer create-project -s dev erik-dubbelboer/php-redis-admin /var/www/dev/phpRedisAdmin"

# Magento DEVELOPMENT permissions (this may take a while...)
#
RUN set -x \
    && cd /var/www/dev/magento2 \
    && rm -rf ./generated/metadata/* ./generated/code/* ./pub/static/* ./var/cache/* ./var/page_cache/* ./var/view_preprocessed/* ./var/log/* \
    && find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} \; && find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} \; && chmod u+x bin/magento

# scripts
#
COPY ./bin/install-magento /usr/local/bin/install-magento
RUN chmod +x /usr/local/bin/install-magento
COPY ./bin/install-sampledata /usr/local/bin/install-sampledata
RUN chmod +x /usr/local/bin/install-sampledata

# configure apache env
#
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2

# cleanup
#
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
COPY ./auth.json /var/www/.composer/

WORKDIR /var/www/dev/magento2
