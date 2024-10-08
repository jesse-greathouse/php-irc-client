<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient;

use Generator;
use Jerodev\PhpIrcClient\Messages\DccMessage;
use Jerodev\PhpIrcClient\Messages\IrcMessage;
use Jerodev\PhpIrcClient\Messages\JoinMessage;
use Jerodev\PhpIrcClient\Messages\InviteMessage;
use Jerodev\PhpIrcClient\Messages\KickMessage;
use Jerodev\PhpIrcClient\Messages\MOTDMessage;
use Jerodev\PhpIrcClient\Messages\ModeMessage;
use Jerodev\PhpIrcClient\Messages\NameReplyMessage;
use Jerodev\PhpIrcClient\Messages\NoticeMessage;
use Jerodev\PhpIrcClient\Messages\PingMessage;
use Jerodev\PhpIrcClient\Messages\PrivmsgMessage;
use Jerodev\PhpIrcClient\Messages\TopicChangeMessage;
use Jerodev\PhpIrcClient\Messages\WelcomeMessage;

class IrcMessageParser
{
    /**
     * Parse one or more IRC messages.
     *
     * @param string $message A string received from the IRC server
     * @return Generator|IrcMessage[]
     */
    public function parse(string $message)
    {
        foreach (explode("\n", $message) as $msg) {
            if ('' === trim($msg)) {
                continue;
            }

            yield $this->parseSingle($msg);
        }
    }

    /**
     * Parse a single message to a corresponding object.
     */
    private function parseSingle(string $message): IrcMessage
    {
        switch ($this->getCommand($message)) {
            case 'JOIN':
                return new JoinMessage($message);
            case 'KICK':
                return new KickMessage($message);
            case 'PING':
                return new PingMessage($message);
            case 'PRIVMSG':
                return new PrivmsgMessage($message);
            case IrcCommand::RPL_WELCOME:
                return new WelcomeMessage($message);
            case 'TOPIC':
            case IrcCommand::RPL_TOPIC:
                return new TopicChangeMessage($message);
            case IrcCommand::RPL_NAMREPLY:
                return new NameReplyMessage($message);
            case IrcCommand::RPL_MOTD:
                return new MOTDMessage($message);
            case 'MODE':
                return new ModeMessage($message);
            case 'NOTICE':
                return new NoticeMessage($message);
            case 'INVITE':
                return new InviteMessage($message);
            case 'DCC':
                return new DccMessage($message);
            default:
                return new IrcMessage($message);
        }
    }

    /**
     * Get the COMMAND part of an IRC message.
     */
    private function getCommand(string $message): bool | string
    {

        if ($message[0] === ':') {
            $s = strstr($message, ' ');
            if (false !== $s) {
                $message = trim($s);
            }
        }

        return strstr($message, ' ', true);
    }
}
