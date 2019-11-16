<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Event;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

if (!class_exists('Symfony\Contracts\EventDispatcher\Event')) {
    @class_alias('Symfony\Contracts\EventDispatcher\Event', 'Symfony\Component\EventDispatcher\Event');
}

class GetFileDataEvent extends Event
{
    protected $request;
    protected $uploadedFile;
    protected $data;

    public function __construct(File $uploadedFile, Request $request, array $data)
    {
        $this->uploadedFile = $uploadedFile;
        $this->request = $request;
        $this->data = $data;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getUploadedFile(): File
    {
        return $this->uploadedFile;
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
