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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

if (!class_exists('Symfony\Contracts\EventDispatcher\Event')) {
    @class_alias('Symfony\Contracts\EventDispatcher\Event', 'Symfony\Component\EventDispatcher\Event');
}

class GetResponseEvent extends Event
{
    protected $request;
    protected $uploadedFiles;
    protected $response;
    protected $chunked;

    public function __construct(array $uploadedFiles, Request $request, Response $response, bool $chunked = false)
    {
        $this->uploadedFiles = $uploadedFiles;
        $this->request = $request;
        $this->response = $response;
        $this->chunked = $chunked;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
        $this->stopPropagation();
    }

    public function getChunked(): bool
    {
        return $this->chunked;
    }
}
