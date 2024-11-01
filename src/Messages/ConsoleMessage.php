<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;

class ConsoleMessage extends IrcMessage
{
    public string $message;
    public string $user;

    public function __construct(string $command)
    {
        parent::__construct($command);
        $this->user = trim($this->commandsuffix);
        $this->message = trim($this->payload);
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('console', [$this->user, $this->message]),
        ];
    }
}
