<?php


namespace Unit\Auth;

use ArangoDB\Admin\Admin;
use ArangoDB\Validation\Exceptions\MissingParameterException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Unit\TestCase;
use ArangoDB\Auth\User;
use ArangoDB\Auth\Exceptions\UserException;

class UserTest extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function testConstructorThrowMissingParameterExceptionOnMissingUsername()
    {
        $this->expectException(MissingParameterException::class);

        $user = new User([
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);
    }

    public function testConstructorThrowMissingParameterExceptionOnMissingActive()
    {
        $this->expectException(MissingParameterException::class);

        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'extra' => ['name' => 'Tester']
        ]);
    }

    public function testDebugInfo()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $this->assertArrayHasKey('user', $user->__debugInfo());
        $this->assertArrayHasKey('active', $user->__debugInfo());
        $this->assertArrayHasKey('extra', $user->__debugInfo());
        $this->assertArrayNotHasKey('password', $user->__debugInfo());
    }

    public function testGet()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $this->assertEquals('testing_user', $user->user);
        $this->assertTrue($user->active);
        $this->assertIsArray($user->extra);
        $this->assertArrayHasKey('name', $user->extra);

        $this->assertNull($user->password);
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

        $this->assertArrayHasKey('user', $return);
        $this->assertArrayHasKey('active', $return);
        $this->assertArrayHasKey('extra', $return);
        $this->assertArrayNotHasKey('password', $return);
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
        $this->assertStringNotContainsString('password', json_encode($user));
    }

    public function testToString()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ]);

        $this->assertIsString((string)$user);
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
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => false,
            'extra' => ['name' => 'Tester']
        ]);
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
        ]);

        $this->assertNull($user->getExtra());
    }

    public function testCreate()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ], $this->getConnectionObject());

        $result = $user->save();

        $this->assertTrue($result);
        $this->assertTrue($user->delete());
    }

    public function testUpdate()
    {
        $user = new User([
            'user' => 'testing_user',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ], $this->getConnectionObject());

        $result = $user->save();
        $this->assertTrue($result);

        // Update user on server.
        $user->setActive(false);
        $user->setExtra(['name' => 'Rafael']);
        $result = $user->save();

        // Find user.
        $searchedUser = Admin::findUser($this->getConnectionObject(), 'testing_user');
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
        ], $this->getConnectionObject());

        // Create user.
        $user->save();

        // Check on server.
        $this->assertInstanceOf(User::class, Admin::findUser($this->getConnectionObject(), 'tester'));

        // Delete user and checks on server.
        $this->assertTrue($user->delete());
        $this->assertFalse(Admin::findUser($this->getConnectionObject(), 'tester'));
    }

    public function testDeleteThrowUserException()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['result' => []])),
            new Response(403, [], json_encode($this->mockServerError()))
        ]);

        $user = new User([
            'user' => 'tester',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ], $this->getConnectionObject($mock));

        // Create user.
        $user->save();

        $this->expectException(UserException::class);
        $user->delete();
    }

    public function testDeleteNonExistingUser()
    {
        $user = new User([
            'user' => 'nontester',
            'password' => 'somePassword',
            'active' => true,
            'extra' => ['name' => 'Tester']
        ], $this->getConnectionObject());

        $this->assertFalse($user->delete());
    }

    public function testThrowDuplicatedUserException()
    {
        $user = new User([
            'user' => getenv('ARANGODB_USERNAME'),
            'password' => getenv('ARANGODB_PASSWORD'),
            'active' => true,
        ], $this->getConnectionObject());

        $this->expectException(UserException::class);
        $user->save();
    }
}
