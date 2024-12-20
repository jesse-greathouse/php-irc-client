<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;
use Jerodev\PhpIrcClient\IrcClient;

class NickMessage extends IrcMessage
{
    public $newNick;
    public $nick;

    public function __construct(string $message)
    {
        parent::__construct($message);
        [$this->nick, , $this->newNick] = explode(' ', $message);
        [$this->nick] = explode('!', $this->nick);
        $this->nick = substr($this->nick, 1);
        $this->newNick = substr($this->newNick, 1);
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('nick', [$this->nick, $this->newNick]
            ),
        ];
    }
}
