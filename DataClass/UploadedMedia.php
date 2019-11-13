<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\DataClass;

use Symfony\Component\HttpFoundation\File\File;
use UploadMediaBundle\Contract\UploadedMediaInterface;

class UploadedMedia extends File implements UploadedMediaInterface
{
    private $id;
    private $isNew;
    private $originalName;
    private $mimeType;

    public function __construct(string $path, string $originalName, string $mimeType = null)
    {
        parent::__construct($path);

        $this->id = sha1($this->getRealPath());
        $this->isNew = true;
        $this->originalName = $originalName;
        $this->mimeType = $mimeType;
    }

    public static function createFromArray(array $arr): UploadedMediaInterface
    {
        $instance = new self($arr['path'], $arr['originalName']);

        if (!empty($arr['id'])) {
            $instance->id = $arr['id'];
        }

        if (!empty($arr['mimeType'])) {
            $instance->mimeType = $arr['mimeType'];
        }

        return $instance;
    }

    public function toArray(): array
    {
        return [
            'path' => $this->getPathname(),
            'originalName' => $this->getOriginalName(),
            'mimeType' => $this->getMimeType(),
            'id' => $this->getId(),
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): UploadedMediaInterface
    {
        $this->isNew = $isNew;

        return $this;
    }

    public function getOriginalName()
    {
        return $this->originalName;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }
}
