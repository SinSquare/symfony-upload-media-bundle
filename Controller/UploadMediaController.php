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
use UploadMediaBundle\Event\GetChunkDataEvent;
use UploadMediaBundle\Event\GetFileDataEvent;
use UploadMediaBundle\Event\GetResponseEvent;
use UploadMediaBundle\Event\GetUploadedFilesEvent;
use UploadMediaBundle\Event\KeepfileEvent;
use UploadMediaBundle\Event\UploadedEvent;
use UploadMediaBundle\Event\UploadMediaEvents;
use UploadMediaBundle\Helper\EventDispatcherHelper;

class UploadMediaController extends AbstractController
{
    /**
     * Upload action. It decides if the upload is single file or chunked.
     */
    public function uploadAction(Request $request, EventDispatcherInterface $dispatcher, string $uploadedMediaDirectory): Response
    {
        $uploadedMediaDirectory = $this->ensureUploadDirectory($uploadedMediaDirectory);

        list($from, $to, $size) = $this->getContentRange($request);

        $getFilesEvent = new GetUploadedFilesEvent($request);
        EventDispatcherHelper::dispatch($dispatcher, $getFilesEvent, UploadMediaEvents::GETFILES);
        $files = $getFilesEvent->getUploadedFiles();

        if (0 === \count($files)) {
            return new JsonResponse(['data' => []]);
        }

        if ((null === $from && null === $to && null === $size) || (0 === $from && $to >= $size - 1)) {
            //non-chunked upload
            $data = [];
            foreach ($files as $file) {
                $d = $this->uploadFile($file, $request, $dispatcher, $uploadedMediaDirectory);
                if (\is_array($d)) {
                    $data[] = $d;
                }
            }

            $response = new JsonResponse(['data' => $data]);
            $chunked = false;
        } else {
            if (\count($files) > 1) {
                throw new \RuntimeException(sprintf('Only 1 file is supported for chunked-upload, but the request contains %d files', \count($files)));
            }

            $data = $this->uploadChunk(array_pop($files), $request, $dispatcher, $uploadedMediaDirectory);

            $response = new JsonResponse(['data' => [$data]]);
            $chunked = true;
        }

        $responseEvent = new GetResponseEvent($files, $request, $response, $chunked);
        EventDispatcherHelper::dispatch($dispatcher, $responseEvent, UploadMediaEvents::RESPONSE);

        return $responseEvent->getResponse();
    }

    /**
     * Manage the upload of non-chunked file(s).
     */
    protected function uploadFile(UploadedFile $file, Request $request, EventDispatcherInterface $dispatcher, string $uploadedMediaDirectory): ?array
    {
        //decide if the uploaded file should be kept
        $keepfileEvent = new KeepfileEvent($file, $request);
        EventDispatcherHelper::dispatch($dispatcher, $keepfileEvent, UploadMediaEvents::KEEPFILE);

        if (false === $keepfileEvent->getKeepFile()) {
            return null;
        }

        $data = $keepfileEvent->getData();

        //get basic data
        $originalName = $file->getClientOriginalName();
        $ext = $file->guessClientExtension();

        //modify/move the file
        $uploadEvent = new UploadedEvent($file, $request);
        EventDispatcherHelper::dispatch($dispatcher, $uploadEvent, UploadMediaEvents::UPLOAD);
        $file = $uploadEvent->getUploadedFile();

        if (!$uploadEvent->getIsMoved()) {
            //if the file was not moved, move to uploadedMediaDirectory with a unique name
            //othervise it would be deleted
            $newName = $this->getUniqueName($uploadedMediaDirectory, $originalName, $ext);
            $file = $file->move($uploadedMediaDirectory, $newName);
        }

        $data['path'] = $file->getPathname();

        $dataEvent = new GetFileDataEvent($file, $request, $data);
        EventDispatcherHelper::dispatch($dispatcher, $dataEvent, UploadMediaEvents::FILEDATA);

        return $dataEvent->getData();
    }

