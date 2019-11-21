<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UploadMediaBundle\Tests\resources\FileTestTrait;

abstract class AbstractTest extends TestCase
{
    use FileTestTrait;
}
