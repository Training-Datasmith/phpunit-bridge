<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\PhpUnit;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 * @author Dominic Tubach <dominic.tubach@to.com>
 */
class ClockMock
{
    private static string|float|null $now = null;

    /**
     * Enables, disables, or queries the clock mock state.
     *
     * When called with no argument, returns whether the clock mock is currently active.
     * When called with true, freezes time at the current microtime value.
     * When called with false, disables the mock and restores real time functions.
     * When called with a numeric value, sets the frozen time to that timestamp.
     *
     * @param bool|float|int|null $enable True/false to enable/disable, a numeric timestamp to set a specific time, null to query
     *
     * @return bool|null Whether the clock mock is active (only when called with null), otherwise null
     */
    public static function withClockMock($enable = null): ?bool
    {
        if (null === $enable) {
            return null !== self::$now;
        }

        self::$now = is_numeric($enable) ? (float) $enable : ($enable ? microtime(true) : null);

        return null;
    }

    /**
     * Returns the current Unix timestamp, honoring the clock mock if active.
     *
     * When the mock is active, returns the frozen timestamp (truncated to int).
     * When inactive, delegates to the real time() function.
     *
     * @return int The current (possibly mocked) Unix timestamp
     */
    public static function time(): int
    {
        if (null === self::$now) {
            return \time();
        }

        return (int) self::$now;
    }

    /**
     * Sleeps for the given number of seconds, advancing the mocked clock if active.
     *
     * When the mock is active, advances the frozen timestamp by `$s` seconds instead
     * of pausing execution. When inactive, delegates to the real sleep() function.
     *
     * @param int|float $s Number of seconds to sleep
     *
     * @return int 0 on success (or the real sleep() return value when mock is inactive)
     */
    public static function sleep($s): int
    {
        if (null === self::$now) {
            return \sleep($s);
        }

        self::$now += (int) $s;

        return 0;
    }

    public static function usleep($us): void
    {
        if (null === self::$now) {
            \usleep($us);
        } else {
            self::$now += $us / 1000000;
        }
    }

    /**
     * @return string|float
     */
    public static function microtime($asFloat = false)
    {
        if (null === self::$now) {
            return \microtime($asFloat);
        }

        if ($asFloat) {
            return self::$now;
        }

        return \sprintf('%0.6f00 %d', self::$now - (int) self::$now, (int) self::$now);
    }

    public static function date($format, $timestamp = null): string
    {
        if (null === $timestamp) {
            $timestamp = self::time();
        }

        return \date($format, $timestamp);
    }

    public static function gmdate($format, $timestamp = null): string
    {
        if (null === $timestamp) {
            $timestamp = self::time();
        }

        return \gmdate($format, $timestamp);
    }

    public static function hrtime($asNumber = false): int|float|array
    {
        $ns = (self::$now - (int) self::$now) * 1000000000;

        if ($asNumber) {
            $number = \sprintf('%d%d', (int) self::$now, $ns);

            return \PHP_INT_SIZE === 8 ? (int) $number : (float) $number;
        }

        return [(int) self::$now, (int) $ns];
    }

    /**
     * @return false|int
     */
    public static function strtotime(string $datetime, ?int $timestamp = null): int|false
    {
        if (null === $timestamp) {
            $timestamp = self::time();
        }

        return \strtotime($datetime, $timestamp);
    }

    /**
     * Registers the clock mock for all namespaces of the given test class.
     *
     * Uses `eval()` to define namespace-scoped override functions (time(), microtime(),
     * sleep(), usleep(), date(), gmdate(), hrtime(), strtotime()) that delegate to this
     * class's static methods. The namespace is derived from the given class name, including
     * its `Tests\` sub-namespace if present.
     *
     * @param class-string $class The fully qualified test class name whose namespace(s) to mock
     */
    public static function register($class): void
    {
        $self = static::class;

        $mockedNs = [substr($class, 0, strrpos($class, '\\'))];
        if (0 < strpos($class, '\\Tests\\')) {
            $ns = str_replace('\\Tests\\', '\\', $class);
            $mockedNs[] = substr($ns, 0, strrpos($ns, '\\'));
        } elseif (str_starts_with($class, 'Tests\\')) {
            $mockedNs[] = substr($class, 6, strrpos($class, '\\') - 6);
        }
        foreach ($mockedNs as $ns) {
            if (\function_exists($ns.'\time')) {
                continue;
            }
            eval(<<<EOPHP
namespace $ns;

function time()
{
    return \\$self::time();
}

function microtime(\$asFloat = false)
{
    return \\$self::microtime(\$asFloat);
}

function sleep(\$s)
{
    return \\$self::sleep(\$s);
}

function usleep(\$us)
{
    \\$self::usleep(\$us);
}

function date(\$format, \$timestamp = null)
{
    return \\$self::date(\$format, \$timestamp);
}

function gmdate(\$format, \$timestamp = null)
{
    return \\$self::gmdate(\$format, \$timestamp);
}

function hrtime(\$asNumber = false)
{
    return \\$self::hrtime(\$asNumber);
}

function strtotime(\$datetime, \$timestamp = null)
{
    return \\$self::strtotime(\$datetime, \$timestamp);
}
EOPHP
            );
        }
    }
}
