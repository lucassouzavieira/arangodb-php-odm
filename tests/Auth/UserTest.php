<?php


namespace Unit\Auth;

use ArangoDB\DataStructures\ArrayList;
use ArangoDB\Validation\Exceptions\DuplicateUserException;
use Unit\TestCase;
use ArangoDB\Auth\User;
use ArangoDB\Connection\Connection;

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
    }

    public function testAll()
    {
        $connection = $this->getConnectionObject();
        $user = new User();
        $user->setConnection($connection);
        $data = $user->all();

        $this->assertInstanceOf(ArrayList::class, $data);
    }

    public function testSave()
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
            'user' => 'tester',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $user->setConnection($this->getConnectionObject());
        $result = $user->save();

        $this->assertTrue($result);

        $sndUser = new User([
            'user' => 'tester',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $sndUser->setConnection($this->getConnectionObject());
        $this->expectException(DuplicateUserException::class);
        $result = $sndUser->save();

        $this->assertTrue($user->delete());
    }

    public function tearDown(): void
    {
        $user = new User();
        $user->setConnection($this->getConnectionObject());
        $users = $user->all();

        foreach ($users as $user) {
            if ($user->getUsername() === 'tester') {
                $user->delete();
            }
        }
    }
}
