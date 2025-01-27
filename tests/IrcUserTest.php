<?php

declare(strict_types=1);

namespace Tests;

use JesseGreathouse\PhpIrcClient\IrcUser;

final class IrcUserTest extends TestCase
{
    public function testToString(): void
    {
        $user = new IrcUser('My Name');
        self::assertSame('My Name', (string)$user);
    }
}
