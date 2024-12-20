<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;
use Jerodev\PhpIrcClient\IrcChannel;
use Jerodev\PhpIrcClient\IrcClient;

class NameReplyMessage extends IrcMessage
{
    /** @var array<int, string> */
    public array $names = [];

    public function __construct(string $message)
    {
        parent::__construct($message);

        if ($this->commandsuffix) {
            $channel = strstr($this->commandsuffix, '#');

            if (false !== $channel && '' !== $channel && '#' !== $channel) {
                $this->channel = new IrcChannel($channel);
                $this->names = explode(' ', $this->payload);
            }
       }
    }

    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        if (null !== $this->channel && !empty($this->names)) {
            $client->getChannel($this->channel->getName())
                ->addUser($this->names);
        }
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        if (null !== $this->channel && !empty($this->names)) {
            return [
                new Event('names', [$this->channel, $this->names]),
                new Event(sprintf('names%s', $this->channel->getName()), [$this->names]),
            ];
        } else {
            return [];
        }
    }
}
