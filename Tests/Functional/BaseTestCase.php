<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Functional;

use Nyholm\BundleTest\AppKernel;
use Nyholm\BundleTest\BaseBundleTestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\TwigBundle\TwigBundle;
use UploadMediaBundle\UploadMediaBundle;

abstract class BaseTestCase extends BaseBundleTestCase
{
    /**
     * @var AppKernel
     */
    protected $kernel;

    protected function getBundleClass()
    {
        return FrameworkBundle::class;
    }

    protected function setUp(): void
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/app/config/default.yml');

        $kernel->addBundle(TwigBundle::class);
        $kernel->addBundle(UploadMediaBundle::class);

        $this->kernel = $kernel;

        parent::setUp();
    }

    /**
     * Creates a KernelBrowser.
     *
     * @param array $options An array of options to pass to the createKernel method
     * @param array $server  An array of server parameters
     *
     * @return KernelBrowser A KernelBrowser instance
     */
    protected function createClient(array $options = [], array $server = [])
    {
        try {
            $client = $this->kernel->getContainer()->get('test.client');
        } catch (ServiceNotFoundException $e) {
            if (class_exists(KernelBrowser::class)) {
                throw new \LogicException('You cannot create the client used in functional tests if the "framework.test" config is not set to true.');
            }
            throw new \LogicException('You cannot create the client used in functional tests if the BrowserKit component is not available. Try running "composer require symfony/browser-kit"');
        }

        $client->setServerParameters($server);

        return $client;
    }
}
