<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use UploadMediaBundle\Event\GetResponseEvent;

class AddFilesizeListener
{
    public function addFilesize(GetResponseEvent $event)
    {
        $response = $event->getResponse();
        if (!$response instanceof JsonResponse) {
            return;
        }

        $size = $event->getUploadedfile()->getSize();
        $sizeHuman = $this->formatBytes($size);

        $content = $event->getResponse()->getContent();
        $contentArray = json_decode($content, true);
        if (!\is_array($contentArray)) {
            return;
        }

        $contentArray['sizeBytes'] = $size;
        $contentArray['sizeHuman'] = $sizeHuman;

        $response->setData($contentArray);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, \count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
