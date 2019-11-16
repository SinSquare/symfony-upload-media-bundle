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
use Symfony\Component\HttpFoundation\JsonResponse;
use UploadMediaBundle\Controller\UploadMediaController;
use UploadMediaBundle\Tests\Unit\AbstractEventTest;

class UploadActionTest extends AbstractEventTest
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

        $response = $this->invokeMethod($controller, 'uploadAction', [$request, $dispatcher, $dir]);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
