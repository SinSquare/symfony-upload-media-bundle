<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Functional\Controller;

use UploadMediaBundle\Tests\Functional\BaseTestCase;

class FormRenderingTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->kernel->boot();
    }

    public function testSingleForm()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/single-form');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        //check form rendering
        $container = $crawler->filter('div.upload_media_container');
        $this->assertCount(1, $container);
        $container = $container->eq(0);

        //upload element
        $input = $container->filter('input.fileupload');
        $this->assertCount(1, $input);
        $this->assertSame('file', $input->getNode(0)->attributes->getNamedItem('type')->value);
        $this->assertNull($input->getNode(0)->attributes->getNamedItem('name'));
        $this->assertNull($input->getNode(0)->attributes->getNamedItem('multiple'));
        $this->assertSame('/media/upload', $input->getNode(0)->attributes->getNamedItem('data-url')->value);

        //hidden element
        $input = $container->filter('input.upload_result');
        $this->assertCount(1, $input);
        $this->assertSame('hidden', $input->getNode(0)->attributes->getNamedItem('type')->value);
        $this->assertNull($input->getNode(0)->attributes->getNamedItem('multiple'));
        $this->assertFalse(mb_strpos($input->getNode(0)->attributes->getNamedItem('name')->value, '[]'));
        $this->assertNull($input->getNode(0)->attributes->getNamedItem('value'));

        //submit
        $form = $crawler->selectButton('form_save_button')->form();

        $client->submit($form);
        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();

        $container = $crawler->filter('div.upload_media_container');
        $container = $container->eq(0);
        $input = $container->filter('input.upload_result');

        $this->assertNull($input->getNode(0)->attributes->getNamedItem('value'));
    }

    public function testMultipleForm()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/multiple-form');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        //check form rendering
        $container = $crawler->filter('div.upload_media_container');
        $this->assertCount(1, $container);
        $container = $container->eq(0);

        //hidden element
        $input = $container->filter('input.upload_result');
        $this->assertCount(1, $input);
        $this->assertSame('hidden', $input->getNode(0)->attributes->getNamedItem('type')->value);
        $this->assertNotNull($input->getNode(0)->attributes->getNamedItem('multiple'));
        $this->assertFalse(mb_strpos($input->getNode(0)->attributes->getNamedItem('name')->value, '[]'));
        $this->assertNull($input->getNode(0)->attributes->getNamedItem('value'));

        //upload element
        $input = $container->filter('input.fileupload');
        $this->assertCount(1, $input);
        $this->assertSame('file', $input->getNode(0)->attributes->getNamedItem('type')->value);
        $this->assertNull($input->getNode(0)->attributes->getNamedItem('name'));
        $this->assertNotNull($input->getNode(0)->attributes->getNamedItem('multiple'));
        $this->assertSame('/media/upload', $input->getNode(0)->attributes->getNamedItem('data-url')->value);

        //submit
        $form = $crawler->selectButton('form_save_button')->form();

        $client->submit($form);
        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();

        $container = $crawler->filter('div.upload_media_container');
        $container = $container->eq(0);
        $input = $container->filter('input.upload_result');

        $this->assertNull($input->getNode(0)->attributes->getNamedItem('value'));
    }

    public function testNotEmptySingleForm()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/not-empty-single-form');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $container = $crawler->filter('div.upload_media_container');
        $container = $container->eq(0);
        $input = $container->filter('input.upload_result');

        $json = $input->getNode(0)->attributes->getNamedItem('value')->value;
        $arr = json_decode($json, true);
        $this->assertIsArray($arr);

        $this->assertCount(1, $arr);

        $arr = $arr[0];

        $this->assertStringContainsStringIgnoringCase('resources/testfile1.txt', $arr['path']);
        $this->assertSame('randomName.txt', $arr['originalName']);
        $this->assertSame('abc', $arr['mimeType']);

        //submit
        $form = $crawler->selectButton('form_save_button')->form();

        $client->submit($form);
        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();

        $container = $crawler->filter('div.upload_media_container');
        $container = $container->eq(0);
        $input = $container->filter('input.upload_result');

        $newJson = $input->getNode(0)->attributes->getNamedItem('value')->value;

        $this->assertSame($json, $newJson);
    }

    public function testNotEmptyMultipleForm()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/not-empty-multiple-form');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $container = $crawler->filter('div.upload_media_container');
        $container = $container->eq(0);
        $input = $container->filter('input.upload_result');

        $json = $input->getNode(0)->attributes->getNamedItem('value')->value;
        $arr = json_decode($json, true);
        $this->assertIsArray($arr);

        $this->assertCount(2, $arr);

        $this->assertStringContainsStringIgnoringCase('resources/testfile1.txt', $arr[0]['path']);
        $this->assertSame('randomName.txt', $arr[0]['originalName']);
        $this->assertSame('abc', $arr[0]['mimeType']);

        $this->assertStringContainsStringIgnoringCase('resources/testfile1.txt', $arr[1]['path']);
        $this->assertSame('randomName2.txt', $arr[1]['originalName']);
        $this->assertNull($arr[1]['mimeType']);

        //submit
        $form = $crawler->selectButton('form_save_button')->form();

        $client->submit($form);
        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();

        $container = $crawler->filter('div.upload_media_container');
        $container = $container->eq(0);
        $input = $container->filter('input.upload_result');

        $newJson = $input->getNode(0)->attributes->getNamedItem('value')->value;

        $this->assertSame($json, $newJson);
    }
}
