FROM varnish:7.4.3
# https://hub.docker.com/_/varnish
LABEL maintainer="paj@gaiterjones.com"
LABEL service="varnish"
LABEL version="7.4.3"

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
ENV VARNISH_DAEMON_OPTIONS ${VARNISH_DAEMON_OPTIONS:-"-p connect_timeout=300 -p thread_pool_min=5 -p thread_pool_max=2000 -p thread_pool_add_delay=4 -p thread_pools=4 -p pcre2_depth_limit=500 -p pcre2_match_limit=2500 -p thread_pool_stack=72k -p http_resp_size=98304 -p http_resp_hdr_len=65536 -p workspace_backend=131072 -S /etc/varnish/secret"}

ARG VARNISH_CONTAINER_LOG_TYPE
ENV VARNISH_CONTAINER_LOG_TYPE ${VARNISH_LOG_TYPE:-"varnishncsa"}

WORKDIR /etc/varnish

EXPOSE 80
COPY ./start.sh /usr/bin/varnishstart.sh
USER root
RUN chmod +x /usr/bin/varnishstart.sh
ENTRYPOINT "/usr/bin/varnishstart.sh"
