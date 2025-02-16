<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcChannel,
    JesseGreathouse\PhpIrcClient\IrcClient,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class NameReplyMessage extends IrcMessage
{
    /** @var array<int, string> */
    public array $names = [];

    /**
     * Constructor for the NameReplyMessage class.
     *
     * @param string $message The raw IRC message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);

        // Process the command suffix to extract channel and names
        if ($this->commandSuffix) {
            $channel = strstr($this->commandSuffix, '#');

            // If channel is valid, initialize channel object and parse names
            if ($channel && $channel !== '#') {
                $this->channel = new IrcChannel($channel);
                $this->names = explode(' ', $this->payload);
            }
        }
    }

    /**
     * Handle the processing of names for a given channel.
     * This will add users to the channel if they are not already handled.
     *
     * @param IrcClient $client The IRC client instance
     * @param bool $force Whether to force re-handling even if already handled
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        // Return early if already handled and not forced
        if ($this->handled && !$force) {
            return;
        }

        // If a channel and names are available, add the users to the channel
        if ($this->channel !== null && !empty($this->names)) {
            $client->getChannel($this->channel->getName())
                ->addUser($this->names);
        }
    }

    /**
     * Get the events triggered by this name reply message.
     *
     * @return array<int, Event> Array of Event objects representing the triggered events
     */
    public function getEvents(): array
    {
        // Return events if channel and names are valid, else return empty array
        if ($this->channel !== null && !empty($this->names)) {
            return [
                new Event(IrcClientEvent::NAMES, [$this->channel, $this->names]),
                new Event(sprintf('%s%s', IrcClientEvent::NAMES, $this->channel->getName()), [$this->names]),
            ];
        }

        return [];
    }
}
