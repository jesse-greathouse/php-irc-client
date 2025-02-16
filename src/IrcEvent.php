<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient;

final class IrcEvent
{
    /** @var string Version constant */
    public const VERSION = 'VERSION';

    /** @var string CTCP constant */
    public const CTCP = 'CTCP';

    /** @var string DCC constant */
    public const DCC = 'DCC';

    /** @var string Join constant */
    public const JOIN = 'JOIN';

    /** @var string Kick constant */
    public const KICK = 'KICK';

    /** @var string Ping constant */
    public const PING = 'PING';

    /** @var string PRIVMSG constant */
    public const PRIVMSG = 'PRIVMSG';

    /** @var string Message constant */
    public const MESSAGE = 'MESSAGE';

    /** @var string Topic constant */
    public const TOPIC = 'TOPIC';

    /** @var string Mode constant */
    public const MODE = 'MODE';

    /** @var string NICK constant */
    public const NICK = 'NICK';

    /** @var string Notice constant */
    public const NOTICE = 'NOTICE';

    /** @var string INVITE constant */
    public const INVITE = 'INVITE';

    /** @var string QUIT constant */
    public const QUIT = 'QUIT';

    /** @var string PART constant */
    public const PART = 'PART';

    // Numeric reply commands.
    /** @var string WELCOME constant */
    public const WELCOME = '001';

    /** @var string NAMREPLY constant */
    public const NAMREPLY = '353';

    /** @var string MOTD constant */
    public const MOTD = '372';

    /** @var string RPL_TOPIC constant */
    public const RPL_TOPIC = '332';
}
