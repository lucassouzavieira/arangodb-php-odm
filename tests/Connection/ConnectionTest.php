<?php

namespace Unit\Connection;

use Dotenv\Dotenv;
use Unit\TestCase;

class ConnectionTest extends TestCase
{
    protected $env;

    public function setUp(): void
    {
        $this->env = Dotenv::create(dir(__DIR__));
        $this->env->load();

        parent::setUp();
    }

    public function testAuthenticate()
    {
        $this->assertNotNull($this->env);
    }
}
