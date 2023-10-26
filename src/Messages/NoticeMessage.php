<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;

class NoticeMessage extends IrcMessage
{
    public string $message;

    public function __construct(string $command)
    {
        parent::__construct($command);
        $this->message = "{$this->commandsuffix} {$this->payload}";
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
