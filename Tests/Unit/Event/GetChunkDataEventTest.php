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

use UploadMediaBundle\Event\GetChunkDataEvent;
use UploadMediaBundle\Tests\Unit\AbstractTest;

class GetChunkDataEventTest extends AbstractTest
{
    public function test()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);

        $data = ['a', 'b'];
        $event = new GetChunkDataEvent($file, $request, $data);

        $data['isChunk'] = true;

        $this->assertSame($request, $event->getRequest());
        $this->assertSame($file, $event->getUploadedFile());
        $this->assertSame($data, $event->getData());

        $data = ['b', 'c'];
        $event->setData($data);
        $this->assertSame($data, $event->getData());
    }
}
