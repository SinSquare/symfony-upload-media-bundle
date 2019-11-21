<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Unit\Controller\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use UploadMediaBundle\Controller\UploadMediaController;
use UploadMediaBundle\Event\GetFileDataEvent;
use UploadMediaBundle\Event\KeepfileEvent;
use UploadMediaBundle\Event\UploadedEvent;
use UploadMediaBundle\Event\UploadMediaEvents;
use UploadMediaBundle\Tests\Unit\AbstractTest;

class UploadMediaEventTest extends AbstractTest
{
    private $data;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->data = null;
    }

    public function testKeepFileNoAction()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);
        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(UploadMediaEvents::KEEPFILE, function (KeepfileEvent $event) use ($file) {
            $this->assertSame($file, $event->getUploadedFile());
        });

        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $controller = new UploadMediaController();

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $arr = json_decode($response->getContent(), true);
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr['data']);
        $this->assertSame('testfile_0', $arr['data'][0]['originalName']);
    }

    public function testKeepFileKeep()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);
        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(UploadMediaEvents::KEEPFILE, function (KeepfileEvent $event) use ($file) {
            $this->assertSame($file, $event->getUploadedFile());
            $event->setKeepFile(true);
        });

        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $controller = new UploadMediaController();

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $arr = json_decode($response->getContent(), true);
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr['data']);
        $this->assertSame('testfile_0', $arr['data'][0]['originalName']);
    }

    public function testKeepFileDontKeep()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);
        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(UploadMediaEvents::KEEPFILE, function (KeepfileEvent $event) use ($file) {
            $this->assertSame($file, $event->getUploadedFile());
            $event->setKeepFile(false);
        });

        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $controller = new UploadMediaController();

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $arr = json_decode($response->getContent(), true);
        $this->assertIsArray($arr);
        $this->assertCount(0, $arr['data']);
    }

    public function testUploadedFileNoMove()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);
        $dispatcher = new EventDispatcher();

        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $dispatcher->addListener(UploadMediaEvents::UPLOAD, function (UploadedEvent $event) use ($file) {
            $this->assertSame($file, $event->getUploadedFile());
        });

        $controller = new UploadMediaController();

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $arr = json_decode($response->getContent(), true);
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr['data']);
        $this->assertSame('testfile_0', $arr['data'][0]['originalName']);
        $this->assertStringStartsWith($dir, $arr['data'][0]['path']);
    }

    public function testUploadedFileWithMove()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);
        $dispatcher = new EventDispatcher();

        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));
        $newDir = $dir.\DIRECTORY_SEPARATOR.'new';

        $dispatcher->addListener(UploadMediaEvents::UPLOAD, function (UploadedEvent $event) use ($file, $newDir) {
            $this->assertSame($file, $event->getUploadedFile());
            $event->move($newDir);
        });

        $controller = new UploadMediaController();

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $arr = json_decode($response->getContent(), true);
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr['data']);
        $this->assertSame('testfile_0', $arr['data'][0]['originalName']);
        $this->assertStringStartsWith($newDir, $arr['data'][0]['path']);
    }

    public function testUploadedFileWithMoveWithName()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);
        $dispatcher = new EventDispatcher();

        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));
        $newDir = $dir.\DIRECTORY_SEPARATOR.'new';

        $dispatcher->addListener(UploadMediaEvents::UPLOAD, function (UploadedEvent $event) use ($file, $newDir) {
            $this->assertSame($file, $event->getUploadedFile());
            $event->move($newDir, 'test');
        });

        $controller = new UploadMediaController();

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $arr = json_decode($response->getContent(), true);
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr['data']);
        $this->assertSame('testfile_0', $arr['data'][0]['originalName']);
        $this->assertSame($newDir.\DIRECTORY_SEPARATOR.'test', $arr['data'][0]['path']);
    }

    public function testGetFileData()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);
        $dispatcher = new EventDispatcher();

        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $dispatcher->addListener(UploadMediaEvents::KEEPFILE, function (KeepfileEvent $event) use ($file) {
            $this->assertSame($file, $event->getUploadedFile());
            $this->data = $event->getData();
        });

        $dispatcher->addListener(UploadMediaEvents::FILEDATA, function (GetFileDataEvent $event) use ($file) {
            $this->assertNotSame($file, $event->getUploadedFile());

            $d = $event->getData();
            unset($d['path']);

            $this->assertSame($this->data, $d);
            $this->data = $event->getData();
        });

        $controller = new UploadMediaController();

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $arr = json_decode($response->getContent(), true);
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr['data']);
        $this->assertSame('testfile_0', $arr['data'][0]['originalName']);
        $this->assertSame($this->data, $arr['data'][0]);
    }

    public function testGetFileDataNew()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);
        $dispatcher = new EventDispatcher();

        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $dispatcher->addListener(UploadMediaEvents::KEEPFILE, function (KeepfileEvent $event) use ($file) {
            $this->assertSame($file, $event->getUploadedFile());
            $this->data = $event->getData();
        });

        $dispatcher->addListener(UploadMediaEvents::FILEDATA, function (GetFileDataEvent $event) use ($file) {
            $this->assertNotSame($file, $event->getUploadedFile());

            $this->data = ['a' => 'b', 'c' => 'd'];
            $event->setData($this->data);
        });

        $controller = new UploadMediaController();

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $arr = json_decode($response->getContent(), true);
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr['data']);
        $this->assertSame($this->data, $arr['data'][0]);
    }
}
