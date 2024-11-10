<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event,
    Jerodev\PhpIrcClient\IrcClient;

class QuitMessage extends IrcMessage
{
    public string $reason = '';
    public string $user = '';

    public function __construct(string $message)
    {
        parent::__construct($message);
        [$this->user] = explode(' ', $message);
        [$this->user] = explode('!', $this->user);
        $this->user = substr($this->user, 1);

        $this->reason = $this->payload;
    }

    /**
     * This function is always called after the message is parsed.
     * The handle will only be executed once unless forced.
     *
     * @param IrcClient $client A reference to the irc client object
     * @param bool $force Force handling this message even if already handled
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        foreach($client->getChannels() as $channel) {
            if ('' !== $this->user) {
                $channel->removeUser($this->user);
            }
        }
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('quit', [$this->user, $this->reason]),
        ];
    }
}
