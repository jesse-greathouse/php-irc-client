<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Exceptions\ParseChannelNameException,
    JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcChannel,
    JesseGreathouse\PhpIrcClient\IrcClient,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

/**
 * Represents a JOIN message in an IRC session.
 */
class JoinMessage extends IrcMessage
{
    /** @var string The nickname of the user joining the channel. */
    public string $user = '';

    /** @var string The name of the channel being joined. */
    public string $channelName = '';

    /**
     * Constructs a JoinMessage instance and parses the input message.
     *
     * @param string $message The raw IRC JOIN message.
     *
     * @throws ParseChannelNameException If the channel name cannot be parsed.
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
        $this->channelName = $this->payload;

        if ($this->channelName !== '' && $this->channelName !== '#' && $this->channelName !== null) {
            $this->channel = new IrcChannel($this->channelName);
        } else {
            throw new ParseChannelNameException(self::class . " cannot parse channel name from: $message");
        }

        $this->user = strstr($this->source ?? '', '!', true) ?: '';
    }

    /**
     * Handles the JOIN message by updating the IRC client's channel state.
     *
     * @param IrcClient $client The IRC client instance.
     * @param bool $force Whether to force handling if already handled.
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        if ($this->user !== '' && $this->channel !== null) {
            $client->getChannel($this->channel->getName())
                ->addUser($this->user);
        }
    }

    /**
     * Retrieves the list of events triggered by this JOIN message.
     *
     * @return array<int, Event> The list of events associated with this message.
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::JOIN, [$this->user, $this->channelName]),
        ];
    }
}
