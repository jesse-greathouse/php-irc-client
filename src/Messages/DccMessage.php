<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;
use Jerodev\PhpIrcClient\IrcChannel;
use Jerodev\PhpIrcClient\IrcClient;

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
        [, $this->action, $this->fileName, $this->ip, $this->port, $this->fileSize] = explode(' ', $command);
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
