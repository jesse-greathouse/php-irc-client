<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient\Messages;

use Jerodev\PhpIrcClient\Helpers\Event;
use Jerodev\PhpIrcClient\IrcChannel;

class PartMessage extends IrcMessage
{
    // https://www.phpliveregex.com/p/MFa
    const MASK = '/^\:(\S+)\!(\S+@\S+)\sPART\s(\S+)\s\:(.*)$/is';

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
                $this->channel = '';
            }

            $this->user = (null !== $user) ? $user : '';

            $this->reason = (null !== $reason) ? $reason : '';
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
