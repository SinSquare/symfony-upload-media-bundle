<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

class UploadedEvent extends Event
{
    protected $isMoved;
    protected $uploadedFile;

    public function __construct(File $uploadedFile, Request $request)
    {
        $this->isMoved = false;
        $this->uploadedFile = $uploadedFile;
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getUploadedFile(): File
    {
        return $this->uploadedFile;
    }

    public function move($directory, $name = null): File
    {
        $newFile = $this->uploadedFile->move($directory, $name);
        $this->uploadedFile = $newFile;
        $this->isMoved = true;

        return $newFile;
    }

    public function getIsMoved(): bool
    {
        return $this->isMoved;
    }
}
