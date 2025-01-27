<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use
    JesseGreathouse\PhpIrcClient\Exceptions\ParseChannelNameException,
    JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcChannel,
    JesseGreathouse\PhpIrcClient\IrcClient;

use \Exception;

class JoinMessage extends IrcMessage
{
    public string $user = '';
    public string $channelName = '';

    public function __construct(string $message)
    {
        parent::__construct($message);
        $this->channelName = $this->payload;

        if (null !== $this->channelName && '' !== $this->channelName && '#' !== $this->channelName) {
            $this->channel = new IrcChannel($this->channelName);
        } else {
            throw new ParseChannelNameException(self::class . " cannot parse channel name from: $message");
        }

        $source = (!$this->source) ? '' : $this->source;
        $user = strstr($source, '!', true);
        if (false !== $user) $this->user = $user;
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
                ->addUser($this->user);
        }
    }

    /**
     * @return array<int, Event>
     */
    public function getEvents(): array
    {
        return [
            new Event('joinInfo', [$this->user, $this->channelName]),
        ];
    }
}
