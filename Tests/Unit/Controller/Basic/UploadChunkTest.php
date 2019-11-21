<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Unit\Controller\Basic;

use Symfony\Component\EventDispatcher\EventDispatcher;
use UploadMediaBundle\Controller\UploadMediaController;
use UploadMediaBundle\Tests\Unit\AbstractTest;

class UploadChunkTest extends AbstractTest
{
    public function testUploadChunk()
    {
        $request = $this->createRequest();
        $request->headers->set('Content-Range', '0-63/128');
        $file = $request->files->get(0);
        $chunk = $this->createUploadedFileChunk($file, 0, 64);
        $request->files->set(0, $chunk);

        $dispatcher = new EventDispatcher();
        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $controller = new UploadMediaController();

        $data = $this->invokeMethod($controller, 'uploadChunk', [$chunk, $request, $dispatcher, $dir]);
        $this->assertIsArray($data);

        $this->assertSame('testfile_0', $data['originalName']);
        $this->assertSame('text/plain', $data['mimeType']);
        $this->assertSame('txt', $data['extension']);
        $this->assertArrayHasKey('isChunk', $data);
        $this->assertArrayNotHasKey('path', $data);
        $this->assertTrue($data['isChunk']);

        $this->assertSame(1, $this->countFilesInDir($dir));

        $request->headers->set('Content-Range', '64-127/128');
        $chunk = $this->createUploadedFileChunk($file, 64, 128);
        $request->files->set(0, $chunk);

        $data = $this->invokeMethod($controller, 'uploadChunk', [$chunk, $request, $dispatcher, $dir]);
        $this->assertIsArray($data);
        $this->assertSame('testfile_0', $data['originalName']);
        $this->assertSame('text/plain', $data['mimeType']);
        $this->assertSame('txt', $data['extension']);
        $this->assertArrayNotHasKey('isChunk', $data);
        $this->assertArrayHasKey('path', $data);

        $this->assertSame(1, $this->countFilesInDir($dir));

        $this->assertSame(sha1_file($file->getRealPath()), sha1_file($data['path']));
    }

    public function testUploadChunkInvalid()
    {
        $this->expectException('\RuntimeException');

        $request = $this->createRequest();
        $request->headers->set('Content-Range', '64-127/128');
        $file = $request->files->get(0);
        $chunk = $this->createUploadedFileChunk($file, 0, 64);
        $request->files->set(0, $chunk);

        $dispatcher = new EventDispatcher();
        $dir = $this->dir.\DIRECTORY_SEPARATOR.'uploaded';
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $controller = new UploadMediaController();

        $data = $this->invokeMethod($controller, 'uploadChunk', [$chunk, $request, $dispatcher, $dir]);
    }
}
