<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class MotdMessage extends IrcMessage
{
    /**
     * Constructor for the MOTDMessage class.
     *
     * @param string $message The raw IRC message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Get the events triggered by this MOTD message.
     *
     * @return array<int, Event> Array of Event objects representing the triggered events
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::MOTD, [$this->payload]),
        ];
    }
}
