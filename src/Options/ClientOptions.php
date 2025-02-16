<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Options;

class ClientOptions
{
    /** Automatically connect to the IRC server when creating the client. */
    public bool $autoConnect = false;

    /** Automatically rejoin a channel when kicked. */
    public bool $autoRejoin = false;

    /** The amount of time in milliseconds to wait between sending messages to the IRC server. */
    public int $floodProtectionDelay = 750;

    /**
     * @param string|null $nickname The nickname used on the IRC server.
     * @param array<int, string> $channels The channels to join on connection.
     */
    public function __construct(
        public ?string $nickname = null,
        public array $channels = []
    ) {
    }

    /**
     * Get the options for the IrcConnection from this collection.
     *
     * @return ConnectionOptions The connection options with flood protection delay set.
     */
    public function connectionOptions(): ConnectionOptions
    {
        $options = new ConnectionOptions();
        $options->floodProtectionDelay = $this->floodProtectionDelay;
        return $options;
    }
}
