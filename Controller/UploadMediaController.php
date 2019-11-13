<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UploadMediaBundle\Event\GetResponseEvent;
use UploadMediaBundle\Event\KeepfileEvent;
use UploadMediaBundle\Event\UploadedEvent;
use UploadMediaBundle\Event\UploadMediaEvents;

class UploadMediaController extends AbstractController
{
    /**
     * Upload action. It decides if the upload is single file or chunked.
     */
    public function uploadAction(Request $request, EventDispatcherInterface $dispatcher, string $uploadedMediaDirectory): Response
    {
        $uploadedMediaDirectory = rtrim($uploadedMediaDirectory, '\\/');

        if (!file_exists($uploadedMediaDirectory)) {
            if (!mkdir($uploadedMediaDirectory, 0777, true)) {
                throw new \RuntimeException(sprintf("Could not create upload directory '%s'", $uploadedMediaDirectory));
            }
        }

        if (!is_dir($uploadedMediaDirectory) || !is_writable($uploadedMediaDirectory)) {
            throw new \RuntimeException(sprintf("The upload path '%s' exists, but is not a directory or is not writable", $uploadedMediaDirectory));
        }

        $from = null;
        $to = null;
        $size = null;

        if ($request->headers->has('Content-Range')) {
            $rangeInfo = $request->headers->get('Content-Range');
            preg_match(
                '/(?P<from>[0-9]+)-(?P<to>[0-9]+)\/(?P<size>[0-9]+)/',
                $rangeInfo,
                $matches
            );

            if (!isset($matches['from']) || !isset($matches['to']) || !isset($matches['size'])) {
                throw new \RuntimeException(sprintf("Content-Range header with value '%s' is invalid", $rangeInfo));
            }

            $from = (int) ($matches['from']);
            $to = (int) ($matches['to']);
            $size = (int) ($matches['size']);

            if ($from < 0 || $to < $from || $size < 1) {
                throw new \RuntimeException(sprintf("Content-Range header with value '%s' is invalid", $rangeInfo));
            }
        }

        if ((null === $from && null === $to && null === $size) || (0 === $from && $to >= $size - 1)) {
            return $this->uploadSingleFile($request, $dispatcher, $uploadedMediaDirectory);
        }

        return $this->uploadChunk($request, $dispatcher, $uploadedMediaDirectory);
    }

    /**
     * Manage the upload of a single-file upload.
     */
    protected function uploadSingleFile(Request $request, EventDispatcherInterface $dispatcher, string $uploadedMediaDirectory): Response
    {
        $data = [];

        $file = $this->getFirstFileFromRequest($request);

        $keepfileEvent = new KeepfileEvent($file, $request);
        $dispatcher->dispatch($keepfileEvent, UploadMediaEvents::KEEPFILE);

        if (false === $keepfileEvent->getKeepFile()) {
            return new JsonResponse(['data' => $data]);
        }

        $originalName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();
        $ext = $file->guessClientExtension();

        $newName = $this->getUniqueName($uploadedMediaDirectory, $originalName, $ext);

        $file = $file->move($uploadedMediaDirectory, $newName);

        $uploadEvent = new UploadedEvent($file, $request);
        $dispatcher->dispatch($uploadEvent, UploadMediaEvents::UPLOAD);

        $file = $uploadEvent->getUploadedFile();

        $d = [
            'path' => $file->getPathname(),
            'originalName' => $originalName,
            'mimeType' => $mime,
            'ext' => $ext,
        ];

        $response = new JsonResponse(['data' => $d]);
        $responseEvent = new GetResponseEvent($file, $request, $response);
        $dispatcher->dispatch($responseEvent, UploadMediaEvents::RESPONSE);

        return $responseEvent->getResponse();
    }

