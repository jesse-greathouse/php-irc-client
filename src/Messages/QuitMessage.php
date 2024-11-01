<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;

class QuitMessage extends IrcMessage
{
    public string $reason;
    public string $user;

    public function __construct(string $message)
    {
        parent::__construct($message);
        [$this->user] = explode(' ', $message);
        [$this->user] = explode('!', $this->user);
        $this->user = substr($this->user, 1);

        $this->reason = $this->payload;
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
