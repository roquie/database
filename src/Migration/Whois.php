<?php declare(strict_types=1);

namespace Roquie\Database\Migration;

use Roquie\Database\Notify\NotifyInterface;

class Whois
{
    public const VERSION = '1.0';
    public const WHOIS   = '<cyan>Rdb – great tool for working with database migrations and seeds (v' . self::VERSION . ').</cyan>';

    /**
     * @var bool
     */
    private static $printed = false;

    /**
     * @var bool
     */
    private static $enabled = true;

    /**
     * @param \Roquie\Database\Notify\NotifyInterface $notify
     */
    public static function print(NotifyInterface $notify): void
    {
        if (self::$enabled) {
            self::$printed || $notify->note(self::WHOIS);
            self::$printed = true;
        }
    }

    /**
     * :'(
     */
    public static function disable()
    {
        self::$enabled = false;
    }
}
