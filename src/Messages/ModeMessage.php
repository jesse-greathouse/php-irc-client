<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcChannel,
    JesseGreathouse\PhpIrcClient\IrcClient,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class ModeMessage extends IrcMessage
{
    public const MODE_CHANGE_ADD = '+';
    public const MODE_CHANGE_REMOVE = '-';

    public const MODE_CHANGES = [
        self::MODE_CHANGE_ADD,
        self::MODE_CHANGE_REMOVE,
    ];

    /** @var string The mode being changed to. */
    public string $mode;

    /** @var string The nickname of the user or chat room being applied. */
    public ?string $target = null;

    /** @var string The nickname of the user who is changing the mode. */
    public string $user;

    /**
     * Constructor for the ModeMessage class.
     *
     * @param string $command The raw IRC command message
     */
    public function __construct(string $command)
    {
        parent::__construct($command);
        $this->mode = $this->payload;

        // Parse the command suffix to extract the target, user, and mode
        if ($this->commandSuffix[0] === '#') {
            $suffixExp = explode(' ', $this->commandSuffix);

            $this->user = $this->payload;
            $this->target = $suffixExp[0] ?? null;

            if (!empty($this->target) && $this->target !== '#') {
                $this->channel = new IrcChannel($this->target);
            }

            $this->mode = $suffixExp[1] ?? $this->mode;
            $this->user = $suffixExp[2] ?? $this->user;
        } else {
            $this->user = $this->commandSuffix;
        }
    }

    /**
     * Handle the mode change event and apply the mode to the user or channel.
     * This will only be executed once unless forced.
     *
     * @param IrcClient $client The IRC client instance
     * @param bool $force Whether to force re-handling even if already handled
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        // Ensure there is a user and a valid channel
        if (!empty($this->user) && $this->channel !== null) {
            $change = $this->mode[0];
            $mode = $this->mode[1];

            if (in_array($change, self::MODE_CHANGES, true) && in_array($mode, IrcChannel::MODES, true)) {
                $channel = $client->getChannel($this->channel->getName());

                // Handle the mode change by adding or removing the mode for the user
                switch ($change) {
                    case self::MODE_CHANGE_ADD:
                        $channel->addMode($this->user, $mode);
                        break;
                    case self::MODE_CHANGE_REMOVE:
                        $channel->removeMode($this->user, $mode);
                        break;
                }
            }
        }
    }

    /**
     * Get the events triggered by this mode change message.
     *
     * @return array<int, Event> Array of Event objects representing the triggered events
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::MODE, [$this->channel, $this->user, $this->mode]),
        ];
    }
}
