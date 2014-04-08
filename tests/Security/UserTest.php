<?php
/**
 * Created by PhpStorm.
 * User: maarek
 * Date: 08/04/2014
 * Time: 22:11
 */

namespace Jma\GaufretteRemoteAdapter\Tests\Security;


use Jma\GaufretteRemoteAdapter\Security\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    protected $adapter;

    /**
     * @var User
     */
    protected $user;

    public function setUp()
    {
        $this->adapter = $this->getMock("Gaufrette\\Adapter");
        $adapter = $this->adapter;
        $this->user = new User('username', 'password', array('ROLE_USER', 'ROLE_ADMIN'), function () use ($adapter) {
            return $adapter;
        });
    }

    public function testGetRoles()
    {
        $this->assertEquals(array('ROLE_USER', 'ROLE_ADMIN'), $this->user->getRoles());
    }

    public function testGetUsername()
    {
        $this->assertEquals('username', $this->user->getUsername());
    }

    public function testGetPassword()
    {
        $this->assertEquals('password', $this->user->getPassword());
    }

    public function testGetSalt()
    {
        $this->assertEquals(null, $this->user->getSalt());
    }

    public function testEraseCredentials()
    {
        $this->user->eraseCredentials();
    }

    public function testGetGaufretteAdapter()
    {
        $this->assertEquals($this->adapter, $this->user->getGaufretteAdapter());
    }
}
 