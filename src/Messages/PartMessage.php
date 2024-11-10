<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event,
    Jerodev\PhpIrcClient\IrcChannel,
    Jerodev\PhpIrcClient\IrcClient;

use \Exception;

class PartMessage extends IrcMessage
{
    // https://www.phpliveregex.com/p/MFw
    const MASK = '/^\:(\S+)\!(\S+@\S+)\sPART\s(\S+)\s?(.*)$/is';

    public string $reason = '';
    public string $user = '';

    public function __construct(string $message)
    {
        parent::__construct($message);

        $matches = [];
        preg_match(self::MASK, $message, $matches, PREG_UNMATCHED_AS_NULL);

        if (0 < count($matches)) {

            [, $user, , $channelName, $reason] = $matches;

            if (null !== $channelName && '' !== $channelName && '#' !== $channelName) {
                $this->channel = new IrcChannel($channelName);
            } else {
                throw new Exception(self::class . " cannot parse channel name from: $message");
            }

            $this->user = (null !== $user) ? $user : '';

            $this->reason = (null !== $reason) ? $reason : '';
        } else {
            throw new Exception(self::class . " cannot parse message: $message");
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
            new Event('part', [$this->user, $this->channel, $this->reason]),
        ];
    }
}
