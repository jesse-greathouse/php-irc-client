<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcClient,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class QuitMessage extends IrcMessage
{
    /** @var string The reason provided for the quit message */
    public string $reason = '';

    /** @var string The user who is quitting */
    public string $user = '';

    /**
     * Constructor for the QuitMessage class.
     * Parses the provided message to extract user and reason for quitting.
     *
     * @param string $message The raw QUIT message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);

        [$this->user] = explode(' ', $message);
        [$this->user] = explode('!', $this->user);
        $this->user = substr($this->user, 1);

        $this->reason = $this->payload;
    }

    /**
     * Handles the quit message by removing the user from all channels.
     * The handle will only be executed once unless forced.
     *
     * @param IrcClient $client A reference to the IRC client object
     * @param bool $force Force handling this message even if already handled
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        foreach ($client->getChannels() as $channel) {
            if ('' !== $this->user) {
                $channel->removeUser($this->user);
            }
        }
    }

    /**
     * Returns the events associated with this QUIT message.
     *
     * @return array<int, Event> An array of Event objects representing the QUIT event
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::QUIT, [$this->user, $this->reason]),
        ];
    }
}
