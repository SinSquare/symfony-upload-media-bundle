<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Unit\DataClass;

use Symfony\Component\EventDispatcher\EventDispatcher;
use UploadMediaBundle\Controller\UploadMediaController;
use UploadMediaBundle\Tests\Unit\AbstractEventTest;

class UploadMediaTest extends AbstractEventTest
{
    public function testUploadFile()
    {
        $request = $this->createRequest();
        $file = $request->files->get(0);
        $dispatcher = new EventDispatcher();
        $dir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.sha1(uniqid('path', true).(string) microtime(true));
        mkdir($dir, 0777, true);

        $controller = new UploadMediaController();

        $this->assertSame(0, $this->countFilesInDir($dir));

        $data = $this->invokeMethod($controller, 'uploadFile', [$file, $request, $dispatcher, $dir]);
        $this->assertIsArray($data);

        $this->assertSame('testfile_0', $data['originalName']);
        $this->assertSame('text/plain', $data['mimeType']);
        $this->assertSame('txt', $data['extension']);
        $this->assertSame(0, mb_strpos($data['path'], $dir));
    }
}
