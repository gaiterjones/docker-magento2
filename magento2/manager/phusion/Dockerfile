FROM phusion/baseimage:0.9.22
LABEL maintainer "paj@gaiterjones.com"
LABEL description "PAJ Scale Manager"

# setup
RUN apt-get update && apt-get -yq install \
    curl \
	php7.0-cli \
	php-memcache \
	php-curl \
	iputils-ping \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# configs
COPY ./php/php.ini /etc/php/7.0/cli/conf.d/php.ini

# >>> DOCKER IN DOCKER
RUN set -x \
    && cd /tmp \
    && curl -L -o docker-latest.tgz  https://get.docker.com/builds/Linux/x86_64/docker-latest.tgz \
    && gzip -d docker-latest.tgz \
    && tar -xvf docker-latest.tar \
    && mv /tmp/docker/docker /usr/local/bin \
    && rm -rf /tmp/docker docker-latest.tar \
    && addgroup --gid 999 docker \
    && usermod -aG docker www-data

WORKDIR /home/dev/PAJ/www/Docker/Scale/Manager

# cron job
ADD crontab /etc/cron.d/scalemanager-cron
RUN chmod 0644 /etc/cron.d/scalemanager-cron
RUN crontab /etc/cron.d/scalemanager-cron

COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh
CMD ["/usr/local/bin/start.sh"]