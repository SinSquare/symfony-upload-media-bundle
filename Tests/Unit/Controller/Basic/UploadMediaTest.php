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
use UploadMediaBundle\Controller\UploadMediaController;
use UploadMediaBundle\Tests\Unit\AbstractTest;

class UploadMediaTest extends AbstractTest
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

        $data = $this->invokeMethod($controller, 'uploadFile', [$file, $request, $dispatcher, $dir]);
        $this->assertIsArray($data);

        $this->assertSame('testfile_0', $data['originalName']);
        $this->assertSame('text/plain', $data['mimeType']);
        $this->assertSame('txt', $data['extension']);
        $this->assertSame(0, mb_strpos($data['path'], $dir));
    }
}
