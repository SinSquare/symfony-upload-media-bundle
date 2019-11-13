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

final class UploadMediaEvents
{
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
     * This event allows you to move, modify or delete the uploaded file.
     *
     * @Event("UploadMediaBundle\Events\UploadedEvent")
     */
    const UPLOAD = 'uploadmedia.upload';

    /**
     * TODO.
     */
    const RESPONSE = 'uploadmedia.response';
}
