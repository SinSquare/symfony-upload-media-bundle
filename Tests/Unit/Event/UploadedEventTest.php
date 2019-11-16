<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Unit\Event;

use UploadMediaBundle\Event\UploadedEvent;
use UploadMediaBundle\Tests\Unit\AbstractEventTest;

class UploadedEventTest extends AbstractEventTest
{
    public function test()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);

        $event = new UploadedEvent($file, $request);

        $this->assertSame($request, $event->getRequest());
        $this->assertSame($file, $event->getUploadedFile());
        $this->assertFalse($event->getIsMoved());

        $oldPath = \dirname($file->getPathname());
        $newPath = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'uploaded';

        $newFile = $event->move($newPath);
        $this->assertNotSame($file, $newFile);

        $this->assertSame(\dirname($newFile->getPathname()), \dirname($newFile->getPathname()));
        $this->assertSame($file->getFilename(), $newFile->getFilename());
    }
}
