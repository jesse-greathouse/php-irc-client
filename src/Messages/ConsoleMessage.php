<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\IrcClientEvent,
    JesseGreathouse\PhpIrcClient\Helpers\Event;

/**
 * Represents a console message received via IRC.
 */
class ConsoleMessage extends IrcMessage
{
    /** @var string The message content. */
    public string $message;

    /** @var string The user who sent the message. */
    public string $user;

    /**
     * Initializes the ConsoleMessage instance.
     *
     * @param string $command The IRC command containing the message.
     */
    public function __construct(string $command)
    {
        parent::__construct($command);

        // Trimmed values for optimized parsing
        $this->user = trim($this->commandSuffix);
        $this->message = trim($this->payload);
    }

    /**
     * Retrieves an array of events triggered by this message.
     *
     * @return Event[] List of events associated with this console message.
     */
    public function getEvents(): array
    {
        return [new Event(IrcClientEvent::CONSOLE, [$this->user, $this->message])];
    }
}
