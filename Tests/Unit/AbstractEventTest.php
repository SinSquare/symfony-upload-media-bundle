<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractEventTest extends TestCase
{
    protected static $uploadedFileArgs;

    protected function createFile(int $length = 128)
    {
        $path = sys_get_temp_dir().\DIRECTORY_SEPARATOR.sha1(uniqid('path', true).(string) microtime(true));
        file_put_contents($path, random_bytes($length));

        return $path;
    }

    protected function createUploadedFileInstance(string $path, string $originalName, string $mimeType = null): UploadedFile
    {
        if (null === self::$uploadedFileArgs) {
            $reflectionClass = new \ReflectionClass(UploadedFile::class);
            $method = $reflectionClass->getMethod('__construct');
            self::$uploadedFileArgs = $method->getNumberOfParameters();
        }

        if (self::$uploadedFileArgs > 5) {
            //old
            $f = new UploadedFile(
                $path,
                $originalName,
                $mimeType,
                filesize($path),
                null,
                true
            );
        } else {
            //new
            $f = new UploadedFile(
                $path,
                $originalName,
                $mimeType,
                null,
                true
            );
        }

        return $f;
    }

    protected function createUploadedFile(int $length = 128, string $originalName = null, string $mimeType = 'text/plain'): UploadedFile
    {
        if (null === $originalName) {
            $originalName = sha1(uniqid('originalName').(string) microtime(true));
        }

        $path = $this->createFile($length);

        $reflectionClass = new \ReflectionClass(UploadedFile::class);
        $method = $reflectionClass->getMethod('__construct');
        $num = $method->getNumberOfParameters();

        return $this->createUploadedFileInstance($path, $originalName, $mimeType);
    }

    protected function createUploadedFileChunk(UploadedFile $file, int $from, int $to): UploadedFile
    {
        $dir = sys_get_temp_dir();
        $name = 'part_'.sha1(uniqid('part_', true));
        $path = $dir.\DIRECTORY_SEPARATOR.$name;

        $handle = fopen($file->getRealPath(), 'r');
        fseek($handle, $from);
        $content = fread($handle, $to - $from);
        fclose($handle);

        file_put_contents($path, $content);

        return $this->createUploadedFileInstance($path, $file->getClientOriginalName(), $file->getClientMimeType());
    }

    protected function createRequest(int $numberOfFiles = 1): Request
    {
        $request = new Request();
        for ($i = 0; $i < $numberOfFiles; ++$i) {
            $file = $this->createUploadedFile(128, 'testfile_'.$i);
            $request->files->set($i, $file);
        }

        return $request;
    }

    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(\get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    protected function countFilesInDir(string $directory): int
    {
        $files = scandir($directory);

        return \count($files) - 2;
    }
}
