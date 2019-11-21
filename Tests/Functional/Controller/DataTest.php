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

use UploadMediaBundle\DataClass\UploadedMedia;
use UploadMediaBundle\Tests\Functional\BaseTestCase;

class DataTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->kernel->boot();
    }

    public function testSingleForm()
    {
        $path = $this->createFile();
        $data = new UploadedMedia($path, 'testfile');
        $dataJson = json_encode([$data->toArray()]);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/get-single-data');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('form_save_button')->form();
        $form->setValues([
            'upload_single[image]' => $dataJson,
        ]);

        $client->submit($form);
        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();

        $contentArr = json_decode($content, true);
        $this->assertIsArray($contentArr);

        $this->assertTrue($contentArr['new']);
        unset($contentArr['new']);
        $this->assertSame($data->toArray(), $contentArr);
    }

    public function testSingleFormEmpty()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/get-single-data');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('form_save_button')->form();
        $form->setValues([
            'upload_single[image]' => '',
        ]);

        $client->submit($form);
        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $contentArr = json_decode($content, true);
        $this->assertIsArray($contentArr);
        $this->assertSame([], $contentArr);
    }

    public function testSingleFormNotJSON()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/get-single-data');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('form_save_button')->form();
        $form->setValues([
            'upload_single[image]' => 'sdsdfsdfsdfsdfsdfsdfsdf',
        ]);

        $client->submit($form);
        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $contentArr = json_decode($content, true);
        $this->assertIsArray($contentArr);
        $this->assertSame([], $contentArr);
    }
}
