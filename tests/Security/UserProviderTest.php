<?php
/**
 * Created by PhpStorm.
 * User: maarek
 * Date: 08/04/2014
 * Time: 09:38
 */

namespace Jma\GaufretteRemoteAdapter\Tests\Security;


use Jma\GaufretteRemoteAdapter\Security\User;
use Jma\GaufretteRemoteAdapter\Security\UserProvider;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $adapter;

    /**
     * @var UserProvider
     */
    protected $provider;

    public function setUp()
    {
        $this->adapter = $this->getMock("Gaufrette\\Adapter");

        $cAdapter = function () {
            return $this->adapter;
        };

        $this->provider = new UserProvider([
            'user' => ['password' => 'password', 'roles' => ['ROLE_USER'], 'adapter' => $cAdapter],
            'admin' => ['password' => 'password', 'roles' => ['ROLE_USER', 'ROLE_ADMIN'], 'adapter' => $cAdapter]
        ]);
    }

    /**
     * @expectedException \LogicException
     */
    public function testWith2UserSameUsername()
    {
        $user1 = $this->getMock("Symfony\Component\Security\Core\User\UserInterface");
        $user2 = $this->getMock("Symfony\Component\Security\Core\User\UserInterface");

        $user1->expects($this->any())->method('getUsername')->willReturn('username');
        $user2->expects($this->any())->method('getUsername')->willReturn('username');

        $provider = new UserProvider();
        $provider->createUser($user1);
        $provider->createUser($user2);
    }

    public function testLoadUserByUserName()
    {
        $user = $this->provider->loadUserByUsername('user');
        $this->assertEquals('user', $user->getUsername());
        $this->assertEquals('password', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
        $this->assertEquals(null, $user->getSalt());
        $this->assertEquals($this->adapter, $user->getGaufretteAdapter());
    }

    public function testLoadAdminByUserName()
    {
        $admin = $this->provider->loadUserByUsername('admin');
        $this->assertEquals('admin', $admin->getUsername());
        $this->assertEquals('password', $admin->getPassword());
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $admin->getRoles());
        $this->assertEquals(null, $admin->getSalt());
        $this->assertEquals($this->adapter, $admin->getGaufretteAdapter());
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadByUserNameNotFound()
    {
        $this->provider->loadUserByUsername('notfound');
    }

    public function testSupportsClass()
    {
        $this->assertTrue($this->provider->supportsClass('Jma\GaufretteRemoteAdapter\Security\User'));
        $this->assertFalse($this->provider->supportsClass('Jma\GaufretteRemoteAdapter\User'));
    }

    public function testRefreshUser()
    {
        $expected = new User('user', 'password', ['ROLE_USER'], function () {
            return $this->adapter;
        });

        $user = $this->provider->refreshUser($expected);

        $this->assertEquals($expected->getUsername(), $user->getUsername());
        $this->assertEquals($expected->getPassword(), $user->getPassword());
        $this->assertEquals($expected->getRoles(), $user->getRoles());
        $this->assertEquals($expected->getSalt(), $user->getSalt());
        $this->assertEquals($expected->getGaufretteAdapter(), $user->getGaufretteAdapter());
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUserException()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $this->provider->refreshUser($user);
    }
}
