<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Options;

class ConnectionOptions
{
    public function __construct(public int $floodProtectionDelay = 0)
    {
    }
}
