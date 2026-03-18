<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\PhpUnit\Legacy;

use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\TraversableContains;

/**
 * This trait is @internal.
 */
trait PolyfillAssertTrait
{
    /**
     * @param iterable $haystack
     * @param string   $message
     */
    public static function assertContainsEquals($needle, $haystack, $message = ''): void
    {
        $constraint = new TraversableContains($needle, false, false);
        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * @param iterable $haystack
     * @param string   $message
     */
    public static function assertNotContainsEquals($needle, $haystack, $message = ''): void
    {
        $constraint = new LogicalNot(new TraversableContains($needle, false, false));
        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * @param string $filename
     * @param string $message
     */
    public static function assertIsNotReadable($filename, $message = ''): void
    {
        static::assertNotIsReadable($filename, $message);
    }

    /**
     * @param string $filename
     * @param string $message
     */
    public static function assertIsNotWritable($filename, $message = ''): void
    {
        static::assertNotIsWritable($filename, $message);
    }

    /**
     * @param string $directory
     * @param string $message
     */
    public static function assertDirectoryDoesNotExist($directory, $message = ''): void
    {
        static::assertDirectoryNotExists($directory, $message);
    }

    /**
     * @param string $directory
     * @param string $message
     */
    public static function assertDirectoryIsNotReadable($directory, $message = ''): void
    {
        static::assertDirectoryNotIsReadable($directory, $message);
    }

    /**
     * @param string $directory
     * @param string $message
     */
    public static function assertDirectoryIsNotWritable($directory, $message = ''): void
    {
        static::assertDirectoryNotIsWritable($directory, $message);
    }

    /**
     * @param string $filename
     * @param string $message
     */
    public static function assertFileDoesNotExist($filename, $message = ''): void
    {
        static::assertFileNotExists($filename, $message);
    }

    /**
     * @param string $filename
     * @param string $message
     */
    public static function assertFileIsNotReadable($filename, $message = ''): void
    {
        static::assertFileNotIsReadable($filename, $message);
    }

    /**
     * @param string $filename
     * @param string $message
     */
    public static function assertFileIsNotWritable($filename, $message = ''): void
    {
        static::assertFileNotIsWritable($filename, $message);
    }

    /**
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    public static function assertMatchesRegularExpression($pattern, $string, $message = ''): void
    {
        static::assertRegExp($pattern, $string, $message);
    }

    /**
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    public static function assertDoesNotMatchRegularExpression($pattern, $string, $message = ''): void
    {
        static::assertNotRegExp($pattern, $string, $message);
    }
}
