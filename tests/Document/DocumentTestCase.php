<?php


namespace Unit\Document;

use Unit\TestCase;

abstract class DocumentTestCase extends TestCase
{
    public function setUp(): void
    {
        $this->loadEnvironment();
        parent::setUp();
    }

    public function getAttributes($withDescriptors = false)
    {
        $descriptors = [
            '_id' => 'sd/178538',
            '_rev' => '_ZcQ9yh----',
            '_key' => '178538'
        ];

        $fake = [
            'field' => 'of soccer',
            'good_music' => [
                'Queen',
                'Motorhead',
                'Anthrax',
                'Metallica',
            ],
            'status' => false,
            'dreamers' => null,
            'value' => 1.5,
            'percent' => 45.4,
            'quantity' => 40
        ];

        if ($withDescriptors) {
            return array_merge($descriptors, $fake);
        }

        return $fake;
    }
}
