<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Tests\Functional\app\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UploadMediaBundle\DataClass\UploadedMedia;
use UploadMediaBundle\Tests\Functional\app\Form\UploadAdditionalDataType;
use UploadMediaBundle\Tests\Functional\app\Form\UploadMultipleType;
use UploadMediaBundle\Tests\Functional\app\Form\UploadSingleType;

class TestController extends Controller
{
    public function getSingleAction(Request $request)
    {
        $form = $this->createForm(
            UploadSingleType::class,
            null,
            [
                'action' => $this->generateUrl($request->attributes->get('_route')),
            ]
        );

        $r = $this->handleForm($request, $form);

        return $this->render(
            '@App/index.html.twig',
            [
                'form' => $form->createView(),
            ],
            $this->getResponseByForm($form)
        );
    }

    public function getMultipleAction(Request $request)
    {
        $form = $this->createForm(
            UploadMultipleType::class,
            null,
            [
                'action' => $this->generateUrl($request->attributes->get('_route')),
            ]
        );

        $this->handleForm($request, $form);

        return $this->render(
            '@App/index.html.twig',
            [
                'form' => $form->createView(),
            ],
            $this->getResponseByForm($form)
        );
    }

    public function getNotEmptySingleForm(Request $request)
    {
        $image = new UploadedMedia(__DIR__.'/../../../resources/testfile1.txt', 'randomName.txt', 'abc');

        $form = $this->createForm(
            UploadSingleType::class,
            ['image' => $image],
            [
                'action' => $this->generateUrl($request->attributes->get('_route')),
            ]
        );

        $this->handleForm($request, $form);

        return $this->render(
            '@App/index.html.twig',
            [
                'form' => $form->createView(),
            ],
            $this->getResponseByForm($form)
        );
    }

    public function getNotEmptyMultipleForm(Request $request)
    {
        $images = [
            new UploadedMedia(__DIR__.'/../../../resources/testfile1.txt', 'randomName.txt', 'abc'),
            new UploadedMedia(__DIR__.'/../../../resources/testfile1.txt', 'randomName2.txt'),
        ];

        $form = $this->createForm(
            UploadSingleType::class,
            ['image' => $images],
            [
                'action' => $this->generateUrl($request->attributes->get('_route')),
            ]
        );

        $this->handleForm($request, $form);

        return $this->render(
            '@App/index.html.twig',
            [
                'form' => $form->createView(),
            ],
            $this->getResponseByForm($form)
        );
    }

    public function getAdditionalDataStringForm(Request $request)
    {
        $form = $this->createForm(
            UploadAdditionalDataType::class,
            null,
            [
                'action' => $this->generateUrl($request->attributes->get('_route')),
                'additional_data' => 'string_data',
            ]
        );

        $this->handleForm($request, $form);

        return $this->render(
            '@App/index.html.twig',
            [
                'form' => $form->createView(),
            ],
            $this->getResponseByForm($form)
        );
    }

    public function getAdditionalDataArrayForm(Request $request)
    {
        $form = $this->createForm(
            UploadAdditionalDataType::class,
            null,
            [
                'action' => $this->generateUrl($request->attributes->get('_route')),
                'additional_data' => ['data' => 'abc'],
            ]
        );

        $this->handleForm($request, $form);

        return $this->render(
            '@App/index.html.twig',
            [
                'form' => $form->createView(),
            ],
            $this->getResponseByForm($form)
        );
    }

    public function getSingleDataAction(Request $request)
    {
        $form = $this->createForm(
            UploadSingleType::class,
            null,
            [
                'action' => $this->generateUrl($request->attributes->get('_route')),
            ]
        );

        $r = $this->handleForm($request, $form);
        if ($r) {
            if ($form->get('image')->getData() instanceof UploadedMedia) {
                $imageArr = $form->get('image')->getData()->toArray();
                $imageArr['new'] = $form->get('image')->getData()->getIsNew();
            } else {
                $imageArr = $form->get('image')->getData();
            }

            return new JsonResponse($imageArr);
        }

        return $this->render(
            '@App/index.html.twig',
            [
                'form' => $form->createView(),
            ],
            $this->getResponseByForm($form)
        );
    }

    private function handleForm(Request $request, FormInterface $form): bool
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            return true;
        }

        return false;
    }

    private function getResponseByForm(FormInterface $form): Response
    {
        $response = new Response();
        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(400);
        }

        return $response;
    }
}
