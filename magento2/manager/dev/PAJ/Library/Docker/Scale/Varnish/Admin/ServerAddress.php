<?php


namespace PAJ\Library\Docker\Scale\Varnish\Admin;

class ServerAddress
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 6082;
    /**
     * Host on which varnishadm is listening.
     *
     * @var string
     */
    private $host;
    /**
     * Port on which varnishadm is listening, usually 6082.
     *
     * @var int port
     */
    private $port;

    /**
     * ServerAddress constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct($host, $port)
    {
        $this->host = $host;
        if (empty($this->host)) {
            $this->host = self::DEFAULT_HOST;
        }
        $this->port = $port;
        if (empty($this->port)) {
            $this->port = self::DEFAULT_PORT;
        }
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
}
