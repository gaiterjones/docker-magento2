<?php


namespace PAJ\Library\Docker\Scale\Varnish\Admin;

use \Exception;

class Socket
{
    private $fp;

    private $host;
    private $port;

    public function openSocket($host, $port, $timeout)
    {

        $this->host = $host;
        $this->port = $port;
        $errno = null;
        $errstr = null;

        $this->fp = fsockopen($this->host, $this->port, $errno, $errstr, $timeout);
        if (!is_resource($this->fp)) {
            // error would have been raised already by fsockopen
            throw new Exception(sprintf(
                'Failed to connect to varnishadm on %s:%s; "%s"',
                $this->host,
                $this->port,
                $errstr
            ));
        }
        // set socket options
        stream_set_blocking($this->fp, 1);
        stream_set_timeout($this->fp, $timeout);
    }

    public function read(&$code)
    {
        $code = null;
        $len = null;
        // get bytes until we have either a response code and message length or an end of file
        // code should be on first line, so we should get it in one chunk
        while (!feof($this->fp)) {
            $response = fgets($this->fp, 1024);
            if (!$response) {
                $meta = stream_get_meta_data($this->fp);
                if ($meta['timed_out']) {
                    throw new Exception(sprintf('Timed out reading from socket %s:%s', $this->host, $this->port));
                }
            }
            if (preg_match('/^(\d{3}) (\d+)/', $response, $r)) {
                $code = (int)$r[1];
                $len = (int)$r[2];
                break;
            }
        }
        if (is_null($code)) {
            throw new Exception('Failed to get numeric code in response');
        }
        $response = '';
        while (!feof($this->fp) && strlen($response) < $len) {
            $response .= fgets($this->fp, 1024);
        }

        return $response;
    }


    public function write($data)
    {
        $bytes = fputs($this->fp, $data);
        if ($bytes !== strlen($data)) {
            throw new Exception(sprintf('Failed to write to varnishadm on %s:%s', $this->host, $this->port));
        }

        return true;
    }

    public function close()
    {
        is_resource($this->fp) && fclose($this->fp);
        $this->fp = null;
    }
}
