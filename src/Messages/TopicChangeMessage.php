<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event;
use JesseGreathouse\PhpIrcClient\IrcChannel;
use JesseGreathouse\PhpIrcClient\IrcClient;

class TopicChangeMessage extends IrcMessage
{
    public string $topic;

    public function __construct(string $message)
    {
        parent::__construct($message);
        $this->channel = new IrcChannel(strstr($this->commandsuffix ?? '', '#'));
        $this->topic = trim($this->payload);
    }

    /**
     * Change the topic for the referenced channel.
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        $client->getChannel($this->channel->getName())->setTopic($this->topic);
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('topic', [$this->channel, $this->topic]),
        ];
    }
}
