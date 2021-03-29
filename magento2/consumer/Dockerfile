FROM php:7.4-cli-alpine
LABEL maintainer magento@gaiterjones.com
# https://github.com/jetrails/docker-magento-alpine/blob/master/src/php-cli-7.4/Dockerfile

RUN apk --update --no-cache add \
	curl \
	tree \
	vim \
	bash \
	jq

RUN apk --update --no-cache add \
	freetype-dev \
	libjpeg-turbo-dev \
	icu-dev \
	libxslt-dev \
	libzip-dev

RUN apk --update --no-cache --virtual build-deps add \
	g++ \
	shadow \
	curl-dev \
	oniguruma-dev \
	libpng-dev \
	libxml2-dev \
	zlib-dev

RUN docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/

RUN docker-php-ext-install \
	bcmath \
	gd \
	intl \
	pdo_mysql \
	soap \
	sockets \
	xsl \
	zip

# ensure www-data matches production group id
RUN groupmod -g 36 xfs
RUN groupmod -g 33 www-data
# add new file owner group 1000
RUN adduser -D -g "" magento
RUN usermod -u 1000 magento
#RUN usermod -u 33 www-data
# file owner member of www-data
RUN usermod -a -G www-data magento

# msmtprc smtp config
#
RUN apk --update --no-cache add \
	msmtp
COPY ./conf/msmtprc /etc/msmtprc
ARG APPDOMAIN
ARG SMTP
RUN set -x \
	&& sed -i "s/XMAILHOSTX/$SMTP/g" /etc/msmtprc \
	&& sed -i "s/XMAILDOMAINX/$APPDOMAIN/g" /etc/msmtprc \
	&& chmod 0644 /etc/msmtprc \
	&& chown magento /etc/msmtprc \
	&& touch /var/log/msmtp.log \
	&& chmod 777 /var/log/msmtp.log

#RUN cat /etc/group
COPY ./conf/php.ini /usr/local/etc/php/php.ini
COPY ./conf/aliases.sh /etc/profile.d/aliases.sh
COPY start_consumer.sh /usr/local/start_consumer.sh
USER magento
WORKDIR /var/www/dev/magento2
ENTRYPOINT [ "sh", "-l" ]
