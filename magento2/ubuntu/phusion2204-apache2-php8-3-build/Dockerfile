FROM phusion/baseimage:jammy-1.0.3
# https://github.com/phusion/baseimage-docker/releases
# VERY JAMMY DUDE
# UBUNTU 22.04

# phpize deps
RUN apt-get update && apt-get install -y \
		autoconf \
		file \
		g++ \
		gcc \
		libc-dev \
		make \
		pkg-config \
		re2c \
		zlib1g-dev \
	--no-install-recommends && rm -r /var/lib/apt/lists/*

# persistent / runtime deps
RUN apt-get update && apt-get install -y \
		ca-certificates \
		curl \
		libedit2 \
		libsqlite3-0 \
		libxml2 \
	--no-install-recommends && rm -r /var/lib/apt/lists/*

ENV PHP_INI_DIR /usr/local/etc/php
RUN mkdir -p $PHP_INI_DIR/conf.d

##<autogenerated>##
RUN apt-get update && apt-get install -y apache2 apache2-utils --no-install-recommends && rm -rf /var/lib/apt/lists/*

RUN rm -rf /var/www/html && mkdir -p /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html && chown -R www-data:www-data /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html

# Apache + PHP requires preforking Apache for best results
RUN a2dismod mpm_event && a2enmod mpm_prefork

RUN mv /etc/apache2/apache2.conf /etc/apache2/apache2.conf.dist && rm /etc/apache2/conf-enabled/* /etc/apache2/sites-enabled/*
COPY apache2.conf /etc/apache2/apache2.conf
# it'd be nice if we could not COPY apache2.conf until the end of the Dockerfile, but its contents are checked by PHP during compilation

##</autogenerated>##

ENV GPG_KEYS 1198C0117593497A5EC5C199286AF1F9897469DC AFD8691FDAEDF03BDF6E460563F15A9B715376CA C28D937575603EB4ABB725861C0779DC5C0A9DE4

# http://php.net/releases/
ENV PHP_VERSION 8.3.6
ENV PHP_FILENAME php-8.3.6.tar.xz
ENV PHP_SHA256 53c8386b2123af97626d3438b3e4058e0c5914cb74b048a6676c57ac647f5eae

RUN set -xe \
	&& buildDeps=" \
		apache2-dev \
		libcurl4-openssl-dev \
		libedit-dev \
		libsqlite3-dev \
		libssl-dev \
		libxml2-dev \
		libonig-dev \
		xz-utils \
	" \
	&& apt-get update && apt-get install -y $buildDeps --no-install-recommends && rm -rf /var/lib/apt/lists/*

RUN set -xe \
	&& curl -fSL "http://php.net/get/$PHP_FILENAME/from/this/mirror" -o "$PHP_FILENAME" \
	&& echo "$PHP_SHA256 *$PHP_FILENAME" | sha256sum -c - \
	&& curl -fSL "http://php.net/get/$PHP_FILENAME.asc/from/this/mirror" -o "$PHP_FILENAME.asc" \
	&& export GNUPGHOME="$(mktemp -d)" \
	&& for key in $GPG_KEYS; do \
		gpg --keyserver hkp://keyserver.ubuntu.com --recv-keys "$key"; \
	done \
	&& gpg --batch --verify "$PHP_FILENAME.asc" "$PHP_FILENAME" \
	&& rm -r "$GNUPGHOME" "$PHP_FILENAME.asc" \
	&& mkdir -p /usr/src/php \
	&& tar -xf "$PHP_FILENAME" -C /usr/src/php --strip-components=1 \
	&& rm "$PHP_FILENAME" \
	&& cd /usr/src/php \
	&& ./configure \
		--with-config-file-path="$PHP_INI_DIR" \
		--with-config-file-scan-dir="$PHP_INI_DIR/conf.d" \
		--with-apxs2 \
		--disable-cgi \
		--disable-shared \
		--with-openssl \
# --enable-mysqlnd is included here because it's harder to compile after the fact than extensions are (since it's a plugin for several extensions, not an extension in itself)
		--enable-mysqlnd \
# --enable-mbstring is included here because otherwise there's no way to get pecl to use it properly (see https://github.com/docker-library/php/issues/195)
		--enable-mbstring \
		--with-curl \
		--with-libedit \
		--with-zlib \
		--with-pear \
	&& make -j"$(nproc)" \
	&& make install \
	&& { find /usr/local/bin /usr/local/sbin -type f -executable -exec strip --strip-all '{}' + || true; } \
	&& make clean \
	&& apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $buildDeps

COPY docker-php-ext-* /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-ext-*

##<autogenerated>##
WORKDIR /var/www/html

EXPOSE 80

CMD ["/sbin/my_init"]

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN mkdir /etc/service/apache2
ADD apache2-foreground /etc/service/apache2/run
RUN chmod +x /etc/service/apache2/run
