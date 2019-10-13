<?php

namespace Unit;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Abstract base test case class
 *
 * @package Tests
 */
abstract class TestCase extends BaseTestCase
{
    protected $env;

    public function loadEnvironment()
    {
        $this->env = Dotenv::create(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../');
        $this->env->load();
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
