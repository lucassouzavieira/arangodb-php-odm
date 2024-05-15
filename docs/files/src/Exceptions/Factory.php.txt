<?php
declare(strict_types=1);

namespace ArangoDB\Exceptions;

use ArangoDB\Exceptions\Storage\DataSourceNotFoundException;

/**
 * Class Factory
 *
 * @package ArangoDB\Exceptions
 */
abstract class Factory
{
    protected static array $exceptions = [
        1203 => DataSourceNotFoundException::class
    ];

    /**
     * Factory the proper exception given the error number
     *
     * @param int $errorNum
     * @param array $data
     * @param \Exception|null $previous
     * @return BaseException
     */
    public static function exception(int $errorNum, array $data = [], \Exception $previous = null): BaseException
    {
        if (isset(self::$exceptions[$errorNum])) {
            $class = self::$exceptions[$errorNum];
            return new $class($data['errorMessage'], $previous, $errorNum);
        }

        // Fallback
        return new Exception($data['errorMessage'], $previous, $errorNum);
    }
}
