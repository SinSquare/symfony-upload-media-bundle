<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Unit\Controller\Basic;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use UploadMediaBundle\Controller\UploadMediaController;
use UploadMediaBundle\Tests\Unit\AbstractTest;

class UploadActionTest extends AbstractTest
{
    public function testUploadFile()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);
        $dispatcher = new EventDispatcher();
        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));
        $controller = new UploadMediaController();

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testUploadChunkMultiple()
    {
        $this->expectException('\RuntimeException');

        $request = $this->createRequest(2);
        $request->headers->set('Content-Range', '0-100/200');
        $dispatcher = new EventDispatcher();
        $controller = new UploadMediaController();

        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testUploadChunk()
    {
        $request = $this->createRequest();
        $request->headers->set('Content-Range', '0-100/200');
        $dispatcher = new EventDispatcher();
        $controller = new UploadMediaController();

        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testUploadNoFile()
    {
        $request = $this->createRequest(0);
        $dispatcher = new EventDispatcher();
        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));
        $controller = new UploadMediaController();

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $arr = json_decode($response->getContent(), true);
        $this->assertSame(['data' => []], $arr);
    }
}
