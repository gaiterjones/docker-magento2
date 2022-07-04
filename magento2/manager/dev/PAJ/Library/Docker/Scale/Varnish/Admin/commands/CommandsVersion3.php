<?php
namespace PAJ\Library\Docker\Scale\Varnish\Admin\commands;

class CommandsVersion3 extends Commands
{
    const NUMBER = 3;
    const URL = '.url';

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
