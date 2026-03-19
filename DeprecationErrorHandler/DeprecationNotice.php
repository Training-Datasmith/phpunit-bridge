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

namespace Symfony\Bridge\PhpUnit\DeprecationErrorHandler;

/**
 * @internal
 */
final class DeprecationNotice
{
    private int $count = 0;

    /**
     * @var int[]
     */
    private array $countsByCaller = [];

    public function addObjectOccurrence($class, $method): void
    {
        if (!isset($this->countsByCaller["$class::$method"])) {
            $this->countsByCaller["$class::$method"] = 0;
        }
        ++$this->countsByCaller["$class::$method"];
        ++$this->count;
    }

    public function addProceduralOccurrence(): void
    {
        ++$this->count;
    }

    public function getCountsByCaller(): array
    {
        return $this->countsByCaller;
    }

    public function count(): int
    {
        return $this->count;
    }
}
