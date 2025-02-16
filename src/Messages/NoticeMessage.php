<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class NoticeMessage extends IrcMessage
{
    /** @var string The notice message content */
    public string $message;

    /**
     * Constructor for the NoticeMessage class.
     * This constructor combines the command suffix and payload to form the full notice message.
     *
     * @param string $command The raw IRC command
     */
    public function __construct(string $command)
    {
        parent::__construct($command);

        // Trim and combine the command suffix and payload to create the message
        $this->message = trim("{$this->commandSuffix} {$this->payload}");
    }

    /**
     * Get the events triggered by this Notice message.
     *
     * @return array<int, Event> Array of Event objects representing the triggered events
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::NOTICE, [$this->message]),
        ];
    }
}
