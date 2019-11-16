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

use Symfony\Component\HttpFoundation\JsonResponse;
use UploadMediaBundle\Event\GetResponseEvent;
use UploadMediaBundle\Tests\Unit\AbstractEventTest;

class GetResponseEventTest extends AbstractEventTest
{
    public function test()
    {
        $request = $this->createRequest();
        $response = new JsonResponse(['data' => ['a', 'b']]);
        $files = $request->files->all();

        $event = new GetResponseEvent($files, $request, $response);
        $this->assertFalse($event->isPropagationStopped());

        $this->assertSame($request, $event->getRequest());
        $this->assertSame($response, $event->getResponse());
        $this->assertSame($files, $event->getUploadedFiles());
        $this->assertFalse($event->getChunked());

        $newResponse = new JsonResponse(['a', 'b']);
        $event->setResponse($newResponse);
        $this->assertSame($newResponse, $event->getResponse());
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testChunked()
    {
        $request = $this->createRequest();
        $response = new JsonResponse(['data' => ['a', 'b']]);
        $files = $request->files->all();

        $event = new GetResponseEvent($files, $request, $response, true);
        $this->assertTrue($event->getChunked());
    }
}
