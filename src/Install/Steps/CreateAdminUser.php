<?php

/*
 * This file is part of Flarum.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flarum\Install\Steps;

use Flarum\Group\Group;
use Flarum\Install\Step;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Hashing\BcryptHasher;
use UnexpectedValueException;

class CreateAdminUser implements Step
{
    /**
     * @var ConnectionInterface
     */
    private $database;

    /**
     * @var array
     */
    private $admin;

    public function __construct(ConnectionInterface $database, array $admin)
    {
        $this->database = $database;
        $this->admin = $admin;
    }

    public function getMessage()
    {
        return 'Creating admin user '.$this->admin['username'];
    }

    public function run()
    {
        if ($this->admin['password'] !== $this->admin['password_confirmation']) {
            throw new UnexpectedValueException('The password did not match its confirmation.');
        }

        $uid = $this->database->table('users')->insertGetId([
            'username' => $this->admin['username'],
            'email' => $this->admin['email'],
            'password' => (new BcryptHasher)->make($this->admin['password']),
            'joined_at' => time(),
            'is_email_confirmed' => 1,
        ]);

        $this->database->table('group_user')->insert([
            'user_id' => $uid,
            'group_id' => Group::ADMINISTRATOR_ID,
        ]);
    }
}
