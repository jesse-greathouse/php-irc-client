<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event;

class NoticeMessage extends IrcMessage
{
    public string $message;

    public function __construct(string $command)
    {
        parent::__construct($command);
        $this->message = trim("{$this->commandsuffix} {$this->payload}");
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('notice', [$this->message]),
        ];
    }
}
