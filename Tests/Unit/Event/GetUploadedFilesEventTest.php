<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Unit\Event;

use UploadMediaBundle\Event\GetUploadedFilesEvent;
use UploadMediaBundle\Tests\Unit\AbstractTest;

class GetUploadedFilesEventTest extends AbstractTest
{
    public function test()
    {
        $request = $this->createRequest(2);
        $file = $request->files->get(0);

        $event = new GetUploadedFilesEvent($request);

        $this->assertSame($request, $event->getRequest());

        $this->assertCount(2, $event->getUploadedFiles());
        $this->assertSame($file, $event->getUploadedFiles()[0]);

        $event->removeUploadedFile($file);
        $this->assertCount(1, $event->getUploadedFiles());
        $event->removeUploadedFile($event->getUploadedFiles()[0]);
        $this->assertCount(0, $event->getUploadedFiles());

        $event->addUploadedFile($file);
        $this->assertCount(1, $event->getUploadedFiles());
        $event->addUploadedFile($file);
        $this->assertCount(1, $event->getUploadedFiles());

        $event->removeUploadedFile($file);
        $this->assertCount(0, $event->getUploadedFiles());

        $event->setUploadedFiles([$file]);
        $this->assertCount(1, $event->getUploadedFiles());

        $file = $this->createUploadedFile();
        $event->removeUploadedFile($file);
        $this->assertCount(1, $event->getUploadedFiles());
    }

    public function testNotUploadedFile()
    {
        $this->expectException('\LogicException');

        $request = $this->createRequest(1);

        $files = [new \stdClass()];

        $event = new GetUploadedFilesEvent($request);
        $event->setUploadedFiles($files);
    }
}
