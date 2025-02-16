<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Options;

class ConnectionOptions
{
    /** The amount of time in milliseconds to wait between sending messages to the IRC server. */
    public int $floodProtectionDelay = 0;

    /**
     * @param int $floodProtectionDelay The amount of time in milliseconds to wait between sending messages to the IRC server.
     */
    public function __construct(int $floodProtectionDelay = 0)
    {
        $this->floodProtectionDelay = $floodProtectionDelay;
    }
}
