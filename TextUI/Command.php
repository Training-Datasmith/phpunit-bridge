<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\PhpUnit\TextUI;

if (version_compare(\PHPUnit\Runner\Version::id(), '9.0.0', '<')) {
    class_alias(\Symfony\Bridge\PhpUnit\Legacy\CommandForV8::class, \Symfony\Bridge\PhpUnit\TextUI\Command::class);
} else {
    class_alias(\Symfony\Bridge\PhpUnit\Legacy\CommandForV9::class, \Symfony\Bridge\PhpUnit\TextUI\Command::class);
}

if (false) {
    class Command
    {
    }
}
