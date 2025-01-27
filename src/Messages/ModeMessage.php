<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcChannel,
    JesseGreathouse\PhpIrcClient\IrcClient;

class ModeMessage extends IrcMessage
{
    const MODE_CHANGE_ADD = '+';
    const MODE_CHANGE_REMOVE = '-';

    const MODE_CHANGES = [
        self::MODE_CHANGE_ADD,
        self::MODE_CHANGE_REMOVE,
    ];

    public string $mode;
    public ?string $target = null;
    public string $user;

    public function __construct(string $command)
    {
        parent::__construct($command);
        $this->mode = $this->payload;

        if ('#' === $this->commandsuffix[0]) {
            $suffixExp = explode(' ', $this->commandsuffix);

            $this->user = $this->payload;
            if (isset($suffixExp[0])) {
                $this->target = $suffixExp[0];
                if (null !== $this->target && '' !== $this->target && '#' !== $this->target) {
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
        }
    }

    /**
     * This function is always called after the message is parsed.
     * The handle will only be executed once unless forced.
     *
     * @param IrcClient $client A reference to the irc client object
     * @param bool $force Force handling this message even if already handled
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        if ('' !== $this->user && null !== $this->channel) {
            $change = $this->mode[0];
            $mode = $this->mode[1];

            if (in_array($change, self::MODE_CHANGES) && in_array($mode, IrcChannel::MODES)) {
                $channel = $client->getChannel($this->channel->getName());

                switch($change) {
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
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('mode', [$this->channel, $this->user, $this->mode]),
        ];
    }
}
