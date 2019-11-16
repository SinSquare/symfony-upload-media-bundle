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

use Symfony\Component\HttpFoundation\Request;
use UploadMediaBundle\Controller\UploadMediaController;
use UploadMediaBundle\Tests\Unit\AbstractEventTest;

class ContentRangeTest extends AbstractEventTest
{
    public function testContentRangeOK()
    {
        $request = new Request();
        $request->headers->set('Content-Range', '0-100/200');

        $controller = new UploadMediaController();

        list($from, $to, $size) = $this->invokeMethod($controller, 'getContentRange', [$request]);

        $this->assertSame(0, $from);
        $this->assertSame(100, $to);
        $this->assertSame(200, $size);
    }

    public function testContentRangeInvalidFormat()
    {
        $this->expectException('\RuntimeException');

        $request = new Request();
        $request->headers->set('Content-Range', 'abcd');

        $controller = new UploadMediaController();

        list($from, $to, $size) = $this->invokeMethod($controller, 'getContentRange', [$request]);
    }

    public function testContentRangeInvalidData()
    {
        $this->expectException('\RuntimeException');

        $request = new Request();
        $request->headers->set('Content-Range', '0-300/200');

        $controller = new UploadMediaController();

        list($from, $to, $size) = $this->invokeMethod($controller, 'getContentRange', [$request]);
    }

    public function testNoContentRange()
    {
        $request = new Request();

        $controller = new UploadMediaController();

        list($from, $to, $size) = $this->invokeMethod($controller, 'getContentRange', [$request]);

        $this->assertNull($from);
        $this->assertNull($to);
        $this->assertNull($size);
    }
}
