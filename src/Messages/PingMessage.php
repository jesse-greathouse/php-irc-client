<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcClient,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class PingMessage extends IrcMessage
{
    /**
     * Constructor for the PingMessage class.
     *
     * @param string $message The raw IRC PING message to parse
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Handles the incoming PING message by responding with a PONG message.
     * This is typically used for keeping the connection alive with the server.
     *
     * @param IrcClient $client The IRC client instance
     * @param bool $force If true, forces handling even if the message was already handled
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        // Respond with a PONG message
        $client->send("PONG :$this->payload");
    }

    /**
     * Returns the events associated with this PING message.
     * This typically includes an event that signals the PING message has been received.
     *
     * @return array<int, Event> An array of Event objects representing the events
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::PING, [$this->payload]),
        ];
    }
}
