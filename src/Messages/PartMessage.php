<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;
use Jerodev\PhpIrcClient\IrcChannel;

class PartMessage extends IrcMessage
{
    public $reason;
    public $user;

    public function __construct(string $message)
    {
        parent::__construct($message);
        [$this->user, , $channelName, $this->reason] = explode(' ', $message);
        [$this->user] = explode('!', $this->user);
        $this->user = substr($this->user, 1);
        $this->reason = substr($this->reason, 1);

        if (false !== $channelName && '' !== $channelName && '#' !== $channelName) {
            $this->channel = new IrcChannel($channelName);
        }
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('part', [$this->user, $this->channel, $this->reason]),
        ];
    }
}
