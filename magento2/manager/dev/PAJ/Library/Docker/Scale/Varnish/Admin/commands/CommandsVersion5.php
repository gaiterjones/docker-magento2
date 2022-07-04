<?php
namespace PAJ\Library\Docker\Scale\Varnish\Admin\commands;

class CommandsVersion5 extends Commands
{
    const NUMBER = 5;
    const URL = ' req.url ~';

    public function getPurgeUrlCommand()
    {
        $command = self::BAN . self::URL;
        return $command;
    }

    /**
     * @return string
     */
    public function getVersionNumber()
    {
        return self::NUMBER;
    }
}
