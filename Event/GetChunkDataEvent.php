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

class GetChunkDataEvent extends GetFileDataEvent
{
    public function __construct(File $uploadedFile, Request $request, array $data)
    {
        $data['isChunk'] = true;
        parent::__construct($uploadedFile, $request, $data);
    }
}
