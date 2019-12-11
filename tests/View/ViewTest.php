<?php

namespace Unit\View;

use ArangoDB\View\View;
use Unit\TestCase;

class ViewTest extends TestCase
{
    public function testConstructorWithDefaults()
    {
        $view = new View("my_view");
        $this->assertTrue($view->isNew());
    }
}
