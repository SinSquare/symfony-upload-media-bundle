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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class GetResponseEvent extends Event
{
    protected $request;
    protected $uploadedFile;
    protected $response;

    public function __construct(File $uploadedFile, Request $request, Response $response)
    {
        $this->uploadedFile = $uploadedFile;
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getUploadedFile(): File
    {
        return $this->uploadedFile;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
