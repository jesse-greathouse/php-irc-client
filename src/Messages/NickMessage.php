<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class NickMessage extends IrcMessage
{
    /** @var string The old nickname */
    public string $nick;

    /** @var string The new nickname */
    public string $newNick;

    /**
     * Constructor for the NickMessage class.
     * Parses the IRC message to extract the nick and newNick.
     *
     * @param string $message The raw IRC message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);

        // Parse the message into nick and newNick by splitting on spaces
        [$this->nick, , $this->newNick] = explode(' ', $message);

        // Extract the nick without the prefix
        [$this->nick] = explode('!', $this->nick);
        $this->nick = substr($this->nick, 1);

        // Extract the newNick without the prefix
        $this->newNick = substr($this->newNick, 1);
    }

    /**
     * Get the events triggered by this Nick message.
     *
     * @return array<int, Event> Array of Event objects representing the triggered events
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::NICK, [$this->nick, $this->newNick]),
        ];
    }
}
