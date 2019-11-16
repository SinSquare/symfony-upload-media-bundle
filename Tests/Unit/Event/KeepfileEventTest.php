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

use UploadMediaBundle\Event\KeepfileEvent;
use UploadMediaBundle\Tests\Unit\AbstractEventTest;

class KeepfileEventTest extends AbstractEventTest
{
    public function test()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);

        $event = new KeepfileEvent($file, $request);

        $data = [
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'extension' => $file->guessClientExtension(),
        ];

        $this->assertSame($request, $event->getRequest());
        $this->assertSame($file, $event->getUploadedFile());
        $this->assertSame($data, $event->getData());
        $this->assertNull($event->getKeepFile());

        $d = ['a' => 'b'];
        $event->setData($d);
        $this->assertSame($d, $event->getData());

        $event->setKeepFile(true);
        $this->assertTrue($event->getKeepFile());
        $event->setKeepFile(false);
        $this->assertFalse($event->getKeepFile());
    }
}
