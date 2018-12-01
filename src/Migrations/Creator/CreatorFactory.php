<?php declare(strict_types=1);

namespace Roquie\Database\Migrations\Creator;

use InvalidArgumentException;
use Roquie\Database\Migrations\Notify\NotifyInterface;

class CreatorFactory
{
    /**
     * @param string $type
     * @param \Roquie\Database\Migrations\Notify\NotifyInterface $notify
     * @return \Roquie\Database\Migrations\Creator\MigrationCreator
     */
    public static function create(string $type, NotifyInterface $notify): MigrationCreatorInterface
    {
        switch ($type) {
            case 'pgsql':
            case 'mysql':
            case 'sqlsrv':
            case 'sqlite':
            case 'default':
                return new MigrationCreator($notify);
            default:
                throw new InvalidArgumentException('Database type not supported.');
        }
    }
}
