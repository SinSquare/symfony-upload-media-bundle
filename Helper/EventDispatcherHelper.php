<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Helper;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class EventDispatcherHelper
{
    private static $dispatcherArgNum;

    public static function dispatch(EventDispatcherInterface $dispatcher, Event $event, string $eventName)
    {
        if (null === self::$dispatcherArgNum) {
            $class = \get_class($dispatcher);
            $reflectionClass = new \ReflectionClass($class);
            $method = $reflectionClass->getMethod('dispatch');
            self::$dispatcherArgNum = $method->getNumberOfParameters();
        }

        if (1 === self::$dispatcherArgNum) {
            //new eventdispatcher
            $dispatcher->dispatch($event, $eventName);

            return;
        }

        $dispatcher->dispatch($eventName, $event);
    }
}
