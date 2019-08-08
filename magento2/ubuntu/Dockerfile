FROM gaiterjones/magento2:2.3.1
LABEL maintainer "paj@gaiterjones.com"
LABEL service "Magento 2 PHP-APACHE Service"
# ------------------------------------------------------------------

# configs
#
COPY ./php/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./apache/apache2.conf /etc/apache2/apache2.conf
COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./apache/remoteip.conf /etc/apache2/conf-available/remoteip.conf
COPY ./php/msmtprc /etc/msmtprc
COPY ./phpRedisAdmin_config.inc.php /var/www/dev/phpRedisAdmin/includes/config.inc.php

# Magento repo auth config
#
ARG MAGENTO_REPO_USERNAME
ARG MAGENTO_REPO_PASSWORD
RUN set -x \
	&& sed -i "s/XUSERNAMEX/$MAGENTO_REPO_USERNAME/g" /var/www/.composer/auth.json \
	&& sed -i "s/XPASSWORDX/$MAGENTO_REPO_PASSWORD/g" /var/www/.composer/auth.json \
	&& cat /var/www/.composer/auth.json

# msmtprc smtp config
#
ARG APPDOMAIN
ARG SMTP
RUN set -x \
	&& sed -i "s/XMAILHOSTX/$SMTP/g" /etc/msmtprc \
	&& sed -i "s/XMAILDOMAINX/$APPDOMAIN/g" /etc/msmtprc \
	&& cat /etc/msmtprc

# enable mods and sites
#
RUN a2enmod rewrite \
	&& a2enmod expires \
	&& a2enmod headers \
	&& a2enmod remoteip \
	&& a2enconf remoteip \
	&& a2ensite 000-default.conf

# https://devdocs.magento.com/guides/v2.3/config-guide/cli/config-cli.html#config-install-cli-first
RUN export PATH=$PATH:/var/www/dev/magento2/bin

# DISABLE XDEBUG
# https://stackoverflow.com/questions/8754826/how-to-disable-xdebug
RUN cd /usr/local/etc/php/conf.d \
	&& mkdir disabled \
	&& mv docker-php-ext-xdebug.ini disabled

WORKDIR /var/www/dev/magento2

# cron job
#
ADD crontab /etc/cron.d/magento2-cron
RUN chmod 0644 /etc/cron.d/magento2-cron
RUN crontab -u www-data /etc/cron.d/magento2-cron
