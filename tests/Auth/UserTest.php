<?php


namespace Unit\Auth;

use Unit\TestCase;
use ArangoDB\Auth\User;
use ArangoDB\Connection\Connection;
use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Auth\Exceptions\DuplicateUserException;

class UserTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function getConnectionObject()
    {
        return new Connection([
            'username' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'database' => getenv('ARANGODB_DBNAME'),
            'host' => getenv('ARANGODB_HOST'),
            'port' => getenv('ARANGODB_PORT')
        ]);
    }

    public function testNewUserCanHavePassword()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $this->assertObjectHasAttribute('password', $user);
        $this->assertEquals('somePassword', $user->password);
    }

    public function testToArray()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $return = $user->toArray();

        $this->assertTrue(array_key_exists('user', $return));
        $this->assertTrue(array_key_exists('active', $return));
        $this->assertTrue(array_key_exists('extra', $return));
        $this->assertFalse(array_key_exists('password', $return));
    }

    public function testGetUsername()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $this->assertEquals('testing_user', $user->getUsername());

        $user = new User();
        $user->user = 'another_user';

        $this->assertEquals('another_user', $user->getUsername());
    }

    public function testGetNonExistentAttribute()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $this->assertNull($user->somefield);
    }

    public function testToJson()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $this->assertJson(json_encode($user));
    }

    public function testToString()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $this->assertJson((string)$user);
    }

    public function testIsActive()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => false,
            'extra' => ['name' => 'Tester']
        ]);

        $this->assertFalse($user->isActive());
    }

    public function testSetActive()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => false,
            'extra' => ['name' => 'Tester']
        ]);

        $this->assertFalse($user->isActive());
        $user->setActive(true);
        $this->assertTrue($user->isActive());
    }

    public function testSetExtra()
    {
        $user = new User();
        $user->setExtra(['name' => 'Layla']);

        $this->assertIsArray($user->getExtra());
        $this->assertArrayHasKey('name', $user->getExtra());
    }

    public function testGetExtra()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => null
        ]);

        $this->assertNull($user->getExtra());
    }

    public function testAll()
    {
        $connection = $this->getConnectionObject();
        $user = new User();
        $user->setConnection($connection);
        $data = $user->all();

        $this->assertInstanceOf(ArrayList::class, $data);
    }

    public function testCreate()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $user->setConnection($this->getConnectionObject());
        $result = $user->save();

        $this->assertTrue($result);
        $this->assertTrue($user->delete());
    }

    public function testFind()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $user->setConnection($this->getConnectionObject());
        $result = $user->save();

        $this->assertTrue($result);
        $otherUserObject = new User();
        $otherUserObject->setConnection($this->getConnectionObject());
        $searchedUser = $otherUserObject->find('testing_user');

        $this->assertInstanceOf(User::class, $searchedUser);
        $this->assertTrue($searchedUser->delete());
    }

    public function testFindNonExistentUser()
    {
        $user = new User();
        $user->setConnection($this->getConnectionObject());
        $searchedUser = $user->find('some_user');

        $this->assertNull($searchedUser);
    }

    public function testUpdate()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $user->setConnection($this->getConnectionObject());
        $result = $user->save();
        $this->assertTrue($result);

        // Update user on server.
        $user->setActive(false);
        $user->setExtra(['name' => 'Rafael']);
        $result = $user->save();

        // Find user.
        $otherUserObject = new User();
        $otherUserObject->setConnection($this->getConnectionObject());
        $searchedUser = $otherUserObject->find('testing_user');

        $this->assertInstanceOf(User::class, $searchedUser);


        $this->assertEquals($user->getUsername(), $searchedUser->getUsername());
        $this->assertEquals($user->isActive(), $searchedUser->isActive());
        $this->assertEquals('Rafael', $searchedUser->getExtra()['name']);
        $this->assertTrue($searchedUser->delete());
    }

    public function testDelete()
    {
        $user = new User([
            'user' => 'tester',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $user->setConnection($this->getConnectionObject());
        $user->save();

        $result = $user->delete();
        $this->assertTrue($result);
    }

    public function testDeleteNonExistingUser()
    {
        $user = new User([
            'user' => 'nontester',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $user->setConnection($this->getConnectionObject());

        $result = $user->delete();
        $this->assertFalse($result);
    }

    public function testThrowDuplicatedUserException()
    {
        $user = new User([
            'user' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'active' => true,
        ]);

        $this->expectException(DuplicateUserException::class);
        $user->setConnection($this->getConnectionObject());
        $user->save();
    }
}
