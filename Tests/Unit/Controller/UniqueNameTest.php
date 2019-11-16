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

use UploadMediaBundle\Controller\UploadMediaController;
use UploadMediaBundle\Tests\Unit\AbstractEventTest;

class UniqueNameTest extends AbstractEventTest
{
    public function testNoFileNoExt()
    {
        $dir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.sha1(uniqid('path', true).(string) microtime(true));
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $originalName = 'testfile';

        $controller = new UploadMediaController();
        $name = $this->invokeMethod($controller, 'getUniqueName', [$dir, $originalName]);
        $this->assertSame(sha1($originalName), $name);
    }

    public function testNoFileExt()
    {
        $dir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.sha1(uniqid('path', true).(string) microtime(true));
        mkdir($dir, 0777, true);
        $this->assertSame(0, $this->countFilesInDir($dir));

        $originalName = 'testfile';
        $ext = 'txt';

        $controller = new UploadMediaController();
        $name = $this->invokeMethod($controller, 'getUniqueName', [$dir, $originalName, $ext]);
        $this->assertSame(sha1($originalName).'.'.$ext, $name);
    }

    public function testFileNoExt()
    {
        $originalName = 'testfile';

        $dir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.sha1(uniqid('path', true).(string) microtime(true));
        mkdir($dir, 0777, true);
        file_put_contents($dir.\DIRECTORY_SEPARATOR.sha1($originalName), 'awbcs');
        $this->assertSame(1, $this->countFilesInDir($dir));

        $controller = new UploadMediaController();
        $name = $this->invokeMethod($controller, 'getUniqueName', [$dir, $originalName]);
        $this->assertNotSame(sha1($originalName), $name);
    }

    public function testFileExt()
    {
        $originalName = 'testfile';
        $ext = 'txt';

        $dir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.sha1(uniqid('path', true).(string) microtime(true));
        mkdir($dir, 0777, true);
        file_put_contents($dir.\DIRECTORY_SEPARATOR.sha1($originalName).'.'.$ext, 'awbcs');
        $this->assertSame(1, $this->countFilesInDir($dir));

        $controller = new UploadMediaController();
        $name = $this->invokeMethod($controller, 'getUniqueName', [$dir, $originalName]);
        $this->assertNotSame(sha1($originalName).'.'.$ext, $name);
    }
}
