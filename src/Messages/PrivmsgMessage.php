<?php

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;

class PrivmsgMessage extends IrcMessage
{
    /** @var string */
    public $user;

    /** @var string */
    public $target;

    /** @var string */
    public $message;

    public function __construct(string $message)
    {
        parent::__construct($message);

        $this->user = preg_replace('/^([^!]+)!.*?$/', '$1', $this->source);
        $this->target = $this->commandsuffix;
        $this->message = $this->payload;
    }

    public function getEvents(): array
    {
        return [
            new Event('message', [$this->user, $this->target, $this->message])
        ];
    }
}
