<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcClient,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class VersionMessage extends IrcMessage
{
    /**
     * Constructor for the VersionMessage class.
     * Initializes the message object, calling the parent constructor.
     *
     * @param string $message The raw VERSION message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Handles the VERSION message by responding with the client's version.
     * This method is only executed once unless forced.
     *
     * @param IrcClient $client A reference to the IRC client object
     * @param bool $force Force handling this message even if already handled
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        // Send the VERSION response with the client's version
        $client->send("VERSION " . $client->getVersion());
    }

    /**
     * Returns the events associated with this VERSION request.
     *
     * @return array<int, Event> An array of Event objects representing the VERSION event
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::VERSION, []),
        ];
    }
}
