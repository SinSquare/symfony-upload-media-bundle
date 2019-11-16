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

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class GetUploadedFilesEvent extends Event
{
    protected $request;
    protected $uploadedFiles;

    public function __construct(Request $request)
    {
        $this->uploadedFiles = [];
        $this->request = $request;

        //by default all uploaded files will be processed
        foreach ($request->files->all() as $key => $files) {
            if ($files instanceof UploadedFile) {
                $this->uploadedFiles[] = $files;
            }
        }
    }

    public function setUploadedFiles(array $files)
    {
        $this->uploadedFiles = $files;
        foreach ($this->uploadedFiles as $file) {
            if (!$file instanceof UploadedFile) {
                throw new \LogicException(sprintf('All files has to be of class "%s", found instance of class "%s".', UploadedFile::class, \get_class($file)));
            }
        }
    }

    public function addUploadedFile(UploadedFile $file)
    {
        foreach ($this->uploadedFiles as $f) {
            if ($f->getRealPath() === $file->getRealPath()) {
                //already added
                return;
            }
        }
        $this->uploadedFiles[] = $file;
    }

    public function removeUploadedFile(UploadedFile $file)
    {
        foreach ($this->uploadedFiles as $k => $f) {
            if ($f->getRealPath() === $file->getRealPath()) {
                unset($this->uploadedFiles[$k]);

                return;
            }
        }
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getUploadedFiles(): array
    {
        return array_values($this->uploadedFiles);
    }
}
