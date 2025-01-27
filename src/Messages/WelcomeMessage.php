<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event;
use JesseGreathouse\PhpIrcClient\IrcClient;

class WelcomeMessage extends IrcMessage
{
    // https://www.phpliveregex.com/p/MF9
    const MASK = '/^\:(.*)\s\S+\s(.*)\s\:(.*)$/is';

    // https://www.phpliveregex.com/p/MF8
    const HOST_MASK = '/^(.*)\s(.*)\!~(.*)$/is';

    public string $message = '';
    public string $server = '';
    public string $user = '';
    public string $hostMask = '';

    public function __construct(string $message)
    {
        parent::__construct($message);

        $matches = [];
        preg_match(self::MASK, $message, $matches);

        if (3 < count($matches)) {
            [, $this->server, $this->user, $this->message] = $matches;
            $hostMaskMatch = [];
            preg_match(self::HOST_MASK, $this->message, $hostMaskMatch);
            if (3 < count($hostMaskMatch)) {
                [, $this->message, $this->user, $this->hostMask] = $matches;
            }
        }
    }

    /**
     * On welcome message, join the selected channels.
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('registered', [$this->server, $this->user, $this->message,$this->hostMask]),
        ];
    }
}
