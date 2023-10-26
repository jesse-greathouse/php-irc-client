<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;

class JoinMessage extends IrcMessage
{
    public string $user;
    public string $channelName;

    public function __construct(string $message)
    {
        parent::__construct($message);
        $source = (!$this->source) ? '' : $this->source;
        $this->user = strstr($source, '!', true);
        $this->channelName = $this->payload;
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('joinInfo', [$this->user, $this->channelName]),
        ];
    }
}
