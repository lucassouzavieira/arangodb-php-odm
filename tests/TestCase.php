<?php

namespace Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Abstract base test case class
 *
 * @package Tests
 */
abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