    /**
     * Manage the upload of a chunked file upload
     * Also it merges the parts if all the parts are uploaded.
     */
    protected function uploadChunk(Request $request, EventDispatcherInterface $dispatcher, string $uploadedMediaDirectory): Response
    {
        preg_match(
            '/(?P<from>[0-9]+)-(?P<to>[0-9]+)\/(?P<size>[0-9]+)/',
            $request->headers->get('Content-Range'),
            $matches
        );

        $data = [];

        $from = (int) ($matches['from']);
        $to = (int) ($matches['to']);
        $size = (int) ($matches['size']);

        $isLast = $to >= $size - 1;

        $file = $this->getFirstFileFromRequest($request);

        $keepfileEvent = new KeepfileEvent($file, $request);
        $dispatcher->dispatch($keepfileEvent, UploadMediaEvents::KEEPFILE);

        if (false === $keepfileEvent->getKeepFile()) {
            return new JsonResponse(['data' => $data]);
        }

        $originalName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();
        $ext = $file->guessClientExtension();

        $chunkBaseName = $this->getMultipartUniqueName($uploadedMediaDirectory, $originalName);
        $chunkName = sprintf('%s_%d_%d_%d', $chunkBaseName, $from, $to, $size);

        $file = $file->move($uploadedMediaDirectory, $chunkName);

        if ($isLast) {
            $newName = $this->getUniqueName($uploadedMediaDirectory, $originalName, $ext);
            $file = $this->mergeChunks($uploadedMediaDirectory, $chunkBaseName, $newName, $size);

            $uploadEvent = new UploadedEvent($file, $request);
            $dispatcher->dispatch($uploadEvent, UploadMediaEvents::UPLOAD);

            $file = $uploadEvent->getUploadedFile();

            $d = [
                'path' => $file->getPathname(),
                'originalName' => $originalName,
                'mimeType' => $mime,
                'ext' => $ext,
            ];

            $response = new JsonResponse(['data' => $d]);
            $responseEvent = new GetResponseEvent($file, $request, $response);
            $dispatcher->dispatch($responseEvent, UploadMediaEvents::RESPONSE);

            $response = $responseEvent->getResponse();
        } else {
            $response = new JsonResponse(['data' => []]);
        }

        return $response;
    }

    /**
     * Merge the uploaded chunks, create a new file and delete old chunks.
     */
    protected function mergeChunks(string $uploadedMediaDirectory, string $baseName, string $newName, int $fileSize): File
    {
        $regex = sprintf('/%s_[0-9]+_[0-9]+_'.$fileSize.'+/', $baseName);
        $finder = new Finder();
        $finder->in($uploadedMediaDirectory)->files()->name($regex);

        $sort = function (\SplFileInfo $a, \SplFileInfo $b) {
            preg_match('/.*_(?P<from>[0-9]+)_(?P<to>[0-9]+)_(?P<size>[0-9]+)/i', $a->getFilename(), $aM);
            preg_match('/.*_(?P<from>[0-9]+)_(?P<to>[0-9]+)_(?P<size>[0-9]+)/i', $b->getFilename(), $bM);

            return $aM['to'] < $bM['to'] ? -1 : 1;
        };

        $finder->sort($sort);

        //TODO check all chunk exists

        $newFilePath = $uploadedMediaDirectory.\DIRECTORY_SEPARATOR.$newName;
        $out = fopen($newFilePath, 'w');
        foreach ($finder as $file) {
            $in = fopen($file->getRealPath(), 'r');
            while ($line = fgets($in, 4096)) {
                fwrite($out, $line);
            }
            fclose($in);
        }
        fclose($out);
        clearstatcache();

        if (filesize($newFilePath) !== $fileSize) {
            throw new \RuntimeException(sprintf('Merged file has invalid length. Expected %d found %d', $fileSize, filesize($newFilePath)));
        }

        foreach ($finder as $file) {
            @unlink($file->getRealPath());
        }

        return new File($newFilePath);
    }

    /**
     * Get the UploadedFile from the request. Only 1 file is supported.
     */
    protected function getFirstFileFromRequest(Request $request): UploadedFile
    {
        $allFiles = [];

        foreach ($request->files->all() as $key => $files) {
            if (\is_array($files)) {
                foreach ($files as $file) {
                    if ($file instanceof UploadedFile) {
                        $allFiles[] = $file;
                    }
                }
            } elseif ($files instanceof UploadedFile) {
                $allFiles[] = $files;
            }
        }

        if (\count($files) > 1) {
            throw new \RuntimeException('Request contains more than 1 file');
        } elseif (0 === \count($files)) {
            throw new \RuntimeException('Could not find UploadedFile in the request');
        }

        return array_pop($files);
    }

    /**
     * Get unique name for multipart upload. It will the same if the uploaded original name is the same.
     */
    protected function getMultipartUniqueName(string $uploadedMediaDirectory, string $originalName)
    {
        $newName = 'multipart_'.sha1($originalName);

        return $newName;
    }

    /**
     * Get unique name for single-file upload. It will generate new name if the filename exists.
     */
    protected function getUniqueName(string $uploadedMediaDirectory, string $originalName, ?string $ext)
    {
        $cnt = 0;
        do {
            ++$cnt;
            $newName = sha1(uniqid($originalName, true).(string) microtime(true));
            if (!empty($ext)) {
                $newName .= '.'.$ext;
            }

            $path = sprintf('%s/%s', $uploadedMediaDirectory, $newName);
            if (!file_exists($path)) {
                return $newName;
            }
        } while ($cnt <= 10);

        throw new \RuntimeException('Could not create unique name for file');
    }
}
