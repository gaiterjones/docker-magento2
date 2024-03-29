FROM phusion/baseimage:jammy-1.0.0
# https://github.com/phusion/baseimage-docker/releases
# build for gaiterjones/phusion1100-apache2-php7-4:latest
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

RUN apt-get update && apt-get install -y apache2 apache2-utils --no-install-recommends && rm -rf /var/lib/apt/lists/*

RUN rm -rf /var/www/html && mkdir -p /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html && chown -R www-data:www-data /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html

# Apache + PHP requires preforking Apache for best results
RUN a2dismod mpm_event && a2enmod mpm_prefork

RUN mv /etc/apache2/apache2.conf /etc/apache2/apache2.conf.dist && rm /etc/apache2/conf-enabled/* /etc/apache2/sites-enabled/*
COPY apache2.conf /etc/apache2/apache2.conf
# it'd be nice if we could not COPY apache2.conf until the end of the Dockerfile, but its contents are checked by PHP during compilation

ENV PHP_EXTRA_BUILD_DEPS apache2-dev
ENV PHP_EXTRA_CONFIGURE_ARGS --with-apxs2

# https://www.openssl.org/source
# Compile OpenSSL 1.1.1 for PHP 7.4.X
#
ENV OPENSSL_VERSION openssl-1.1.1p
RUN set -xe \
	&& curl -fSL "https://www.openssl.org/source/$OPENSSL_VERSION.tar.gz" -o "$OPENSSL_VERSION.tar.gz" \
	&& ls -al \
	&& tar xzf $OPENSSL_VERSION.tar.gz \
	&& cd $OPENSSL_VERSION \
	&& ./Configure --prefix=/opt/$OPENSSL_VERSION/bin -fPIC -shared linux-x86_64 \
	&& make -j 8  \
	&& make install \
	&& export PKG_CONFIG_PATH=/opt/$OPENSSL_VERSION/bin/lib/pkgconfig

ENV GPG_KEYS 5A52880781F755608BF815FC910DEB46F53EA312 42670A7FE4D0441C8E4632349E4FDC074A4EF02D

# http://php.net/releases/
ENV PHP_VERSION 7.4.30
ENV PHP_FILENAME php-7.4.30.tar.xz
ENV PHP_SHA256 ea72a34f32c67e79ac2da7dfe96177f3c451c3eefae5810ba13312ed398ba70d

RUN set -xe \
	&& buildDeps=" \
		$PHP_EXTRA_BUILD_DEPS \
		libcurl4-openssl-dev \
		libedit-dev \
		libsqlite3-dev \
		libssl-dev \
		libxml2-dev \
		libonig-dev \
		libsodium-dev \
		xz-utils \
	" \
	&& apt-get update && apt-get install -y $buildDeps --no-install-recommends && rm -rf /var/lib/apt/lists/* \
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
		$PHP_EXTRA_CONFIGURE_ARGS \
		--disable-cgi \
# --enable-mysqlnd is included here because it's harder to compile after the fact than extensions are (since it's a plugin for several extensions, not an extension in itself)
		--enable-mysqlnd \
# --enable-mbstring is included here because otherwise there's no way to get pecl to use it properly (see https://github.com/docker-library/php/issues/195)
		--enable-mbstring \
		--with-curl \
		--with-webp \
		--with-libedit \
		--with-openssl \
		--with-zlib \
		--with-pear \
		PKG_CONFIG_PATH=/opt/$OPENSSL_VERSION/bin/lib/pkgconfig \
	&& make -j"$(nproc)" \
	&& make install \
	&& { find /usr/local/bin /usr/local/sbin -type f -executable -exec strip --strip-all '{}' + || true; } \
	&& make clean \
	&& apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $buildDeps

COPY docker-php-ext-* /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-ext-*

# install open ssl cert.pem
RUN set -xe \
	&& curl -fSL https://curl.se/ca/cacert.pem -o /opt/openssl-1.1.1p/bin/ssl/cert.pem \
	&& chmod 644 /opt/openssl-1.1.1p/bin/ssl/cert.pem \
	&& rm -R /opt/openssl-1.1.1p/bin/ssl/certs \
	&& rm -R /opt/openssl-1.1.1p/bin/ssl/private \
	&& ln -s /usr/lib/ssl/certs /opt/openssl-1.1.1p/bin/ssl/certs \
	&& ln -s /usr/lib/ssl/private /opt/openssl-1.1.1p/bin/ssl/private

WORKDIR /var/www/html

EXPOSE 80

CMD ["/sbin/my_init"]

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN mkdir /etc/service/apache2
ADD apache2-foreground /etc/service/apache2/run
RUN chmod +x /etc/service/apache2/run
