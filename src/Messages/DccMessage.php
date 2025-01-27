<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event;
use JesseGreathouse\PhpIrcClient\IrcChannel;
use JesseGreathouse\PhpIrcClient\IrcClient;

class DccMessage extends IrcMessage
{
    /**
     * Name of the user inviting the client to.
     */
    public $action;
    public $fileName;
    public $ip;
    public $port;
    public $fileSize;

    public function __construct(string $command)
    {
        parent::__construct($command);
        $parts = explode(' ', $command);

        foreach([ 1 => 'action', 2 => 'fileName', 3 => 'ip', 4 => 'port', 5 => 'fileSize'] as $key => $val) {
            if (isset($parts[$key])) {
                $this->{$val} = $parts[$key];
            }
        }
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('dcc', [$this->fileName, $this->ip, $this->port, $this->fileSize]),
        ];
    }
}
