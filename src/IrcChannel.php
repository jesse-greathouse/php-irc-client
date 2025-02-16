<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient;

use JesseGreathouse\PhpIrcClient\Exceptions\InvalidModeException,
    JesseGreathouse\PhpIrcClient\Exceptions\InvalidNameException;


/**
 * Represents an IRC channel with user and mode management.
 */
class IrcChannel
{
    // Regex pattern for extracting channel name from PART message
    const PART_MASK = '/#PART\:\s(.*)$/is';

    // Channel user modes
    const MODE_OP = 'o';
    const MODE_VOICE = 'v';
    const MODE_AWAY = 'a';
    const MODE_BAN = 'b';

    // List of available modes
    const MODES = [
        self::MODE_OP,
        self::MODE_VOICE,
        self::MODE_AWAY,
        self::MODE_BAN,
    ];

    // Mapping of mode symbols to their corresponding mode names
    const MODE_MAP = [
        self::MODE_OP       => 'op',
        self::MODE_VOICE    => 'voice',
        self::MODE_AWAY     => 'away',
        self::MODE_BAN      => 'ban',
    ];

    /** @var string Name of the channel */
    private string $name;

    /** @var string|null Topic of the channel */
    private ?string $topic = null;

    /** @var array<int, string> List of users on the channel */
    private array $users = [];

    /** @var array<int, string> List of users with voice mode */
    private array $voice = [];

    /** @var array<int, string> List of users with op mode */
    private array $op = [];

    /** @var array<int, string> List of users with away mode */
    private array $away = [];

    /** @var array<int, string> List of banned users */
    private array $ban = [];

    /**
     * IrcChannel constructor.
     *
     * @param string $name Name of the channel
     * @throws InvalidNameException if the channel name is invalid
     */
    public function __construct(string $name)
    {
        $name = trim($name);

        if ($name === '' || $name === '#') {
            throw new InvalidNameException('Channel name is empty.');
        }

        // Ensure the name is extracted correctly and always remains a string
        $this->name = $this->nameFromPartMsg($name) ?: $name;

        // Prepend "#" if missing
        if (!str_starts_with($this->name, '#')) {
            $this->name = "#{$this->name}";
        }
    }

    /**
     * Add one or more users to the channel.
     *
     * @param string|array $users Single user or array of users to add
     */
    public function addUser(string|array $users): void
    {
        if (is_string($users)) {
            $users = [$users];
        }

        foreach ($users as $user) {
            if ($user && !in_array($user, $this->users, true)) {
                $this->users[] = $this->stripMode($user);
            }
        }
    }

    /**
     * Remove a user from all lists except ban.
     *
     * @param string $nick Nickname of the user to remove
     */
    public function removeUser(string $nick): void
    {
        $lists = ['users', 'op', 'voice', 'away'];

        foreach ($lists as $list) {
            $index = array_search($nick, $this->$list, true);
            if ($index !== false) {
                unset($this->$list[$index]);
                $this->$list = array_values($this->$list); // Re-index array
            }
        }
    }

    /**
     * Add a mode to a user.
     *
     * @param string $nick Nickname of the user
     * @param string $mode Mode to add
     * @throws InvalidModeException if the mode is unknown
     */
    public function addMode(string $nick, string $mode): void
    {
        if (!in_array($mode, self::MODES, true)) {
            throw new InvalidModeException("Unknown mode: $mode for user: $nick");
        }

        $list = self::MODE_MAP[$mode];
        if (!in_array($nick, $this->$list, true)) {
            $this->$list[] = $nick;
        }
    }

    /**
     * Remove a mode from a user.
     *
     * @param string $nick Nickname of the user
     * @param string $mode Mode to remove
     * @throws InvalidModeException if the mode is unknown
     */
    public function removeMode(string $nick, string $mode): void
    {
        if (!in_array($mode, self::MODES, true)) {
            throw new InvalidModeException("Unknown mode: $mode for user: $nick");
        }

        $list = self::MODE_MAP[$mode];
        $index = array_search($nick, $this->$list, true);
        if ($index !== false) {
            unset($this->$list[$index]);
            $this->$list = array_values($this->$list); // Re-index array
        }
    }

