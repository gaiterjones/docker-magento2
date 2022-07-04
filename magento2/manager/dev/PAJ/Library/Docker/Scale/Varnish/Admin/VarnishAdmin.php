<?php

/**
 * Varnish admin socket for executing varnishadm CLI commands.
 *
 * @see https://www.varnish-cache.org/docs/4.0/reference/varnish-cli.html
 *
 * @author Jesus Lopez http://jesuslc.com
 **/

namespace PAJ\Library\Docker\Scale\Varnish\Admin;

interface VarnishAdmin
{
    /**
     * Brutal close, doesn't send quit command to varnishadm.
     */
    public function close();

    /**
     * Connect to admin socket.
     *
     * @param int $timeout in seconds, defaults to 5; used for connect and reads
     *
     * @return string the banner, in case you're interested
     */
    public function connect($timeout = 5);

    /**
     * Shortcut to purge function.
     *
     * @see https://www.varnish-cache.org/docs/4.0/users-guide/purging.html
     *
     * @param string $expr is a purge expression in form "<field> <operator> <arg> [&& <field> <oper> <arg>]..."
     *
     * @return string
     */
    public function purge($expr);

    /**
     * Shortcut to purge.url function.
     *
     * @see https://www.varnish-cache.org/docs/4.0/users-guide/purging.html
     *
     * @param string $url is a url to purge
     *
     * @return string
     */
    public function purgeUrl($url);

    /**
     * Graceful close, sends quit command.
     */
    public function quit();

    /**
     * @return bool
     */
    public function start();

    /**
     * Test varnish child status.
     *
     * @return bool whether child is alive
     */
    public function status();

    /**
     * Set authentication secret.
     * Warning: may require a trailing newline if passed to varnishadm from a text file.
     *
     * @param string
     */
    public function setSecret($secret);

    /**
     * @return bool
     */
    public function stop();
}
