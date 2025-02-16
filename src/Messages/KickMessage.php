<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcClient,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class KickMessage extends IrcMessage
{

    /** @var string The message received in the kick event. */
    public string $message;

    /** @var string The nickname of the user who kicked the bot. */
    public string $kicker;

    /** @var string The nickname of the user being kicked. */
    public string $user;

    /**
     * Constructor for the KickMessage class.
     *
     * @param string $message The raw IRC message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);

        // Extract kicker information from the message
        $this->kicker = strtok($message, '!');
        $this->kicker = ltrim($this->kicker, '@');

        // Split the command suffix to get target and user details
        $c = explode(' ', $this->commandSuffix ?? '');

        $this->target = $c[0] ?? null;
        $this->user = $c[1] ?? null;

        $this->message = $this->payload;
    }

    /**
     * Handle the kick event, potentially making the bot auto-rejoin a channel.
     *
     * @param IrcClient $client The IRC client instance
     * @param bool $force Whether to force re-handling of the message
     *
     * @return void
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        if (null === $this->user) {
            return;
        }

        // If the bot is kicked, auto-rejoin if it's the bot's nickname
        if ($client->getNickname() === $this->user && $client->shouldAutoRejoin()) {
            $client->join($this->target);
            return;
        }

        // Remove user from the channel if the user is not empty
        if ('' !== $this->user && null !== $this->channel->getName()) {
            $client->getChannel($this->channel->getName())
                ->removeUser($this->user);
        }
    }

    /**
     * Get the events triggered by this message.
     *
     * @return array<int, Event> Array of Event objects representing the triggered events
     */
    public function getEvents(): array
    {
        return [
            new Event(
                IrcClientEvent::KICK,
                [$this->channel, $this->user, $this->kicker, $this->message]
            ),
        ];
    }
}