    /**
     * User modes (`+`, `@`) will be removed from the nicknames.
     * @param string $user
     * @return string
     */
    public function stripMode(string $user): string
    {
        $firstChar = $user[0];
        if (in_array($firstChar, ['+', '@'])) {
            $user = substr($user, 1);
            switch ($firstChar) {
                case '+':
                    $this->addVoice($user);
                    break;
                case '@':
                    $this->addOp($user);
                    break;
            }
        }

        return $user;
    }

    /**
     * Adds an operator mode to a user.
     * @param string $nick
     */
    public function addOp(string $nick): void
    {
        $this->addMode($nick, self::MODE_OP);
    }

    /**
     * Removes an operator mode from a user.
     * @param string $nick
     */
    public function removeOp(string $nick): void
    {
        $this->removeMode($nick, self::MODE_OP);
    }

    /**
     * Adds a voice mode to a user.
     * @param string $nick
     */
    public function addVoice(string $nick): void
    {
        $this->addMode($nick, self::MODE_VOICE);
    }

    /**
     * Removes a voice mode from a user.
     * @param string $nick
     */
    public function removeVoice(string $nick): void
    {
        $this->removeMode($nick, self::MODE_VOICE);
    }

    /**
     * Adds an away mode to a user.
     * @param string $nick
     */
    public function addAway(string $nick): void
    {
        $this->addMode($nick, self::MODE_AWAY);
    }

    /**
     * Removes an away mode from a user.
     * @param string $nick
     */
    public function removeAway(string $nick): void
    {
        $this->removeMode($nick, self::MODE_AWAY);
    }

    /**
     * Adds a ban mode to a user.
     * @param string $nick
     */
    public function addBan(string $nick): void
    {
        $this->addMode($nick, self::MODE_BAN);
    }

    /**
     * Removes a ban mode from a user.
     * @param string $nick
     */
    public function removeBan(string $nick): void
    {
        $this->removeMode($nick, self::MODE_BAN);
    }

    /**
     * Convert class properties to an array.
     *
     * @return array Array representation of the channel
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
     * Convert class properties to a JSON string.
     *
     * @return string JSON string representation of the channel
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Extract the channel name from a PART message.
     *
     * @param string $msg Message to extract the name from
     * @return string|null The extracted channel name, or null if not found
     */
    private function nameFromPartMsg(string $msg): ?string
    {
        preg_match(self::PART_MASK, $msg, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Get the list of users with op mode.
     *
     * @return array<int, string> List of users with op mode
     */
    public function getOp(): array
    {
        return $this->op;
    }

    /**
     * Get the list of users with voice mode.
     *
     * @return array<int, string> List of users with voice mode
     */
    public function getVoice(): array
    {
        return $this->voice;
    }

    /**
     * Get the list of users with away mode.
     *
     * @return array<int, string> List of users with away mode
     */
    public function getAway(): array
    {
        return $this->away;
    }

    /**
     * Get the list of banned users.
     *
     * @return array<int, string> List of banned users
     */
    public function getBan(): array
    {
        return $this->ban;
    }

    /**
     * Fetch the name of the channel.
     *
     * @return string The channel name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the current channel topic.
     *
     * @return string|null The channel topic, or null if none
     */
    public function getTopic(): ?string
    {
        return $this->topic;
    }

    /**
     * Set the current channel topic.
     *
     * @param string $topic The topic to set
     */
    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
    }

    /**
     * Fetch the list of users currently on this channel.
     *
     * @return array<int, string> List of users
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * Set the list of active users on the channel.
     *
     * @param array<int, string> $users List of usernames
     */
    public function setUsers(array $users): void
    {
        $this->users = array_map(fn($user) => $this->stripMode($user), $users);
    }
}
