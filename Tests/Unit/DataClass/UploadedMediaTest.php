<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Unit\DataClass;

use UploadMediaBundle\DataClass\UploadedMedia;
use UploadMediaBundle\Tests\Unit\AbstractTest;

class UploadedMediaTest extends AbstractTest
{
    private function assertMedia(UploadedMedia $media, string $path, string $originalName, string $mime = null)
    {
        $this->assertSame($path, $media->getPathname());
        $this->assertSame($originalName, $media->getOriginalName());
        $this->assertSame($mime, $media->getMimeType());
        $this->assertNotNull($media->getId());
        $this->assertTrue($media->getIsNew());

        $array = $media->toArray();
        $this->assertIsArray($array);
        $this->assertSame($media->getPathname(), $array['path']);
        $this->assertSame($media->getOriginalName(), $array['originalName']);
        $this->assertSame($media->getMimeType(), $array['mimeType']);
        $this->assertSame($media->getId(), $array['id']);

        $newMedia = UploadedMedia::createFromArray($array);
        $this->assertSame($media->getPathname(), $newMedia->getPathname());
        $this->assertSame($media->getOriginalName(), $newMedia->getOriginalName());
        $this->assertSame($media->getMimeType(), $newMedia->getMimeType());
        $this->assertSame($media->getId(), $newMedia->getId());
        $this->assertSame($media->getIsNew(), $newMedia->getIsNew());
    }

    public function testCreationWithoutMIME()
    {
        $name = sha1(uniqid('originalName').(string) microtime(true));
        $path = $this->createFile();
        $media = new UploadedMedia($path, $name);

        $this->assertMedia($media, $path, $name);
    }

    public function testCreationWithMIME()
    {
        $name = sha1(uniqid('originalName').(string) microtime(true));
        $path = $this->createFile();
        $media = new UploadedMedia($path, $name, 'mime/mime');

        $this->assertMedia($media, $path, $name, 'mime/mime');
    }

    public function testNew()
    {
        $name = sha1(uniqid('originalName').(string) microtime(true));
        $path = $this->createFile();
        $media = new UploadedMedia($path, $name, 'mime/mime');

        $this->assertTrue($media->getIsNew());
        $media->setIsNew(false);
        $this->assertFalse($media->getIsNew());
    }
}
