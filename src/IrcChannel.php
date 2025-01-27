<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient;

use Exception;

class IrcChannel
{
    const PART_MASK = '/#PART\:\s(.*)$/is';

    const MODE_OP = 'o';
    const MODE_VOICE = 'v';
    const MODE_AWAY = 'a';
    const MODE_BAN = 'b';

    const MODES = [
        self::MODE_OP,
        self::MODE_VOICE,
        self::MODE_AWAY,
        self::MODE_BAN,
    ];

    const MODE_MAP = [
        self::MODE_OP       => 'op',
        self::MODE_VOICE    => 'voice',
        self::MODE_AWAY     => 'away',
        self::MODE_BAN      => 'ban',
    ];

    /**
     * Name of the channel.
     *
     * @var string
     */
    private $name;

    /**
     * Topic of the channel.
     *
     * @var string
     */
    private $topic;

    /** @var array<int, string> */
    private array $users = [];

    /** @var array<int, string> */
    private array $voice = [];

    /** @var array<int, string> */
    private array $op = [];

    /** @var array<int, string> */
    private array $away = [];

    /** @var array<int, string> */
    private array $ban = [];

    public function __construct(string $name)
    {
        $name = trim($name);

        if ('' === $name || '#' === $name) {
            throw new Exception('Channel name is empty.');
        }

        $name = $this->namefromPartMsg($name);

        $this->name = $name;

        if ($this->name[0] !== '#') {
            $this->name = '#' . $this->name;
        }
    }

    /**
     * Fetch the name of the channel, including the `#`.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the current channel topic.
     */
    public function getTopic(): ?string
    {
        return $this->topic;
    }

    /**
     * Fetch the list of users currently on this channel.
     * @return array<int, string>
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * Set the current channel topic.
     * @param string $topic The topic
     */
    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
    }

    /**
     * Set the list of active users on the channel.
     * @param array<int, string> $users An array of user names.
     */
    public function setUsers(array $users): void
    {
        $this->users = array_map(function ($user): string {
            if (null !== $user && $user !== '') {
                $user = $this->stripMode($user);
            }

            return $user;
        }, $users);
    }

    /**
     * Appends a single string or list to users.
     *
     * @param string|array $users
     * @return void
     */
    public function addUser(string|array $users): void
    {
        if ('string' === gettype($users)) $users = [$users];

        foreach($users as $user) {
            if (null !== $user && $user !== '') {
                $user = $this->stripMode($user);
                if (!in_array($user, $this->users)) $this->users[] = $user;
            }
        }
    }

    /**
     * Removes a user from lists.
     *
     * @param string $nick
     * @return void
     */
    public function removeUser(string $nick): void
    {
        // Remove nick from all lists except ban.
        $lists = [
            'users',
            self::MODE_MAP[self::MODE_OP],
            self::MODE_MAP[self::MODE_VOICE],
            self::MODE_MAP[self::MODE_AWAY],
        ];

        foreach ($lists as $list) {
            if (in_array($nick, $this->$list)) {
                $index = array_search($nick, $this->$list);
                unset($this->$list[$index]);
                $this->$list = array_values($this->$list);
            }
        }
    }

    /**
     * Adds a mode to a user.
     *
     * @param string $nick
     * @param string $mode
     * @return void
     */
    public function addMode(string $nick, string $mode): void
    {
        if (!in_array($mode, array_keys(self::MODE_MAP))) {
            throw new Exception("Add mode on: $nick with unknown mode: $mode");
        }

        $list = self::MODE_MAP[$mode];

        if (!in_array($nick, $this->$list)) {
            $this->$list[] = $nick;
        }
    }

    /**
     * Removes a mode to a user.
     *
     * @param string $nick
     * @param string $mode
     * @return void
     */
    public function removeMode(string $nick, string $mode): void
    {
        if (!in_array($mode, array_keys(self::MODE_MAP))) {
            throw new Exception("Remove mode on: $nick with unknown mode: $mode");
        }

        $list = self::MODE_MAP[$mode];

        if (in_array($nick, $this->$list)) {
            $index = array_search($nick, $this->$list);
            unset($this->$list[$index]);
            $this->$list = array_values($this->$list);
        }
    }

    /**
     * User modes (`+`, `@`) will be removed from the nicknames.
     *
     * @string $user
     * @return string
     */
    public function stripMode($user): string
    {
        $firstChar = $user[0];
        if (in_array($firstChar, ['+', '@'])) {
            $user = substr($user, 1);
            switch($firstChar) {
                case '+':
                    $this->addVoice($user);
                    break;
                case '@':
                    $this->addOp($user);
                    break;
                default:
                    // User has no voice or op.
                    break;
            }
        }

        return $user;
    }

    /**
     * Helper Method for addMode
     */
    public function addOp(string $nick): void
    {
        $this->addMode($nick, self::MODE_OP);
    }

    /**
     * Helper Method for removeMode
     */
    Public function removeOp(string $nick): void
    {
        $this->removeMode($nick, self::MODE_OP);
    }

   /**
     * Helper Method for addMode
     */
    public function addVoice(string $nick): void
    {
        $this->addMode($nick, self::MODE_VOICE);
    }

    /**
     * Helper Method for removeMode
     */
    Public function removeVoice(string $nick): void
    {
        $this->removeMode($nick, self::MODE_VOICE);
    }

   /**
     * Helper Method for addMode
     */
    public function addAway(string $nick): void
    {
        $this->addMode($nick, self::MODE_AWAY);
    }

    /**
     * Helper Method for removeMode
     */
    Public function removeAway(string $nick): void
    {
        $this->removeMode($nick, self::MODE_AWAY);
    }

   /**
     * Helper Method for addMode
     */
    public function addBan(string $nick): void
    {
        $this->addMode($nick, self::MODE_BAN);
    }

    /**
     * Helper Method for removeMode
     */
    Public function removeBan(string $nick): void
    {
        $this->removeMode($nick, self::MODE_BAN);
    }

    /**
     * Converts the properties of this class to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name'  => $this->getName(),
            'topic' => $this->getTopic(),
            'users' => $this->getUsers(),
            'op'    => $this->getOp(),
            'voice' => $this->getVoice(),
            'away'  => $this->getAway(),
            'ban'   => $this->getBan(),
        ];
    }

    /**
     * Converts the properties of this class to JSON string.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Attempts to get the chat name from a weird part message.
     * Use case came from part message: user-name parted #PART: channel-name
     *
     * @param string $name
     * @return string
     */
    private function namefromPartMsg(string $name): string|null
    {
        $matches = [];

        preg_match(self::PART_MASK, $name, $matches);

        if (1 < count($matches)) {
            [, $name] = $matches;
        }

        return $name;
    }

    /**
     * Get the usernames with the voice mode.
     */
    public function getVoice(): array
    {
        return $this->voice;
    }

    /**
     * Get the usernames with op mode.
     */
    public function getOp(): array
    {
        return $this->op;
    }

    /**
     * Get the usernames with away mode.
     */
    public function getAway(): array
    {
        return $this->away;
    }

    /**
     * Get the usernames with bad mode.
     */
    public function getBan()
    {
        return $this->ban;
    }
}
