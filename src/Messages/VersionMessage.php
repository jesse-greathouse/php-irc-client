<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event;
use JesseGreathouse\PhpIrcClient\IrcClient;

class VersionMessage extends IrcMessage
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Reply the ping message with a pong response.
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        $client->send("VERSION " . $client->getVersion());
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('version', []),
        ];
    }
}
