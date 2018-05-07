FROM gaiterjones/phusion1001-apache2-php7-1
LABEL maintainer="paj@gaiterjones.com"
LABEL description="Magento 2 PHP-APACHE Service"

ENV MAGENTO_VERSION 2.2.4
# sha256sum Magento-CE-2.x.x.tar.gz
ENV MAGENTO_SHA256 bcaf0bb9267f0837ff856416ccf5daad8ed8d0c7d2958cd2d494d551228a68d0

# dependencies
RUN requirements="libpng12-dev libmcrypt-dev libmcrypt4 libcurl3-dev libfreetype6 libjpeg-turbo8 libjpeg-turbo8-dev libpng12-dev libfreetype6-dev libicu-dev libxslt1-dev msmtp nano git" \
    && apt-get update && apt-get install -y $requirements && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install mcrypt \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install zip \
    && docker-php-ext-install intl \
    && docker-php-ext-install xsl \
    && docker-php-ext-install soap \
    && docker-php-ext-install opcache \
    && requirementsToRemove="libpng12-dev libmcrypt-dev libcurl3-dev libpng12-dev libfreetype6-dev libjpeg-turbo8-dev" \
    && apt-get purge --auto-remove -y $requirementsToRemove

# Install XDEBUG extension
# Xdebug replaces PHP's var_dump() function for displaying variables.
# https://xdebug.org/download.php
# confirm => php -m | grep -i xdebug
RUN set -x \
    && cd /tmp \
    && curl -sSL -o xdebug-2.5.5.tgz http://xdebug.org/files/xdebug-2.5.5.tgz \
    && tar -xf xdebug-2.5.5.tgz \
    && cd xdebug-2.5.5 \
    && /usr/local/bin/phpize \
    && ./configure \
    && make \
    && make install \
    && echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/ext-xdebug.ini \
    && rm -rf /tmp/xdebug-2.5.5.tgz xdebug-2.5.5.tgz


# Install memcache extension
#
RUN set -x \
    && apt-get update && apt-get install -y --no-install-recommends unzip libssl-dev libpcre3 libpcre3-dev \
    && cd /tmp \
    && curl -sSL -o php7.zip https://github.com/websupport-sk/pecl-memcache/archive/php7.zip \
    && unzip php7 \
    && cd pecl-memcache-php7 \
    && /usr/local/bin/phpize \
    && ./configure --with-php-config=/usr/local/bin/php-config \
    && make \
    && make install \
    && echo "extension=memcache.so" > /usr/local/etc/php/conf.d/ext-memcache.ini \
    && rm -rf /tmp/pecl-memcache-php7 php7.zip

# get magento 2 and extract
#
RUN set -x \
	&& mkdir /var/www/dev \
	&& mkdir /var/www/dev/magento2 \
	&& rm -rf /var/www/html/* \
	&& mkdir /tmp/magento2 \
	&& cd /tmp/magento2 \
	&& curl https://pe.terjon.es/dropbox/Magento-CE-$MAGENTO_VERSION.tar.gz -o $MAGENTO_VERSION.tar.gz \
	&& echo "$MAGENTO_SHA256 $MAGENTO_VERSION.tar.gz" | sha256sum -c - \
	&& tar xvf $MAGENTO_VERSION.tar.gz \
	&& cd .. \
	&& mv magento2/* magento2/.htaccess /var/www/dev/magento2

# install composer
#
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

# install magerun 2
#
RUN set -x \
	&& cd /tmp \
	&& curl -s -o n98-magerun2.phar https://files.magerun.net/n98-magerun2.phar \
	&& chmod +x ./n98-magerun2.phar \
	&& mv ./n98-magerun2.phar /usr/local/bin/

# prepare Mage source
#
COPY ./healthcheck.php /var/www/dev/magento2/
COPY ./auth.json /var/www/.composer/
RUN set -x \
	&& usermod -u 1000 www-data \
	&& chsh -s /bin/bash www-data

RUN chown -R www-data:www-data /var/www

# composer
RUN chsh -s /bin/bash www-data
RUN su www-data -c "cd /var/www/dev/magento2 && composer install"

# php RedisAdmin
# https://github.com/erikdubbelboer/phpRedisAdmin
#
RUN su www-data -c "cd /var/www/dev && composer create-project -s dev erik-dubbelboer/php-redis-admin /var/www/dev/phpRedisAdmin"

# Magento DEVELOPMENT permissions (this may take a while...)
#
RUN set -x \
    && cd /var/www/dev/magento2 \
    && find var vendor pub/static pub/media app/etc -type f -exec chmod g+w {} \; && find var vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} \; && chmod u+x bin/magento

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

WORKDIR /var/www/dev/magento2
