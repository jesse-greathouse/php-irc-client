<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Exceptions\ParseChannelNameException,
    JesseGreathouse\PhpIrcClient\Exceptions\ParseMessageException,
    JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcChannel,
    JesseGreathouse\PhpIrcClient\IrcClient,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class PartMessage extends IrcMessage
{
    /** Regular expression to match PART message format
     * https://www.phpliveregex.com/p/MFw
     */
    const MASK = '/^\:(\S+)\!(\S+@\S+)\sPART\s(\S+)\s?(.*)$/is';

    /** @var string The reason for the PART message */
    public string $reason = '';

    /** @var string The user who sent the PART message */
    public string $user = '';

    /**
     * Constructor for the PartMessage class.
     * This constructor parses the provided message using a regular expression to extract user, channel, and reason.
     *
     * @param string $message The raw IRC PART message to parse
     *
     * @throws ParseChannelNameException If the channel name cannot be parsed from the message
     * @throws ParseMessageException If the message does not match the expected format
     */
    public function __construct(string $message)
    {
        parent::__construct($message);

        $matches = [];
        preg_match(self::MASK, $message, $matches, PREG_UNMATCHED_AS_NULL);

        if (count($matches) > 0) {
            [, $user, , $channelName, $reason] = $matches;

            if (null !== $channelName && '' !== $channelName && '#' !== $channelName) {
                $this->channel = new IrcChannel($channelName);
            } else {
                throw new ParseChannelNameException(self::class . " cannot parse channel name from: $message");
            }

            $this->user = $user ?? '';
            $this->reason = $reason ?? '';
        } else {
            throw new ParseMessageException(self::class . " cannot parse message: $message");
        }
    }

    /**
     * Handles the PART message by removing the user from the channel if applicable.
     *
     * @param IrcClient $client The IRC client instance
     * @param bool $force If true, forces handling even if the message was already handled
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        if ('' !== $this->user && null !== $this->channel) {
            $client->getChannel($this->channel->getName())
                ->removeUser($this->user);
        }
    }

    /**
     * Returns the events associated with this PART message.
     * This typically includes an event that signals the user has partied from the channel.
     *
     * @return array<int, Event> An array of Event objects representing the events
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::PART, [$this->user, $this->channel, $this->reason]),
        ];
    }
}
