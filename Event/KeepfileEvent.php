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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class KeepfileEvent extends Event
{
    protected $request;
    protected $uploadedFile;
    protected $keepFile;
    protected $data;

    public function __construct(UploadedFile $uploadedFile, Request $request)
    {
        $this->uploadedFile = $uploadedFile;
        $this->request = $request;
        $this->data = [
            'originalName' => $uploadedFile->getClientOriginalName(),
            'mimeType' => $uploadedFile->getClientMimeType(),
            'extension' => $uploadedFile->guessClientExtension(),
        ];
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getUploadedFile(): UploadedFile
    {
        return $this->uploadedFile;
    }

    public function getKeepFile(): ?bool
    {
        return $this->keepFile;
    }

    public function setKeepFile(bool $keepFile)
    {
        $this->keepFile = $keepFile;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }
}
