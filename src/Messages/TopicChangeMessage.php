<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcChannel,
    JesseGreathouse\PhpIrcClient\IrcClient,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class TopicChangeMessage extends IrcMessage
{
    /** @var string The topic being set for the channel */
    public string $topic;

    /**
     * Constructor for the TopicChangeMessage class.
     * Parses the provided message to extract channel and topic details.
     *
     * @param string $message The raw TOPIC message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
        $this->channel = new IrcChannel(strstr($this->commandSuffix ?? '', '#'));
        $this->topic = trim($this->payload);
    }

    /**
     * Handles the topic change for the referenced channel.
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

        // Change the topic for the given channel
        $client->getChannel($this->channel->getName())->setTopic($this->topic);
    }

    /**
     * Returns the events associated with this TOPIC change.
     *
     * @return array<int, Event> An array of Event objects representing the topic change event
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::TOPIC, [$this->channel, $this->topic]),
        ];
    }
}
