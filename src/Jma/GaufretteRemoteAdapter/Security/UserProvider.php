<?php
/**
 * Created by PhpStorm.
 * User: maarek
 * Date: 08/04/2014
 * Time: 09:38
 */

namespace Jma\GaufretteRemoteAdapter\Security;


use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    protected $users;

    /**
     * Constructor.
     *
     * The user array is a hash where the keys are usernames and the values are
     * an array of attributes: 'password', 'enabled', and 'roles'.
     *
     * @param array $users An array of users
     */
    public function __construct(array $users = array())
    {
        foreach ($users as $username => $attributes) {
            $password = isset($attributes['password']) ? $attributes['password'] : null;
            $roles = isset($attributes['roles']) ? $attributes['roles'] : array();
            $adapter = $attributes['adapter'];
            $user = new User($username, $password, $roles, $adapter);

            $this->createUser($user);
        }
    }

    /**
     * Adds a new User to the provider.
     *
     * @param UserInterface $user A UserInterface instance
     *
     * @throws \LogicException
     */
    public function createUser(UserInterface $user)
    {
        if (isset($this->users[strtolower($user->getUsername())])) {
            throw new \LogicException('Another user with the same username already exists.');
        }

        $this->users[strtolower($user->getUsername())] = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if (!isset($this->users[strtolower($username)])) {
            $ex = new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            $ex->setUsername($username);

            throw $ex;
        }

        $user = $this->users[strtolower($username)];

        return new User($user->getUsername(), $user->getPassword(), $user->getRoles(), function () use ($user) {
            return $user->getGaufretteAdapter();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Jma\GaufretteRemoteAdapter\Security\User';
    }
}
