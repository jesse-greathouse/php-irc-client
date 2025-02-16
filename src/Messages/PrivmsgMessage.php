<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class PrivmsgMessage extends IrcMessage
{
    /** @var string The message content */
    public string $message;

    /** @var string The user sending the message */
    public string $user;

    /**
     * Constructor for the PrivmsgMessage class.
     * Parses the provided message to extract user, target, and message content.
     *
     * @param string $message The raw PRIVMSG message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);

        $source = $this->source ?: '';
        $user = strstr($source, '!', true) ?: '';

        $this->user = $user;
        $this->target = (string) $this->commandSuffix;
        $this->message = $this->payload;
    }

    /**
     * Returns the events associated with this PRIVMSG message.
     * If the message is directed to a channel, two events are created: one for the channel
     * and one for the target user.
     *
     * @return array<int, Event> An array of Event objects representing the events
     */
    public function getEvents(): array
    {
        if ($this->target[0] === '#') {
            return [
                new Event(IrcClientEvent::MESSAGE, [$this->user, $this->channel, $this->message]),
                new Event(IrcClientEvent::MESSAGE . $this->target, [$this->user, $this->channel, $this->message]),
            ];
        }

        return [
            new Event(IrcClientEvent::PRIVMSG, [$this->user, $this->target, $this->message]),
        ];
    }
}
