<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UploadMediaBundle\DataClass\UploadedMedia;

class UploadedMediaTest extends TestCase
{
    public function testCreationWithoutMIME()
    {
        $assertMedia = function ($media) {
            $this->assertSame(__DIR__.'/../resources/testfile1.txt', $media->getPathname());
            $this->assertSame('randomName.txt', $media->getOriginalName());
            $this->assertNull($media->getMimeType());
            $this->assertNotNull($media->getId());
            $this->assertTrue($media->getIsNew());
        };

        $media = new UploadedMedia(__DIR__.'/../resources/testfile1.txt', 'randomName.txt');
        $assertMedia($media);

        $array = $media->toArray();
        $this->assertIsArray($array);
        $this->assertSame($media->getPathname(), $array['path']);
        $this->assertSame($media->getOriginalName(), $array['originalName']);
        $this->assertSame($media->getMimeType(), $array['mimeType']);
        $this->assertSame($media->getId(), $array['id']);

        $newMedia = UploadedMedia::createFromArray($array);
        $assertMedia($media);
    }

    public function testCreationWithMIME()
    {
        $assertMedia = function ($media) {
            $this->assertSame(__DIR__.'/../resources/testfile1.txt', $media->getPathname());
            $this->assertSame('randomName.txt', $media->getOriginalName());
            $this->assertSame($media->getMimeType(), 'mime/mime');
            $this->assertNotNull($media->getId());
            $this->assertTrue($media->getIsNew());
        };

        $media = new UploadedMedia(__DIR__.'/../resources/testfile1.txt', 'randomName.txt', 'mime/mime');
        $assertMedia($media);

        $array = $media->toArray();
        $this->assertIsArray($array);
        $this->assertSame($media->getPathname(), $array['path']);
        $this->assertSame($media->getOriginalName(), $array['originalName']);
        $this->assertSame($media->getMimeType(), $array['mimeType']);
        $this->assertSame($media->getId(), $array['id']);

        $newMedia = UploadedMedia::createFromArray($array);
        $assertMedia($media);
    }
}
