#!/bin/bash
set -e

startVarnish() {
    /usr/sbin/varnishd \
    -F \
    -f $VARNISH_VCL_CONF \
    -a ${VARNISH_LISTEN_ADDRESS}:${VARNISH_LISTEN_PORT} \
    -T ${VARNISH_ADMIN_LISTEN_ADDRESS}:${VARNISH_ADMIN_LISTEN_PORT} \
    -t $VARNISH_TTL \
    -s $VARNISH_STORAGE \
    $VARNISH_DAEMON_OPTIONS
}

startVarnishWithLogging() {
    /usr/sbin/varnishd \
    -f $VARNISH_VCL_CONF \
    -a ${VARNISH_LISTEN_ADDRESS}:${VARNISH_LISTEN_PORT} \
    -T ${VARNISH_ADMIN_LISTEN_ADDRESS}:${VARNISH_ADMIN_LISTEN_PORT} \
    -t $VARNISH_TTL \
    -s $VARNISH_STORAGE \
    $VARNISH_DAEMON_OPTIONS

    /usr/bin/$VARNISH_CONTAINER_LOG_TYPE -F "%t - %{%Y-%m-%d}t - %{%H:%M:%S}t - %{X-Forwarded-For}i - %h - %r - %s - %b - %T - %D - %{Varnish:time_firstbyte}x - %{Varnish:handling}x - %{Referer}i - %{User-agent}i"
}

if [ "$VARNISH_LOGGING_ENABLED" = true ]; then
    echo "Starting varnish with logging..."
    startVarnishWithLogging
else
    echo "Starting varnish without logging..."
    startVarnish
fi
