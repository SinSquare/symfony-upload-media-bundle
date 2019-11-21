<?php

/*
 * This file is part of the UploadMediaBundle.
 *
 * (c) Abel Katona <katona.abel at gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UploadMediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UploadMediaBundle\Contract\UploadedMediaInterface;
use UploadMediaBundle\DataClass\UploadedMedia;

class UploadMediaType extends AbstractType implements DataTransformerInterface
{
    private $dataClass;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $emptyData = function (Options $options) {
            return $options['multiple'] ? [] : null;
        };

        $resolver->setDefaults([
            'data_class' => UploadedMedia::class,
            'accept' => 'image/*',
            'upload_path_name' => 'upload_media_ajax',
            'multiple' => false,
            'empty_data' => $emptyData,
            'compound' => false,
            'additiona_data' => null,
        ]);

        $resolver->setNormalizer('data_class', function (Options $options, $value) {
            if (!is_subclass_of($value, UploadedMediaInterface::class)) {
                throw new InvalidOptionsException(sprintf('The option "%s" with value %s is expected to be of type "%s".', 'data_class', $value, UploadedMediaInterface::class));
            }

            $this->dataClass = $value;
            $value = null;

            return $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['multiple']) {
            $view->vars['attr']['multiple'] = 'multiple';
        }

        if (\is_array($options['additiona_data'])) {
            $view->vars['additiona_data'] = json_encode($options['additiona_data']);
        } else {
            $view->vars['additiona_data'] = (string) $options['additiona_data'];
        }

        $view->vars['multiple'] = $options['multiple'];
        $view->vars['type'] = 'hidden';
        $view->vars['accept'] = $options['accept'];
        $view->vars['upload_path_name'] = $options['upload_path_name'];
        $view->vars['attr']['class'] = 'upload_result';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data === $options['empty_data']) {
                return;
            }

            $dataArr = json_decode($data, true);
            if (!\is_array($dataArr)) {
                $event->setData($options['empty_data']);

                return;
            }

            $newData = [];
            $class = $this->dataClass;
            foreach ($dataArr as $d) {
                $newData[] = \call_user_func_array([$class, 'createFromArray'], [$d]);
            }

            //resolve new status
            $existingIds = [];
            $oldData = $form->getData();

            if (!\is_array($oldData)) {
                $oldData = [$oldData];
            }

            foreach ($oldData as $d) {
                if ($d instanceof UploadedMediaInterface) {
                    $existingIds[] = $d->getId();
                }
            }

            foreach ($newData as $d) {
                if (\in_array($d->getId(), $existingIds, true)) {
                    $d->setIsNew(false);
                } else {
                    $d->setIsNew(true);
                }
            }

            //return last element if not multiple
            if (!$options['multiple']) {
                $newData = array_pop($newData);
            }

            $event->setData($newData);
        }, 128);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        if (empty($data)) {
            return null;
        }

        if (!\is_array($data)) {
            $data = [$data];
        }

        $newData = [];

        foreach ($data as $d) {
            if ($d instanceof UploadedMediaInterface) {
                $newData[] = $d->toArray();
            } else {
                // @codeCoverageIgnoreStart
                throw new \LogicException(sprintf('All files has to be of class "%s", found instance of class "%s".', $this->dataClass, \get_class($d)));
                // @codeCoverageIgnoreEnd
            }
        }

        return json_encode($newData);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        //SUBMIT event already transformed the data
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'upload_media';
    }
}
