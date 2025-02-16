<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\IrcClientEvent,
    JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcChannel;

/**
 * Represents an INVITE message received via IRC.
 */
class InviteMessage extends IrcMessage
{
    /** @var string Name of the user sending the invite. */
    public string $user;

    /** @var string Target of the invitation. */
    public string $target;

    /** @var IrcChannel The channel to which the user is invited. */
    public IrcChannel $channel;

    /**
     * Initializes the InviteMessage instance.
     *
     * @param string $command The raw IRC INVITE command.
     */
    public function __construct(string $command)
    {
        parent::__construct($command);

        // Extract the username before the '!' character
        $this->user = ltrim(strtok($command, '!'), ':');

        // Assign the target channel from the payload
        $this->target = $this->payload;
        $this->channel = new IrcChannel($this->target);
    }

    /**
     * Retrieves an array of events triggered by this message.
     *
     * @return Event[] List of events associated with this invite message.
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::INVITE, [$this->channel, $this->user])
        ];
    }
}
