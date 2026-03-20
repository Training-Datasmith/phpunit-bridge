<?php

declare(strict_types=1);

/**
 * Example: Using ClockMock for time-sensitive tests.
 *
 * The Symfony PHPUnit Bridge lets you freeze time during tests so that date/time
 * functions return predictable values without sleeping or relying on wall-clock time.
 *
 * Setup in phpunit.xml:
 *
 *   <extensions>
 *     <bootstrap class="Symfony\Bridge\PhpUnit\SymfonyExtension"/>
 *   </extensions>
 *
 * Or register manually in bootstrap.php:
 *   \Symfony\Bridge\PhpUnit\ClockMock::register(MyTest::class);
 */

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\Attribute\TimeSensitive;
use Symfony\Bridge\PhpUnit\ClockMock;

// Option 1: Use the attribute (with SymfonyExtension installed)
#[TimeSensitive]
class OrderExpiryTest extends TestCase
{
    public function testOrderExpiresAfterOneHour(): void
    {
        // Freeze time at a known timestamp
        ClockMock::withClockMock(strtotime('2024-01-15 10:00:00'));

        $order = new Order();
        $order->createdAt = new \DateTimeImmutable();

        // Advance time by 61 minutes
        ClockMock::sleep(61 * 60);

        $this->assertTrue($order->isExpired());
    }

    public function testOrderNotExpiredAfterThirtyMinutes(): void
    {
        ClockMock::withClockMock(strtotime('2024-01-15 10:00:00'));

        $order = new Order();
        $order->createdAt = new \DateTimeImmutable();

        ClockMock::sleep(30 * 60);

        $this->assertFalse($order->isExpired());
    }
}

// Option 2: Register manually (without the attribute)
class ManualClockMockTest extends TestCase
{
    protected function setUp(): void
    {
        ClockMock::register(static::class);
        ClockMock::withClockMock(true); // freeze at current real time
    }

    protected function tearDown(): void
    {
        ClockMock::withClockMock(false); // restore real time
    }

    public function testSomethingTimeSensitive(): void
    {
        $before = time();
        ClockMock::sleep(10);
        $after = time();

        $this->assertSame(10, $after - $before);
    }
}

// The class under test — uses PHP time functions normally
class Order
{
    public \DateTimeImmutable $createdAt;
    private const EXPIRY_SECONDS = 3600;

    public function isExpired(): bool
    {
        return time() > $this->createdAt->getTimestamp() + self::EXPIRY_SECONDS;
    }
}
