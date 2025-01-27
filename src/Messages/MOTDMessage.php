<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event;

class MOTDMessage extends IrcMessage
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('motd', [$this->payload]),
        ];
    }
}
