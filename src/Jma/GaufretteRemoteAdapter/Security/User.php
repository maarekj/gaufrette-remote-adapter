<?php
/**
 * Created by PhpStorm.
 * User: maarek
 * Date: 08/04/2014
 * Time: 09:40
 */

namespace Jma\GaufretteRemoteAdapter\Security;

use Gaufrette\Adapter;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private $username;
    private $password;
    private $roles;

    /**
     * @var callable
     */
    private $adapter;

    function __construct($username, $password, $roles, callable $adapter)
    {
        $this->adapter = $adapter;
        $this->password = $password;
        $this->roles = $roles;
        $this->username = $username;
    }


    /**
     * @return Adapter
     */
    public function getGaufretteAdapter()
    {
        $c = $this->adapter;
        return $c();
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }
}