    /**
     * Manage the upload of a chunked file upload
     * Also it merges the parts if all the parts are uploaded.
     */
    protected function uploadChunk(UploadedFile $file, Request $request, EventDispatcherInterface $dispatcher, string $uploadedMediaDirectory): ?array
    {
        list($from, $to, $size) = $this->getContentRange($request);
        $isLast = $to >= $size - 1;

        //decide if the uploaded file should be kept
        $keepfileEvent = new KeepfileEvent($file, $request);
        EventDispatcherHelper::dispatch($dispatcher, $keepfileEvent, UploadMediaEvents::KEEPFILE);

        if (false === $keepfileEvent->getKeepFile()) {
            return null;
        }

        $data = $keepfileEvent->getData();

        $originalName = $file->getClientOriginalName();
        $ext = $file->guessClientExtension();

        $chunkBaseName = $this->getMultipartUniqueName($uploadedMediaDirectory, $originalName);
        $chunkName = sprintf('%s_%d_%d_%d', $chunkBaseName, $from, $to, $size);

        $file = $file->move($uploadedMediaDirectory, $chunkName);

        if ($isLast) {
            $newName = $this->getUniqueName($uploadedMediaDirectory, $originalName, $ext);
            $file = $this->mergeChunks($uploadedMediaDirectory, $chunkBaseName, $newName, $size);

            $uploadEvent = new UploadedEvent($file, $request);
            EventDispatcherHelper::dispatch($dispatcher, $uploadEvent, UploadMediaEvents::UPLOAD);
            $file = $uploadEvent->getUploadedFile();

            $data['path'] = $file->getPathname();

            $dataEvent = new GetFileDataEvent($file, $request, $data);
            EventDispatcherHelper::dispatch($dispatcher, $dataEvent, UploadMediaEvents::FILEDATA);

            return $dataEvent->getData();
        }

        $dataEvent = new GetChunkDataEvent($file, $request, $data);
        EventDispatcherHelper::dispatch($dispatcher, $dataEvent, UploadMediaEvents::CHUNKDATA);

        return $dataEvent->getData();
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
    protected function getUniqueName(string $uploadedMediaDirectory, string $originalName, string $ext = null)
    {
        $cnt = 0;
        $additionalStr = '';
        do {
            ++$cnt;
            $newName = sha1($originalName.$additionalStr);
            if (!empty($ext)) {
                $newName .= '.'.$ext;
            }

            $path = sprintf('%s%s%s', $uploadedMediaDirectory, \DIRECTORY_SEPARATOR, $newName);
            if (!file_exists($path)) {
                return $newName;
            }
            $additionalStr .= uniqid(microtime(true));
        } while ($cnt <= 3);

        // @codeCoverageIgnoreStart
        throw new \RuntimeException('Could not create unique name for file');
        // @codeCoverageIgnoreEnd
    }

    protected function getContentRange(Request $request): ?array
    {
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

            if ($from < 0 || $to < $from || $to >= $size || $size < 1) {
                throw new \RuntimeException(sprintf("Content-Range header with value '%s' is invalid", $rangeInfo));
            }

            return [$from, $to, $size];
        }

        return null;
    }

    protected function ensureUploadDirectory(string $uploadedMediaDirectory): string
    {
        $uploadedMediaDirectory = rtrim($uploadedMediaDirectory, '\\/');

        // @codeCoverageIgnoreStart
        if (!file_exists($uploadedMediaDirectory)) {
            if (!mkdir($uploadedMediaDirectory, 0777, true)) {
                throw new \RuntimeException(sprintf("Could not create upload directory '%s'", $uploadedMediaDirectory));
            }
        }

        if (!is_dir($uploadedMediaDirectory) || !is_writable($uploadedMediaDirectory)) {
            throw new \RuntimeException(sprintf("The upload path '%s' exists, but is not a directory or is not writable", $uploadedMediaDirectory));
        }
        // @codeCoverageIgnoreEnd

        return $uploadedMediaDirectory;
    }
}
