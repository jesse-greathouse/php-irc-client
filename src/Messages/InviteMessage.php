<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event;
use JesseGreathouse\PhpIrcClient\IrcChannel;
use JesseGreathouse\PhpIrcClient\IrcClient;

class InviteMessage extends IrcMessage
{
    /**
     * Name of the user inviting the client to.
     */
    public string $user;

    public function __construct(string $command)
    {
        parent::__construct($command);
        [$this->user] = explode(' ', $command);
        [$this->user] = explode('!', $this->user);
        $this->user = substr($this->user, 1);
        $this->target = $this->payload;
        $this->channel = new IrcChannel($this->target);
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('invite', [$this->channel, $this->user]),
        ];
    }
}
