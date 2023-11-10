<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;
use Jerodev\PhpIrcClient\IrcChannel;
use Jerodev\PhpIrcClient\IrcClient;

class ModeMessage extends IrcMessage
{
    public string $mode;
    public ?string $target = null;
    public string $user;

    public function __construct(string $command)
    {
        parent::__construct($command);
        if ('#' === $this->commandsuffix[0]) {
            $suffixExp = explode(' ', $this->commandsuffix);

            $this->user = $this->payload;
            if (isset($suffixExp[0])) {
                $this->target = $suffixExp[0];
                if (null !== $this->target && '' !== $this->target) {
                    $this->channel = new IrcChannel($this->target);
                }

                if (isset($suffixExp[1])) {
                    $this->mode = $suffixExp[1];

                    if (isset($suffixExp[2])) {
                        $this->user = $suffixExp[2];
                    }
                }
            }
        } else {
            $this->user = $this->commandsuffix;
            $this->mode = $this->payload;
        }
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('mode', [$this->channel, $this->user, $this->mode]),
        ];
    }
}
