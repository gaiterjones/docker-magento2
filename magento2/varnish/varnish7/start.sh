#!/bin/bash
set -e

# Trap termination signals
trap 'echo "Stopping Varnish..."; kill -TERM $PID; wait $PID' TERM INT

echo "Starting Varnish with the following configuration:"
echo "VCL File: $VARNISH_VCL_CONF"
echo "Listen Address: ${VARNISH_LISTEN_ADDRESS}:${VARNISH_LISTEN_PORT}"
echo "Admin Address: ${VARNISH_ADMIN_LISTEN_ADDRESS}:${VARNISH_ADMIN_LISTEN_PORT}"
echo "TTL: $VARNISH_TTL"
echo "Storage: $VARNISH_STORAGE"
echo "Daemon Options: $VARNISH_DAEMON_OPTIONS"

startVarnish() {
    /usr/sbin/varnishd \
    -F \
    -f $VARNISH_VCL_CONF \
    -a ${VARNISH_LISTEN_ADDRESS}:${VARNISH_LISTEN_PORT} \
    -T ${VARNISH_ADMIN_LISTEN_ADDRESS}:${VARNISH_ADMIN_LISTEN_PORT} \
    -t $VARNISH_TTL \
    -p feature=+http2 \
    -s $VARNISH_STORAGE \
    $VARNISH_DAEMON_OPTIONS &
    
    PID=$!  # Capture the process ID of varnishd
    wait $PID
}

startVarnishWithLogging() {
    /usr/sbin/varnishd \
    -f $VARNISH_VCL_CONF \
    -a ${VARNISH_LISTEN_ADDRESS}:${VARNISH_LISTEN_PORT} \
    -T ${VARNISH_ADMIN_LISTEN_ADDRESS}:${VARNISH_ADMIN_LISTEN_PORT} \
    -t $VARNISH_TTL \
    -p feature=+http2 \
    -s $VARNISH_STORAGE \
    $VARNISH_DAEMON_OPTIONS &
    
    PID=$!  # Capture the process ID of varnishd

    sleep 2  # Give varnishd some time to initialize before starting logging
    /usr/bin/$VARNISH_CONTAINER_LOG_TYPE \
        -F "%t - %{%Y-%m-%d}t - %{%H:%M:%S}t - %{X-Forwarded-For}i - %h - %r - %s - %b - %T - %D - %{Varnish:time_firstbyte}x - %{Varnish:handling}x - %{Referer}i - %{User-agent}i" &

    wait $PID
}

if [ "$VARNISH_LOGGING_ENABLED" = true ]; then
    echo "Starting varnish with logging..."
    startVarnishWithLogging
else
    echo "Starting varnish without logging..."
    startVarnish
fi
