<?php
namespace PAJ\Library\Docker\Scale\Varnish\Admin\commands;

abstract class Commands
{
    const DEFAULT_VERSION = '3';

    const QUIT = 'quit';
    const START = 'start';
    const STATUS = 'status';
    const STOP = 'stop';
    const BAN = 'ban';
    const AUTH = 'auth';

    /**
     * @return string
     */
    public function getPurgeCommand()
    {
        return self::BAN;
    }

    /**
     * @return string
     */
    public function getQuit()
    {
        return self::QUIT;
    }

    /**
     * @return string
     */
    abstract public function getPurgeUrlCommand();

    /**
     * @return string
     */
    abstract public function getVersionNumber();

    /**
     * @return string
     */
    public function getStart()
    {
        return self::START;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return self::STATUS;
    }

    /**
     * @return string
     */
    public function getStop()
    {
        return self::STOP;
    }

    /**
     * @return string
     */
    public function getAuth()
    {
        return self::AUTH;
    }
}
