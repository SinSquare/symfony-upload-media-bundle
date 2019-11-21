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

use UploadMediaBundle\Event\GetFileDataEvent;
use UploadMediaBundle\Tests\Unit\AbstractTest;

class GetFileDataEventTest extends AbstractTest
{
    public function test()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);

        $data = ['a', 'b'];
        $event = new GetFileDataEvent($file, $request, $data);

        $this->assertSame($request, $event->getRequest());
        $this->assertSame($file, $event->getUploadedFile());
        $this->assertSame($data, $event->getData());

        $data = ['b', 'c'];
        $event->setData($data);
        $this->assertSame($data, $event->getData());
    }
}
