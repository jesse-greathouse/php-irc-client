<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event;
use JesseGreathouse\PhpIrcClient\IrcClient;

class KickMessage extends IrcMessage
{
    public $message;
    public $kicker;
    public $user;

    public function __construct(string $message)
    {
        parent::__construct($message);
        [$this->kicker] = explode(' ', $message);
        [$this->kicker] = explode('!', $this->kicker);
        $this->kicker = substr($this->kicker, 1);

        $c = explode(' ', $this->commandsuffix ?? '');

        if (isset($c[0])) {
            $this->target = $c[0];
        }

        if (isset($c[1])) {
            $this->user = $c[1];
        }

        $this->message = $this->payload;
    }

    /**
     * When the bot is kicked form a channel, it might need to auto-rejoin.
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        if (null === $this->user) {
            return;
        }

        if ($client->getNickname() === $this->user && $client->shouldAutoRejoin()) {
            $client->join($this->target);
            return;
        }

        if ('' !== $this->user && null !== $this->channel->getName()) {
            $client->getChannel($this->channel->getName())
                ->removeUser($this->user);
        }
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event(
                'kick',
                [$this->channel, $this->user, $this->kicker, $this->message]
            ),
        ];
    }
}
