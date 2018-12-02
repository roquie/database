<?php declare(strict_types=1);

namespace Roquie\Database\Seed\Exception;

use Roquie\Database\Exception\SeedException;

class InvalidArgumentException extends SeedException
{
    public static function forNotRegisteredSeeder()
    {
        return new self('Seeder not registered in the container.');
    }

    public static function forExtendRule()
    {
        return new self('Seeder class must be extended from AbstractSeed.');
    }

    public static function forNotFoundSeeder()
    {
        return new self('Seeder class not found.');
    }
}
