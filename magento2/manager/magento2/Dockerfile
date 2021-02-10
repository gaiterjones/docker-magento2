FROM gaiterjones/phusion1100-apache2-php7-4:14
LABEL maintainer="paj@gaiterjones.com"
LABEL service "Magento 2 MANAGER Production Service"

ENV CREATION_DATE 10022021

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

# MYSQLI for PAJ/APPLICATIONS
RUN docker-php-ext-install mysqli

# manager utils / deps
RUN apt-get update && apt-get -yq install \
    jpegoptim \
	pngcrush \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install memcache extension
#
RUN set -x \
    && apt-get update && apt-get install -y --no-install-recommends unzip libssl-dev libpcre3 libpcre3-dev \
    && cd /tmp \
    && curl -sSL -o php7.zip https://github.com/websupport-sk/pecl-memcache/archive/4.0.5.2.zip \
    && unzip php7 \
    && cd pecl-memcache-4.0.5.2 \
    && /usr/local/bin/phpize \
    && ./configure --with-php-config=/usr/local/bin/php-config \
    && make \
    && make install \
    && echo "extension=memcache.so" > /usr/local/etc/php/conf.d/ext-memcache.ini \
    && rm -rf /tmp/pecl-memcache-php7 php7.zip

# prepare user/s
#
RUN set -x \
    && usermod -u 33 www-data \
    && adduser --disabled-password --gecos '' magento \
    && usermod -u 1000 magento \
    && usermod -a -G www-data magento

# configure apache env
#
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
# Ensure old PID due to previous usage killed
RUN rm -f /var/run/apache2/apache2.pid

# configs
#
COPY ./php/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./apache/apache2.conf /etc/apache2/apache2.conf
COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./apache/remoteip.conf /etc/apache2/conf-available/remoteip.conf
COPY ./apache/.htpasswd /etc/apache2/.htpasswd
COPY ./phpRedisAdmin_config.inc.php /var/www/dev/phpRedisAdmin/includes/config.inc.php

# msmtprc smtp config
#
COPY ./php/msmtprc /usr/local/etc/msmtprc
ARG APPDOMAIN
ARG SMTP
RUN set -x \
	&& sed -i "s/XMAILHOSTX/$SMTP/g" /usr/local/etc/msmtprc \
	&& sed -i "s/XMAILDOMAINX/$APPDOMAIN/g" /usr/local/etc/msmtprc \
	&& cat /usr/local/etc/msmtprc

# enable mods and sites
#
RUN a2enmod rewrite \
	&& a2enmod expires \
	&& a2enmod headers \
	&& a2enmod remoteip \
	&& a2enconf remoteip \
	&& a2ensite 000-default.conf

# >>> DOCKER IN DOCKER
RUN set -x \
    && cd /tmp \
    && curl -L -o docker-latest.tgz  https://get.docker.com/builds/Linux/x86_64/docker-latest.tgz \
    && gzip -d docker-latest.tgz \
    && tar -xvf docker-latest.tar \
    && mv /tmp/docker/docker /usr/local/bin \
    && rm -rf /tmp/docker docker-latest.tar \
    && addgroup --gid 998 docker \
    && usermod -aG docker www-data \
    && usermod -aG docker magento

# Uncomment for Magepack
#RUN set -x \
#    && cd /tmp \
#    && curl -sL https://deb.nodesource.com/setup_10.x | bash - \
#    && apt-get install -y nodejs gconf-service libasound2 libatk1.0-0 libatk-bridge2.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget \
#    && npm install -g magepack  --unsafe-perm=true --allow-root \
#    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# log rotate for Magento2
COPY ./logrotate /etc/logrotate.d/magento2

# https://devdocs.magento.com/guides/v2.3/config-guide/cli/config-cli.html#config-install-cli-first
RUN export PATH=$PATH:/var/www/dev/magento2/bin

# cleanup
#
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# cron job
#
ADD crontab /etc/cron.d/magento2-cron
RUN chmod 0644 /etc/cron.d/magento2-cron
RUN crontab -u magento /etc/cron.d/magento2-cron

WORKDIR /var/www/dev/magento2

# Manager STARTUP script
COPY start.sh /usr/local/bin/startmanager.sh
RUN chmod +x /usr/local/bin/startmanager.sh
CMD ["/usr/local/bin/startmanager.sh"]
