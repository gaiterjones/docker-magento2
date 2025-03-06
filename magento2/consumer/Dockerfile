# Stage 1: Build PHP extensions and dependencies
FROM php:8.3-cli-alpine AS builder

# Install build dependencies
RUN apk --update --no-cache add \
    linux-headers \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    libxslt-dev \
    libzip-dev \
    g++ \
    curl-dev \
    oniguruma-dev \
    libpng-dev \
    libxml2-dev \
    zlib-dev

# Configure and build PHP extensions
RUN docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/ \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        pdo_mysql \
        soap \
        sockets \
        xsl \
        zip

# Stage 2: Create the final image with minimal footprint
FROM php:8.3-cli-alpine
LABEL maintainer paj@gaiterjones.com

# Copy compiled extensions from builder stage
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Install runtime dependencies only
# Note: shadow is needed for user/group operations
RUN apk --update --no-cache add \
    linux-headers \
    curl \
    bash \
    shadow \
    msmtp \
    # Runtime libraries needed by extensions
    freetype \
    libjpeg-turbo \
    icu \
	jq \
    libxslt \
    libzip \
    libpng \
    libxml2

# Configure users and groups
RUN groupmod -g 33 www-data \
    && adduser -D -g "" magento \
    && usermod -u 1000 magento \
    && usermod -a -G www-data magento

# Copy configuration files
COPY ./conf/php.ini /usr/local/etc/php/php.ini
COPY ./conf/aliases.sh /etc/profile.d/aliases.sh
COPY start_consumer.sh /usr/local/start_consumer.sh

# Configure email
COPY ./conf/msmtprc /etc/msmtprc
ARG APPDOMAIN
ARG SMTP
RUN set -x \
    && sed -i "s/XMAILHOSTX/$SMTP/g" /etc/msmtprc \
    && sed -i "s/XMAILDOMAINX/$APPDOMAIN/g" /etc/msmtprc \
    && chmod 0644 /etc/msmtprc \
    && chown paj /etc/msmtprc \
    && touch /var/log/msmtp.log \
    && chmod 777 /var/log/msmtp.log

# Switch to non-root user
USER magento
WORKDIR /var/www/dev/magento2
ENTRYPOINT [ "sh", "-l" ]