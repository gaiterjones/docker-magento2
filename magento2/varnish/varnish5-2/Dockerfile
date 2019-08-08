FROM alpine:3.7

LABEL maintainer="paj@gaiterjones.com"
LABEL service="varnish"
LABEL version="5.2"

ARG ENV_TYPE
ENV ENV_TYPE ${ENV_TYPE:-development}

ARG VARNISH_STORAGE
ENV VARNISH_STORAGE ${VARNISH_STORAGE:-malloc,1G}

ARG VARNISH_TTL
ENV VARNISH_TTL ${VARNISH_TTL:-120}

ARG VARNISH_VCL_CONF
ENV VARNISH_VCL_CONF ${VARNISH_VCL_CONF:-/etc/varnish/default.vcl}

ARG VARNISH_LISTEN_ADDRESS
ENV VARNISH_LISTEN_ADDRESS ${VARNISH_LISTEN_ADDRESS:-0.0.0.0}

ARG VARNISH_LISTEN_PORT
ENV VARNISH_LISTEN_PORT ${VARNISH_LISTEN_PORT:-80}

ARG VARNISH_ADMIN_LISTEN_ADDRESS
ENV VARNISH_ADMIN_LISTEN_ADDRESS ${VARNISH_ADMIN_LISTEN_ADDRESS:-0.0.0.0}

ARG VARNISH_ADMIN_LISTEN_PORT
ENV VARNISH_ADMIN_LISTEN_PORT ${VARNISH_ADMIN_LISTEN_PORT:-6082}

# https://community.magento.com/t5/Magento-2-x-Programming/how-to-fix-Error-503-Backend-fetch-failed/td-p/84061
ARG VARNISH_DAEMON_OPTIONS
ENV VARNISH_DAEMON_OPTIONS ${VARNISH_DAEMON_OPTIONS:-"-p connect_timeout=300 -p thread_pool_min=5 -p thread_pool_max=2000 -p thread_pool_add_delay=4 -p thread_pools=4 -p pcre_match_limit_recursion=500 -p pcre_match_limit=2500 -p thread_pool_stack=72k -p http_resp_size=98304 -p http_resp_hdr_len=65536 -p workspace_backend=131072 -S /etc/varnish/secret"}

ARG VARNISH_CONTAINER_LOG_TYPE
ENV VARNISH_CONTAINER_LOG_TYPE ${VARNISH_LOG_TYPE:-"varnishncsa"}

# ARG VARNISHNCSA_LOG_OPTIONS
# ENV VARNISHNCSA_LOG_OPTIONS ${VARNISHNCSA_LOG_OPTIONS:-'"%t - %{%Y-%m-%d}t - %{%H:%M:%S}t - %{X-Forwarded-For}i - %h - %r - %s - %b - %T - %D - %{Varnish:time_firstbyte}x - %{Varnish:handling}x - %{Referer}i - %{User-agent}i"'}

RUN apk update && \
apk add nano && \
apk add bash && \
apk add dos2unix && \
apk add varnish && \
apk add git && \
apk add build-base && \
apk add automake && \
apk add autoconf && \
apk add libtool && \
apk add python && \
apk add py-docutils && \
apk add varnish && \
apk add varnish-libs && \
apk add varnish-dev && \
apk add varnish-geoip

WORKDIR /opt

RUN git clone https://github.com/varnish/varnish-modules.git

WORKDIR /opt/varnish-modules

RUN chmod +x bootstrap && \
./bootstrap && \
./configure && \
make && \
make install && \
apk del py-docutils && \
apk del python && \
apk del libtool && \
apk del autoconf && \
apk del automake && \
apk del build-base && \
apk del git

WORKDIR /

COPY ./start.sh /usr/bin/varnish-start.sh

RUN dos2unix /usr/bin/varnish-start.sh && \
chmod +x /usr/bin/varnish-start.sh


CMD "/usr/bin/varnish-start.sh"
