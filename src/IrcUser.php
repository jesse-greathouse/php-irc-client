<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient;

class IrcUser
{
    /** @var string The nickname of the IRC user. */
    public string $nickname;

    /**
     * IrcUser constructor.
     *
     * @param string $nickname The nickname of the IRC user.
     */
    public function __construct(string $nickname)
    {
        $this->nickname = $nickname;
    }

    /**
     * Return the nickname of the IRC user as a string.
     *
     * @return string The nickname of the IRC user.
     */
    public function __toString(): string
    {
        return $this->nickname;
    }
}
