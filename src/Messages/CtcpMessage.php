<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;
use Jerodev\PhpIrcClient\IrcChannel;
use Jerodev\PhpIrcClient\IrcClient;

class CtcpMessage extends IrcMessage
{
    /**
     * Name of the CTCP action.
     */
    public $action;

    /**
     * Full payload of the message
     */
    public $command;

    /**
     * additional arguments of the action.
     */
    public $args = [];

    public function __construct(string $command)
    {
        parent::__construct($command);
        $this->args = explode(' ', $command);

        // just removes the first element (ctcp).
        array_shift($this->args);

        // Remove the next element and make it the action.
        $this->action = array_shift($this->args);

        $this->command = $this->payload;
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('ctcp', [$this->action, $this->args, $this->command]),
        ];
    }
}
