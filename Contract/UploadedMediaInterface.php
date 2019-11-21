<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Contract;

interface UploadedMediaInterface
{
    public function getId();

    public function getIsNew(): bool;

    public function setIsNew(bool $isNew): self;

    public static function createFromArray(array $arr): self;

    public function toArray(): array;
}
