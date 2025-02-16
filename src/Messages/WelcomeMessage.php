<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcClient,
    JesseGreathouse\PhpIrcClient\IrcClientEvent;

class WelcomeMessage extends IrcMessage
{
    // https://www.phpliveregex.com/p/MF9
    public const MASK = '/^\:(.*)\s\S+\s(.*)\s\:(.*)$/is';

    // https://www.phpliveregex.com/p/MF8
    public const HOST_MASK = '/^(.*)\s(.*)\!~(.*)$/is';

    /** @var string The message body of the welcome message */
    public string $message = '';

    /** @var string The server name from the welcome message */
    public string $server = '';

    /** @var string The user name from the welcome message */
    public string $user = '';

    /** @var string The host mask from the welcome message */
    public string $hostMask = '';

    /**
     * Constructor for the WelcomeMessage class.
     * Parses the given message to extract server, user, message, and host mask.
     *
     * @param string $message The raw welcome message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);

        $matches = [];
        preg_match(self::MASK, $message, $matches);

        if (count($matches) > 3) {
            [, $this->server, $this->user, $this->message] = $matches;

            // Attempt to extract host mask if present
            $hostMaskMatch = [];
            preg_match(self::HOST_MASK, $this->message, $hostMaskMatch);
            if (count($hostMaskMatch) > 3) {
                [, $this->message, $this->user, $this->hostMask] = $hostMaskMatch;
            }
        }
    }

    /**
     * Handles the welcome message, typically used to join selected channels.
     * Currently, it does not perform any operations on the client.
     *
     * @param IrcClient $client The IRC client instance
     * @param bool $force Whether to force the handling even if already handled
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }

        // Further actions on the client can be added here
    }

    /**
     * Returns events related to the welcome message.
     *
     * @return array<int, Event> An array of Event objects for the registered event
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::REGISTERED, [$this->server, $this->user, $this->message, $this->hostMask]),
        ];
    }
}
