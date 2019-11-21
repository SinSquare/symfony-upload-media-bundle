<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Event;

final class UploadMediaEvents
{
    /**
     * The GETFILES event occurs at the beginning of the upload process.
     *
     * This event is for extracting the upload files from the request.
     * By default it extracts all files.
     *
     * @Event("UploadMediaBundle\Events\GetUploadedFilesEvent")
     */
    const GETFILES = 'uploadmedia.getfiles';

    /**
     * The KEEPFILE event occurs before the uploaded file is moved.
     *
     * This event allows you to decide if the file should be moved(kept) or not.
     * If it is not moved, the file will be deleted after the script executed.
     *
     * @Event("UploadMediaBundle\Events\KeepfileEvent")
     */
    const KEEPFILE = 'uploadmedia.keepfile';

    /**
     * The UPLOAD event occurs after the file is uploaded.
     *
     * This event allows you to move, modify the uploaded file.
     *
     * @Event("UploadMediaBundle\Events\UploadedEvent")
     */
    const UPLOAD = 'uploadmedia.upload';

    /**
     * The CHUNKDATA event occurs after the chunk is uploaded, and before the response is created.
     *
     * This event allows you to modify the data that will be sent back in an array form.
     *
     * @Event("UploadMediaBundle\Events\GetChunkDataEvent")
     */
    const CHUNKDATA = 'uploadmedia.chunkdata';

    /**
     * The FILEDATA event occurs after the file is moved, and before the response is created.
     *
     * This event allows you to modify the data that will be sent back in an array form.
     *
     * @Event("UploadMediaBundle\Events\GetFileDataEvent")
     */
    const FILEDATA = 'uploadmedia.filedata';

    /**
     * The RESPONSE event occurs before the response is sent back.
     *
     * This event allows you modify the response.
     *
     * @Event("UploadMediaBundle\Events\GetResponseEvent")
     */
    const RESPONSE = 'uploadmedia.response';
}
